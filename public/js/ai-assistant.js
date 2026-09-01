/*!
 * Huvanti AI Assistant — editor integration.
 *
 * Wires [data-ai-action] buttons to the server-side AI proxy. The endpoint
 * URL is read from the container's data-ai-endpoint attribute (the view
 * injects the route); the API key and model list NEVER reach this script.
 *
 * Buttons look like:
 *   <button data-ai-action="meta_title" data-ai-target="[name=meta_title]">Suggest</button>
 * Optional attributes:
 *   data-ai-keyword-target="[name=focus_keyword]"  — supply focus keyword
 *   data-ai-output="#ai-output"                    — show result here too (ask)
 */
(function () {
    'use strict';

    function $(sel, root) { return (root || document).querySelector(sel); }

    function currentContent() {
        var ed = document.getElementById('editor');
        return ed ? ed.value : '';
    }

    function currentTitle() {
        var f = document.querySelector('[name="title"]');
        return f ? f.value : '';
    }

    function currentKeyword() {
        var f = document.querySelector('[name="focus_keyword"]');
        return f ? f.value : '';
    }

    function init() {
        var root = document.querySelector('[data-ai-endpoint]');
        if (!root) return;
        var endpoint = root.getAttribute('data-ai-endpoint');

        document.querySelectorAll('[data-ai-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-ai-action');
                var targetSel = btn.getAttribute('data-ai-target');
                var outputSel = btn.getAttribute('data-ai-output');
                var questionSel = btn.getAttribute('data-ai-question');

                var payload = {
                    action: action,
                    title: currentTitle(),
                    content: currentContent(),
                    keyword: currentKeyword()
                };
                if (questionSel) {
                    var q = $(questionSel);
                    payload.question = q ? q.value : '';
                }

                var original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Thinking…';
                btn.setAttribute('aria-busy', 'true');

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                }).then(function (r) {
                    return r.json().catch(function () { return { ok: false, message: 'Unexpected server response.' }; });
                }).then(function (data) {
                    if (!data.ok) {
                        if (outputSel) { setOutput(outputSel, data.message || 'The AI is unavailable right now.', true); }
                        else { alert(data.message || 'The AI is unavailable right now.'); }
                        return;
                    }
                    if (targetSel) {
                        var target = $(targetSel);
                        if (target) {
                            target.value = data.result;
                            target.dispatchEvent(new Event('input', { bubbles: true }));
                            target.dispatchEvent(new Event('change', { bubbles: true }));
                            target.focus();
                        }
                    }
                    if (outputSel) setOutput(outputSel, data.result, false);
                }).catch(function () {
                    alert('Network error — could not reach the AI assistant.');
                }).finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    btn.removeAttribute('aria-busy');
                });
            });
        });
    }

    function setOutput(sel, text, isError) {
        var out = document.querySelector(sel);
        if (!out) return;
        out.textContent = text;
        out.classList.toggle('ai-output-error', !!isError);
        out.classList.remove('hidden');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
