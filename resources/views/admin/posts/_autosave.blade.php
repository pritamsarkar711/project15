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

    function restore() {
        var title = form.querySelector('[name="title"]');
        var ed = document.getElementById('editor');
        var pristine = title && !title.value && ed && !ed.value;
        if (!pristine) return;
        var raw = null;
        try { raw = localStorage.getItem(STORAGE_KEY); } catch (e) { return; }
        if (!raw) return;
        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }
        var restored = 0;
        Object.keys(data).forEach(function (key) {
            if (key === '__savedAt') return;
            var field = form.querySelector('[name="' + key + '"]');
            if (field && field.type !== 'file') { field.value = data[key]; restored++; }
        });
        if (restored) {
            if (ed) {
                ed.value = data['content'] || '';
                ed.dispatchEvent(new Event('input', { bubbles: true }));
            }
            setStatus('Your last unsaved work in this browser was restored (saved ' + formatTime(data.__savedAt) + ').');
            dirtyLocal = false; dirtyServer = true;
        }
    }

    form.addEventListener('input', function () { dirtyLocal = true; dirtyServer = true; }, true);
    form.addEventListener('change', function () { dirtyLocal = true; dirtyServer = true; }, true);

    setInterval(function () {
        if (stopped || !dirtyLocal || document.hidden) return;
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot())); dirtyLocal = false; } catch (e) {}
    }, 3000);

    function sendToServer(useBeacon) {
        if (stopped || inFlight || !navigator.onLine) return;
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
        if (stopped || !dirtyServer) return;
        sendToServer(false);
    }, 45000);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot())); dirtyLocal = false; } catch (e) {}
            if (dirtyServer) sendToServer(true);
        }
    });
    window.addEventListener('pagehide', function () {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot())); dirtyLocal = false; } catch (e) {}
        if (dirtyServer) sendToServer(true);
    });

    // Stop everything once the form is really submitted (page will navigate).
    form.addEventListener('submit', function () {
        stopped = true;
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
    });

    restore();
})();
</script>
