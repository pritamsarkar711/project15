<?php

namespace App\Services;

/**
 * Allowlist HTML sanitizer for rich text content.
 *
 * Used for post content (author + admin submitted) so stored content can
 * never carry script tags, event handlers or javascript: URLs. Runs on save
 * AND on render (defense in depth for legacy rows).
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p','br','hr','strong','b','em','i','u','s','del','ins','mark','code','pre',
        'h1','h2','h3','h4','h5','h6','blockquote','q',
        'ul','ol','li','table','thead','tbody','tr','th','td',
        'a','img','figure','figcaption','span','div','sup','sub','small',
        // execCommand('foreColor' | 'hiliteColor' | 'fontName' | 'fontSize')
        // serialises to <font color=... face=... size=...> in Chrome, Edge,
        // Firefox and Safari. Without allowlisting it, every text colour,
        // highlight, font family and font size the editor applies was
        // SILENTLY DELETED on save (the tag was unwrapped, its attributes
        // dropped) — the stored post then looked nothing like the editor.
        'font',
    ];

    private const ALLOWED_ATTRS = [
        'a'    => ['href', 'title', 'target', 'rel'],
        'img'  => ['src', 'alt', 'width', 'height', 'loading', 'decoding'],
        'th'   => ['colspan', 'rowspan', 'style'],
        'td'   => ['colspan', 'rowspan', 'style'],
        'font' => ['color', 'face', 'size', 'style'],
        // style= carries the editor's line-height, text colour fallbacks
        // (style="color:...") and background highlights. The style value is
        // vetted below (expression/behavior/position/javascript rejected).
        'span' => ['style'],
        'div'  => ['style'],
        'p'    => ['style'],
        'li'   => ['style'],
        'blockquote' => ['style'],
        'h1'   => ['style'],
        'h2'   => ['style'],
        'h3'   => ['style'],
        'h4'   => ['style'],
        'h5'   => ['style'],
        'h6'   => ['style'],
        'mark' => ['style'],
        'u'    => ['style'],
        's'    => ['style'],
        'em'   => ['style'],
        'strong' => ['style'],
        'b'    => ['style'],
        'i'    => ['style'],
        'small' => ['style'],
        'sup'  => ['style'],
        'sub'  => ['style'],
        'figure' => ['style'],
        'figcaption' => ['style'],
        'pre'  => ['style'],
        'code' => ['style'],
    ];

    /** style="..." values that never pass, even though style itself is allowed. */
    private const BLOCKED_STYLE_PATTERN = '/(expression|behavior|position\s*:\s*fixed|javascript|vbscript|import\s|url\s*\(\s*["\']?\s*data)/i';

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        // mb_encode_numericentity keeps UTF-8 intact through loadHTML.
        $doc->loadHTML(
            mb_encode_numericentity($html, [0x80, 0x10FFFF, 0, 0xFFFFFFFF], 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $all = $doc->getElementsByTagName('*');
        for ($i = $all->length - 1; $i >= 0; $i--) {
            $node = $all->item($i);
            $tag = strtolower($node->nodeName);

            // Drop dangerous elements entirely (with their children).
            if (in_array($tag, ['script','style','iframe','object','embed','form','input','button','select','textarea','link','meta','base','applet','frame','frameset'], true)) {
                $node->parentNode?->removeChild($node);
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unwrap: keep children text/nodes, drop the unknown wrapper.
                while ($node->firstChild) {
                    $node->parentNode->insertBefore($node->firstChild, $node);
                }
                $node->parentNode?->removeChild($node);
                continue;
            }

            // Strip every attribute, then re-add allowlisted safe ones.
            $attrs = iterator_to_array($node->attributes ?? []);
            foreach ($attrs as $attr) {
                $node->removeAttribute($attr->name);
            }
            foreach (self::ALLOWED_ATTRS[$tag] ?? [] as $keep) {
                $val = $attrs[$keep]->value ?? null;
                if ($val === null) continue;
                $lower = strtolower($val);
                if (str_contains($lower, 'javascript:') || str_contains($lower, 'vbscript:') || str_contains($lower, 'data:text')) {
                    continue;
                }
                if (str_starts_with((string) $keep, 'on')) {
                    continue;
                }
                if ($keep === 'style' && preg_match(self::BLOCKED_STYLE_PATTERN, $val)) {
                    continue;
                }
                $node->setAttribute($keep, $val);
            }

            // Hardened links: always safe rel on target=_blank.
            if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
                $node->setAttribute('rel', 'noopener noreferrer');
            }
        }

        $body = $doc->getElementsByTagName('body')->item(0);
        $out = '';
        foreach ($body?->childNodes ?? [] as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }
}
