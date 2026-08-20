<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Lets a test control what file_get_contents('php://input') returns, since
 * FamilyRequestController::request() reads the raw JSON body that way and
 * there's no real HTTP request body in a CLI test process.
 *
 * Registering this replaces the *entire* "php" stream wrapper, not just
 * php://input — unrelated code that happens to run while it's active (e.g.
 * Symfony Mailer building a message via php://temp during error-logging)
 * still needs php://temp, php://memory, php://output etc. to work, so every
 * path other than php://input is proxied to a real temp file instead of
 * failing closed.
 *
 * Usage:
 *   PhpInputStreamWrapper::setContent(json_encode($payload));
 *   PhpInputStreamWrapper::register();
 *   // ... call the controller ...
 *   PhpInputStreamWrapper::restore();
 */
class PhpInputStreamWrapper
{
    private static string $content = '';
    private int $position = 0;

    /** @var resource|null backing file for any php:// path that isn't php://input */
    private $passthroughHandle = null;

    /**
     * PHP's stream wrapper API sets this on every wrapper instance for stream
     * context support. Without an explicit declaration it's created as a
     * dynamic property, which fires a deprecation notice — and code that
     * converts warnings to exceptions during fopen() (e.g. Guzzle's
     * Utils::tryFopen(), used deep in the mail/HTTP stack) turns that notice
     * into a thrown exception, breaking anything unrelated that opens a
     * php://temp stream while this wrapper is registered.
     */
    public $context;

    public static function setContent(string $content): void
    {
        self::$content = $content;
    }

    public static function register(): void
    {
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', self::class);
    }

    public static function restore(): void
    {
        stream_wrapper_restore('php');
    }

    public function stream_open(string $path): bool
    {
        if ($path === 'php://input') {
            $this->position = 0;
            return true;
        }

        $this->passthroughHandle = tmpfile();
        return $this->passthroughHandle !== false;
    }

    public function stream_read(int $count): string|false
    {
        if ($this->passthroughHandle !== null) {
            return fread($this->passthroughHandle, $count);
        }

        $chunk = substr(self::$content, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function stream_write(string $data): int
    {
        if ($this->passthroughHandle !== null) {
            $written = fwrite($this->passthroughHandle, $data);
            return $written === false ? 0 : $written;
        }

        return 0;
    }

    public function stream_eof(): bool
    {
        if ($this->passthroughHandle !== null) {
            return feof($this->passthroughHandle);
        }

        return $this->position >= strlen(self::$content);
    }

    public function stream_tell(): int
    {
        if ($this->passthroughHandle !== null) {
            $pos = ftell($this->passthroughHandle);
            return $pos === false ? 0 : $pos;
        }

        return $this->position;
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        if ($this->passthroughHandle !== null) {
            return fseek($this->passthroughHandle, $offset, $whence) === 0;
        }

        return false;
    }

    public function stream_stat(): array
    {
        if ($this->passthroughHandle !== null) {
            $stat = fstat($this->passthroughHandle);
            return $stat === false ? [] : $stat;
        }

        return [];
    }

    public function stream_close(): void
    {
        if ($this->passthroughHandle !== null) {
            fclose($this->passthroughHandle);
            $this->passthroughHandle = null;
        }
    }
}
