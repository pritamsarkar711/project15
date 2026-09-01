@php
    // Reusable social share row.
    //   $shareUrl    — raw (unencoded) absolute URL of the page to share
    //   $shareTitle  — raw page title
    $u = urlencode($shareUrl ?? url('/'));
    $t = urlencode(($shareTitle ?? 'Huvanti') . ' #Huvanti');
    $btnClass = $shareBtnClass ?? 'w-10 h-10 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 hover:!text-white hover:!border-transparent inline-flex items-center justify-center transition';
@endphp
<div class="flex items-center gap-2 flex-wrap" data-share-buttons>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#1877F2]" aria-label="Share on Facebook" title="Facebook">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94Z"/></svg>
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $t }}&url={{ $u }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#000000]" aria-label="Share on X (Twitter)" title="X (Twitter)">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93Zm-1.29 19.5h2.04L6.49 3.24H4.3l13.31 17.41Z"/></svg>
    </a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#0A66C2]" aria-label="Share on LinkedIn" title="LinkedIn">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.55V9h3.57v11.45Z"/></svg>
    </a>
    <a href="https://wa.me/?text={{ $t }}%20{{ $u }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#25D366]" aria-label="Share on WhatsApp" title="WhatsApp">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.5 0 1.47 1.07 2.89 1.22 3.09.15.2 2.1 3.2 5.1 4.49.71.3 1.27.49 1.7.63.72.23 1.37.2 1.88.12.58-.09 1.76-.72 2.01-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35ZM12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.9-9.88a9.83 9.83 0 0 1 7 2.9 9.83 9.83 0 0 1 2.89 7c0 5.45-4.44 9.87-9.9 9.87Zm8.42-18.3A11.82 11.82 0 0 0 12.04 0C5.5 0 .16 5.34.16 11.9c0 2.1.55 4.14 1.6 5.95L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.9 0-3.18-1.24-6.16-3.47-8.41Z"/></svg>
    </a>
    <a href="https://t.me/share/url?url={{ $u }}&text={{ $t }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#229ED9]" aria-label="Share on Telegram" title="Telegram">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M23.91 3.79 20.3 20.84c-.25 1.21-.98 1.5-1.99.94l-5.5-4.07-2.66 2.57c-.3.3-.55.55-1.1.55l.39-5.6 10.19-9.2c.44-.4-.1-.62-.69-.22L6.32 13.21.64 11.44c-1.18-.37-1.2-1.18.25-1.75l21.26-8.2c.99-.37 1.86.22 1.76 2.3Z"/></svg>
    </a>
    <a href="https://www.reddit.com/submit?url={{ $u }}&title={{ $t }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#FF4500]" aria-label="Share on Reddit" title="Reddit">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.14c0-1.35-1.1-2.44-2.44-2.44-.66 0-1.25.26-1.69.68a12 12 0 0 0-6.56-2.08l1.11-3.52 3.02.71a1.74 1.74 0 1 0 .17-.85l-3.36-.8a.44.44 0 0 0-.52.3l-1.24 3.93a12 12 0 0 0-6.63 2.08 2.44 2.44 0 1 0-3.1 3.6c-.04.26-.06.53-.06.8 0 3.7 4.13 6.7 9.21 6.7s9.21-3 9.21-6.7c0-.27-.02-.53-.06-.79A2.44 2.44 0 0 0 24 12.14ZM6.4 13.87c0-.96.78-1.74 1.74-1.74s1.73.78 1.73 1.74-.78 1.73-1.73 1.73-1.74-.77-1.74-1.73Zm9.7 4.53c-1.19 1.19-3.47 1.28-4.14 1.28-.67 0-2.95-.09-4.14-1.28a.45.45 0 0 1 .64-.64c.75.75 2.36 1.02 3.5 1.02 1.14 0 2.75-.27 3.5-1.02a.45.45 0 1 1 .64.64Zm-.31-2.8c-.96 0-1.73-.77-1.73-1.73s.77-1.74 1.73-1.74 1.74.78 1.74 1.74-.78 1.73-1.74 1.73Z"/></svg>
    </a>
    <a href="https://pinterest.com/pin/create/button/?url={{ $u }}&description={{ $t }}" target="_blank" rel="noopener" class="{{ $btnClass }} hover:!bg-[#E60023]" aria-label="Save to Pinterest" title="Pinterest">
        <svg class="w-[18px] h-[18px] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0a12 12 0 0 0-4.37 23.17c-.1-.92-.2-2.34.04-3.35l1.4-5.98s-.36-.72-.36-1.78c0-1.66.96-2.9 2.16-2.9 1.02 0 1.51.77 1.51 1.69 0 1.02-.65 2.56-1 3.98-.28 1.19.6 2.17 1.78 2.17 2.14 0 3.78-2.25 3.78-5.51 0-2.88-2.07-4.9-5.02-4.9a5.2 5.2 0 0 0-5.43 5.22c0 1.03.4 2.14.9 2.75.1.12.11.22.08.34l-.33 1.36c-.05.22-.17.27-.4.16-1.5-.7-2.43-2.88-2.43-4.64 0-3.78 2.74-7.25 7.92-7.25 4.15 0 7.38 2.96 7.38 6.91 0 4.13-2.6 7.45-6.22 7.45-1.21 0-2.36-.63-2.75-1.38l-.75 2.85c-.27 1.04-1 2.35-1.49 3.15A12 12 0 1 0 12 0Z"/></svg>
    </a>
    <a href="mailto:?subject={{ $t }}&body={{ $u }}" class="{{ $btnClass }} hover:!bg-[#2E7856]" aria-label="Share via Email" title="Email">
        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
    </a>
    <button type="button" data-copy-share="{{ $shareUrl ?? url('/') }}" class="{{ $btnClass }} hover:!bg-[#2E7856]" aria-label="Copy link" title="Copy link">
        <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
    </button>
    <script>
        // Copy-link buttons on this share row (inline so it works everywhere).
        (function(){
            document.querySelectorAll('[data-copy-share]').forEach(function(btn){
                if (btn.__shareCopyBound) return;
                btn.__shareCopyBound = true;
                btn.addEventListener('click', function(){
                    var url = btn.getAttribute('data-copy-share');
                    var done = function(ok){
                        var prev = btn.getAttribute('title');
                        btn.style.background = ok ? '#2E7856' : '';
                        btn.style.color = '#fff';
                        btn.setAttribute('title', ok ? 'Copied!' : 'Press Ctrl+C');
                        setTimeout(function(){ btn.style.background=''; btn.style.color=''; btn.setAttribute('title', prev); }, 1500);
                    };
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(url).then(function(){ done(true); }).catch(function(){ done(false); });
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = url; ta.style.position='fixed'; ta.style.opacity='0';
                        document.body.appendChild(ta); ta.select();
                        try { document.execCommand('copy'); done(true); } catch(e) { done(false); }
                        document.body.removeChild(ta);
                    }
                });
            });
        })();
    </script>
</div>
