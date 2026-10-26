<?php

declare(strict_types=1);

namespace App\classes;

use Src\I18n as BaseI18n;

/**
 * Class I18n
 *
 * Extends the centralized modernman00/shared-lib I18n engine.
 * Preserves backwards compatibility for existing App\classes\I18n references
 * while inheriting cross-platform cascading localization.
 */
class I18n extends BaseI18n
{
    /**
     * Backwards-compatible alias constant for legacy code.
     */
    public const SUPPORTED_LOCALES = BaseI18n::DEFAULT_LOCALES;
}
