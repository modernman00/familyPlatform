<?php

declare(strict_types=1);

namespace App\controller;

use App\services\AvatarStickerService;

/**
 * PublicLivingAvatarController
 *
 * Public viral teaser funnel for Living Photo Avatars (GET /living-avatar).
 * Whitelisted in RouteDispatch::$publicControllers; performs zero DB writes
 * and processes uploaded selfies 100% on-device in the browser.
 */
final class PublicLivingAvatarController
{
    private const VALID_FRAMES = ['none', 'birthday', 'sunday', 'proud', 'surprise'];
    private const VALID_PACKS = ['classic', 'royal_crown', 'fireworks', 'kente_prism'];

    public function show(): void
    {
        $reaction = strtolower(trim((string)($_GET['reaction'] ?? 'hello')));
        if (!AvatarStickerService::isValidReaction($reaction)) {
            $reaction = 'hello';
        }

        $frame = strtolower(trim((string)($_GET['frame'] ?? 'none')));
        if (!in_array($frame, self::VALID_FRAMES, true)) {
            $frame = 'none';
        }

        $pack = strtolower(trim((string)($_GET['pack'] ?? 'classic')));
        if (!in_array($pack, self::VALID_PACKS, true)) {
            $pack = 'classic';
        }

        $rawFrom = trim((string)($_GET['from'] ?? ''));
        $cleanFrom = preg_replace('/[^A-Za-z0-9 \'-]/', '', $rawFrom) ?? '';
        $fromName = mb_substr(trim($cleanFrom), 0, 32);

        $reactionLabel = AvatarStickerService::REACTION_LABELS[$reaction] ?? 'Hello';
        $reactionEmoji = AvatarStickerService::REACTIONS[$reaction] ?? '👋';

        $ogTitle = $fromName !== ''
            ? "{$fromName} sent you a Living Avatar ({$reactionLabel} {$reactionEmoji}) — Try yours!"
            : "Living Photo Avatar ({$reactionLabel} {$reactionEmoji}) | FamilyPlatform";

        $ogDescription = 'Bring any portrait to life with 60fps breathing, eye blinks, reaction particles and downloadable video — free on FamilyPlatform.';

        view('public.livingAvatar', [
            'reaction'      => $reaction,
            'frame'         => $frame,
            'pack'          => $pack,
            'fromName'      => $fromName,
            'reactionLabel' => $reactionLabel,
            'reactionEmoji' => $reactionEmoji,
            'ogTitle'       => $ogTitle,
            'ogDescription' => $ogDescription,
        ]);
    }
}
