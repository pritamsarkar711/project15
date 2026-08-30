<?php

namespace App\Support;

/**
 * SEO title / meta-description finalizers.
 *
 * Ahrefs flagged 13 pages with "Title too short" and 4 pages with "Meta
 * description too short". Root cause: policy/about/contact/category pages
 * fell back to tiny labels ("Disclaimer", "Blog · Huvanti.com", a 37-char
 * category blurb). Blog POSTS are excluded on purpose — the owner wants
 * post titles/meta descriptions rendered exactly as authored in the
 * dashboard.
 *
 * finalizeTitle() pads short NON-post titles with the site name (and the
 * tagline when still short) so every indexable page clears the ~30 char
 * audit threshold. finalizeDescription() tops short descriptions up with
 * the site description sentence and caps the result at a word boundary.
 */
class Seo
{
    /** Minimum length search audits expect from a page title. */
    public const MIN_TITLE = 30;

    /** Minimum length search audits expect from a meta description. */
    public const MIN_DESCRIPTION = 70;

    /** Hard cap for meta descriptions (Google truncates around 155-160). */
    public const MAX_DESCRIPTION = 158;

    public static function finalizeTitle(?string $title, bool $isPost = false): string
    {
        $title = trim((string) $title);
        $site = setting('site_name', 'huvanti.com');

        if ($title === '') {
            $title = $site;
        }

        if ($isPost) {
            // Posts render exactly what the author typed — owner requirement.
            return $title;
        }

        if (mb_strlen($title) < self::MIN_TITLE) {
            $title = trim($title.' | '.$site);
        }
        if (mb_strlen($title) < self::MIN_TITLE) {
            $tagline = trim((string) setting('site_tagline', ''));
            if ($tagline !== '') {
                $title = trim($title.' — '.$tagline);
            }
        }

        return $title;
    }

    public static function finalizeDescription(?string $description, bool $isPost = false): string
    {
        $description = trim(strip_tags((string) $description));

        // Posts show the dashboard-set meta description verbatim (owner
        // requirement from the SEO round: "search engines must show the
        // meta description I set").
        if ($isPost && $description !== '') {
            return $description;
        }

        $site = trim((string) setting('site_description', 'Huvanti is a multi niche blog covering technology, health, finance, travel, lifestyle and education.'));

        if ($description === '') {
            $description = $site;
        }

        if (mb_strlen($description) < self::MIN_DESCRIPTION) {
            // Join as a proper second sentence, not a run-on string.
            if (!preg_match('/[.!?…」』]$/u', $description)) {
                $description .= '.';
            }
            $description = trim($description.' '.$site);
        }

        if (mb_strlen($description) > self::MAX_DESCRIPTION) {
            $cropped = wordwrap($description, self::MAX_DESCRIPTION, "\n", false);
            $description = trim(substr($cropped, 0, (int) strpos($cropped."\n", "\n")));
            // Always end clean, never mid-word or with a dangling comma.
            $description = rtrim($description, " \t\n\r\0\x0B,;:-");
        }

        return $description;
    }
}
