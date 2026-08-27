/**
 * Huvanti RTE — a self-made rich text editor (no third-party dependencies).
 *
 * Replaces the 4.3 MB CKEditor 5 super-build with one small, self-contained
 * file. Styles are injected from this file, so there is no compiled-CSS
 * rebuild step and no CDN dependency.
 *
 * Features (TinyMCE/CKEditor-style toolbar):
 *   undo/redo | icon paragraph/heading dropdown (P/H1-H4/quote/code) |
 *   font family | icon text-size dropdown (visual A-scale) |
 *   bold/italic/underline/strikethrough/code | text color / highlight |
 *   sup/sub | lists (bullet, numbered) | indent/outdent | align L/C/R/J |
 *   link | image upload+paste+drag (base64, ≤1.5MB guard) | table grid | hr |
 *   special chars | emoji picker | find and replace | code block |
 *   line height | clear formatting | source view | TRUE fullscreen
 *   (editor moves to <body> so no ancestor can clip it) | word count |
 *   keyboard shortcuts | autosave to localStorage
 *
 * Usage:
 *   huvantiEditorInit('#editor');            // textarea selector
 *   huvantiEditorInit('#editor', { placeholder: 'Write here...' });
 *
 * The original textarea stays in the form and is synced on every change and
 * on submit, so the server-side code does not change at all.
 */
