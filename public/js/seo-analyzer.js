/*!
 * Huvanti SEO Analyzer — live RankMath-style on-page scoring for the post
 * editor. Mirrors app/Services/SeoAnalyzer.php (same checks, same weights)
 * so the number the author sees while typing matches the persisted score.
 *
 * Usage: give the editor form the data-seo-panel attribute container:
 *   <div id="seo-score-panel"></div>
 * and it auto-wires to fields named title / slug / meta_title /
 * meta_description / focus_keyword plus the #editor content textarea.
 */
(function () {
    'use strict';

    function textOf(html) {
        var div = document.createElement('div');
        div.innerHTML = html || '';
        return (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
    }

    function words(text) {
        return text ? text.split(/\s+/).filter(Boolean).length : 0;
    }

    function analyze(input) {
        var text = textOf(input.content);
        var wordCount = words(text);
        var kw = (input.focusKeyword || '').trim().toLowerCase();
        var checks = [];
        var add = function (ok, label, hint, neutral) {
            checks.push({ ok: neutral ? null : !!ok, label: label, hint: ok || neutral ? '' : hint });
        };

        var title = (input.title || '').trim();
        var metaTitle = (input.metaTitle || '').trim();
        var metaDesc = (input.metaDescription || '').trim();
        var slug = (input.slug || '').toLowerCase().replace(/[-_.]/g, ' ');
        var titleLower = title.toLowerCase();
        var metaTitleLower = metaTitle.toLowerCase();
        var metaDescLower = metaDesc.toLowerCase();

        // keyword density
        var density = 0;
        if (kw) {
            var kwWords = kw.split(/\s+/).filter(Boolean);
            var re = new RegExp(kwWords.map(function (w) { return w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }).join('\\s+'), 'g');
            var count = (text.toLowerCase().match(re) || []).length;
            density = wordCount ? Math.round((count * kwWords.length / wordCount) * 10000) / 100 : 0;
        }

        // headings + images + links from the HTML
        var container = document.createElement('div');
        container.innerHTML = input.content || '';
        var headings = Array.prototype.slice.call(container.querySelectorAll('h2,h3,h4'))
            .map(function (h) { return (h.textContent || '').toLowerCase(); });
        var imgs = container.querySelectorAll('img');
        var imgNoAlt = 0;
        Array.prototype.forEach.call(imgs, function (img) {
            if (!img.getAttribute('alt')) imgNoAlt++;
        });
        var internal = 0, external = 0;
        Array.prototype.forEach.call(container.querySelectorAll('a[href]'), function (a) {
            var href = a.getAttribute('href') || '';
            if (href.charAt(0) === '#') return;
            if (/^https?:\/\//i.test(href) && href.indexOf(location.host) === -1) external++;
            else internal++;
        });
        var firstChunk = text.toLowerCase().substring(0, Math.max(120, text.length * 0.12));

        // ----- checks (same wording as the server for a consistent UX) -----
        add(kw && titleLower.indexOf(kw) !== -1, 'Focus keyword in post title', 'Add the focus keyword to the post title — it carries the most ranking weight.');
        add(kw && metaTitleLower.indexOf(kw) !== -1, 'Focus keyword in SEO title', 'Work the focus keyword into the SEO title (ideally near the start).');
        add(kw && metaDescLower.indexOf(kw) !== -1, 'Focus keyword in meta description', 'Mention the focus keyword in the meta description so search engines bold it.');
        add(kw && slug.indexOf(kw) !== -1, 'Focus keyword in URL slug', 'Include the focus keyword in the URL slug (e.g. /blog/best-budget-phones).');
        add(title.length >= 30 && title.length <= 65, 'Post title length is ' + title.length + ' characters (aim 30–65)', 'Post title should be 30–65 characters — long enough to be descriptive, short enough not to be cut off.');
        add(metaTitle.length >= 30 && metaTitle.length <= 60, 'SEO title length is ' + metaTitle.length + ' characters (aim 30–60)', 'SEO title should be 30–60 characters so it is not truncated on results pages.');
        add(metaDesc.length >= 120 && metaDesc.length <= 165, 'Meta description length is ' + metaDesc.length + ' characters (aim 120–165)', 'Meta description should be 120–165 characters — Google rewrites shorter/longer ones.');
        add(kw && firstChunk.indexOf(kw) !== -1, 'Focus keyword in the opening paragraph', 'Use the focus keyword within the first ~10% of the content.');
        add(kw && density >= 0.5 && density <= 3.0, 'Keyword density is ' + density + '% (aim 0.5–3%)', density > 3.0 ? 'Keyword density is too high — it reads as keyword stuffing. Use the keyword fewer times or write more.' : 'Use the focus keyword a few more times naturally (0.5–3% of all words).');
        add(kw && headings.some(function (h) { return h.indexOf(kw) !== -1; }), 'Focus keyword in at least one subheading (H2/H3)', 'Add the focus keyword to one subheading (H2/H3) to strengthen topical relevance.');
        add(wordCount >= 600, 'Content length is ' + wordCount + ' words (aim 600+)', 'Deepen the article — posts under 600 words rarely rank. Target 600–1500 words.');
        add(headings.length >= 2, headings.length + ' subheadings found (aim 2+)', 'Break the article up with at least two H2/H3 subheadings.');
        add(external >= 1, external + ' external link(s) found', 'Add at least one outbound link to an authoritative source — it builds trust.');
        add(internal >= 1, internal + ' internal link(s) found', 'Link to at least one other page on your site to spread ranking power.');
        add(imgs.length > 0 && imgNoAlt === 0, imgs.length === 0 ? 'No images in content' : (imgNoAlt === 0 ? 'All ' + imgs.length + ' image(s) have alt text' : imgNoAlt + ' of ' + imgs.length + ' image(s) missing alt text'), 'Add descriptive alt text to every image (and consider adding an image or two).');

        // ----- score -----
        var scored = checks.filter(function (c) { return c.ok !== null; });
        var passed = scored.filter(function (c) { return c.ok === true; }).length;
        var score;
        if (!kw) {
            score = scored.length ? Math.round((passed / scored.length) * 24) : 0;
        } else {
            score = Math.round((passed / Math.max(1, scored.length)) * 100);
        }
        return { score: Math.max(0, Math.min(100, score)), checks: checks, hasKeyword: !!kw, words: wordCount };
    }

    function scoreColor(score) {
        return score >= 70 ? '#16a34a' : score >= 40 ? '#d97706' : '#dc2626';
    }

    function scoreLabel(score) {
        return score >= 70 ? 'Good' : score >= 40 ? 'Okay' : 'Needs work';
    }

    function render(el, result) {
        var color = scoreColor(result.score);
        var circumference = 2 * Math.PI * 26;
        var offset = circumference * (1 - result.score / 100);
        // Markup uses theme-aware Tailwind classes already on the page, so the
        // panel follows the site's light/dark design system automatically.
        var html = '' +
            '<div class="seo-card rounded-[10px] border border-[#e6e8ee] dark:border-[#262a33] bg-white dark:bg-[#101319] p-4 text-[13px] leading-relaxed">' +
            '<div class="flex items-center gap-4">' +
            '<div class="relative w-16 h-16 shrink-0">' +
            '<svg width="64" height="64" viewBox="0 0 64 64" class="block" style="transform:rotate(-90deg);">' +
            '<circle cx="32" cy="32" r="26" fill="none" stroke="#e6e8ee" class="seo-ring-track" stroke-width="7"/>' +
            '<circle cx="32" cy="32" r="26" fill="none" stroke="' + color + '" stroke-width="7" stroke-linecap="round" stroke-dasharray="' + circumference.toFixed(1) + '" stroke-dashoffset="' + offset.toFixed(1) + '"/>' +
            '</svg>' +
            '<div class="absolute inset-0 flex items-center justify-center font-extrabold text-[17px]" style="color:' + color + ';">' + result.score + '</div>' +
            '</div>' +
            '<div class="min-w-0">' +
            '<div class="font-bold" style="color:' + color + ';">SEO Score: ' + scoreLabel(result.score) + '</div>' +
            '<div class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">' + (result.hasKeyword ? 'Focus keyword set · ' : 'Add a focus keyword to unlock full scoring · ') + result.words + ' words' + '</div>' +
            '</div>' +
            '</div>' +
            '<ul class="mt-3 pt-3 border-t border-[#eef0f4] dark:border-[#22262e] m-0 p-0 list-none grid gap-1.5">';
        result.checks.forEach(function (c) {
            var passed = c.ok === true;
            var mark = c.ok === null ? '–' : (passed ? '✓' : '✕');
            var dotCls = c.ok === null ? 'bg-slate-300 dark:bg-slate-600' : (passed ? 'bg-green-600' : 'bg-red-500');
            html += '<li class="flex gap-2 items-start">' +
                '<span class="shrink-0 w-4 h-4 rounded-full text-white text-[10px] font-bold inline-flex items-center justify-center mt-0.5 ' + dotCls + '">' + mark + '</span>' +
                '<span class="text-slate-600 dark:text-slate-300">' + escapeHtml(c.label) +
                (c.hint ? '<span class="block text-slate-400 dark:text-slate-500 text-[11.5px]">' + escapeHtml(c.hint) + '</span>' : '') +
                '</span></li>';
        });
        html += '</ul></div>';
        el.innerHTML = html;
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
    }

    function init() {
        var panel = document.getElementById('seo-score-panel');
        if (!panel) return;
        var form = panel.closest('form') || document;
        var get = function (name) { return form.querySelector('[name="' + name + '"]'); };
        var fields = {
            title: get('title'),
            slug: get('slug'),
            metaTitle: get('meta_title'),
            metaDescription: get('meta_description'),
            focusKeyword: get('focus_keyword'),
            editor: document.getElementById('editor')
        };

        var update = function () {
            render(panel, analyze({
                title: fields.title ? fields.title.value : '',
                slug: fields.slug ? fields.slug.value : '',
                metaTitle: fields.metaTitle ? fields.metaTitle.value : '',
                metaDescription: fields.metaDescription ? fields.metaDescription.value : '',
                focusKeyword: fields.focusKeyword ? fields.focusKeyword.value : '',
                content: fields.editor ? fields.editor.value : ''
            }));
        };

        Object.keys(fields).forEach(function (k) {
            var f = fields[k];
            if (!f) return;
            f.addEventListener('input', debounce(update, 400));
            f.addEventListener('change', update);
        });
        // Also refresh whenever the rich editor syncs into its textarea
        // (it dispatches an input event on the hidden textarea).
        if (fields.editor) {
            fields.editor.addEventListener('input', debounce(update, 600));
            fields.editor.addEventListener('huvanti-sync', update);
        }

        update();
    }

    function debounce(fn, ms) {
        var t;
        return function () {
            clearTimeout(t);
            t = setTimeout(fn, ms);
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
