<?php

namespace App\Services;

/**
 * RankMath-style on-page SEO analyzer (server side).
 *
 * The browser shows the SAME checks live while writing (public/js/seo-analyzer.js
 * mirrors this logic 1:1). The server-side copy exists so the score can be
 * persisted per post (posts.seo_score) and shown as badges in list views
 * without trusting anything from the client.
 *
 * Scoring model (100 points, mirrors RankMath's weighting):
 *   Title / meta block ................ 34
 *   Focus keyword placement ........... 30
 *   Content depth & keyword density ... 22
 *   Links & media ..................... 14
 * A missing focus keyword caps the displayed score at 24 (checks still run
 * so the author sees WHAT to fix before setting a keyword).
 */
class SeoAnalyzer
{
    public function analyze(?string $title, ?string $metaTitle, ?string $metaDescription,
                            ?string $focusKeyword, ?string $slug, ?string $contentHtml): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $contentHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
        $keyword = mb_strtolower(trim((string) $focusKeyword));
        $titleLower = mb_strtolower((string) $title);
        $metaTitleLower = mb_strtolower((string) $metaTitle);
        $metaDescLower = mb_strtolower((string) $metaDescription);
        $slugLower = mb_strtolower(str_replace(['-', '_', '.'], ' ', (string) $slug));
        $metaTitleLen = mb_strlen(trim((string) $metaTitle));
        $metaDescLen = mb_strlen(trim((string) $metaDescription));

