<?php
declare(strict_types=1);

namespace App\Utility;

class StringHelper
{
    /**
     * Removes salutation titles from name
     *
     * @param string $name Full name with potential title
     * @return string Name without title
     */
    public static function removeTitle(string $name): string
    {
        $titles = ['Mr', 'Ms', 'Mrs', 'Miss', 'Dr', 'Prof', 'Dr.', 'Prof.', 'Herr', 'Frau'];
        $pattern = '/^(' . implode('|', array_map('preg_quote', $titles)) . ')\s+/i';

        return trim(preg_replace($pattern, '', $name));
    }

    /**
     * Extracts initials from full name (first letter of first and last name)
     * Strips emojis and variation selectors before extraction
     *
     * @param string $name Full name
     * @return string Initials (e.g., "John Doe" -> "JD")
     * @example
     * StringHelper::getInitials('John Doe'); // Returns "JD"
     * StringHelper::getInitials('Test Hase 🕶️'); // Returns "TH"
     * StringHelper::getInitials('Alice'); // Returns "AL"
     */
    public static function getInitials(string $name): string
    {
        $cleanName = self::stripEmojis($name);

        $words = array_filter(array_map('trim', explode(' ', trim($cleanName))));

        if (empty($words)) {
            return '?';
        }

        if (count($words) === 1) {
            $word = $words[0];
            $first = mb_substr($word, 0, 1);
            $second = mb_substr($word, 1, 1) ?: mb_substr($word, 0, 1);

            return mb_strtoupper($first . $second);
        }

        $firstWord = reset($words);
        $lastWord = end($words);

        return mb_strtoupper(mb_substr($firstWord, 0, 1) . mb_substr($lastWord, 0, 1));
    }

    /**
     * Strips emojis, variation selectors, and emoji-related characters from string
     *
     * @param string $text Text containing potential emoji characters
     * @return string Text with emojis removed
     * @example
     * StringHelper::stripEmojis('Hello 🕶️ World'); // Returns "Hello  World"
     */
    public static function stripEmojis(string $text): string
    {
        return preg_replace('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{FE00}-\x{FE0F}\x{1F1E0}-\x{1F1FF}]/u', '', $text);
    }
}
