{{--
    Shared crash-safe autosave for the admin post editor (create + edit).
    Expects the surrounding form to carry data-autosave="admin" and a hidden
    input #autosave-post-id. Behaviour mirrors the author dashboard:
      Layer 1: whole-form snapshot to localStorage every 3 s + restore on
               reopen (works offline).
      Layer 2: POST to the admin autosave endpoint every 45 s and on tab
               close; the server stores/updates a real DRAFT row.
    Published/scheduled posts are never auto-mutated server-side (endpoint
    answers 409 and JS pauses), so live content is safe.
--}}
<script>
(function(){
    var form = document.querySelector('form[data-autosave="admin"]');
    if (!form) return;

    var STORAGE_KEY = 'huv-form-draft:' + location.pathname;
    var statusEl = document.getElementById('autosave-status');
    var idEl = document.getElementById('autosave-post-id');
    var dirtyLocal = false, dirtyServer = false, inFlight = false, stopped = false;

    function setStatus(msg) { if (statusEl) statusEl.textContent = msg; }

    function snapshot() {
        var fd = new FormData(form);
        var data = {};
        fd.forEach(function (value, key) {
            if (key === '_token' || key === '_method' || key === 'action' || key === 'featured_image' || key === 'featured_image_url' || key === 'autosave_post_id') return;
            data[key] = typeof value === 'string' ? value : '';
        });
        data.__savedAt = new Date().toISOString();
        return data;
    }

    function formatTime(iso) {
        try { return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }
        catch (e) { return ''; }
    }

    // Ignore snapshots that carry nothing but an empty form.
    function hasMeaningful(data) {
        for (var k in data) {
            if (!Object.prototype.hasOwnProperty.call(data, k)) continue;
            if (k === '__savedAt') continue;
            var v = typeof data[k] === 'string' ? data[k].replace(/<[^>]*>/g, '') : '';
            if (v.trim() !== '') return true;
        }
        return false;
    }

    // Recovery is OPT-IN and always visible (same behaviour as the author
    // dashboard). The old silent auto-fill — combined with the navigation
    // handlers re-writing the snapshot after a successful save — made the
    // NEXT "New Post" page open with the just-saved post already filled in.
    function offerRecovery() {
        var title = form.querySelector('[name="title"]');
        var ed = document.getElementById('editor');
        var pristine = title && !title.value && ed && !ed.value;
        if (!pristine) return;
        var raw = null;
        try { raw = localStorage.getItem(STORAGE_KEY); } catch (e) { return; }
        if (!raw) return;
        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }
        if (!hasMeaningful(data)) { try { localStorage.removeItem(STORAGE_KEY); } catch (e2) {} return; }
        var when = formatTime(data.__savedAt);
        var banner = document.createElement('div');
        banner.className = 'mb-5 flex flex-wrap items-center justify-between gap-3 border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-4';
        var left = document.createElement('div');
        left.className = 'min-w-0';
        var h = document.createElement('div');
        h.className = 'text-sm font-semibold text-amber-800 dark:text-amber-300';
        h.textContent = 'Unsaved work found in this browser';
        var p = document.createElement('p');
        p.className = 'text-sm text-amber-700 dark:text-amber-400 mt-0.5 truncate';
        p.textContent = (data.title ? '\u201C' + data.title + '\u201D' : 'Untitled draft') + (when ? ' — last saved ' + when : '');
        left.appendChild(h); left.appendChild(p);
        var right = document.createElement('div');
        right.className = 'flex items-center gap-2 shrink-0';
        var restoreBtn = document.createElement('button');
        restoreBtn.type = 'button';
        restoreBtn.className = 'inline-flex items-center h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-xs font-semibold';
        restoreBtn.textContent = 'Restore it';
        var discardBtn = document.createElement('button');
        discardBtn.type = 'button';
        discardBtn.className = 'inline-flex items-center h-9 px-3 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10';
        discardBtn.textContent = 'Discard';
        right.appendChild(restoreBtn); right.appendChild(discardBtn);
        banner.appendChild(left); banner.appendChild(right);
        restoreBtn.addEventListener('click', function () {
            Object.keys(data).forEach(function (key) {
                if (key === '__savedAt') return;
                var field = form.querySelector('[name="' + key + '"]');
                if (field && field.type !== 'file') field.value = data[key];
            });
            if (ed) {
                // __huvSet is the editor's official restore bridge (plain
                // value assignment is invisible to it and left the editor
                // looking empty).
                if (typeof ed.__huvSet === 'function') { ed.__huvSet(data['content'] || ''); }
                else {
                    ed.value = data['content'] || '';
                    ed.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
            setStatus('Unsaved work restored (saved ' + when + '). Save it or keep writing.');
            dirtyLocal = false; dirtyServer = true;
            banner.remove();
        });
        discardBtn.addEventListener('click', function () {
            try { localStorage.removeItem(STORAGE_KEY); } catch (e2) {}
            banner.remove();
            setStatus('Discarded the unsaved copy kept in this browser.');
        });
        form.parentNode.insertBefore(banner, form);
    }

    form.addEventListener('input', function () { dirtyLocal = true; dirtyServer = true; }, true);
    form.addEventListener('change', function () { dirtyLocal = true; dirtyServer = true; }, true);

    setInterval(function () {
        if (stopped || window.__huvAutosaveStop || !dirtyLocal || document.hidden) return;
        var data = snapshot();
        if (!hasMeaningful(data)) return; // never snapshot an empty form
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); dirtyLocal = false; } catch (e) {}
    }, 3000);

    function sendToServer(useBeacon) {
        if (stopped || window.__huvAutosaveStop || inFlight || !navigator.onLine) return;
        var fd = new FormData(form);
        // Never re-upload file inputs every 45 s — the featured image rides
        // only on the real submit. SendBeacon also cannot carry files.
        var drop = [];
        fd.forEach(function (v, k) { if (typeof v !== 'string') drop.push(k); });
        drop.forEach(function (k) { fd.delete(k); });
        if (idEl && idEl.value) fd.set('autosave_post_id', idEl.value);
        if (useBeacon && navigator.sendBeacon) {
            navigator.sendBeacon('{{ route("admin.posts.autosave") }}', fd);
            return;
        }
        inFlight = true;
        fetch('{{ route("admin.posts.autosave") }}', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); }).then(function (j) {
            inFlight = false;
            if (j && j.ok && j.autosave_post_id) {
                if (idEl) idEl.value = j.autosave_post_id;
                dirtyServer = false;
                setStatus('Draft auto-saved' + (j.saved_at ? ' at ' + j.saved_at : '') + '. Safe from crashes and power cuts.');
            } else if (j && j.locked) {
                stopped = true;
                setStatus(j.message || 'Autosave paused for this post.');
            }
        }).catch(function () {
            inFlight = false;
            setStatus('Offline or server busy — your work is saved in this browser.');
        });
    }

    setInterval(function () {
        if (stopped || window.__huvAutosaveStop || !dirtyServer) return;
        sendToServer(false);
    }, 45000);

    // Last flush when the tab is hidden or closed. The stop-flag guard is
    // CRITICAL: after a real submit these handlers fire DURING the navigation
    // — re-writing the snapshot here resurrected the just-saved post inside
    // the next "New Post" form (same root cause as the author dashboard bug).
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            if (!stopped && !window.__huvAutosaveStop) {
                var data = snapshot();
                if (hasMeaningful(data)) {
                    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); dirtyLocal = false; } catch (e) {}
                }
            }
            if (dirtyServer) sendToServer(true);
        }
    });
    window.addEventListener('pagehide', function () {
        if (!stopped && !window.__huvAutosaveStop) {
            var data = snapshot();
            if (hasMeaningful(data)) {
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); dirtyLocal = false; } catch (e) {}
            }
        }
        if (dirtyServer) sendToServer(true);
    });

    // Back/forward-cache restore of an already-submitted page: blank the
    // form (the data is saved server-side; the fields still held the
    // submitted values, which read like "my saved post is back in the
    // editor").
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted || !window.__huvAutosaveStop) return;
        form.reset();
        var ed = document.getElementById('editor');
        if (ed && typeof ed.__huvSet === 'function') ed.__huvSet('');
        var img = document.getElementById('featured-preview');
        if (img) { img.classList.add('hidden'); img.removeAttribute('src'); }
        dirtyLocal = false; dirtyServer = false;
        setStatus('');
    });

    // Stop everything once the form is really submitted (page will navigate).
    form.addEventListener('submit', function () {
        stopped = true;
        window.__huvAutosaveStop = true;
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });

    offerRecovery();
})();
</script>