        // Keyword density across plain text (percent).
        $density = 0.0;
        if ($keyword !== '' && $words > 0) {
            $kwWords = preg_split('/\s+/u', $keyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if (count($kwWords) === 1) {
                $count = preg_match_all('/(?<![^\W_])'.preg_quote($kwWords[0], '/').'(?! [^\W_])/ui', $text);
            } else {
                $count = substr_count(mb_strtolower($text), $keyword);
            }
            $density = round(($count * max(1, count($kwWords)) / $words) * 100, 2);
        }

        $imgTotal = 0;
        $imgNoAlt = 0;
        if (preg_match_all('/<img[^>]*>/i', (string) $contentHtml, $imgs)) {
            $imgTotal = count($imgs[0]);
            foreach ($imgs[0] as $tag) {
                if (!preg_match('/\balt\s*=\s*["\'][^"\']+["\']/i', $tag)) $imgNoAlt++;
            }
        }
        $internalLinks = 0;
        $externalLinks = 0;
        if (preg_match_all('/<a[^>]+href\s*=\s*["\']([^"\']+)["\']/i', (string) $contentHtml, $links)) {
            $host = null;
            try { $host = parse_url(config('app.url'), PHP_URL_HOST); } catch (\Throwable $e) {}
            foreach ($links[1] as $href) {
                $h = parse_url($href, PHP_URL_HOST);
                if ($h === null) { if (!preg_match('/^(#|mailto:|tel:)/i', $href)) $internalLinks++; }
                elseif ($host && strcasecmp($h, $host) === 0) $internalLinks++;
                else $externalLinks++;
            }
        }
        preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/is', (string) $contentHtml, $heads);
        $headings = array_map(fn ($h) => mb_strtolower(trim(html_entity_decode(strip_tags($h), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), $heads[1]);
        $firstChunk = mb_substr(mb_strtolower($text), 0, (int) max(120, mb_strlen($text) * 0.12));

        // ---------------- checks ----------------
        $checks = [];
        // $ok: true/false = scored pass/fail, null = informational (not scored)
        $add = function (string $id, $ok, string $label, string $hintIfBad = '') use (&$checks) {
            $checks[] = [
                'id'    => $id,
                'ok'    => $ok === null ? null : (bool) $ok,
                'label' => $label,
                'hint'  => ($ok === null || $ok) ? '' : $hintIfBad,
            ];
        };

        // Title / meta (34)
        $add('kw_title', $keyword === '' ? false : str_contains($titleLower, $keyword), 'Focus keyword in post title', 'Add the focus keyword to the post title — it carries the most ranking weight.');
        $add('kw_metatitle', $keyword === '' ? false : str_contains($metaTitleLower, $keyword), 'Focus keyword in SEO title', 'Work the focus keyword into the SEO title (ideally near the start).');
        $add('kw_metadesc', $keyword === '' ? false : str_contains($metaDescLower, $keyword), 'Focus keyword in meta description', 'Mention the focus keyword in the meta description so search engines bold it.');
        $add('kw_slug', $keyword === '' ? false : str_contains($slugLower, $keyword), 'Focus keyword in URL slug', 'Include the focus keyword in the URL slug (e.g. /blog/best-budget-phones).');
        $titleLen = mb_strlen(trim((string) $title));
        $add('title_len', $titleLen >= 30 && $titleLen <= 65, "Post title length is $titleLen characters (aim 30–65)", 'Post title should be 30–65 characters — long enough to be descriptive, short enough not to be cut off.');
        $add('metatitle_len', $metaTitleLen >= 30 && $metaTitleLen <= 60, "SEO title length is $metaTitleLen characters (aim 30–60)", 'SEO title should be 30–60 characters so it is not truncated on results pages.');
        $add('metadesc_len', $metaDescLen >= 120 && $metaDescLen <= 165, "Meta description length is $metaDescLen characters (aim 120–165)", 'Meta description should be 120–165 characters — Google rewrites shorter/longer ones.');

        // Keyword placement (30)
        $add('kw_first', $keyword === '' ? false : str_contains($firstChunk, $keyword), 'Focus keyword in the opening paragraph', 'Use the focus keyword within the first ~10% of the content.');
        $add('kw_density', $keyword === '' ? false : ($density >= 0.5 && $density <= 3.0), "Keyword density is {$density}% (aim 0.5–3%)", $density > 3.0 ? 'Keyword density is too high — it reads as keyword stuffing. Use the keyword fewer times or write more.' : 'Use the focus keyword a few more times naturally (0.5–3% of all words).');
        $add('kw_headings', $keyword === '' ? false : (bool) array_filter($heads[1] ?? [], fn ($h) => str_contains(mb_strtolower($h), $keyword)), 'Focus keyword in at least one subheading (H2/H3)', 'Add the focus keyword to one subheading (H2/H3) to strengthen topical relevance.');

        // Content depth (22 within this block + links/media below)
        $add('words', $words >= 600, "Content length is $words words (aim 600+)", 'Deepen the article — posts under 600 words rarely rank. Target 600–1500 words.');
        $add('headings_count', count($heads[1] ?? []) >= 2, count($heads[1] ?? []).' subheadings found (aim 2+)', 'Break the article up with at least two H2/H3 subheadings.');

        // Links & media (14)
        $add('ext_links', $externalLinks >= 1, $externalLinks.' external link(s) found', 'Add at least one outbound link to an authoritative source — it builds trust.');
        $add('int_links', $internalLinks >= 1, $internalLinks.' internal link(s) found', 'Link to at least one other page on your site to spread ranking power.');
        $add('images_alt', $imgTotal === 0 ? false : $imgNoAlt === 0, $imgTotal === 0 ? 'No images in content' : ($imgNoAlt === 0 ? "All $imgTotal image(s) have alt text" : "$imgNoAlt of $imgTotal image(s) missing alt text"), 'Add descriptive alt text to every image (and consider adding an image or two).');

        // ---------------- score ----------------
        $scored = array_filter($checks, fn ($c) => $c['ok'] !== null);
        $passed = count(array_filter($scored, fn ($c) => $c['ok'] === true));
        $score = $keyword === ''
            ? min(24, (int) round(count($scored) > 0 ? ($passed / count($scored)) * 24 : 0))
            : (int) round(($passed / max(1, count($scored))) * 100);

        return [
            'score'     => max(0, min(100, $score)),
            'words'     => $words,
            'density'   => $density,
            'hasKeyword'=> $keyword !== '',
            'checks'    => $checks,
        ];
    }
}
