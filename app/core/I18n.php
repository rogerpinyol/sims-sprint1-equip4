<?php

declare(strict_types=1);

final class I18n
{
    private const DEFAULT_LOCALE = 'en';
    private const SUPPORTED_LOCALES = ['en', 'ca'];

    /** @var array<string,string> */
    private static array $messages = [];
    private static string $locale = self::DEFAULT_LOCALE;

    public static function init(?string $requestedLocale = null): void
    {
        $locale = is_string($requestedLocale) ? strtolower($requestedLocale) : self::DEFAULT_LOCALE;
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = self::DEFAULT_LOCALE;
        }
        self::$locale = $locale;

        $basePath = __DIR__ . '/../lang';
        $fallbackPath = $basePath . '/' . self::DEFAULT_LOCALE . '.php';
        $messages = is_file($fallbackPath) ? (require $fallbackPath) : [];

        if ($locale !== self::DEFAULT_LOCALE) {
            $localePath = $basePath . '/' . $locale . '.php';
            if (is_file($localePath)) {
                $localeMessages = require $localePath;
                if (is_array($localeMessages)) {
                    $messages = array_replace($messages, $localeMessages);
                }
            }
        }

        self::$messages = is_array($messages) ? $messages : [];
    }

    public static function t(string $key, array $vars = []): string
    {
        if (self::$messages === []) {
            self::init(self::DEFAULT_LOCALE);
        }
        $message = self::$messages[$key] ?? $key;

        if ($vars !== []) {
            $replacements = [];
            foreach ($vars as $name => $value) {
                $replacements['{' . $name . '}'] = (string) $value;
            }
            $message = strtr($message, $replacements);
        }

        return $message;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    /**
     * @return array<int,string>
     */
    public static function availableLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }
}

if (!function_exists('__')) {
    function __(string $key, array $vars = []): string
    {
        return I18n::t($key, $vars);
    }
}
