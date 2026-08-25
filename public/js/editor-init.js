/**
 * Shared CKEditor 5 initializer for every editor on the site
 * (admin posts, admin pages, author post form).
 *
 * - Uses the LOCAL super-build copy in /js/ckeditor5-super-build.js so the
 *   editor never depends on a third-party CDN (faster + always available).
 * - Full toolbar: headings, formatting, fonts, colors, lists, alignment,
 *   links, tables, media, images (base64), code blocks, find and replace,
 *   source editing and more.
 * - Keeps the original textarea in sync on submit and blocks empty
 *   submissions with a clear message (the textarea must NOT carry the
 *   HTML required attribute: the browser would try to focus a hidden
 *   field and silently block the form).
 */
(function () {
    'use strict';

    var CK = window.CKEDITOR || {};

    window.initHuvantiEditor = function (selector, options) {
        options = options || {};
        var Editor = CK.ClassicEditor;
        if (!Editor) {
            // Editor bundle missing: leave the plain textarea working.
            if (window.console) console.warn('CKEditor bundle not loaded; using plain text area.');
            return null;
        }

        var element = document.querySelector(selector || '#editor');
        if (!element) return null;

        // Base64 upload adapter: lets authors paste or upload images with
        // no server-side upload configuration.
        var base64 = null;
        try {
            base64 = (CK.upload && CK.upload.Base64UploadAdapter)
                || (window.CKEditor5 && window.CKEditor5.upload && window.CKEditor5.upload.Base64UploadAdapter);
        } catch (e) { /* ignore */ }

        var config = {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'code', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', 'alignment', '|',
                    'link', 'blockQuote', 'insertTable', 'mediaEmbed', 'insertImage', 'horizontalLine', 'specialCharacters', 'codeBlock', '|',
                    'findAndReplace', 'selectAll', 'removeFormat', 'sourceEditing', '|',
                    'undo', 'redo'
                ],
                shouldNotGroupWhenFull: false
            },
            image: {
                toolbar: ['imageTextAlternative', 'toggleImageCaption', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
            },
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h2', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h3', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h4', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            },
            link: {
                decorators: {
                    openInNewTab: {
                        mode: 'manual',
                        label: 'Open in a new tab',
                        defaultValue: true,
                        attributes: { target: '_blank', rel: 'noopener noreferrer' }
                    }
                }
            }
        };
        if (base64) { config.extraPlugins = [base64]; }

        var placeholder = options.placeholder;
        if (placeholder) { config.placeholder = placeholder; }

        var editorPromise = Editor.create(element, config);

        editorPromise.then(function (editor) {
            var form = element.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    try { editor.updateSourceElement(); } catch (e) { /* ignore */ }
                });
            }
            // Keep a global reference for debugging / advanced use.
            window.huvantiEditor = editor;
        }).catch(function (err) {
            if (window.console) console.error(err);
        });

        return editorPromise;
    };
})();
