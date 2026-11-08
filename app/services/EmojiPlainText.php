<?php

declare(strict_types=1);

namespace App\services;

/**
 * Plain-text rendering of in-app emoji tokens for surfaces that cannot show images:
 * push notifications, the notification bell, and share hooks / OG text.
 *
 *  - Me-stickers  `:me_clap@ID:`        → "👏"  (RFC-0011)
 *  - Living emoji `:family_clap_3d:`    → "✨"
 */
final class EmojiPlainText
{
    public static function humanize(string $text): string
    {
        if ($text === '' || !str_contains($text, ':')) {
            return $text;
        }
        $stickers = 0;
        $living = 0;
        $out = (string)preg_replace_callback(
            AvatarStickerService::TOKEN_REGEX,
            static fn(array $m): string => AvatarStickerService::REACTIONS[$m[1]],
            $text,
            -1,
            $stickers
        );
        $out = (string)preg_replace('/:[a-z0-9_]+_3d:/i', '✨', $out, -1, $living);
        if ($stickers + $living === 0) {
            return $text; // untouched: never reformat ordinary text such as "10:30"
        }
        // Collapse the double spaces a replaced token can leave behind.
        return trim((string)preg_replace('/[ \t]{2,}/', ' ', $out));
    }
}
