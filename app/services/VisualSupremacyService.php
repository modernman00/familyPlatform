<?php

declare(strict_types=1);

namespace App\services;

use Src\Media\SovereignMediaEngine;

/**
 * VisualSupremacyService
 *
 * Thin application facade delegating all Visual Supremacy Engine (VSE)
 * transcoding, metadata stripping, bounds gating, and LQIP synthesis to
 * `Src\Media\SovereignMediaEngine` in `modernman00/shared-lib`.
 */
final class VisualSupremacyService
{
    public const MAX_UNCOMPRESSED_BYTES = SovereignMediaEngine::MAX_UNCOMPRESSED_BYTES;
    public const MAX_DIMENSION = SovereignMediaEngine::MAX_INPUT_DIMENSION;
    public const MAX_FILE_SIZE = SovereignMediaEngine::MAX_FILE_SIZE;
    public const ALLOWED_MIME_TYPES = SovereignMediaEngine::ALLOWED_MIME_TYPES;
    public const SUBPROCESS_TIMEOUT_SECONDS = SovereignMediaEngine::SUBPROCESS_TIMEOUT_SECONDS;

    /**
     * @return array<string, mixed>
     */
    public static function process(string $sourcePath, string $targetDir, string $baseFilename): array
    {
        return SovereignMediaEngine::process($sourcePath, $targetDir, $baseFilename);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function processInPlace(string $filePath): ?array
    {
        return SovereignMediaEngine::processInPlace($filePath);
    }

    public static function validateMediaBounds(string $sourcePath): void
    {
        SovereignMediaEngine::validateMediaBounds($sourcePath);
    }

    public static function stripMetadata(string $filePath): bool
    {
        return SovereignMediaEngine::stripMetadata($filePath);
    }

    public static function applySuperHdUnsharpMask(mixed $gdImg): void
    {
        SovereignMediaEngine::applySuperHdUnsharpMask($gdImg);
    }

    public static function generateMicroPreview(string $sourcePath): ?string
    {
        return SovereignMediaEngine::generateMicroPreview($sourcePath);
    }

    public static function detectColorSpace(string $filePath): string
    {
        return SovereignMediaEngine::detectColorSpace($filePath);
    }
}
