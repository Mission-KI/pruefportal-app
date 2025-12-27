<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * HTML Sanitizer Utility
 *
 * Provides safe HTML sanitization for user-facing content like tooltips.
 * Uses a whitelist approach to allow only safe HTML tags and attributes.
 */
class HtmlSanitizer
{
    /**
     * Allowed HTML tags (whitelist)
     */
    private const ALLOWED_TAGS = [
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        'p',
        'span',
        'ul',
        'ol',
        'li',
    ];

    /**
     * Allowed attributes per tag
     */
    private const ALLOWED_ATTRIBUTES = [
        'span' => ['class'],
    ];

    /**
     * Sanitize HTML content for safe display in tooltips
     *
     * @param string|null $html Raw HTML content
     * @return string|null Sanitized HTML or null if input was null
     */
    public static function sanitizeTooltip(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        // Use PHP's built-in strip_tags with allowed tags
        $allowedTagsString = '<' . implode('><', self::ALLOWED_TAGS) . '>';
        $sanitized = strip_tags($html, $allowedTagsString);

        // Remove dangerous attributes (keep only whitelisted ones)
        $sanitized = self::sanitizeAttributes($sanitized);

        // Remove any JavaScript protocols
        $sanitized = preg_replace('/javascript:/i', '', $sanitized);
        $sanitized = preg_replace('/on\w+\s*=/i', '', $sanitized);

        return $sanitized;
    }

    /**
     * Sanitize attributes in HTML tags
     *
     * @param string $html HTML content with tags
     * @return string HTML with sanitized attributes
     */
    private static function sanitizeAttributes(string $html): string
    {
        // For each allowed tag with attributes, sanitize them
        foreach (self::ALLOWED_ATTRIBUTES as $tag => $allowedAttrs) {
            $pattern = '/<' . $tag . '\s+([^>]*)>/i';
            $html = preg_replace_callback($pattern, function ($matches) use ($tag, $allowedAttrs) {
                $tagContent = $matches[0];
                $attributes = $matches[1];

                // Remove all attributes first
                $cleaned = '<' . $tag;

                // Extract only allowed attributes
                foreach ($allowedAttrs as $attr) {
                    if (preg_match('/' . $attr . '=["\']([^"\']*)["\']/', $attributes, $attrMatch)) {
                        $value = htmlspecialchars($attrMatch[1], ENT_QUOTES, 'UTF-8');
                        $cleaned .= ' ' . $attr . '="' . $value . '"';
                    }
                }

                $cleaned .= '>';

                return $cleaned;
            }, $html);
        }

        return $html;
    }

    /**
     * Strip all HTML tags completely (for plain text output)
     *
     * @param string|null $html HTML content
     * @return string|null Plain text or null if input was null
     */
    public static function toPlainText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        return strip_tags($html);
    }
}
