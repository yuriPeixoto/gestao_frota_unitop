<?php

namespace App\Telescope;

use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;

class TelescopeEntryFilter
{
    public static function filter(IncomingEntry $entry): IncomingEntry
    {
        $content = $entry->content;

        if (is_array($content)) {
            $entry->content = self::sanitizeArray($content);
        } elseif (is_string($content)) {
            $entry->content = self::sanitizeString($content);
        }

        return $entry;
    }

    private static function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = self::sanitizeString($value);
            } elseif (is_object($value)) {
                $data[$key] = self::sanitizeArray((array) $value);
            }
        }

        return $data;
    }

    private static function sanitizeString(string $value): string
    {
        $value = str_replace(["\x00", "\u0000"], '', $value);

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8//IGNORE');
        }

        if (strlen($value) > 65000) {
            $value = substr($value, 0, 65000).'... [truncated]';
        }

        return $value;
    }

    public static function register(): void
    {
        Telescope::filter(function (IncomingEntry $entry) {
            if (in_array($entry->type, ['cache', 'redis', 'request', 'query'])) {
                return self::filter($entry);
            }

            return $entry;
        });
    }
}
