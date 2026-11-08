<?php

declare(strict_types=1);

/**
 * ATG 3D Emoji & Avatar Studio Routes
 * Developed by Advanced Technology Group (ATG Pods 3 & 4)
 */

$router->map('GET', '/member/emoji-studio', 'App\controller\members\AtgEmojiController@studio', 'atg_emoji_studio');
$router->map('GET', '/member/emojiStudio', 'App\controller\members\AtgEmojiController@studio', 'atg_emoji_studio_alias');
$router->map('GET', '/api/emoji/catalog', 'App\controller\members\AtgEmojiController@catalog', 'api_emoji_catalog');
$router->map('POST', '/api/emoji/render-avatar', 'App\controller\members\AtgEmojiController@renderAvatar', 'api_emoji_render_avatar');
$router->map('GET', '/api/emoji/svg/[*:id]', 'App\controller\members\AtgEmojiController@renderEmojiSvg', 'api_emoji_render_svg');
$router->map('POST', '/api/emoji/save-avatar', 'App\controller\members\AtgEmojiController@saveAvatar', 'api_emoji_save_avatar');
$router->map('POST', '/api/emoji/photo-to-avatar', 'App\controller\members\AtgEmojiController@photoToAvatar', 'api_emoji_photo_to_avatar');
// RFC-0011 Me-stickers (session-gated: must NOT be in $publicControllers)
$router->map('GET', '/api/emoji/me-stickers', 'App\controller\members\AtgEmojiController@meStickers', 'api_emoji_me_stickers');
$router->map('GET', '/api/emoji/avatar-sticker/[:userId]/[a:reaction]', 'App\controller\members\AtgEmojiController@avatarSticker', 'api_emoji_avatar_sticker');
