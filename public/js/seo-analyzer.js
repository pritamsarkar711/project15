/*!
 * Huvanti SEO Analyzer — live RankMath-style on-page scoring for the post
 * editor. Mirrors app/Services/SeoAnalyzer.php (same checks, same weights)
 * so the number the author sees while typing matches the persisted score.
 *
 * UI model (RankMath-style):
 *   - Score gauge + verdict + pass/fix counters on top.
 *   - Segmented tabs — Basic SEO / Content / Media & links — like RankMath's
 *     check groups. Failed rows read first, passed rows stay dimmed below.
 *   - Short labels, one-line hints with live values only where they help.
 *
 * Design rules: brand palette only, driven by the site theme variables
 * (var(--brand) and friends) so the panel recolors with the admin-chosen
 * theme. Green = passed, amber = attention, slate = neutral. No red anywhere.
 *
 * Usage: give the editor form the data-seo-panel attribute container:
 *   <div id="seo-score-panel"></div>
 * and it auto-wires to fields named title / slug / meta_title /
 * meta_description / focus_keyword plus the #editor content textarea.
 */
(function () {
    'use strict';

    var BRAND = 'var(--brand)';
    var AMBER = '#B45309';

    var ICON_CHECK = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path d="m5 12.5 4.5 4.5L19 7"/></svg>';
    var ICON_CROSS = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path d="M6 6l12 12M18 6L6 18"/></svg>';
    var ICON_DASH = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" style="display:block"><path d="M6 12h12"/></svg>';

    var GROUPS = [
        { id: 'basic',   label: 'Basic SEO' },
        { id: 'content', label: 'Content' },
        { id: 'media',   label: 'Media & links' }
    ];
    var activeGroup = null; // remembered between renders

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
        var add = function (group, ok, label, hint, neutral) {
            checks.push({ group: group, ok: neutral ? null : !!ok, label: label, hint: ok || neutral ? '' : hint });
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
        add('basic', kw && titleLower.indexOf(kw) !== -1, 'Keyword in title', 'Add the keyword to the post title.');
        add('basic', kw && metaTitleLower.indexOf(kw) !== -1, 'Keyword in SEO title', 'Work the keyword into the SEO title.');
        add('basic', kw && metaDescLower.indexOf(kw) !== -1, 'Keyword in meta description', 'Mention the keyword in the meta description.');
        add('basic', kw && slug.indexOf(kw) !== -1, 'Keyword in URL slug', 'Include the keyword in the slug.');
        add('basic', title.length >= 30 && title.length <= 65, 'Title length 30-65', 'Title is ' + title.length + ' characters. Aim for 30 to 65.');
        add('basic', metaTitle.length >= 30 && metaTitle.length <= 60, 'SEO title length 30-60', 'SEO title is ' + metaTitle.length + ' characters. Aim for 30 to 60.');
        add('basic', metaDesc.length >= 120 && metaDesc.length <= 165, 'Meta description 120-165', 'Meta description is ' + metaDesc.length + ' characters. Aim for 120 to 165.');
        add('content', kw && firstChunk.indexOf(kw) !== -1, 'Keyword in opening paragraph', 'Use the keyword early in the content.');
        add('content', kw && density >= 0.5 && density <= 3.0, 'Keyword density 0.5-3%', density > 3.0 ? 'Density is ' + density + '%, which is high. Ease off a little.' : 'Density is ' + density + '%. Use the keyword a few more times.');
        add('content', kw && headings.some(function (h) { return h.indexOf(kw) !== -1; }), 'Keyword in a subheading', 'Add the keyword to one H2 or H3.');
        add('content', wordCount >= 600, '600+ words', 'Content is ' + wordCount + ' words. Aim for 600 or more.');
        add('content', headings.length >= 2, '2+ subheadings', headings.length + ' subheading(s) so far. Add more H2 or H3 headings.');
        add('media', external >= 1, 'External link', 'Add one outbound link to a trusted source.');
        add('media', internal >= 1, 'Internal link', 'Link to another page on your site.');
        add('media', imgs.length > 0 && imgNoAlt === 0, 'Image alt text', imgs.length === 0 ? 'Add an image with descriptive alt text.' : 'Add alt text to ' + imgNoAlt + ' image(s).');

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
        return score >= 70 ? BRAND : AMBER;
    }

    function scoreLabel(score) {
        return score >= 70 ? 'Good' : score >= 40 ? 'Okay' : 'Needs work';
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"']/g, function (m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
        });
    }

    function groupStats(checks, groupId) {
        var pass = 0, fail = 0, neutral = 0;
        checks.forEach(function (c) {
            if (c.group !== groupId) return;
            if (c.ok === null) { neutral++; return; }
            c.ok ? pass++ : fail++;
        });
        return { pass: pass, fail: fail, neutral: neutral, total: pass + fail + neutral };
    }

    function row(c) {
        var icon, iconCls, textCls;
        if (c.ok === true) {
            icon = ICON_CHECK; iconCls = 'seo-ic seo-ic-pass'; textCls = 'seo-row-done';
        } else if (c.ok === false) {
            icon = ICON_CROSS; iconCls = 'seo-ic seo-ic-fix'; textCls = 'seo-row-todo';
        } else {
            icon = ICON_DASH; iconCls = 'seo-ic seo-ic-na'; textCls = 'seo-row-done';
        }
        return '<li class="seo-row' + (c.ok === false ? ' seo-row-fix' : '') + '">' +
            '<span class="' + iconCls + '">' + icon + '</span>' +
            '<span class="min-w-0 ' + textCls + '">' + escapeHtml(c.label) +
            (c.hint ? '<span class="seo-hint">' + escapeHtml(c.hint) + '</span>' : '') +
            '</span></li>';
    }

    function render(el, result) {
        var color = scoreColor(result.score);
        var circumference = 2 * Math.PI * 27;
        var offset = circumference * (1 - result.score / 100);
        var failed = 0, passed = 0;
        result.checks.forEach(function (c) {
            if (c.ok === null) { return; }
            c.ok ? passed++ : failed++;
        });

        // Pick the default tab: first group that still has something to fix,
        // otherwise the first group. Remember the author's choice afterwards.
        if (!activeGroup) {
            activeGroup = GROUPS[0].id;
            for (var g = 0; g < GROUPS.length; g++) {
                if (groupStats(result.checks, GROUPS[g].id).fail > 0) { activeGroup = GROUPS[g].id; break; }
            }
        }

        var html = '' +
            '<div class="seo-panel">' +
            // Header: gauge + verdict + counters
            '<div class="flex items-center gap-4">' +
            '<div class="relative w-[76px] h-[76px] shrink-0">' +
            '<svg width="76" height="76" viewBox="0 0 64 64" class="block" style="transform:rotate(-90deg);" aria-hidden="true">' +
            '<circle cx="32" cy="32" r="27" fill="none" stroke="#e6e8ee" class="seo-ring-track" stroke-width="6"/>' +
            '<circle cx="32" cy="32" r="27" fill="none" style="stroke:' + color + '" stroke-width="6" stroke-linecap="round" stroke-dasharray="' + circumference.toFixed(1) + '" stroke-dashoffset="' + offset.toFixed(1) + '"/>' +
            '</svg>' +
            '<div class="absolute inset-0 flex flex-col items-center justify-center">' +
            '<span class="font-extrabold text-[21px] leading-none tracking-tight" style="color:' + color + ';">' + result.score + '</span>' +
            '<span class="text-[8.5px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500 mt-1">score</span>' +
            '</div>' +
            '</div>' +
            '<div class="min-w-0 flex-1">' +
            '<div class="font-bold text-[15px] text-slate-900 dark:text-white leading-tight tracking-[-0.01em]">' + scoreLabel(result.score) + '</div>' +
            '<div class="text-slate-500 dark:text-slate-400 text-xs mt-1">' + result.words + ' words' +
            (result.hasKeyword ? '' : ' &middot; add a focus keyword to unlock full scoring') + '</div>' +
            '<div class="flex flex-wrap gap-1.5 mt-2.5">' +
            '<span class="seo-count seo-count-pass"><span class="seo-dot" style="background:var(--brand);"></span>' + passed + ' passed</span>' +
            '<span class="seo-count seo-count-fix"><span class="seo-dot" style="background:#F59E0B;"></span>' + failed + ' to fix</span>' +
            '</div>' +
            '</div>' +
            '</div>';

        // Segmented group tabs with pass counts (same control as the site tabs)
        html += '<div class="mt-4 pt-3 border-t border-[#eef0f4] dark:border-[#22262e]"><div class="seg !p-0.5 w-full" role="tablist">';
            GROUPS.forEach(function (g) {
                var st = groupStats(result.checks, g.id);
                var isActive = g.id === activeGroup;
                html += '<button type="button" role="tab" data-seo-group="' + g.id + '" class="seg-item !flex-1 !justify-center !text-[11px] !gap-1 ' + (isActive ? 'is-active' : '') + '">' + escapeHtml(g.label) +
                    '<span class="' + (isActive ? 'opacity-70' : 'opacity-60') + ' font-bold">' + st.pass + '/' + (st.total - st.neutral) + '</span>' +
                    '</button>';
            });
            html += '</div></div>';

            // Rows for the active group — failed first, then passed, then neutral.
            var rows = [];
            result.checks.filter(function (c) { return c.group === activeGroup && c.ok === false; })
                .forEach(function (c) { rows.push(row(c)); });
            result.checks.filter(function (c) { return c.group === activeGroup && c.ok === true; })
                .forEach(function (c) { rows.push(row(c)); });
            result.checks.filter(function (c) { return c.group === activeGroup && c.ok === null; })
                .forEach(function (c) { rows.push(row(c)); });
            html += '<ul class="mt-2.5 m-0 p-0 list-none grid gap-0.5">' + rows.join('') + '</ul>';

        html += '</div>';
        el.innerHTML = html;

        el.querySelectorAll('[data-seo-group]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                activeGroup = btn.getAttribute('data-seo-group');
                render(el, result);
            });
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