(function () {
    'use strict';

    var SVG = {
        undo: '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/>',
        redo: '<path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3L21 13"/>',
        pilcrow: '<path d="M13 4v16"/><path d="M17 4v16"/><path d="M19 4H9.5a4.5 4.5 0 0 0 0 9H13"/>',
        quote: '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>',
        type: '<polyline points="4 7 4 4 20 4 20 7"/><line x1="9" x2="15" y1="20" y2="20"/><line x1="12" x2="12" y1="4" y2="20"/>',
        bold: '<path d="M6 12h9a4 4 0 0 1 0 8H7a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h7a4 4 0 0 1 0 8"/>',
        italic: '<line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/>',
        underline: '<path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/>',
        strike: '<path d="M16 4H9a3 3 0 0 0-2.83 4"/><path d="M14 12a4 4 0 0 1 0 8H6"/><line x1="4" x2="20" y1="12" y2="12"/>',
        codeInline: '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><rect x="10" y="8" width="4" height="8" rx="1" fill="currentColor" opacity="0.35"/>',
        sup: '<path d="M6 16v-8l6 3.5Z"/><path d="M18 12h-2l-1-4 2-1 1 1.5-1 1.5 1 1.5-1 .5Z"/>',
        sub: '<path d="M6 8v8l6-3.5Z"/><path d="M18 16h-2l-1 4 2 1 1-1.5-1-1.5 1-1.5-1-.5Z"/>',
        selectAll: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 9h8"/><path d="M8 12h8"/><path d="M8 15h5"/>',
        ul: '<line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>',
        ol: '<line x1="10" x2="21" y1="6" y2="6"/><line x1="10" x2="21" y1="12" y2="12"/><line x1="10" x2="21" y1="18" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>',
        indent: '<polyline points="3 8 7 12 3 16"/><line x1="21" x2="11" y1="12" y2="12"/><line x1="21" x2="11" y1="6" y2="6"/><line x1="21" x2="11" y1="18" y2="18"/><line x1="3" x2="11" y1="4" y2="4"/><line x1="3" x2="11" y1="20" y2="20"/>',
        outdent: '<polyline points="7 8 3 12 7 16"/><line x1="21" x2="11" y1="12" y2="12"/><line x1="21" x2="11" y1="6" y2="6"/><line x1="21" x2="11" y1="18" y2="18"/><line x1="3" x2="11" y1="4" y2="4"/><line x1="3" x2="11" y1="20" y2="20"/>',
        alignL: '<line x1="21" x2="3" y1="6" y2="6"/><line x1="15" x2="3" y1="12" y2="12"/><line x1="17" x2="3" y1="18" y2="18"/>',
        alignC: '<line x1="18" x2="6" y1="6" y2="6"/><line x1="21" x2="3" y1="12" y2="12"/><line x1="19" x2="5" y1="18" y2="18"/>',
        alignR: '<line x1="21" x2="3" y1="6" y2="6"/><line x1="21" x2="9" y1="12" y2="12"/><line x1="19" x2="7" y1="18" y2="18"/>',
        alignJ: '<line x1="21" x2="3" y1="6" y2="6"/><line x1="21" x2="3" y1="12" y2="12"/><line x1="21" x2="3" y1="18" y2="18"/>',
        link: '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        unlink: '<path d="M18.84 12.25l1.72-1.71a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M5.17 11.75l-1.72 1.71a5 5 0 0 0 7.07 7.07l1.71-1.71"/><line x1="8" x2="8" y1="2" y2="5"/><line x1="16" x2="16" y1="2" y2="5"/><line x1="8" x2="8" y1="19" y2="22"/><line x1="16" x2="16" y1="19" y2="22"/><line x1="2" x2="5" y1="8" y2="8"/><line x1="2" x2="5" y1="16" y2="16"/><line x1="19" x2="22" y1="8" y2="8"/><line x1="19" x2="22" y1="16" y2="16"/>',
        image: '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
        table: '<rect width="18" height="18" x="3" y="3" rx="2"/><line x1="3" x2="21" y1="9" y2="9"/><line x1="3" x2="21" y1="15" y2="15"/><line x1="9" x2="9" y1="3" y2="21"/><line x1="15" x2="15" y1="3" y2="21"/>',
        hr: '<line x1="3" x2="21" y1="12" y2="12"/>',
        code: '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        eraser: '<path d="m7 21-4.3-4.3c-1-1-1-2.5 0-3.4l9.6-9.6c1-1 2.5-1 3.4 0l5.6 5.6c1 1 1 2.5 0 3.4L13 21"/><path d="M22 21H7"/><path d="m5 11 9 9"/>',
        source: '<polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/>',
        expand: '<path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>',
        shrink: '<path d="M4 14h6v6"/><path d="M20 10h-6V4"/><path d="M14 10l7-7"/><path d="M3 21l7-7"/>',
        smile: '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/>',
        find: '<circle cx="11" cy="11" r="7"/><path d="M21 21l-3.5-3.5"/><path d="M8 11h6"/>',
        emoji: '<circle cx="12" cy="12" r="10"/><path d="M8 14c1.5 1.5 4.5 1.5 6 0"/><path d="M9 9h.01"/><path d="M15 9h.01"/>',
        palette: '<circle cx="12" cy="12" r="10"/><circle cx="8" cy="10" r="1"/><circle cx="12" cy="7" r="1"/><circle cx="16" cy="10" r="1"/><circle cx="12" cy="16" r="1"/>'
    };

    function icon(name) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (SVG[name] || '') + '</svg>';
    }

    var STYLES = [
        '.huv-rte{border:1px solid #cbd5e1;background:#fff;}',
        '.dark .huv-rte{border-color:#334155;background:#1e293b;}',
        '.huv-rte-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:2px;padding:6px;border-bottom:1px solid #e2e8f0;background:#f8fafc;position:sticky;top:0;z-index:30;}',
        '.dark .huv-rte-toolbar{border-color:#334155;background:#0f172a;}',
        '.huv-rte-sep{width:1px;align-self:stretch;margin:2px 4px;background:#cbd5e1;}',
        '.dark .huv-rte-sep{background:#334155;}',
        '.huv-rte-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;border:0;background:transparent;color:#334155;border-radius:4px;cursor:pointer;font:inherit;vertical-align:middle;}',
        '.huv-rte-btn:hover{background:#e2e8f0;}',
        '.huv-rte-btn.active{background:#d1fae5;color:#065f46;}',
        '.dark .huv-rte-btn{color:#cbd5e1;}',
        '.dark .huv-rte-btn:hover{background:#1e293b;}',
        '.dark .huv-rte-btn.active{background:#064e3b;color:#a7f3d0;}',
        '.huv-rte-btn svg{width:18px;height:18px;pointer-events:none;}',
        '.huv-rte-select{height:32px;max-width:130px;border:1px solid #cbd5e1;border-radius:4px;background:#fff;color:#334155;font-size:13px;padding:0 4px;cursor:pointer;}',
        '.dark .huv-rte-select{background:#1e293b;color:#e2e8f0;border-color:#334155;}',
        '.huv-rte-content{min-height:320px;max-height:65vh;overflow-y:auto;padding:18px 22px;outline:none;font-size:16px;line-height:1.7;color:#0f172a;caret-color:#059669;}',
        '.dark .huv-rte-content{color:#e2e8f0;}',
        '.huv-rte-content:empty:before{content:attr(data-placeholder);color:#94a3b8;pointer-events:none;}',
        '.huv-rte-content h1{font-size:2em;font-weight:800;margin:.6em 0 .4em;line-height:1.2;}',
        '.huv-rte-content h2{font-size:1.5em;font-weight:700;margin:.6em 0 .4em;line-height:1.25;}',
        '.huv-rte-content h3{font-size:1.25em;font-weight:700;margin:.6em 0 .4em;}',
        '.huv-rte-content h4{font-size:1.1em;font-weight:700;margin:.6em 0 .4em;}',
        '.huv-rte-content p{margin:0 0 .8em;}',
        '.huv-rte-content ul,.huv-rte-content ol{padding-left:1.6em;margin:0 0 .8em;}',
        '.huv-rte-content ul{list-style:disc;}',
        '.huv-rte-content ol{list-style:decimal;}',
        '.huv-rte-content blockquote{border-left:4px solid #10b981;margin:0 0 .8em;padding:.4em 1em;background:#f0fdf4;color:#065f46;}',
        '.dark .huv-rte-content blockquote{background:#052e1b;color:#a7f3d0;}',
        '.huv-rte-content pre{background:#0f172a;color:#e2e8f0;padding:12px 14px;border-radius:6px;overflow-x:auto;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:14px;margin:0 0 .8em;}',
        '.huv-rte-content code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.92em;background:#f1f5f9;padding:1px 5px;border-radius:4px;}',
        '.dark .huv-rte-content code{background:#334155;}',
        '.huv-rte-content pre code{background:transparent;padding:0;}',
        '.huv-rte-content a{color:#059669;text-decoration:underline;}',
        '.huv-rte-content img{max-width:100%;height:auto;border-radius:6px;margin:6px 0;}',
        '.huv-rte-content table{border-collapse:collapse;margin:0 0 .8em;width:100%;}',
        '.huv-rte-content table td,.huv-rte-content table th{border:1px solid #cbd5e1;padding:7px 10px;min-width:32px;}',
        '.dark .huv-rte-content table td,.dark .huv-rte-content table th{border-color:#475569;}',
        '.huv-rte-content table th{background:#f1f5f9;font-weight:700;}',
        '.dark .huv-rte-content table th{background:#334155;}',
        '.huv-rte-content hr{border:0;border-top:2px solid #cbd5e1;margin:1.2em 0;}',
        '.dark .huv-rte-content hr{border-color:#475569;}',
        '.huv-rte-status{display:flex;justify-content:space-between;align-items:center;padding:4px 10px;border-top:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:12px;}',
        '.dark .huv-rte-status{border-color:#334155;background:#0f172a;color:#94a3b8;}',
        '.huv-rte-pop{position:absolute;top:calc(100% + 4px);left:0;background:#fff;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.18);padding:10px;z-index:60;min-width:200px;}',
        '.dark .huv-rte-pop{background:#1e293b;border-color:#475569;color:#e2e8f0;}',
        '.huv-rte-pop-grid{display:grid;grid-template-columns:repeat(8,24px);gap:4px;}',
        '.huv-rte-swatch{width:24px;height:24px;border-radius:4px;border:1px solid rgba(0,0,0,.15);cursor:pointer;}',
        '.huv-rte-swatch:hover{outline:2px solid #10b981;}',
        '.huv-rte-char{width:28px;height:28px;border:1px solid #e2e8f0;border-radius:4px;background:#fff;cursor:pointer;font-size:15px;color:#334155;}',
        '.dark .huv-rte-char{background:#0f172a;border-color:#334155;color:#e2e8f0;}',
        '.huv-rte-char:hover{background:#d1fae5;}',
        '.huv-rte-emoji{width:32px;height:32px;border:0;border-radius:6px;background:#fff;cursor:pointer;font-size:18px;display:inline-flex;align-items:center;justify-content:center;}',
        '.dark .huv-rte-emoji{background:#0f172a;}',
        '.huv-rte-emoji:hover{background:#d1fae5;}',
        '.huv-rte-findmark{background:#fef08a;color:#422006;padding:1px 2px;border-radius:3px;}',
        '.huv-rte-findmark.active{background:#0C3B2E;color:#fff;}',
        '.huv-rte-field{width:100%;box-sizing:border-box;margin-bottom:8px;padding:7px 9px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;background:#fff;color:#0f172a;}',
        '.dark .huv-rte-field{background:#0f172a;color:#e2e8f0;border-color:#475569;}',
        '.huv-rte-label{display:flex;align-items:center;gap:6px;font-size:13px;margin-bottom:8px;color:inherit;}',
        '.huv-rte-row{display:flex;gap:6px;justify-content:flex-end;}',
        '.huv-rte-btn-primary{padding:6px 14px;border:0;border-radius:6px;background:#0C3B2E;color:#fff;font-weight:600;font-size:13px;cursor:pointer;}',
        '.huv-rte-btn-primary:hover{background:#072A20;}',
        '.huv-rte-btn-ghost{padding:6px 14px;border:1px solid #cbd5e1;border-radius:6px;background:transparent;color:#334155;font-size:13px;cursor:pointer;}',
        '.dark .huv-rte-btn-ghost{border-color:#475569;color:#e2e8f0;}',
        '.huv-rte-table-grid{display:grid;grid-template-columns:repeat(6,22px);gap:3px;}',
        '.huv-rte-cell{width:22px;height:22px;border:1px solid #cbd5e1;background:#f8fafc;cursor:pointer;}',
        '.dark .huv-rte-cell{border-color:#475569;background:#0f172a;}',
        '.huv-rte-cell.on{background:#10b981;}',
        '.huv-rte.src .huv-rte-content{display:none;}',
        '.huv-rte.src .huv-rte-src{display:block;}',
        '.huv-rte-src{display:none;width:100%;box-sizing:border-box;min-height:320px;max-height:65vh;padding:16px;border:0;resize:vertical;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;line-height:1.6;background:#0f172a;color:#e2e8f0;outline:none;}',
        '.huv-rte.full{position:fixed;inset:0;z-index:9999;width:100vw;height:100vh;border:0;border-radius:0;margin:0;box-shadow:none;}',
        '.huv-rte.full .huv-rte-content,.huv-rte.full .huv-rte-src{max-height:none;height:calc(100vh - 110px);width:100%;}',
        'body.huv-rte-lock{overflow:hidden !important;}',
        '.huv-rte-dd{position:relative;display:inline-flex;align-items:center;}',
        '.huv-rte-dd-btn{gap:1px;padding:0 4px;}',
        '.huv-rte-dd-btn .huv-rte-chev{width:12px;height:12px;opacity:.55;pointer-events:none;}',
        '.huv-rte-dd-list{position:absolute;top:calc(100% + 6px);left:0;background:#fff;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.18);padding:6px;z-index:70;min-width:190px;max-height:70vh;overflow-y:auto;}',
        '.dark .huv-rte-dd-list{background:#1e293b;border-color:#475569;}',
        '.huv-rte-dd-item{display:flex;align-items:center;gap:10px;width:100%;padding:7px 9px;border:0;background:transparent;border-radius:6px;cursor:pointer;font:inherit;font-size:13px;color:#334155;text-align:left;}',
        '.dark .huv-rte-dd-item{color:#e2e8f0;}',
        '.huv-rte-dd-item:hover{background:#d1fae5;color:#065f46;}',
        '.dark .huv-rte-dd-item:hover{background:#064e3b;color:#a7f3d0;}',
        '.huv-rte-dd-ico{width:30px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;color:#334155;}',
        '.dark .huv-rte-dd-ico{color:#cbd5e1;}',
        '.huv-rte-dd-item:hover .huv-rte-dd-ico{color:#065f46;}',
        '.dark .huv-rte-dd-item:hover .huv-rte-dd-ico{color:#a7f3d0;}',
        '.huv-rte-dd-ico svg{width:16px;height:16px;}',
        '.huv-rte-glyph{font-weight:800;line-height:1;display:inline-block;font-family:inherit;}',
        '.huv-rte.dragover{outline:2px dashed #10b981;outline-offset:-2px;}'
    ].join('\n');

    var FONTS = ['Default', '"Google Sans", Roboto, Arial, sans-serif', 'Arial, Helvetica, sans-serif', 'Georgia, serif', '"Times New Roman", Times, serif', '"Courier New", monospace', 'Verdana, Geneva, sans-serif', '"Trebuchet MS", sans-serif', '"Work Sans", Arial, sans-serif'];
    var LINE_HEIGHTS = ['Default', '1', '1.2', '1.5', '1.7', '2', '2.5'];
    var SIZES = [
        { label: 'S', v: '2', title: 'Small' }, { label: 'N', v: '3', title: 'Normal' }, { label: 'M', v: '4', title: 'Medium' },
        { label: 'L', v: '5', title: 'Large' }, { label: 'XL', v: '6', title: 'Huge' }, { label: '2XL', v: '7', title: 'Extra large' }
    ];
    var COLORS = ['#0f172a', '#334155', '#64748b', '#dc2626', '#ea580c', '#d97706', '#16a34a', '#059669', '#0891b2', '#2563eb', '#7c3aed', '#db2777', '#ffffff', '#f1f5f9', '#fef3c7', '#d1fae5'];
    var CHARS = ['\u00A9', '\u00AE', '\u2122', '\u2192', '\u2190', '\u2191', '\u2193', '\u2022', '\u2026', '\u2018', '\u2019', '\u201C', '\u201D', '\u2013', '\u2014', '\u00D7', '\u00F7', '\u2260', '\u2264', '\u2265', '\u00B0', '\u00B1', '\u221E', '\u20AC', '\u00A3', '\u00A5', '\u20B9', '\u0024', '\u03B1', '\u03B2', '\u03C0', '\u221A'];
    var EMOJIS = ['\uD83D\uDE00','\uD83D\uDE02','\uD83D\uDE0D','\uD83D\uDC4D','\uD83D\uDC4F','\u2764\uFE0F','\uD83D\uDD25','\u2B50','\u2705','\u274C','\uD83D\uDCA1','\uD83D\uDE80','\uD83C\uDF89','\uD83D\uDCD6','\uD83D\uDCC5','\uD83D\uDCE2','\u26A0\uFE0F','\uD83D\uDCAC','\uD83D\uDCDA','\uD83C\uDFAF','\uD83E\uDD1D','\uD83D\uDE4F','\u2728','\uD83D\uDC99','\uD83D\uDFE2','\uD83D\uDFE1','\uD83D\uDCF8','\uD83C\uDF0D','\uD83D\uDCBB','\uD83D\uDD0D','\u270F\uFE0F','\uD83D\uDCC4'];
    var AUTOSAVE_KEY_PREFIX = 'huv-rte-autosave-';

    function closeAllPops(root) {
        root.querySelectorAll('.huv-rte-pop').forEach(function (p) { p.remove(); });
        root.querySelectorAll('.huv-rte-dd-list').forEach(function (l) { l.remove(); });
        root.querySelectorAll('.huv-rte-dd-btn.active').forEach(function (b) { b.classList.remove('active'); });
    }

    function pop(anchor, root, html, onOpen) {
        closeAllPops(root);
        var popEl = document.createElement('div');
        popEl.className = 'huv-rte-pop';
        popEl.innerHTML = html;
        var tb = root.querySelector('.huv-rte-toolbar');
        tb.appendChild(popEl);
        var r = anchor.getBoundingClientRect();
        var tr = tb.getBoundingClientRect();
        var left = Math.min(r.left - tr.left, tr.width - popEl.offsetWidth - 8);
        popEl.style.left = Math.max(0, left) + 'px';
        if (onOpen) onOpen(popEl);
        return popEl;
    }

    function sanitizeHTML(html) {
        var t = document.createElement('div');
        t.innerHTML = html;
        t.querySelectorAll('script,style,iframe,object,embed,form,input,button,link,meta').forEach(function (n) { n.remove(); });
        t.querySelectorAll('*').forEach(function (n) {
            [].slice.call(n.attributes).forEach(function (a) {
                var name = a.name.toLowerCase();
                if (name.indexOf('on') === 0 || (a.value && a.value.trim().toLowerCase().indexOf('javascript:') === 0)) {
                    n.removeAttribute(a.name);
                }
            });
        });
        return t.innerHTML;
    }

    function HuvantiEditor(textarea, options) {
        var self = this;
        options = options || {};
        this.textarea = textarea;
        this.savedRange = null;

        var wrap = document.createElement('div');
        wrap.className = 'huv-rte';
        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(wrap, textarea);

        var toolbar = document.createElement('div');
        toolbar.className = 'huv-rte-toolbar';

        var content = document.createElement('div');
        content.className = 'huv-rte-content';
        content.setAttribute('contenteditable', 'true');
        content.setAttribute('role', 'textbox');
        content.setAttribute('aria-multiline', 'true');
        content.setAttribute('data-placeholder', options.placeholder || 'Start writing your story...');
        content.innerHTML = sanitizeHTML(textarea.value) || '';

        var srcArea = document.createElement('textarea');
        srcArea.className = 'huv-rte-src';
        srcArea.setAttribute('aria-label', 'HTML source view');
        srcArea.value = textarea.value;

        var status = document.createElement('div');
        status.className = 'huv-rte-status';

        wrap.appendChild(toolbar);
        wrap.appendChild(content);
        wrap.appendChild(srcArea);
        wrap.appendChild(status);

        this.wrap = wrap; this.content = content; this.srcArea = srcArea;

        // ---------------- helpers ----------------
        function cmd(name, value) {
            content.focus();
            document.execCommand(name, false, value || null);
            sync();
            updateStates();
        }

        function sync() {
            if (wrap.classList.contains('src')) {
                textarea.value = srcArea.value;
            } else {
                var html = content.innerHTML;
                if (html === '<br>' || html === '<div><br></div>') html = '';
                // Semantic tags instead of presentational ones (better SEO).
                html = html.replace(/<b(\s|>)/g, '<strong$1').replace(/<\/b>/g, '</strong>')
                           .replace(/<i(\s|>)/g, '<em$1').replace(/<\/i>/g, '</em>');
                textarea.value = html;
                srcArea.value = html;
            }
            updateCount();
        }

        function wordCount() {
            var text = (wrap.classList.contains('src') ? srcArea.value : content.innerText) || '';
            var m = text.trim().match(/\S+/g);
            return m ? m.length : 0;
        }

        function updateCount() {
            var wc = status.querySelector('.huv-rte-wc');
            if (wc) wc.textContent = wordCount() + ' words';
        }

        function updateStates() {
            try {
                setA('bold', document.queryCommandState('bold'));
                setA('italic', document.queryCommandState('italic'));
                setA('underline', document.queryCommandState('underline'));
                setA('strike', document.queryCommandState('strikeThrough'));
                setA('ul', document.queryCommandState('insertUnorderedList'));
                setA('ol', document.queryCommandState('insertOrderedList'));
                var blocks = ['alignL', 'alignC', 'alignR', 'alignJ'];
                var justify = document.queryCommandState('justifyCenter');
                var right = document.queryCommandState('justifyRight');
                var full = document.queryCommandState('justifyFull');
                setA('alignL', !justify && !right && !full);
                setA('alignC', justify); setA('alignR', right); setA('alignJ', full);
            } catch (e) { /* queryCommandState can throw on some selections */ }
        }

        function setA(name, on) {
            var b = toolbar.querySelector('[data-cmd="' + name + '"]');
            if (b) b.classList.toggle('active', !!on);
        }

        function restoreRange() {
            if (self.savedRange) {
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(self.savedRange);
            }
            content.focus();
        }

        function insertHTML(html) {
            restoreRange();
            document.execCommand('insertHTML', false, html);
            sync();
        }

        // ---------------- toolbar ----------------
        function btn(name, svgName, title, handler) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'huv-rte-btn';
            b.setAttribute('data-cmd', name);
            b.title = title;
            b.setAttribute('aria-label', title);
            b.innerHTML = icon(svgName);
            b.addEventListener('mousedown', function (e) { e.preventDefault(); });
            b.addEventListener('click', handler);
            toolbar.appendChild(b);
            return b;
        }
        function sep() {
            var s = document.createElement('span');
            s.className = 'huv-rte-sep';
            toolbar.appendChild(s);
        }
        function select(name, title, items, handler) {
            var s = document.createElement('select');
            s.className = 'huv-rte-select';
            s.title = title;
            s.setAttribute('data-cmd', name);
            items.forEach(function (it) {
                var o = document.createElement('option');
                o.value = it.v;
                o.textContent = it.label;
                s.appendChild(o);
            });
            s.addEventListener('change', function () { handler(s.value); s.blur(); });
            toolbar.appendChild(s);
            return s;
        }
        /**
         * Icon-driven dropdown button (professional editor UI): a compact
         * toolbar button that opens a styled list of icon + label rows.
         * Replaces the plain text <select> controls for paragraph/heading
         * and font size, which looked poor with labels like "¶ P" or "S/N/M".
         */
        function dropdown(name, title, triggerIcon, items, handler) {
            var dd = document.createElement('span');
            dd.className = 'huv-rte-dd';
            dd.setAttribute('data-cmd', name);
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'huv-rte-btn huv-rte-dd-btn';
            b.title = title;
            b.setAttribute('aria-label', title);
            b.setAttribute('aria-haspopup', 'true');
            b.innerHTML = icon(triggerIcon) + '<svg class="huv-rte-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
            b.addEventListener('mousedown', function (e) { e.preventDefault(); });
            b.addEventListener('click', function () {
                var existing = dd.querySelector('.huv-rte-dd-list');
                if (existing) { existing.remove(); b.classList.remove('active'); return; }
                closeAllPops(wrap);
                var list = document.createElement('div');
                list.className = 'huv-rte-dd-list';
                list.setAttribute('role', 'menu');
                items.forEach(function (it) {
                    var row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'huv-rte-dd-item';
                    row.setAttribute('role', 'menuitem');
                    row.innerHTML = '<span class="huv-rte-dd-ico">' + it.icon + '</span><span>' + it.label + '</span>';
                    row.addEventListener('click', function () {
                        list.remove();
                        b.classList.remove('active');
                        handler(it.v);
                    });
                    list.appendChild(row);
                });
                dd.appendChild(list);
                b.classList.add('active');
            });
            dd.appendChild(b);
            toolbar.appendChild(dd);
            return dd;
        }

        btn('undo', 'undo', 'Undo (Ctrl+Z)', function () { cmd('undo'); });
        btn('redo', 'redo', 'Redo (Ctrl+Y)', function () { cmd('redo'); });
        sep();
        // Paragraph / heading dropdown — icon driven, Google-Docs style rows
        dropdown('block', 'Paragraph & headings', 'pilcrow', [
            { v: 'p',          icon: icon('pilcrow'), label: 'Paragraph' },
            { v: 'h1',         icon: '<span class="huv-rte-glyph" style="font-size:15px">H1</span>', label: 'Heading 1' },
            { v: 'h2',         icon: '<span class="huv-rte-glyph" style="font-size:13px">H2</span>', label: 'Heading 2' },
            { v: 'h3',         icon: '<span class="huv-rte-glyph" style="font-size:12px">H3</span>', label: 'Heading 3' },
            { v: 'h4',         icon: '<span class="huv-rte-glyph" style="font-size:11px">H4</span>', label: 'Heading 4' },
            { v: 'blockquote', icon: icon('quote'), label: 'Quote' },
            { v: 'pre',        icon: icon('code'), label: 'Code block' }
        ], function (v) {
            content.focus();
            document.execCommand('formatBlock', false, v === 'pre' ? 'pre' : v);
            sync(); updateStates();
        });
        select('font', 'Font family', FONTS.map(function (f) { return { label: f === 'Default' ? 'Default' : f.split(',')[0].replace(/"/g, '').replace('Google Sans','Google Sans'), v: f }; }), function (v) {
            if (v === 'Default') { cmd('removeFormat'); return; }
            cmd('fontName', v);
        });
        // Font size dropdown — visual "A" scale instead of cryptic S/N/M/L letters
        dropdown('size', 'Text size', 'type', [
            { v: '2', icon: '<span class="huv-rte-glyph" style="font-size:11px">A</span>', label: 'Small' },
            { v: '3', icon: '<span class="huv-rte-glyph" style="font-size:14px">A</span>', label: 'Normal' },
            { v: '4', icon: '<span class="huv-rte-glyph" style="font-size:17px">A</span>', label: 'Medium' },
            { v: '5', icon: '<span class="huv-rte-glyph" style="font-size:21px">A</span>', label: 'Large' },
            { v: '6', icon: '<span class="huv-rte-glyph" style="font-size:25px">A</span>', label: 'Huge' },
            { v: '7', icon: '<span class="huv-rte-glyph" style="font-size:29px">A</span>', label: 'Extra large' }
        ], function (v) { cmd('fontSize', v); });
        sep();
        btn('bold', 'bold', 'Bold (Ctrl+B)', function () { cmd('bold'); });
        btn('italic', 'italic', 'Italic (Ctrl+I)', function () { cmd('italic'); });
        btn('underline', 'underline', 'Underline (Ctrl+U)', function () { cmd('underline'); });
        btn('strike', 'strike', 'Strikethrough', function () { cmd('strikeThrough'); });
        btn('inlineCode', 'codeInline', 'Inline code', function () {
            restoreRange();
            var sel = window.getSelection();
            var text = sel && sel.toString() ? sel.toString() : '';
            if (text) {
                insertHTML('<code>' + text.replace(/</g, '&lt;') + '</code>');
            } else {
                var code = document.createElement('code');
                code.textContent = 'code';
                code.style.background = '#f1f5f9';
                code.style.padding = '1px 5px';
                code.style.borderRadius = '4px';
                insertHTML(code.outerHTML + '&nbsp;');
            }
        });
        btn('sup', 'sup', 'Superscript', function () { cmd('superscript'); });
        btn('sub', 'sub', 'Subscript', function () { cmd('subscript'); });
        sep();
        btn('foreColor', 'palette', 'Text color', function () {
            var anchor = this;
            var html = '<div class="huv-rte-pop-grid">' + COLORS.map(function (c) {
                return '<button type="button" class="huv-rte-swatch" data-color="' + c + '" style="background:' + c + '" title="' + c + '"></button>';
            }).join('') + '</div>' +
                '<label class="huv-rte-label" style="margin-top:10px"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 0-9 5 10 10 0 0 1 9-5z"/></svg> <input type="color" class="huv-rte-custom" value="#059669" style="width:40px;height:26px;border:0;background:none;padding:0;cursor:pointer"></label>';
            pop(anchor, wrap, html, function (p) {
                p.querySelectorAll('.huv-rte-swatch').forEach(function (sw) {
                    sw.addEventListener('click', function () { closeAllPops(wrap); cmd('foreColor', sw.getAttribute('data-color')); });
                });
                p.querySelector('.huv-rte-custom').addEventListener('change', function () { closeAllPops(wrap); cmd('foreColor', this.value); });
                p.querySelector('.huv-rte-custom').addEventListener('input', function () { cmd('foreColor', this.value); });
            });
        });
        btn('hiliteColor', 'smile', 'Highlight', function () {
            var anchor = this;
            var html = '<div class="huv-rte-pop-grid">' + COLORS.map(function (c) {
                return '<button type="button" class="huv-rte-swatch" data-color="' + c + '" style="background:' + c + '" title="' + c + '"></button>';
            }).join('') + '</div>';
            pop(anchor, wrap, html, function (p) {
                p.querySelectorAll('.huv-rte-swatch').forEach(function (sw) {
                    sw.addEventListener('click', function () { closeAllPops(wrap); cmd('hiliteColor', sw.getAttribute('data-color')); });
                });
            });
        });
        sep();
        btn('ul', 'ul', 'Bullet list', function () { cmd('insertUnorderedList'); });
        btn('ol', 'ol', 'Numbered list', function () { cmd('insertOrderedList'); });
        btn('outdent', 'outdent', 'Outdent', function () { cmd('outdent'); });
        btn('indent', 'indent', 'Indent', function () { cmd('indent'); });
        sep();
        btn('alignL', 'alignL', 'Align left', function () { cmd('justifyLeft'); });
        btn('alignC', 'alignC', 'Align center', function () { cmd('justifyCenter'); });
        btn('alignR', 'alignR', 'Align right', function () { cmd('justifyRight'); });
        btn('alignJ', 'alignJ', 'Justify', function () { cmd('justifyFull'); });
        sep();
        btn('link', 'link', 'Insert link (Ctrl+K)', function () {
            var anchor = this;
            restoreRange();
            var sel = window.getSelection();
            var selectedText = sel && sel.toString() ? sel.toString() : '';
            var html =
                '<label class="huv-rte-label">URL</label>' +
                '<input type="url" class="huv-rte-field" data-role="url" placeholder="https://example.com">' +
                '<label class="huv-rte-label"><input type="checkbox" data-role="blank" checked> Open in new tab</label>' +
                '<div class="huv-rte-row">' +
                '<button type="button" class="huv-rte-btn-ghost" data-role="remove">Remove link</button>' +
                '<button type="button" class="huv-rte-btn-primary" data-role="apply">Apply</button></div>';
            pop(anchor, wrap, html, function (p) {
                var url = p.querySelector('[data-role="url"]');
                url.focus();
                p.querySelector('[data-role="apply"]').addEventListener('click', function () {
                    var v = url.value.trim();
                    if (!v) return;
                    if (!/^(https?:\/\/|mailto:|#|\/)/i.test(v)) v = 'https://' + v;
                    closeAllPops(wrap);
                    restoreRange();
                    if (selectedText) {
                        document.execCommand('createLink', false, v);
                        var a = null, node = window.getSelection().anchorNode;
                        if (node) {
                            if (node.nodeType === 3) node = node.parentNode;
                            a = node.closest ? node.closest('a') : null;
                        }
                        if (a && p.querySelector('[data-role="blank"]').checked) {
                            a.setAttribute('target', '_blank');
                            a.setAttribute('rel', 'noopener noreferrer');
                        }
                    } else {
                        insertHTML('<a href="' + v.replace(/"/g, '&quot;') + '"' + (p.querySelector('[data-role="blank"]').checked ? ' target="_blank" rel="noopener noreferrer"' : '') + '>' + v + '</a>');
                    }
                    sync();
                });
                p.querySelector('[data-role="remove"]').addEventListener('click', function () {
                    closeAllPops(wrap);
                    restoreRange();
                    document.execCommand('unlink');
                    sync();
                });
            });
        });
        btn('image', 'image', 'Insert image (upload / paste / drag)', function () {
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.addEventListener('change', function () {
                if (input.files && input.files[0]) self.insertImageFile(input.files[0]);
            });
            input.click();
        });
        btn('table', 'table', 'Insert table', function () {
            var anchor = this;
            var html = '<div class="huv-rte-table-grid">';
            for (var i = 0; i < 24; i++) html += '<button type="button" class="huv-rte-cell" data-r="' + (Math.floor(i / 6) + 1) + '" data-c="' + (i % 6 + 1) + '"></button>';
            html += '</div><div class="huv-rte-label" style="margin-top:8px" data-role="info">1 x 1 table</div>';
            pop(anchor, wrap, html, function (p) {
                var cells = p.querySelectorAll('.huv-rte-cell');
                var info = p.querySelector('[data-role="info"]');
                cells.forEach(function (cell) {
                    cell.addEventListener('mouseenter', function () {
                        var r = +cell.getAttribute('data-r'), c = +cell.getAttribute('data-c');
                        cells.forEach(function (o) {
                            o.classList.toggle('on', +o.getAttribute('data-r') <= r && +o.getAttribute('data-c') <= c);
                        });
                        info.textContent = r + ' x ' + c + ' table';
                    });
                    cell.addEventListener('click', function () {
                        var r = +cell.getAttribute('data-r'), c = +cell.getAttribute('data-c');
                        var t = '<table><tbody>';
                        for (var i = 0; i < r; i++) {
                            t += '<tr>';
                            for (var j = 0; j < c; j++) t += (i === 0 ? '<th>Heading</th>' : '<td>Cell</td>');
                            t += '</tr>';
                        }
                        t += '</tbody></table><p><br></p>';
                        closeAllPops(wrap);
                        insertHTML(t);
                    });
                });
            });
        });
        btn('hr', 'hr', 'Horizontal line', function () { cmd('insertHorizontalRule'); });
        btn('chars', 'smile', 'Special characters', function () {
            var anchor = this;
            var html = '<div class="huv-rte-pop-grid" style="grid-template-columns:repeat(8,28px)">' + CHARS.map(function (ch) {
                return '<button type="button" class="huv-rte-char">' + ch + '</button>';
            }).join('') + '</div>';
            pop(anchor, wrap, html, function (p) {
                p.querySelectorAll('.huv-rte-char').forEach(function (cb) {
                    cb.addEventListener('click', function () { closeAllPops(wrap); insertHTML(cb.textContent); });
                });
            });
        });
        btn('emoji', 'emoji', 'Emoji', function () {
            var anchor = this;
            var html = '<div class="huv-rte-pop-grid" style="grid-template-columns:repeat(8,32px)">' + EMOJIS.map(function (e) {
                return '<button type="button" class="huv-rte-emoji">' + e + '</button>';
            }).join('') + '</div>';
            pop(anchor, wrap, html, function (p) {
                p.querySelectorAll('.huv-rte-emoji').forEach(function (b) {
                    b.addEventListener('click', function () { closeAllPops(wrap); insertHTML(b.textContent); });
                });
            });
        });
        btn('find', 'find', 'Find and replace (Ctrl+F)', function () {
            var anchor = this;
            var html =
                '<input type="text" class="huv-rte-field" data-role="find" placeholder="Find\u2026">' +
                '<input type="text" class="huv-rte-field" data-role="replace" placeholder="Replace with">' +
                '<div class="huv-rte-row">' +
                '<button type="button" class="huv-rte-btn-ghost" data-role="next">Next</button>' +
                '<button type="button" class="huv-rte-btn-ghost" data-role="all">Replace all</button>' +
                '<button type="button" class="huv-rte-btn-primary" data-role="one">Replace</button></div>';
            pop(anchor, wrap, html, function (p) {
                var findEl = p.querySelector('[data-role="find"]');
                var repEl = p.querySelector('[data-role="replace"]');
                var marks = [];
                var idx = -1;
                function clear() { content.querySelectorAll('.huv-rte-findmark').forEach(function (m) { var t = document.createTextNode(m.textContent); m.parentNode.replaceChild(t, m); }); content.normalize(); marks = []; idx = -1; }
                function doFind() {
                    clear();
                    var q = findEl.value;
                    if (!q) return;
                    var walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT, null);
                    var nodes = [];
                    while (walker.nextNode()) nodes.push(walker.currentNode);
                    nodes.forEach(function (tn) {
                        var text = tn.nodeValue;
                        var pos = text.toLowerCase().indexOf(q.toLowerCase());
                        if (pos === -1) return;
                        var frag = document.createDocumentFragment();
                        var last = 0;
                        while (pos !== -1) {
                            frag.appendChild(document.createTextNode(text.slice(last, pos)));
                            var mark = document.createElement('mark');
                            mark.className = 'huv-rte-findmark';
                            mark.textContent = text.slice(pos, pos + q.length);
                            frag.appendChild(mark);
                            marks.push(mark);
                            last = pos + q.length;
                            pos = text.toLowerCase().indexOf(q.toLowerCase(), last);
                        }
                        frag.appendChild(document.createTextNode(text.slice(last)));
                        tn.parentNode.replaceChild(frag, tn);
                    });
                }
                findEl.addEventListener('input', doFind);
                p.querySelector('[data-role="next"]').addEventListener('click', function () {
                    if (!marks.length) doFind();
                    if (!marks.length) return;
                    if (idx >= 0) marks[idx].classList.remove('active');
                    idx = (idx + 1) % marks.length;
                    marks[idx].classList.add('active');
                    marks[idx].scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                p.querySelector('[data-role="one"]').addEventListener('click', function () {
                    if (idx >= 0 && marks[idx]) {
                        marks[idx].textContent = repEl.value;
                        marks[idx].classList.remove('active');
                        marks[idx].outerHTML = marks[idx].textContent;
                        marks.splice(idx, 1); idx--;
                        sync();
                    }
                });
                p.querySelector('[data-role="all"]').addEventListener('click', function () {
                    marks.forEach(function (m) { m.textContent = repEl.value; m.outerHTML = m.textContent; });
                    marks = []; idx = -1; sync();
                });
                findEl.focus();
                p.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAllPops(wrap); });
            });
        });
        select('lh', 'Line height', LINE_HEIGHTS.map(function (h) { return { label: h === 'Default' ? '↕' : h, v: h }; }), function (v) {
            restoreRange();
            var blocks = content.querySelectorAll('p, h1, h2, h3, h4, li, blockquote, pre');
            if (blocks.length === 0) blocks = [content];
            var sel = window.getSelection();
            var targetBlocks = [];
            if (sel && sel.rangeCount && !sel.isCollapsed && content.contains(sel.anchorNode)) {
                blocks.forEach(function(b){ if (sel.containsNode(b, true) || b.contains(sel.anchorNode)) targetBlocks.push(b); });
                if (targetBlocks.length === 0 && sel.anchorNode) {
                    var anc = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
                    var blk = anc.closest ? anc.closest('p, h1, h2, h3, h4, li, blockquote, pre') : null;
                    if (blk && content.contains(blk)) targetBlocks = [blk];
                }
            }
            if (targetBlocks.length === 0) targetBlocks = Array.prototype.slice.call(blocks);
            targetBlocks.forEach(function(b){ b.style.lineHeight = (v === 'Default' ? '' : v); if (!b.style.lineHeight) b.removeAttribute('style'); });
            if (targetBlocks.indexOf(content) === -1) content.style.lineHeight = '';
            sync();
        });
        btn('codeBlock', 'code', 'Code block', function () {
            content.focus();
            var sel = window.getSelection();
            var text = sel && sel.toString() ? sel.toString() : '';
            insertHTML('<pre><code>' + (text || '// code').replace(/</g, '&lt;') + '</code></pre><p><br></p>');
        });
        btn('selectAll', 'selectAll', 'Select all (Ctrl+A)', function () { content.focus(); document.execCommand('selectAll'); });
        btn('removeFormat', 'eraser', 'Clear formatting', function () {
            cmd('removeFormat');
            content.focus();
            document.execCommand('unlink');
            content.querySelectorAll('[style*="line-height"]').forEach(function(el){ el.style.lineHeight=''; if(!el.getAttribute('style')) el.removeAttribute('style'); });
            content.style.lineHeight = '';
            sync();
        });
        sep();
        btn('src', 'source', 'HTML source view', function () {
            var isSrc = wrap.classList.toggle('src');
            if (isSrc) { srcArea.value = content.innerHTML; srcArea.focus(); }
            else { content.innerHTML = sanitizeHTML(srcArea.value); content.focus(); }
            this.classList.toggle('active', isSrc);
            sync();
        });
        // Fullscreen: the editor element is MOVED to document.body while the
        // overlay is open and put back to its exact original position on
        // close. This defeats every ancestor that can break position:fixed
        // (transforms, backdrop-filter, overflow clipping, narrow grid
        // columns), so fullscreen now ALWAYS spans the real viewport edge to
        // edge — fixing the "fullscreen is not really fullscreen" bug.
        var fsPlaceholder = null;
        function toggleFullscreen() {
            var on = !wrap.classList.contains('full');
            var fullBtn = toolbar.querySelector('[data-cmd="full"]');
            if (on) {
                fsPlaceholder = document.createComment('huv-rte-fullscreen-anchor');
                if (wrap.parentNode) wrap.parentNode.insertBefore(fsPlaceholder, wrap);
                document.body.appendChild(wrap);
                document.body.classList.add('huv-rte-lock');
                wrap.classList.add('full');
            } else {
                wrap.classList.remove('full');
                document.body.classList.remove('huv-rte-lock');
                if (fsPlaceholder && fsPlaceholder.parentNode) {
                    fsPlaceholder.parentNode.insertBefore(wrap, fsPlaceholder);
                    fsPlaceholder.parentNode.removeChild(fsPlaceholder);
                }
                fsPlaceholder = null;
            }
            if (fullBtn) {
                fullBtn.innerHTML = icon(on ? 'shrink' : 'expand');
                fullBtn.classList.toggle('active', on);
            }
            closeAllPops(wrap);
            content.focus();
        }
        btn('full', 'expand', 'Fullscreen', function () { toggleFullscreen(); });

        status.innerHTML = '<span class="huv-rte-wc"></span><span>Huvanti Editor · <a href="#" data-role="help" style="color:inherit;text-decoration:underline">Shortcuts</a></span>';
        status.querySelector('[data-role="help"]').addEventListener('click', function (e) {
            e.preventDefault();
            alert('Shortcuts:\nCtrl+B  Bold\nCtrl+I  Italic\nCtrl+U  Underline\nCtrl+K  Link\nCtrl+F  Find and replace\nCtrl+Z  Undo\nCtrl+Y  Redo\nTab     Indent  ·  Shift+Tab Outdent');
        });
        updateCount();

        // ---------------- events ----------------
        content.addEventListener('input', sync);
        content.addEventListener('keyup', updateStates);
        content.addEventListener('mouseup', updateStates);

        document.addEventListener('selectionchange', function () {
            var sel = window.getSelection();
            if (sel && sel.rangeCount && content.contains(sel.anchorNode)) {
                self.savedRange = sel.getRangeAt(0).cloneRange();
            }
        });

        content.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey) {
                var k = e.key.toLowerCase();
                if (k === 'b') { e.preventDefault(); cmd('bold'); }
                else if (k === 'i') { e.preventDefault(); cmd('italic'); }
                else if (k === 'u') { e.preventDefault(); cmd('underline'); }
                else if (k === 'k') { e.preventDefault(); var b = toolbar.querySelector('[data-cmd="link"]'); if (b) b.click(); }
                else if (k === 'f') { e.preventDefault(); var b = toolbar.querySelector('[data-cmd="find"]'); if (b) b.click(); }
                else if (k === 'y') { e.preventDefault(); cmd('redo'); }
            }
            if (e.key === 'Tab') {
                e.preventDefault();
                cmd(e.shiftKey ? 'outdent' : 'indent');
            }
            if (e.key === 'Escape') {
                // Escape also leaves fullscreen — standard editor behaviour.
                if (wrap.classList.contains('full')) toggleFullscreen();
                closeAllPops(wrap);
            }
        });

        // Paste: sanitize dangerous markup, keep formatting.
        content.addEventListener('paste', function (e) {
            var items = e.clipboardData && e.clipboardData.items;
            if (items) {
                for (var i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') === 0) {
                        e.preventDefault();
                        self.insertImageFile(items[i].getAsFile());
                        return;
                    }
                }
            }
            e.preventDefault();
            var html = e.clipboardData ? e.clipboardData.getData('text/html') : '';
            var text = e.clipboardData ? e.clipboardData.getData('text/plain') : '';
            if (html) { insertHTML(sanitizeHTML(html)); }
            else if (text) {
                var lines = text.split(/\n{2,}/);
                if (lines.length > 1) { insertHTML(lines.map(function (l) { return '<p>' + l.replace(/</g, '&lt;').replace(/\n/g, '<br>') + '</p>'; }).join('')); }
                else { document.execCommand('insertText', false, text); }
                sync();
            }
        });

        // Drag & drop images.
        ['dragover', 'dragleave', 'drop'].forEach(function (ev) {
            content.addEventListener(ev, function (e) {
                if (ev === 'dragover') { e.preventDefault(); wrap.classList.add('dragover'); }
                else if (ev === 'dragleave') { wrap.classList.remove('dragover'); }
                else {
                    e.preventDefault();
                    wrap.classList.remove('dragover');
                    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                        self.insertImageFile(e.dataTransfer.files[0]);
                    }
                }
            });
        });

        // Close popovers on outside click.
        document.addEventListener('mousedown', function (e) {
            if (!wrap.contains(e.target)) closeAllPops(wrap);
        });
        toolbar.addEventListener('mousedown', function (e) {
            if (!e.target.closest('.huv-rte-pop') && !e.target.closest('.huv-rte-select') && !e.target.closest('.huv-rte-dd')) {
                var p = toolbar.querySelector('.huv-rte-pop');
                if (p && !p.contains(e.target)) closeAllPops(wrap);
            }
        });

        srcArea.addEventListener('input', function () {
            textarea.value = srcArea.value;
            updateCount();
        });

        // Autosave every 3s to localStorage (recovered on reload).
        var form = textarea.closest('form');
        var autosaveKey = AUTOSAVE_KEY_PREFIX + (textarea.getAttribute('name') || textarea.id || 'editor');
        var draft = null;
        try { draft = localStorage.getItem(autosaveKey); } catch (e) {}
        if (draft && !textarea.value.trim() && !content.textContent.trim()) {
            content.innerHTML = sanitizeHTML(draft);
            sync();
        }
        setInterval(function () {
            try { localStorage.setItem(autosaveKey, wrap.classList.contains('src') ? srcArea.value : content.innerHTML); } catch (e) {}
        }, 3000);
        if (form) form.addEventListener('submit', function () { try { localStorage.removeItem(autosaveKey); } catch (e) {} });

        // Sync on submit (safety net besides live sync).
        if (form) {
            form.addEventListener('submit', function () {
                if (wrap.classList.contains('src')) { content.innerHTML = sanitizeHTML(srcArea.value); }
                sync();
            }, true);
        }

        sync();
    }

    HuvantiEditor.prototype.insertImageFile = function (file) {
        var self = this;
        if (!file || file.type.indexOf('image') !== 0) return;
        // Size guard: images placed in the text are embedded as base64 data
        // URLs inside the post body. A multi-megabyte phone photo would
        // silently bloat the form POST beyond the server's post_max_size and
        // end as a 419/500 error when the author clicks save. Embedded images
        // must stay lean; large photos belong in "Featured Image" (up to
        // 4 MB, auto-optimised server side).
        var MAX_EMBED_BYTES = 1.5 * 1024 * 1024;
        if (file.size > MAX_EMBED_BYTES) {
            window.alert('This image is ' + Math.round(file.size / 1024 / 1024 * 10) / 10 + ' MB. Images placed inside the text must be under 1.5 MB. Please resize it first, or use the "Featured Image" uploader which handles up to 4 MB.');
            return;
        }
        var reader = new FileReader();
        reader.onload = function () {
            self.content.focus();
            self.restoreRange();
            document.execCommand('insertHTML', false, '<img src="' + reader.result + '" alt="">');
            self.syncNow();
        };
        reader.readAsDataURL(file);
    };

    HuvantiEditor.prototype.restoreRange = function () {
        if (this.savedRange) {
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(this.savedRange);
        }
        this.content.focus();
    };

    HuvantiEditor.prototype.syncNow = function () {
        var html = this.content.innerHTML;
        if (html === '<br>' || html === '<div><br></div>') html = '';
        this.textarea.value = html;
        this.srcArea.value = html;
    };

    window.huvantiEditorInit = function (selector, options) {
        var el = document.querySelector(selector || '#editor');
        if (!el || el.tagName !== 'TEXTAREA') return null;
        if (el.dataset.huvantiRte === '1') return null;
        el.dataset.huvantiRte = '1';
        if (!document.querySelector('link[data-huv-google-sans]')) {
            var gs = document.createElement('link');
            gs.rel = 'stylesheet';
            gs.href = 'https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Google+Sans+Text:wght@400;500&display=swap';
            gs.setAttribute('data-huv-google-sans','1');
            document.head.appendChild(gs);
        }
        return new HuvantiEditor(el, options || {});
    };

    // Inject styles once.
    if (!document.getElementById('huv-rte-styles')) {
        var st = document.createElement('style');
        st.id = 'huv-rte-styles';
        st.textContent = STYLES;
        document.head.appendChild(st);
    }
})();
