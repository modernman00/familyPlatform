<?php

declare(strict_types=1);

namespace App\controller\members;

use Src\WorldEngine\WorldStudio;

/**
 * WorldStudioController — Exposes the ATG World Engine Studio
 * (CapCut-Class Video & Photo Lab, Explainer Videos, and Posters & Flyers)
 * to authenticated FamilyPlatform members.
 */
final class WorldStudioController
{
    /**
     * GET /world-studio (also /video-photo-lab)
     */
    public function index(): void
    {
        $userId = $_SESSION['id'] ?? null;
        if (!$userId || !is_string($userId)) {
            header('Location: /login');
            exit;
        }

        $mediaUrl = isset($_GET['media_url']) && is_string($_GET['media_url']) ? $_GET['media_url'] : null;
        $mediaKind = isset($_GET['kind']) && $_GET['kind'] === 'video' ? 'video' : 'image';

        (new WorldStudio())->showPage('familyplatform', [
            'fontBase' => '/world-engine/font?file={file}',
            'title' => 'FamilyPlatform · Video & Photo Lab (World Studio)',
            'mediaUrl' => $mediaUrl,
            'mediaKind' => $mediaKind,
        ]);
    }

    /**
     * GET /world-engine/font?file=we-font-*.woff2
     */
    public function font(): void
    {
        $file = isset($_GET['file']) && is_string($_GET['file']) ? $_GET['file'] : '';
        (new WorldStudio())->serveFont($file);
    }
}
