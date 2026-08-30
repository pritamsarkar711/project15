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
 *   special chars | icon picker (60 crisp stroke icons — no emojis) |
 *   find and replace | code block | line height | clear formatting |
 *   source view | TRUE fullscreen (editor moves to <body> so no ancestor
 *   can clip it) | word count | keyboard shortcuts | autosave to localStorage
 *
 * Images in the text get a WordPress/Medium-style overlay: click an image
 * to select it → corner drag handles resize it with the mouse, the S/M/L
 * buttons apply preset sizes, the original-size button restores natural
 * width, and the pencil button opens an alt-text/title form (SEO).
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
        codeInline: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m10 9-3 3 3 3"/><path d="m14 15 3-3-3-3"/>',
        // Superscript / subscript: clean "X + plus" convention (the old
        // hand-drawn paths read like broken arrows).
        sup: '<path d="M5 19 15 5"/><path d="M15 19 5 5"/><path d="M19 3v6"/><path d="M16 6h6"/>',
        sub: '<path d="M5 5 15 19"/><path d="M15 5 5 19"/><path d="M19 15v6"/><path d="M16 18h6"/>',
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
        find: '<circle cx="11" cy="11" r="7"/><path d="M21 21l-3.5-3.5"/><path d="M8 11h6"/>',
        palette: '<circle cx="12" cy="12" r="10"/><circle cx="8" cy="10" r="1"/><circle cx="12" cy="7" r="1"/><circle cx="16" cy="10" r="1"/><circle cx="12" cy="16" r="1"/>',
        // The old Highlight and Special-chars buttons reused a SMILEY icon —
        // the main reason the toolbar "icons look bad". Proper glyphs now.
        highlighter: '<path d="m9 11-6 6v3h9l3-3"/><path d="m22 12-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"/>',
        atSign: '<circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/>',
        sparkles: '<path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>',
        trash: '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/>',
        pencil: '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
        maximize: '<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>'
    };

    /**
     * Icon library for the in-post icon picker (replaces the old emoji
     * picker — high-quality stroke icons that stay crisp at any size and
     * inherit the text colour, unlike emojis which render differently on
     * every platform). 24x24 lucide-style paths, grouped for the picker.
     */
    var ICONS = {
        star: '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        heart: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        bell: '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        bookmark: '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
        checkCircle: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        clock: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        flag: '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/>',
        info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
        alertTriangle: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        thumbsUp: '<path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/>',
        arrowRight: '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        arrowLeft: '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        arrowUp: '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
        arrowDown: '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
        cornerDownRight: '<polyline points="15 10 20 15 15 20"/><path d="M4 4v7a4 4 0 0 0 4 4h12"/>',
        repeat: '<path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>',
        externalLink: '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        trendingUp: '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        trendingDown: '<polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/>',
        shuffle: '<path d="M2 18h1.4c1.3 0 2.5-.6 3.3-1.7l6.1-8.6c.8-1.1 2-1.7 3.3-1.7H22"/><path d="m18 2 4 4-4 4"/><path d="M2 6h1.9c1.5 0 2.9.9 3.6 2.2"/><path d="M22 18h-5.9c-1.3 0-2.6-.7-3.3-1.8l-.5-.8"/><path d="m18 14 4 4-4 4"/>',
        camera: '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
        mail: '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        mapPin: '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        gift: '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/>',
        cart: '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
        creditCard: '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
        key: '<circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/>',
        lightbulb: '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
        pkg: '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
        moon: '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        cloud: '<path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"/>',
        leaf: '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>',
        flame: '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        zap: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        globe: '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
        droplet: '<path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>',
        play: '<polygon points="6 3 20 12 6 21 6 3"/>',
        music: '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        mic: '<path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/>',
        headphones: '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
        video: '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2"/>',
        picture: '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
        speaker: '<rect width="14" height="20" x="5" y="2" rx="2"/><circle cx="12" cy="14" r="3"/><path d="M12 7h.01"/>',
        briefcase: '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/>',
        barChart: '<line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/>',
        pieChart: '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
        dollar: '<line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        target: '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        award: '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
        rocket: '<path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/>',
        shield: '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
        users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'
    };

    var ICON_GROUPS = [
        { name: 'Popular', icons: ['star', 'heart', 'checkCircle', 'zap', 'flame', 'lightbulb', 'target', 'rocket', 'award', 'thumbsUp', 'bell', 'bookmark', 'flag', 'clock', 'alertTriangle', 'info'] },
        { name: 'Arrows', icons: ['arrowRight', 'arrowLeft', 'arrowUp', 'arrowDown', 'cornerDownRight', 'repeat', 'externalLink', 'trendingUp', 'trendingDown', 'shuffle'] },
        { name: 'Objects', icons: ['camera', 'mail', 'phone', 'mapPin', 'gift', 'cart', 'creditCard', 'key', 'pkg', 'bookmark', 'clock'] },
        { name: 'Nature', icons: ['sun', 'moon', 'cloud', 'leaf', 'flame', 'droplet', 'globe', 'zap'] },
        { name: 'Media', icons: ['play', 'music', 'mic', 'headphones', 'video', 'picture', 'speaker'] },
        { name: 'Business', icons: ['briefcase', 'barChart', 'pieChart', 'dollar', 'target', 'shield', 'users', 'lightbulb', 'rocket'] }
    ];

    function icon(name) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (SVG[name] || ICONS[name] || '') + '</svg>';
    }

    var STYLES = [
        // position:relative gives the image-selection overlay its positioning
        // context (the overlay lives inside the editor wrap, OUTSIDE the
        // contenteditable area, so it never pollutes the saved HTML).
        '.huv-rte{border:1px solid #cbd5e1;background:#fff;position:relative;}',
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
        // TRUE fullscreen — bulletproof version.
        // Why the old one failed: width:100vw/height:100vh OVER-CONSTRAIN a
        // fixed box (top/left + size win, right/bottom are dropped) and 100vw
        // INCLUDES the scrollbar width, so the editor was wider than the
        // visible screen and threw a horizontal scrollbar ("not fully
        // width"). Stretching with all four edges instead sizes the box to
        // the EXACT visible viewport on every browser, desktop and mobile.
        // Every critical property carries !important so no theme CSS can
        // fight it, and the wrap becomes a flex column: toolbar + status bar
        // take their natural height, the writing area absorbs all remaining
        // space (the old "100vh - 110px" magic number broke whenever the
        // toolbar wrapped onto two rows).
        '.huv-rte.full{position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:auto !important;height:auto !important;max-width:none !important;max-height:none !important;z-index:2147483000 !important;display:flex !important;flex-direction:column !important;margin:0 !important;border:0 !important;border-radius:0 !important;box-shadow:none !important;overflow:hidden !important;}',
        '.huv-rte.full .huv-rte-toolbar{flex:0 0 auto;position:static;}',
        '.huv-rte.full .huv-rte-content,.huv-rte.full .huv-rte-src{flex:1 1 auto;min-height:0;max-height:none;height:auto;width:100%;box-sizing:border-box;}',
        '.huv-rte.full .huv-rte-status{flex:0 0 auto;}',
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
        '.huv-rte.dragover{outline:2px dashed #10b981;outline-offset:-2px;}',
        // Source-mode guard: every formatting control except the source-view
        // toggle itself and fullscreen is dimmed AND click-disabled. Before,
        // clicking Bold while in source view silently ran execCommand on the
        // hidden editable area — invisible corruption of the undo stack.
        '.huv-rte.src .huv-rte-btn:not([data-cmd="src"]):not([data-cmd="full"]),.huv-rte.src .huv-rte-select,.huv-rte.src .huv-rte-dd-btn{opacity:.35;pointer-events:none;}',
        // ---- image selection overlay (resize + SEO) ----
        '.huv-rte-content img.huv-img-sel{outline:2px solid #10b981;outline-offset:2px;}',
        '.huv-img-ov{position:absolute;inset:0;z-index:55;pointer-events:none;}',
        '.huv-img-frame{position:absolute;border:1px dashed #10b981;pointer-events:none;}',
        '.huv-img-h{position:absolute;width:13px;height:13px;background:#fff;border:2px solid #0C3B2E;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,.3);pointer-events:auto;}',
        '.huv-img-h-nw{top:-7px;left:-7px;cursor:nwse-resize;}',
        '.huv-img-h-ne{top:-7px;right:-7px;cursor:nesw-resize;}',
        '.huv-img-h-sw{bottom:-7px;left:-7px;cursor:nesw-resize;}',
        '.huv-img-h-se{bottom:-7px;right:-7px;cursor:nwse-resize;}',
        '.huv-img-tools{position:absolute;display:flex;align-items:center;gap:1px;background:#0C3B2E;border-radius:7px;padding:3px;box-shadow:0 6px 18px rgba(0,0,0,.28);white-space:nowrap;pointer-events:auto;}',
        '.huv-img-tools-lab{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#86efac;padding:0 3px 0 5px;pointer-events:none;}',
        '.huv-img-tools button{height:24px;min-width:24px;padding:0 5px;border:0;border-radius:5px;background:transparent;color:#d1fae5;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-family:inherit;}',
        '.huv-img-tools button:hover{background:rgba(255,255,255,.16);color:#fff;}',
        '.huv-img-tools button.active{background:#10b981;color:#fff;}',
        '.huv-img-tools button svg{width:13px;height:13px;pointer-events:none;}',
        '.huv-img-tools-sep{width:1px;height:15px;background:rgba(255,255,255,.28);margin:0 3px;}',
        '.huv-img-form{position:absolute;display:none;width:280px;max-width:calc(100vw - 40px);background:#fff;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 12px 32px rgba(0,0,0,.22);padding:10px;pointer-events:auto;box-sizing:border-box;}',
        '.dark .huv-img-form{background:#1e293b;border-color:#475569;}',
        '.huv-img-form.open{display:block;}',
        '.huv-img-form label{display:block;font-size:11px;font-weight:700;color:#334155;margin:6px 0 3px;}',
        '.dark .huv-img-form label{color:#cbd5e1;}',
        '.huv-img-form label small{font-weight:400;color:#64748b;}',
        '.dark .huv-img-form label small{color:#94a3b8;}',
        '.huv-img-form input{width:100%;box-sizing:border-box;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;background:#fff;color:#0f172a;font-family:inherit;}',
        '.dark .huv-img-form input{background:#0f172a;color:#e2e8f0;border-color:#475569;}',
        '.huv-img-form-btns{display:flex;gap:6px;justify-content:flex-end;margin-top:9px;}',
        '.huv-img-save{padding:5px 12px;border:0;border-radius:6px;background:#0C3B2E;color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}',
        '.huv-img-save:hover{background:#072A20;}',
        '.huv-img-cancel{padding:5px 12px;border:1px solid #cbd5e1;border-radius:6px;background:transparent;color:#334155;font-size:12px;cursor:pointer;font-family:inherit;}',
        '.dark .huv-img-cancel{border-color:#475569;color:#e2e8f0;}',
        'body.huv-img-resizing,body.huv-img-resizing *{cursor:nwse-resize !important;user-select:none !important;}',
        // ---- icon picker (replaces the emoji picker) ----
        '.huv-ico-tabs{display:flex;flex-wrap:wrap;gap:3px;margin-bottom:8px;}',
        '.huv-ico-tab{padding:4px 9px;border:1px solid #e2e8f0;border-radius:999px;background:#fff;color:#334155;font-size:11px;font-weight:600;cursor:pointer;font-family:inherit;}',
        '.dark .huv-ico-tab{background:#0f172a;border-color:#334155;color:#e2e8f0;}',
        '.huv-ico-tab.active{background:#0C3B2E;border-color:#0C3B2E;color:#fff;}',
        '.huv-ico-grid{display:grid;grid-template-columns:repeat(8,30px);gap:3px;}',
        '.huv-ico-btn{width:30px;height:30px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;color:#334155;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;padding:0;}',
        '.dark .huv-ico-btn{background:#0f172a;border-color:#334155;color:#e2e8f0;}',
        '.huv-ico-btn:hover{background:#d1fae5;border-color:#10b981;color:#065f46;}',
        '.dark .huv-ico-btn:hover{background:#064e3b;color:#a7f3d0;}',
        '.huv-ico-btn svg{width:16px;height:16px;pointer-events:none;}',
        // ---- table context tools (status bar, shown while the caret is
        // inside a table — before this, an inserted table could never be
        // extended or removed without mangling the HTML by hand) ----
        '.huv-rte-tbl{display:none;align-items:center;gap:5px;}',
        '.huv-rte-tbl.on{display:flex;}',
        '.huv-rte-tbl button{border:1px solid #cbd5e1;background:#fff;color:#334155;border-radius:4px;font-size:11px;font-weight:600;padding:2px 8px;cursor:pointer;font-family:inherit;}',
        '.huv-rte-tbl button:hover{background:#d1fae5;border-color:#10b981;color:#065f46;}',
        '.dark .huv-rte-tbl button{background:#0f172a;border-color:#334155;color:#e2e8f0;}',
        '.dark .huv-rte-tbl button:hover{background:#064e3b;color:#a7f3d0;}',
        // Image resize handles must receive touch input directly — without
        // touch-action:none, the browser scrolls the page instead of resizing.
        '.huv-img-h{touch-action:none;}',
        // ---- non-blocking toast (replaces window.alert for upload guards) ----
        '.huv-toast{position:fixed;left:50%;transform:translateX(-50%);bottom:26px;background:#0f172a;color:#f8fafc;padding:10px 16px;border-radius:8px;font-size:13px;line-height:1.45;z-index:2147483600;box-shadow:0 10px 30px rgba(0,0,0,.35);max-width:min(480px,90vw);border-left:4px solid #dc2626;}',
        // ---- recovered-draft notice (editor-owned recovery, e.g. pages) ----
        '.huv-restore{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 12px;background:#fffbeb;border-bottom:1px solid #fde68a;color:#92400e;font-size:12px;}',
        '.huv-restore button{border:1px solid #fcd34d;background:#fff;color:#92400e;border-radius:4px;font-size:11px;font-weight:700;padding:2px 9px;cursor:pointer;font-family:inherit;flex-shrink:0;}',
        '.huv-restore button:hover{background:#fef3c7;}'
    ].join('\n');

    // NOTE: no "Google Sans" — that family is NOT on Google Fonts, the old
    // <link> to fonts.googleapis.com returned HTTP 400 on every page load
    // (wasted request, console error) and the font never rendered anyway.
    var FONTS = ['Default', 'Arial, Helvetica, sans-serif', 'Georgia, serif', '"Times New Roman", Times, serif', '"Courier New", monospace', 'Verdana, Geneva, sans-serif', '"Trebuchet MS", sans-serif', '"Work Sans", Arial, sans-serif'];
    var LINE_HEIGHTS = ['Default', '1', '1.2', '1.5', '1.7', '2', '2.5'];
    var SIZES = [
        { label: 'S', v: '2', title: 'Small' }, { label: 'N', v: '3', title: 'Normal' }, { label: 'M', v: '4', title: 'Medium' },
        { label: 'L', v: '5', title: 'Large' }, { label: 'XL', v: '6', title: 'Huge' }, { label: '2XL', v: '7', title: 'Extra large' }
    ];
    var COLORS = ['#0f172a', '#334155', '#64748b', '#dc2626', '#ea580c', '#d97706', '#16a34a', '#059669', '#0891b2', '#2563eb', '#7c3aed', '#db2777', '#ffffff', '#f1f5f9', '#fef3c7', '#d1fae5'];
    var CHARS = ['\u00A9', '\u00AE', '\u2122', '\u2192', '\u2190', '\u2191', '\u2193', '\u2022', '\u2026', '\u2018', '\u2019', '\u201C', '\u201D', '\u2013', '\u2014', '\u00D7', '\u00F7', '\u2260', '\u2264', '\u2265', '\u00B0', '\u00B1', '\u221E', '\u20AC', '\u00A3', '\u00A5', '\u20B9', '\u0024', '\u03B1', '\u03B2', '\u03C0', '\u221A'];
    // Emoji picker removed on purpose — inline SVG icons (ICONS above) look
    // equally sharp on every OS and inherit the text colour.
    var AUTOSAVE_KEY_PREFIX = 'huv-rte-autosave-';

    // Non-blocking toast at MODULE level: also needed by the paste pipeline
    // (below), which runs outside the editor instance.
    function editorToast(msg) {
        var t = document.createElement('div');
        t.className = 'huv-toast';
        t.setAttribute('role', 'status');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { if (t.parentNode) t.remove(); }, 6000);
    }

    // An image pasted INSIDE an HTML payload (Word, Google Docs, another web
    // page) never passes through _insertImageFile's 1.5 MB file guard — the
    // clipboard hands us ready-made <img src="data:image/...base64…"> tags
    // instead. One 8 MB screenshot pasted this way bloated the save POST
    // past post_max_size and the whole article save ended in a 419/500 with
    // everything lost. Anything over the same 1.5 MB limit as the upload
    // path is stripped and the author is told why (1.5 MB of binary encodes
    // to ~2,097,152 base64 chars, so anything beyond ~2.1M chars is over).
    function stripOversizeDataImages(html) {
        if (!html || html.indexOf('data:') === -1) return html;
        var t = document.createElement('div');
        t.innerHTML = html;
        var stripped = 0;
        t.querySelectorAll('img').forEach(function (img) {
            var src = img.getAttribute('src') || '';
            if (src.indexOf('data:') === 0 && src.length > 2100500) {
                img.remove();
                stripped++;
            }
        });
        if (stripped) {
            editorToast(stripped + ' oversized image' + (stripped > 1 ? 's were' : ' was') + ' removed while pasting — images inside the text must be under 1.5 MB. Use the \u201CFeatured Image\u201D uploader for large photos.');
        }
        return t.innerHTML;
    }

    // Popovers register an optional cleanup (e.g. find and replace clears
    // its yellow find-marks) that runs whenever any popover/dropdown closes —
    // before this, marks left behind by simply closing the Find pop were
    // SAVED INTO the post as yellow highlight junk.
    function closeAllPops(root) {
        root.querySelectorAll('.huv-rte-pop').forEach(function (p) { p.remove(); });
        root.querySelectorAll('.huv-rte-dd-list').forEach(function (l) { l.remove(); });
        root.querySelectorAll('.huv-rte-dd-btn.active').forEach(function (b) { b.classList.remove('active'); });
        if (typeof root.__huvPopCleanup === 'function') {
            var fn = root.__huvPopCleanup;
            root.__huvPopCleanup = null;
            fn();
        }
    }

    function pop(anchor, root, html, onOpen) {
        closeAllPops(root);
        var popEl = document.createElement('div');
        popEl.className = 'huv-rte-pop';
        popEl.innerHTML = html;
        // Clicking a popover control must not steal the contenteditable
        // selection (Safari collapses it, so Bold/foreColor then applies to
        // nothing). Form fields stay exempt so they still receive the caret.
        popEl.addEventListener('mousedown', function (e) {
            if (e.target && e.target.closest && e.target.closest('input,textarea,select')) return;
            e.preventDefault();
        });
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
        // Comments: Word/Google-Docs paste payloads carry kilobytes of
        // conditional comments that would otherwise be stored in the DB.
        var commentWalker = document.createTreeWalker(t, NodeFilter.SHOW_COMMENT, null);
        var comments = [];
        while (commentWalker.nextNode()) comments.push(commentWalker.currentNode);
        comments.forEach(function (c) { c.remove(); });
        // Office-XML junk elements (<o:p>, <v:shape>, <w:sdt>, <st1:place>…)
        // from Word / Excel / Visio paste payloads. The server sanitizer
        // unwraps unknown tags on save, but the editor must SHOW the author
        // the same thing that will be stored — so drop the junk up front.
        var officeJunk = [];
        t.querySelectorAll('*').forEach(function (n) {
            if (n.tagName.indexOf(':') !== -1) officeJunk.push(n);
        });
        officeJunk.forEach(function (n) { n.remove(); });
        t.querySelectorAll('*').forEach(function (n) {
            [].slice.call(n.attributes).forEach(function (a) {
                var name = a.name.toLowerCase();
                if (name.indexOf('on') === 0 || (a.value && a.value.trim().toLowerCase().indexOf('javascript:') === 0)) {
                    n.removeAttribute(a.name);
                }
            });
        });
        // Mirror the server-side style blacklist: pasted position/z-index
        // declarations could pin an element over the whole article (or the
        // site header) once published. The editor itself never emits them.
        t.querySelectorAll('[style]').forEach(function (n) {
            var s = n.getAttribute('style') || '';
            var cleaned = s.replace(/position\s*:\s*(?:fixed|absolute|sticky)[^;]*;?/gi, '')
                           .replace(/z-index\s*:\s*[^;]+;?/gi, '');
            if (cleaned !== s) {
                if (cleaned.trim()) n.setAttribute('style', cleaned.trim());
                else n.removeAttribute('style');
            }
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

        // Programmatic bridge for the form-level autosave layers: they
        // restore a whole-form snapshot (title + excerpt + content TOGETHER)
        // and push the saved content back through here. Plain textarea value
        // assignment is invisible to the editor — it never listens to
        // textarea events — so recovery silently showed an empty editor.
        textarea.__huvSet = function (html) {
            content.innerHTML = sanitizeHTML(html || '');
            sync();
        };

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
                if (html === '<br>' || html === '<div><br></div>') {
                    html = '';
                    // Truly empty the content so the :empty placeholder
                    // reappears after deleting everything (contenteditable
                    // loves leaving a stray <br> behind — the placeholder
                    // never came back without this).
                    content.innerHTML = '';
                }
                // Semantic tags instead of presentational ones (better SEO).
                html = html.replace(/<b(\s|>)/g, '<strong$1').replace(/<\/b>/g, '</strong>')
                           .replace(/<i(\s|>)/g, '<em$1').replace(/<\/i>/g, '</em>');
                textarea.value = html;
                // NOTE: srcArea is deliberately NOT mirrored here — entering
                // source view refreshes it from the content. Mirroring up to
                // megabytes of base64 HTML into TWO places on EVERY keystroke
                // made long image-heavy posts visibly lag while typing.
            }
            if (typeof autosaveDirty === 'boolean') autosaveDirty = true;
            updateCount();
        }

        function wordCount() {
            var text = (wrap.classList.contains('src') ? srcArea.value : content.innerText) || '';
            var m = text.trim().match(/\S+/g);
            return m ? m.length : 0;
        }

        function updateCount() {
            var wc = status.querySelector('.huv-rte-wc');
            if (!wc) return;
            var text = (wrap.classList.contains('src') ? srcArea.value : content.innerText) || '';
            var m = text.trim().match(/\S+/g);
            // Characters (excluding spaces) shown as well: the server rejects
            // drafts under 120 content characters, so the author can watch
            // that threshold approach instead of guessing.
            wc.textContent = (m ? m.length : 0) + ' words · ' + text.replace(/\s/g, '').length + ' characters';
        }

        function updateStates() {
            try {
                setA('bold', document.queryCommandState('bold'));
                setA('italic', document.queryCommandState('italic'));
                setA('underline', document.queryCommandState('underline'));
                setA('strike', document.queryCommandState('strikeThrough'));
                setA('ul', document.queryCommandState('insertUnorderedList'));
                setA('ol', document.queryCommandState('insertOrderedList'));
                var justify = document.queryCommandState('justifyCenter');
                var right = document.queryCommandState('justifyRight');
                var full = document.queryCommandState('justifyFull');
                setA('alignL', !justify && !right && !full);
                setA('alignC', justify); setA('alignR', right); setA('alignJ', full);
            } catch (e) { /* queryCommandState can throw on some selections */ }
            // Table context toolbar: visible while the caret is inside a
            // table — the only place where adding/removing rows and columns
            // makes sense (see tableOp below).
            var inTable = false;
            try {
                var ts = window.getSelection();
                var ta = ts && ts.anchorNode ? (ts.anchorNode.nodeType === 3 ? ts.anchorNode.parentNode : ts.anchorNode) : null;
                var tt = ta && ta.closest ? ta.closest('table') : null;
                inTable = !!(tt && content.contains(tt));
            } catch (e2) {}
            if (tblBar) tblBar.classList.toggle('on', inTable);
        }

        function setA(name, on) {
            var b = toolbar.querySelector('[data-cmd="' + name + '"]');
            if (b) b.classList.toggle('active', !!on);
        }

        function restoreRange() {
            if (self.savedRange) {
                // A range whose anchors were removed from the DOM (deleted
                // surrounding content, form autofill, etc.) must NOT be
                // re-added — some browsers then apply commands to nowhere or
                // throw. Fall back to the end of the document instead.
                var alive = false;
                try { alive = content.contains(self.savedRange.commonAncestorContainer); } catch (e) {}
                if (alive) {
                    var sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(self.savedRange);
                } else {
                    self.savedRange = null;
                }
            }
            content.focus();
        }

        function insertHTML(html) {
            restoreRange();
            document.execCommand('insertHTML', false, html);
            sync();
        }

        /**
         * Insert a DOM node at the caret. Used for inline SVG icons —
         * execCommand('insertHTML') mangles SVG markup in some browsers,
         * while a direct Range insert is byte-exact.
         */
        function insertNode(node) {
            restoreRange();
            var s = window.getSelection();
            var r = (s && s.rangeCount) ? s.getRangeAt(0) : null;
            if (!r || !content.contains(r.commonAncestorContainer)) {
                r = document.createRange();
                r.selectNodeContents(content);
                r.collapse(false);
            }
            r.deleteContents();
            r.insertNode(node);
            r.setStartAfter(node);
            r.setEndAfter(node);
            s.removeAllRanges();
            s.addRange(r);
            sync();
        }

        // ------------------------------------------------------------
        //  Image selection overlay — WordPress/Medium-style.
        //  Click an image → dashed frame + 4 corner drag handles + a
        //  floating toolbar (S / M / L / Original · alt & title · delete).
        //  The overlay lives in the editor WRAP, outside the
        //  contenteditable area, so it is never part of the saved HTML.
        // ------------------------------------------------------------
        var IMG_PRESETS = { s: 350, m: 560, l: 760 };
        var imgSel = { img: null, ov: null, frame: null, tools: null, form: null, dragging: null };

        function deselectImage() {
            if (imgSel.dragging) { imgSel.dragging = null; document.body.classList.remove('huv-img-resizing'); }
            if (imgSel.ov) { imgSel.ov.remove(); }
            if (imgSel.img) { imgSel.img.classList.remove('huv-img-sel'); }
            imgSel.img = imgSel.ov = imgSel.frame = imgSel.tools = imgSel.form = null;
        }

        function markActiveSize() {
            if (!imgSel.img || !imgSel.tools) return;
            var w = imgSel.img.getAttribute('width');
            ['s', 'm', 'l'].forEach(function (k) {
                var b = imgSel.tools.querySelector('[data-w="' + k + '"]');
                if (b) b.classList.toggle('active', w === String(IMG_PRESETS[k]));
            });
            var full = imgSel.tools.querySelector('[data-w="full"]');
            if (full) full.classList.toggle('active', !w);
        }

        function toast(msg) {
            var t = document.createElement('div');
            t.className = 'huv-toast';
            t.setAttribute('role', 'status');
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(function () { if (t.parentNode) t.remove(); }, 6000);
        }

        function selectImage(img) {
            deselectImage();
            imgSel.img = img;
            img.classList.add('huv-img-sel');

            var ov = document.createElement('div');
            ov.className = 'huv-img-ov';
            ov.setAttribute('contenteditable', 'false');

            var frame = document.createElement('div');
            frame.className = 'huv-img-frame';
            ['nw', 'ne', 'sw', 'se'].forEach(function (pos) {
                var h = document.createElement('span');
                h.className = 'huv-img-h huv-img-h-' + pos;
                h.addEventListener('pointerdown', function (e) { startResize(e, pos, h); });
                frame.appendChild(h);
            });
            ov.appendChild(frame);

            var tools = document.createElement('div');
            tools.className = 'huv-img-tools';
            tools.innerHTML =
                '<span class="huv-img-tools-lab">Size</span>' +
                '<button type="button" data-w="s" title="Small (350px)">S</button>' +
                '<button type="button" data-w="m" title="Medium (560px)">M</button>' +
                '<button type="button" data-w="l" title="Large (760px)">L</button>' +
                '<button type="button" data-w="full" title="Original size">' + icon('maximize') + '</button>' +
                '<span class="huv-img-tools-sep"></span>' +
                '<button type="button" data-act="seo" title="Edit alt text & title (SEO)">' + icon('pencil') + '</button>' +
                '<button type="button" data-act="del" title="Remove image">' + icon('trash') + '</button>';
            tools.addEventListener('mousedown', function (e) { if (e.target.closest('button')) e.preventDefault(); });
            tools.addEventListener('click', function (e) {
                var b = e.target.closest('button');
                if (!b || !imgSel.img) return;
                if (b.getAttribute('data-w')) {
                    var k = b.getAttribute('data-w');
                    if (k === 'full') imgSel.img.removeAttribute('width');
                    else imgSel.img.setAttribute('width', IMG_PRESETS[k]);
                    markActiveSize();
                    sync();
                    positionImageOverlay();
                } else if (b.getAttribute('data-act') === 'seo') {
                    openImageForm();
                } else if (b.getAttribute('data-act') === 'del') {
                    var im = imgSel.img;
                    deselectImage();
                    // Deleting an image that lives inside a link used to
                    // leave an empty <a></a> shell behind — clicking it did
                    // nothing and Backspace inside it confused the caret.
                    var parentA = (im.parentNode && im.parentNode.nodeName === 'A') ? im.parentNode : null;
                    if (im.parentNode) im.parentNode.removeChild(im);
                    if (parentA && !parentA.firstChild) parentA.parentNode.removeChild(parentA);
                    sync();
                }
            });
            ov.appendChild(tools);

            var form = document.createElement('div');
            form.className = 'huv-img-form';
            form.innerHTML =
                '<label>Alt text <small>— describes the image for Google Images (SEO)</small></label>' +
                '<input type="text" data-f="alt" maxlength="250" placeholder="e.g. Coffee cup on a wooden desk">' +
                '<label>Title <small>— shown on hover, extra SEO signal</small></label>' +
                '<input type="text" data-f="title" maxlength="250" placeholder="e.g. Morning coffee routine">' +
                '<div class="huv-img-form-btns">' +
                '<button type="button" class="huv-img-cancel">Cancel</button>' +
                '<button type="button" class="huv-img-save">Save</button></div>';
            form.querySelector('.huv-img-save').addEventListener('click', function () {
                if (!imgSel.img) return;
                var alt = form.querySelector('[data-f="alt"]').value.trim();
                var title = form.querySelector('[data-f="title"]').value.trim();
                if (alt) imgSel.img.setAttribute('alt', alt); else imgSel.img.removeAttribute('alt');
                if (title) imgSel.img.setAttribute('title', title); else imgSel.img.removeAttribute('title');
                closeImageForm();
                sync();
            });
            form.querySelector('.huv-img-cancel').addEventListener('click', closeImageForm);
            ov.appendChild(form);

            wrap.appendChild(ov);
            imgSel.ov = ov; imgSel.frame = frame; imgSel.tools = tools; imgSel.form = form;
            img.addEventListener('load', positionImageOverlay);
            markActiveSize();
            positionImageOverlay();
        }

        function positionImageOverlay() {
            if (!imgSel.img || !imgSel.ov) return;
            if (!content.contains(imgSel.img)) { deselectImage(); return; }
            var wr = wrap.getBoundingClientRect();
            var ir = imgSel.img.getBoundingClientRect();
            if (!ir.width && !ir.height) { imgSel.ov.style.display = 'none'; return; }
            imgSel.ov.style.display = '';
            var top = ir.top - wr.top, left = ir.left - wr.left;
            imgSel.frame.style.top = top + 'px';
            imgSel.frame.style.left = left + 'px';
            imgSel.frame.style.width = ir.width + 'px';
            imgSel.frame.style.height = ir.height + 'px';
            var toolsH = imgSel.tools.offsetHeight || 30;
            var tTop = top - toolsH - 5;
            if (tTop < 0) tTop = top + ir.height + 5;
            imgSel.tools.style.top = tTop + 'px';
            imgSel.tools.style.left = Math.max(2, left) + 'px';
            if (imgSel.form.classList.contains('open')) {
                var fH = imgSel.form.offsetHeight || 150;
                var fTop = (tTop < top ? tTop + toolsH + 4 : tTop - fH - 8);
                if (fTop + fH > wrap.offsetHeight - 4) fTop = Math.max(2, wrap.offsetHeight - fH - 4);
                imgSel.form.style.top = fTop + 'px';
                imgSel.form.style.left = Math.max(2, Math.min(left, wrap.offsetWidth - imgSel.form.offsetWidth - 6)) + 'px';
            }
        }

        function openImageForm() {
            if (!imgSel.img || !imgSel.form) return;
            imgSel.form.querySelector('[data-f="alt"]').value = imgSel.img.getAttribute('alt') || '';
            imgSel.form.querySelector('[data-f="title"]').value = imgSel.img.getAttribute('title') || '';
            imgSel.form.classList.add('open');
            positionImageOverlay();
            // Autofocusing pops the on-screen keyboard over half the editor
            // on phones/tablets — only auto-focus on fine pointers (mouse).
            if (!(window.matchMedia && window.matchMedia('(pointer: coarse)').matches)) {
                imgSel.form.querySelector('[data-f="alt"]').focus();
            }
        }

        function closeImageForm() {
            if (imgSel.form) imgSel.form.classList.remove('open');
            positionImageOverlay();
        }

        function startResize(e, pos, handle) {
            if (!imgSel.img) return;
            if (e.button !== undefined && e.button !== 0) return;
            e.preventDefault();
            e.stopPropagation();
            imgSel.dragging = {
                img: imgSel.img,
                startX: e.clientX,
                startW: imgSel.img.getBoundingClientRect().width,
                // The SE handle grows when dragged right; EVERY other handle
                // (nw/ne/sw) grows when dragged LEFT — the old map treated ne
                // like se, so dragging the top-right handle felt inverted.
                dir: (pos === 'se') ? 1 : -1
            };
            // Pointer capture keeps the drag glued to the handle even when
            // the cursor/finger leaves it — and makes TOUCH resize work at
            // all (the old mousedown-only handles were dead on phones).
            if (handle && handle.setPointerCapture && e.pointerId !== undefined) {
                try { handle.setPointerCapture(e.pointerId); } catch (err) {}
            }
            document.body.classList.add('huv-img-resizing');
        }

        document.addEventListener('pointermove', function (e) {
            var d = imgSel.dragging;
            if (!d) return;
            var maxW = Math.max(120, content.clientWidth - 44);
            var w = Math.round(d.startW + (e.clientX - d.startX) * d.dir);
            w = Math.max(80, Math.min(maxW, w));
            d.img.setAttribute('width', w);
            d.img.style.width = w + 'px'; // smooth live resize; removed on release
            positionImageOverlay();
        });
        function endResize() {
            var d = imgSel.dragging;
            if (!d) return;
            imgSel.dragging = null;
            document.body.classList.remove('huv-img-resizing');
            d.img.style.width = ''; // the width ATTRIBUTE keeps the size (it survives the server-side sanitizer)
            sync();
            positionImageOverlay();
        }
        document.addEventListener('pointerup', endResize);
        document.addEventListener('pointercancel', endResize);

        // Keep the overlay glued to the image when the writing area scrolls,
        // the window resizes, or the image itself changes size (lazy load).
        content.addEventListener('scroll', function () { if (imgSel.img) positionImageOverlay(); });
        window.addEventListener('resize', function () { if (imgSel.img) positionImageOverlay(); });

        /**
         * Real insert pipeline for images (upload button, paste, drag & drop).
         * Defined inside the constructor so it can auto-select the new image
         * and open the SEO form — images are never inserted "naked": alt is
         * prefilled from the file name, title from the post title field, and
         * the alt/title form opens immediately so the author completes SEO.
         */
        self._insertImageFile = function (file) {
            if (!file || file.type.indexOf('image') !== 0) return;
            // Size guard: images placed in the text are embedded as base64
            // data URLs inside the post body. A multi-megabyte phone photo
            // would bloat the form POST beyond post_max_size and end as a
            // 419/500 on save. Large photos belong in "Featured Image".
            var MAX_EMBED_BYTES = 1.5 * 1024 * 1024;
            if (file.size > MAX_EMBED_BYTES) {
                toast('This image is ' + Math.round(file.size / 1024 / 1024 * 10) / 10 + ' MB. Images placed inside the text must be under 1.5 MB. Please resize it first, or use the \u201CFeatured Image\u201D uploader which handles up to 4 MB.');
                return;
            }
            var reader = new FileReader();
            reader.onload = function () {
                var img = document.createElement('img');
                img.setAttribute('src', reader.result);
                var alt = (file.name || '').replace(/\.[a-z0-9]+$/i, '').replace(/[-_]+/g, ' ').replace(/\s+/g, ' ').trim();
                if (alt) alt = alt.charAt(0).toUpperCase() + alt.slice(1); // sentence case reads like a real description
                var postTitle = '';
                try {
                    var f = textarea.closest('form');
                    var t = f && f.querySelector('input[name="title"]');
                    if (t) postTitle = t.value.trim();
                } catch (err) { /* no form around — skip prefill */ }
                if (!alt && postTitle) alt = postTitle;
                if (alt) img.setAttribute('alt', alt);
                if (postTitle) img.setAttribute('title', postTitle);
                insertNode(img);
                selectImage(img);
                openImageForm();
            };
            reader.readAsDataURL(file);
        };

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
                // Keep the contenteditable selection alive while the menu is
                // used (same reason pop() guards mousedown).
                list.addEventListener('mousedown', function (e) { e.preventDefault(); });
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
        select('font', 'Font family', FONTS.map(function (f) { return { label: f === 'Default' ? 'Default' : f.split(',')[0].replace(/"/g, ''), v: f }; }), function (v) {
            if (v === 'Default') {
                // Old behaviour ran removeFormat(), which nuked bold/italic/
                // links — everything. "Default" must only strip the FONT.
                restoreRange();
                var s = window.getSelection();
                if (s && s.rangeCount && !s.isCollapsed) {
                    var r = s.getRangeAt(0);
                    var frag = r.extractContents();
                    var tmp = document.createElement('div');
                    tmp.appendChild(frag);
                    tmp.querySelectorAll('font[face]').forEach(function (f) {
                        f.removeAttribute('face');
                        if (!f.attributes.length) {
                            while (f.firstChild) f.parentNode.insertBefore(f.firstChild, f);
                            f.remove();
                        }
                    });
                    // Insert the CLEANED CHILDREN, not the tmp wrapper: a
                    // plain div.insertNode() wrapped the selection in a
                    // <div> that then travelled into the saved markup.
                    var outFrag = document.createDocumentFragment();
                    while (tmp.firstChild) outFrag.appendChild(tmp.firstChild);
                    r.insertNode(outFrag);
                    s.removeAllRanges();
                    s.addRange(r);
                }
                sync();
                return;
            }
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
                insertHTML('<code style="background:#f1f5f9;padding:1px 5px;border-radius:4px">code</code>&nbsp;');
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
                // NOTE: no live 'input' handler — dragging the color slider
                // fired an execCommand per pixel (dozens of undo steps and a
                // full sync each), which stuttered long posts.
            });
        });
        btn('hiliteColor', 'highlighter', 'Highlight', function () {
            var anchor = this;
            var html = '<div class="huv-rte-pop-grid">' +
                // First swatch = REMOVE the highlight (previously there was
                // no way to un-highlight without picking white and hoping).
                '<button type="button" class="huv-rte-swatch" data-color="transparent" title="No highlight" style="background:#fff;color:#dc2626;font-size:12px;font-weight:700;line-height:1">\u2715</button>' +
                COLORS.map(function (c) {
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
            // Caret inside an existing link (collapsed selection)? Then this
            // is an EDIT session: prefill its URL and update it in place.
            // The old code always INSERTED, producing nested <a><a>… markup
            // that browsers split into broken halves.
            var existing = null;
            if (self.savedRange && self.savedRange.commonAncestorContainer) {
                var anc = self.savedRange.commonAncestorContainer;
                if (anc.nodeType === 3) anc = anc.parentNode;
                existing = (anc && anc.closest) ? anc.closest('a') : null;
                if (existing && !content.contains(existing)) existing = null;
            }
            var html =
                '<label class="huv-rte-label">URL</label>' +
                '<input type="url" class="huv-rte-field" data-role="url" placeholder="https://example.com">' +
                '<label class="huv-rte-label"><input type="checkbox" data-role="blank" checked> Open in new tab</label>' +
                '<div class="huv-rte-row">' +
                '<button type="button" class="huv-rte-btn-ghost" data-role="remove">Remove link</button>' +
                '<button type="button" class="huv-rte-btn-primary" data-role="apply">Apply</button></div>';
            pop(anchor, wrap, html, function (p) {
                var url = p.querySelector('[data-role="url"]');
                var blank = p.querySelector('[data-role="blank"]');
                if (existing) {
                    url.value = existing.getAttribute('href') || '';
                    blank.checked = existing.getAttribute('target') === '_blank';
                }
                url.focus();
                function applyLink() {
                    var v = url.value.trim();
                    if (!v) return;
                    if (!/^(https?:\/\/|mailto:|#|\/)/i.test(v)) v = 'https://' + v;
                    closeAllPops(wrap);
                    if (existing && !selectedText) {
                        existing.setAttribute('href', v);
                        if (blank.checked) { existing.setAttribute('target', '_blank'); existing.setAttribute('rel', 'noopener noreferrer'); }
                        else { existing.removeAttribute('target'); existing.removeAttribute('rel'); }
                        sync();
                        return;
                    }
                    restoreRange();
                    if (selectedText) {
                        document.execCommand('createLink', false, v);
                        var a = null, node = window.getSelection().anchorNode;
                        if (node) {
                            if (node.nodeType === 3) node = node.parentNode;
                            a = node.closest ? node.closest('a') : null;
                        }
                        if (a && blank.checked) {
                            a.setAttribute('target', '_blank');
                            a.setAttribute('rel', 'noopener noreferrer');
                        }
                    } else {
                        insertHTML('<a href="' + v.replace(/"/g, '&quot;') + '"' + (blank.checked ? ' target="_blank" rel="noopener noreferrer"' : '') + '>' + v.replace(/</g, '&lt;') + '</a>');
                    }
                    sync();
                }
                p.querySelector('[data-role="apply"]').addEventListener('click', applyLink);
                url.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); applyLink(); } });
                p.querySelector('[data-role="remove"]').addEventListener('click', function () {
                    closeAllPops(wrap);
                    restoreRange();
                    // A collapsed caret gives unlink() nothing to work with
                    // in some browsers — select the whole link first so
                    // removal is deterministic.
                    if (existing && (!sel || sel.isCollapsed)) {
                        var r = document.createRange();
                        r.selectNode(existing);
                        var s2 = window.getSelection();
                        s2.removeAllRanges();
                        s2.addRange(r);
                    }
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
        btn('hr', 'hr', 'Horizontal line', function () {
            // Fully deterministic insertion — no execCommand: Chromium's
            // insertHorizontalRule does NOTHING inside table cells, emits
            // junk like <hr id="null">, and no-ops entirely when the focus
            // dance leaves no caret. Plain node insertion works everywhere.
            restoreRange();
            var s = window.getSelection();
            var anc = s && s.rangeCount ? (s.anchorNode.nodeType === 3 ? s.anchorNode.parentNode : s.anchorNode) : null;
            var inContent = !!(anc && content.contains(anc));
            var cell = inContent && anc.closest ? anc.closest('td,th') : null;
            var hr = document.createElement('hr');
            var p = document.createElement('p');
            p.innerHTML = '<br>';
            if (cell && anc.closest('table') && anc.closest('table').parentNode) {
                // Caret inside a table: the line goes AFTER the table (an
                // <hr> inside a cell is never what the author wants).
                var table = anc.closest('table');
                table.parentNode.insertBefore(hr, table.nextSibling);
                hr.parentNode.insertBefore(p, hr.nextSibling);
            } else if (inContent) {
                var r = s.getRangeAt(0);
                r.deleteContents();
                r.insertNode(hr);
                hr.parentNode.insertBefore(p, hr.nextSibling);
            } else {
                // No usable caret (focus in toolbar, dead range…) — append
                // at the end of the content instead of doing nothing.
                content.appendChild(hr);
                content.appendChild(p);
            }
            var r2 = document.createRange();
            r2.setStart(p, 0);
            r2.collapse(true);
            s.removeAllRanges();
            s.addRange(r2);
            sync();
        });
        btn('chars', 'atSign', 'Special characters', function () {
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
        /**
         * Icon picker — replaces the old emoji picker. Inserts crisp inline
         * SVG icons (created via createElementNS so no browser mangles the
         * markup) that inherit the text colour and stay sharp at any size.
         */
        btn('icons', 'sparkles', 'Insert icon (high-quality SVG icons)', function () {
            var anchor = this;
            var html = '<div class="huv-ico-tabs">' + ICON_GROUPS.map(function (g, i) {
                return '<button type="button" class="huv-ico-tab' + (i === 0 ? ' active' : '') + '" data-g="' + i + '">' + g.name + '</button>';
            }).join('') + '</div><div class="huv-ico-grid" data-role="grid"></div>';
            pop(anchor, wrap, html, function (p) {
                var grid = p.querySelector('[data-role="grid"]');
                function show(gi) {
                    grid.innerHTML = ICON_GROUPS[gi].icons.map(function (n) {
                        // Human-readable tooltip: "checkCircle" → "Check Circle".
                        var label = n.replace(/([a-z0-9])([A-Z])/g, '$1 $2');
                        return '<button type="button" class="huv-ico-btn" title="' + label + '" aria-label="' + label + '" data-n="' + n + '">' + icon(n) + '</button>';
                    }).join('');
                    grid.querySelectorAll('.huv-ico-btn').forEach(function (b) {
                        b.addEventListener('click', function () {
                            var name = b.getAttribute('data-n');
                            if (!ICONS[name]) return;
                            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            svg.setAttribute('viewBox', '0 0 24 24');
                            svg.setAttribute('fill', 'none');
                            svg.setAttribute('stroke', 'currentColor');
                            svg.setAttribute('stroke-width', '2');
                            svg.setAttribute('stroke-linecap', 'round');
                            svg.setAttribute('stroke-linejoin', 'round');
                            svg.setAttribute('aria-hidden', 'true');
                            svg.setAttribute('class', 'huv-inline-icon');
                            // One Backspace/Delete removes the WHOLE icon on
                            // every browser; without this, some engines put
                            // the caret inside the SVG and behave erratically.
                            // (The attribute is stripped on save by the
                            // sanitizer allowlist — published pages are clean.)
                            svg.setAttribute('contenteditable', 'false');
                            svg.style.width = '1.15em';
                            svg.style.height = '1.15em';
                            svg.style.verticalAlign = '-0.2em';
                            svg.innerHTML = ICONS[name];
                            closeAllPops(wrap);
                            insertNode(svg);
                        });
                    });
                }
                show(0);
                p.querySelectorAll('.huv-ico-tab').forEach(function (t) {
                    t.addEventListener('click', function () {
                        p.querySelectorAll('.huv-ico-tab').forEach(function (x) { x.classList.remove('active'); });
                        t.classList.add('active');
                        show(parseInt(t.getAttribute('data-g'), 10) || 0);
                    });
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
                // Any popover close (Escape, another button, outside click)
                // used to leave the yellow find-marks IN the content — they
                // then got SAVED into the post. The cleanup hook runs on
                // every closeAllPops.
                wrap.__huvPopCleanup = function () { clear(); };
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
                        // Replace via a TEXT node — the old outerHTML approach
                        // parsed the replacement as HTML (typing "<img …>"
                        // into the Replace field literally created an image).
                        marks[idx].parentNode.replaceChild(document.createTextNode(repEl.value), marks[idx]);
                        marks.splice(idx, 1); idx--;
                        sync();
                    }
                });
                p.querySelector('[data-role="all"]').addEventListener('click', function () {
                    marks.forEach(function (m) { m.parentNode.replaceChild(document.createTextNode(repEl.value), m); });
                    marks = []; idx = -1; sync();
                });
                findEl.focus();
                // Enter = find next (what every editor user expects).
                findEl.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') { e.preventDefault(); p.querySelector('[data-role="next"]').click(); }
                });
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
            if (isSrc) { deselectImage(); srcArea.value = content.innerHTML; srcArea.focus(); }
            else { content.innerHTML = sanitizeHTML(srcArea.value); content.focus(); }
            this.classList.toggle('active', isSrc);
            sync();
        });
        // Fullscreen: the editor element is MOVED to document.body while the
        // overlay is open and put back to its exact original position on
        // close. This defeats every ancestor that can break position:fixed
        // (transforms, backdrop-filter, overflow clipping, narrow grid
        // columns). Critical geometry is ALSO applied inline as a second
        // line of defence, so fullscreen always spans the real viewport
        // edge to edge.
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
                // Inline fallback styles beat every stylesheet rule that is
                // not !important; they are removed on exit so the editor
                // flows exactly as before.
                wrap.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:2147483000;margin:0;border-radius:0;';
            } else {
                wrap.classList.remove('full');
                wrap.style.cssText = '';
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
            // The wrap just moved (page → body or back): re-glue the image
            // overlay to its image in the new geometry.
            if (imgSel.img) requestAnimationFrame(positionImageOverlay);
            // In source view the content area is hidden — focusing it leaves
            // the keyboard dead until a click. Focus whatever is visible.
            if (wrap.classList.contains('src')) srcArea.focus(); else content.focus();
        }
        btn('full', 'expand', 'Fullscreen', function () { toggleFullscreen(); });

        status.innerHTML = '<span class="huv-rte-wc"></span>';
        // Table tools live in the status bar (always visible, zero
        // positioning headaches) and light up only when the caret is inside
        // a table. Until now an inserted table could not be extended or
        // removed without hand-editing HTML in the source view.
        var tblBar = document.createElement('span');
        tblBar.className = 'huv-rte-tbl';
        tblBar.innerHTML =
            '<button type="button" data-t="row-below" title="Insert row below">+ Row</button>' +
            '<button type="button" data-t="col-right" title="Insert column right">+ Col</button>' +
            '<button type="button" data-t="row-del" title="Delete row">\u2212 Row</button>' +
            '<button type="button" data-t="col-del" title="Delete column">\u2212 Col</button>' +
            '<button type="button" data-t="table-del" title="Delete whole table">\u2715 Table</button>';
        tblBar.addEventListener('click', function (e) {
            var b = e.target.closest('button');
            if (b) tableOp(b.getAttribute('data-t'));
        });
        status.appendChild(tblBar);
        status.insertAdjacentHTML('beforeend', '<span>Huvanti Editor · <a href="#" data-role="help" style="color:inherit;text-decoration:underline">Shortcuts</a></span>');
        function tableOp(op) {
            var s = window.getSelection();
            var anc = s && s.anchorNode ? s.anchorNode : null;
            if (!anc) return;
            if (anc.nodeType === 3) anc = anc.parentNode;
            var cell = anc.closest ? anc.closest('td,th') : null;
            var table = anc.closest ? anc.closest('table') : null;
            if (!cell || !table || !content.contains(table)) return;
            var row = cell.parentNode;
            if (!row || row.nodeName !== 'TR') return;
            var cellIndex = Array.prototype.indexOf.call(row.cells, cell);
            var rowIndex = Array.prototype.indexOf.call(table.rows, row);
            var i, idx2;
            if (op === 'row-below') {
                var nr = table.insertRow(rowIndex + 1);
                for (i = 0; i < row.cells.length; i++) nr.insertCell(i);
            } else if (op === 'col-right') {
                for (i = 0; i < table.rows.length; i++) {
                    idx2 = Math.min(cellIndex + 1, table.rows[i].cells.length);
                    table.rows[i].insertCell(idx2);
                }
            } else if (op === 'row-del') {
                if (table.rows.length <= 1) { table.parentNode.removeChild(table); }
                else table.deleteRow(rowIndex);
            } else if (op === 'col-del') {
                if (row.cells.length <= 1) { table.parentNode.removeChild(table); }
                else for (i = 0; i < table.rows.length; i++) { if (table.rows[i].cells[cellIndex]) table.rows[i].deleteCell(cellIndex); }
            } else if (op === 'table-del') {
                table.parentNode.removeChild(table);
            }
            sync();
            updateStates();
        }
        status.querySelector('[data-role="help"]').addEventListener('click', function (e) {
            e.preventDefault();
            alert('Shortcuts:\nCtrl+B  Bold\nCtrl+I  Italic\nCtrl+U  Underline\nCtrl+K  Link\nCtrl+F  Find and replace\nCtrl+Z  Undo\nCtrl+Y  Redo\nTab     Indent  ·  Shift+Tab Outdent');
        });
        updateCount();

        // ---------------- events ----------------
        content.addEventListener('input', function () {
            sync();
            // Typing can delete the selected image (Backspace) — the
            // positioner re-checks containment and deselects if it's gone.
            if (imgSel.img) positionImageOverlay();
        });
        content.addEventListener('keyup', updateStates);
        content.addEventListener('mouseup', updateStates);

        // Click an image → select it and show the resize/SEO overlay.
        content.addEventListener('click', function (e) {
            var img = e.target && e.target.closest ? e.target.closest('img') : null;
            if (img && content.contains(img)) {
                if (imgSel.img !== img) selectImage(img);
            } else if (imgSel.img) {
                deselectImage();
            }
        });

        var statesQueued = false;
        document.addEventListener('selectionchange', function () {
            var sel = window.getSelection();
            if (sel && sel.rangeCount && content.contains(sel.anchorNode)) {
                self.savedRange = sel.getRangeAt(0).cloneRange();
            }
            // Refresh toolbar/button states (and the table tools) even for
            // caret moves that are not key/mouse driven (mobile selection
            // handles, accessibility navigation). rAF-throttled.
            if (!statesQueued) {
                statesQueued = true;
                requestAnimationFrame(function () { statesQueued = false; updateStates(); });
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
            // NOTE: Escape is handled ONLY by the central document-level
            // listener below (staged: popover → image form → image selection
            // → fullscreen). Handling it here too meant that with a popover
            // open in fullscreen, this handler closed the pop, the document
            // handler then found no pop and ALSO exited fullscreen — one
            // keypress doing two things.
        });

        // ONE central Escape handler for everything, staged: popover → image
        // form → image selection → fullscreen exit. Works no matter where
        // the focus sits (content, toolbar, source view, page background).
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            if (wrap.querySelector('.huv-rte-pop, .huv-rte-dd-list')) { closeAllPops(wrap); return; }
            if (imgSel.form && imgSel.form.classList.contains('open')) { closeImageForm(); return; }
            if (imgSel.img) { deselectImage(); return; }
            if (wrap.classList.contains('full')) toggleFullscreen();
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
            if (html) { insertHTML(stripOversizeDataImages(sanitizeHTML(html))); }
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

        // Close popovers on outside click; also drop the image selection.
        document.addEventListener('mousedown', function (e) {
            if (!wrap.contains(e.target)) { closeAllPops(wrap); deselectImage(); }
        });
        toolbar.addEventListener('mousedown', function (e) {
            if (!e.target.closest('.huv-rte-pop') && !e.target.closest('.huv-rte-select') && !e.target.closest('.huv-rte-dd')) {
                var p = toolbar.querySelector('.huv-rte-pop');
                if (p && !p.contains(e.target)) closeAllPops(wrap);
            }
        });

        srcArea.addEventListener('input', function () {
            textarea.value = srcArea.value;
            autosaveDirty = true; // source edits must reach the local snapshot too
            updateCount();
        });

        // ---- editor-level local autosave (crash recovery) -----------------
        // KEY SCOPING FIX: every editor form on this site uses
        // <textarea name="content">, so the old global key
        // "huv-rte-autosave-content" was SHARED by all of them — text written
        // in the admin PAGE editor could resurrect itself inside a NEW POST
        // (and vice versa). The key is now scoped per URL + field.
        var form = textarea.closest('form');
        var autosaveKey = AUTOSAVE_KEY_PREFIX + location.pathname + ':' + (textarea.getAttribute('name') || textarea.id || 'editor');
        // When the surrounding form runs its own whole-form autosave (author
        // and admin post forms carry data-autosave), THAT layer owns crash
        // recovery: it restores title + excerpt + content TOGETHER through
        // textarea.__huvSet. A second restore here used to race it — the
        // editor refilled first, the form layer then saw a non-pristine form
        // and silently DROPPED the recovered title and excerpt.
        var formOwnsRecovery = !!(form && form.hasAttribute('data-autosave'));
        var autosaveDirty = false;
        var lastAutosaved = null;
        if (!formOwnsRecovery) {
            var draft = null;
            try { draft = localStorage.getItem(autosaveKey); } catch (e) {}
            if (draft && !textarea.value.trim() && !content.textContent.trim()) {
                content.innerHTML = sanitizeHTML(draft);
                sync();
                // Recovery must be VISIBLE and reversible — silently
                // resurrecting old text looked like the editor was haunted.
                var note = document.createElement('div');
                note.className = 'huv-restore';
                var noteTxt = document.createElement('span');
                noteTxt.textContent = 'Unsaved changes from your last session were restored.';
                note.appendChild(noteTxt);
                var disc = document.createElement('button');
                disc.type = 'button';
                disc.textContent = 'Discard';
                disc.addEventListener('click', function () {
                    try { localStorage.removeItem(autosaveKey); } catch (e2) {}
                    content.innerHTML = '';
                    sync();
                    if (note.parentNode) note.remove();
                    content.focus();
                });
                note.appendChild(disc);
                wrap.insertBefore(note, content);
            }
            setInterval(function () {
                if (!autosaveDirty) return; // idle: don't even READ innerHTML (megabyte posts)
                autosaveDirty = false;
                var html = wrap.classList.contains('src') ? srcArea.value : content.innerHTML;
                if (html === lastAutosaved) return;
                lastAutosaved = html;
                try {
                    localStorage.setItem(autosaveKey, html);
                } catch (e) {
                    // Quota exceeded (big base64 images): keep at least the
                    // TEXT by stripping embedded images from the snapshot.
                    try { localStorage.setItem(autosaveKey, html.replace(/<img[^>]*>/gi, '')); } catch (e2) {}
                }
            }, 3000);
        }
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

    /**
     * Insert an image file. The real pipeline lives on the instance
     * (self._insertImageFile) where it can auto-select the image and open
     * the SEO form; this prototype wrapper stays for API compatibility.
     */
    HuvantiEditor.prototype.insertImageFile = function (file) {
        if (typeof this._insertImageFile === 'function') {
            this._insertImageFile(file);
            return;
        }
        if (!file || file.type.indexOf('image') !== 0) return;
        var self = this;
        var reader = new FileReader();
        reader.onload = function () {
            self.content.focus();
            self.restoreRange();
            document.execCommand('insertHTML', false, '<img src="' + reader.result + '">');
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
        // Enter must create REAL paragraphs. The browser default inside a
        // contenteditable area is <div> (Chrome/Edge) or <br> (Safari), so
        // every paragraph an author typed produced inconsistent markup —
        // bare text nodes and <div>s that the post page then rendered with
        // no spacing at all. 'p' matches what every professional editor
        // stores and what the .prose styles on the site expect.
        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) { /* older browsers: browser default stays */ }
        // NOTE: the old "Google Sans" stylesheet injection was removed — that
        // family is not distributed via Google Fonts, so the request 404'd on
        // every page load while the font stack silently fell back anyway.
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
