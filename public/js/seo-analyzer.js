/*!
 * Huvanti SEO Analyzer — live RankMath-style on-page scoring for the post
 * editor. Mirrors app/Services/SeoAnalyzer.php (same checks, same weights)
 * so the number the author sees while typing matches the persisted score.
 *
 * UI model (RankMath-style, compact):
 *   - Score gauge + verdict on top.
 *   - Failed checks first as short one-line rows with a tiny fix hint.
 *   - Passed checks collapsed behind a "+ N passed" toggle so the panel
 *     stays small while you write.
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
        // Short label + a one-line fix hint (shown only for failed rows).
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

        // ----- checks (short labels; same scoring as the server) -----
        add(kw && titleLower.indexOf(kw) !== -1, 'Keyword in title', 'Add the keyword to the post title.');
        add(kw && metaTitleLower.indexOf(kw) !== -1, 'Keyword in SEO title', 'Work the keyword into the SEO title.');
        add(kw && metaDescLower.indexOf(kw) !== -1, 'Keyword in meta description', 'Mention the keyword in the meta description.');
        add(kw && slug.indexOf(kw) !== -1, 'Keyword in URL slug', 'Include the keyword in the slug.');
        add(title.length >= 30 && title.length <= 65, 'Title length 30–65', 'Title is ' + title.length + ' characters — aim 30–65.');
        add(metaTitle.length >= 30 && metaTitle.length <= 60, 'SEO title length 30–60', 'SEO title is ' + metaTitle.length + ' characters — aim 30–60.');
        add(metaDesc.length >= 120 && metaDesc.length <= 165, 'Meta description 120–165', 'Meta description is ' + metaDesc.length + ' characters — aim 120–165.');
        add(kw && firstChunk.indexOf(kw) !== -1, 'Keyword in opening paragraph', 'Use the keyword early in the content.');
        add(kw && density >= 0.5 && density <= 3.0, 'Keyword density 0.5–3%', density > 3.0 ? 'Density ' + density + '% is too high — ease off.' : 'Density ' + density + '% — use the keyword a few more times.');
        add(kw && headings.some(function (h) { return h.indexOf(kw) !== -1; }), 'Keyword in a subheading', 'Add the keyword to one H2/H3.');
        add(wordCount >= 600, '600+ words', 'Content is ' + wordCount + ' words — aim 600+.');
        add(headings.length >= 2, '2+ subheadings', headings.length + ' subheading(s) — add more H2/H3.');
        add(external >= 1, 'External link', 'Add one outbound link to a trusted source.');
        add(internal >= 1, 'Internal link', 'Link to another page on your site.');
        add(imgs.length > 0 && imgNoAlt === 0, 'Image alt text', imgs.length === 0 ? 'Add an image with descriptive alt text.' : 'Add alt text to ' + imgNoAlt + ' image(s).');

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

    var passedExpanded = false;

    function render(el, result) {
        var color = scoreColor(result.score);
        var circumference = 2 * Math.PI * 26;
        var offset = circumference * (1 - result.score / 100);
        var failed = [], passed = [], neutral = 0;
        result.checks.forEach(function (c) {
            if (c.ok === null) { neutral++; return; }
            if (c.ok === true) { passed.push(c); } else { failed.push(c); }
        });

        var html = '' +
            '<div class="seo-card rounded-[10px] border border-[#e6e8ee] dark:border-[#262a33] bg-white dark:bg-[#101319] p-4 text-[13px] leading-relaxed">' +
            // Header: gauge + verdict + counters
            '<div class="flex items-center gap-4">' +
            '<div class="relative w-16 h-16 shrink-0">' +
            '<svg width="64" height="64" viewBox="0 0 64 64" class="block" style="transform:rotate(-90deg);" aria-hidden="true">' +
            '<circle cx="32" cy="32" r="26" fill="none" stroke="#e6e8ee" class="seo-ring-track" stroke-width="7"/>' +
            '<circle cx="32" cy="32" r="26" fill="none" stroke="' + color + '" stroke-width="7" stroke-linecap="round" stroke-dasharray="' + circumference.toFixed(1) + '" stroke-dashoffset="' + offset.toFixed(1) + '"/>' +
            '</svg>' +
            '<div class="absolute inset-0 flex items-center justify-center font-extrabold text-[17px]" style="color:' + color + ';">' + result.score + '</div>' +
            '</div>' +
            '<div class="min-w-0 flex-1">' +
            '<div class="font-bold text-[15px]" style="color:' + color + ';">' + scoreLabel(result.score) + '</div>' +
            '<div class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">' + result.words + ' words' +
            (result.hasKeyword ? '' : ' · add a focus keyword to unlock full scoring') + '</div>' +
            '<div class="flex gap-3 mt-1.5 text-[11px] font-semibold">' +
            '<span class="text-green-600 dark:text-green-400">' + passed.length + ' passed</span>' +
            '<span class="text-red-500">' + failed.length + ' to fix</span>' +
            '</div>' +
            '</div>' +
            '</div>';

        // Failed checks first — short rows, one-line hints.
        if (failed.length) {
            html += '<ul class="mt-3 pt-3 border-t border-[#eef0f4] dark:border-[#22262e] m-0 p-0 list-none grid gap-1.5">';
            failed.forEach(function (c) {
                html += row(c, false);
            });
            html += '</ul>';
        }

        // Passed checks collapsed behind a compact toggle.
        if (passed.length) {
            html += '<div class="mt-2">' +
                '<button type="button" data-seo-toggle class="text-[11px] font-semibold text-slate-400 hover:text-[#1F513A] dark:hover:text-[#6FB393] transition">' +
                (passedExpanded ? 'Hide passed' : '+ ' + passed.length + ' passed') +
                '</button>' +
                (passedExpanded ? '<ul class="mt-1.5 m-0 p-0 list-none grid gap-1.5">' + passed.map(function (c) { return row(c, true); }).join('') + '</ul>' : '') +
                '</div>';
        }

        html += '</div>';
        el.innerHTML = html;

        var toggle = el.querySelector('[data-seo-toggle]');
        if (toggle) {
            toggle.addEventListener('click', function () {
                passedExpanded = !passedExpanded;
                render(el, result);
                // Keep the toggle visible after re-render.
                var t2 = el.querySelector('[data-seo-toggle]');
                if (t2) t2.scrollIntoView({ block: 'nearest' });
            });
        }
    }

    function row(c, isPassed) {
        var mark = isPassed ? '✓' : '✕';
        var dotCls = isPassed
            ? 'bg-green-600'
            : 'bg-red-500';
        return '<li class="flex gap-2 items-start">' +
            '<span class="shrink-0 w-4 h-4 rounded-full text-white text-[10px] font-bold inline-flex items-center justify-center mt-0.5 ' + dotCls + '">' + mark + '</span>' +
            '<span class="' + (isPassed ? 'text-slate-400 dark:text-slate-500' : 'text-slate-600 dark:text-slate-300') + '">' + escapeHtml(c.label) +
            (c.hint ? '<span class="block text-slate-400 dark:text-slate-500 text-[11.5px]">' + escapeHtml(c.hint) + '</span>' : '') +
            '</span></li>';
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
