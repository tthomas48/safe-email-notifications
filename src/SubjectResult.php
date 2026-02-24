<?php

namespace Freescout\SafeEmailNotifications;

/**
 * Value object for a built subject (prefix + message key + replace params).
 * Translation is done by the caller (e.g. ServiceProvider) so SubjectBuilder stays framework-agnostic.
 */
final class SubjectResult
{
    /** @var string */
    public $prefix;

    /** @var string */
    public $key;

    /** @var array */
    public $replace;

    public function __construct(string $prefix, string $key, array $replace = [])
    {
        $this->prefix = $prefix;
        $this->key = $key;
        $this->replace = $replace;
    }

    /**
     * Build final subject string using a translator callable (e.g. Laravel __()).
     */
    public function format(callable $translator): string
    {
        $message = $translator('safeemailnotifications::messages.' . $this->key, $this->replace);
        return $this->prefix . $message;
    }
}
