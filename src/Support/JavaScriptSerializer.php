<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use DateTimeImmutable;

/**
 * Handles serialization and deserialization of JavaScript values.
 *
 * @internal
 */
final class JavaScriptSerializer
{
    /**
     * Serialize arguments for JavaScript evaluation according to Playwright protocol.
     *
     * @return array<string, mixed>
     */
    public static function serializeArgument(mixed $value): array
    {
        return [
            'value' => self::serializeValue($value),
            'handles' => [],
        ];
    }

    /**
     * Serialize a value according to Playwright's serialization format.
     *
     * @return array<string, mixed>
     */
    public static function serializeValue(mixed $value): array
    {
        if ($value === null) {
            return ['v' => 'null'];
        }

        if (is_bool($value)) {
            return ['b' => $value];
        }

        if (is_int($value) || is_float($value)) {
            if (is_float($value) && is_nan($value)) {
                return ['v' => 'NaN'];
            }
            if (is_float($value) && is_infinite($value)) {
                return ['v' => $value > 0 ? 'Infinity' : '-Infinity'];
            }
            if (is_int($value) && ($value < -9007199254740992 || $value > 9007199254740991)) {
                return ['bi' => (string) $value];
            }

            return ['n' => $value];
        }

        if (is_string($value)) {
            return ['s' => $value];
        }

        if (is_array($value)) {
            $isAssoc = array_keys($value) !== range(0, count($value) - 1);
            if ($isAssoc) {
                $result = [];
                foreach ($value as $key => $val) {
                    $result[] = ['k' => $key, 'v' => self::serializeValue($val)];
                }

                return ['o' => $result];
            }
            $result = [];
            foreach ($value as $item) {
                $result[] = self::serializeValue($item);
            }

            return ['a' => $result];
        }

        if (is_object($value)) {
            if ($value instanceof DateTimeImmutable) {
                return ['d' => $value->format('c')];
            }

            $result = [];
            foreach (get_object_vars($value) as $key => $val) {
                $result[] = ['k' => $key, 'v' => self::serializeValue($val)];
            }

            return ['o' => $result];
        }

        return ['s' => (string) $value]; // @phpstan-ignore-line
    }

    /**
     * Parse a value from JavaScript according to Playwright protocol.
     */
    public static function parseValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        /**
         * @var array{
         *      v?: string, // null, undefined, NaN, Infinity, -Infinity
         *      b?: bool,   // boolean
         *      n?: int|float, // number
         *      s?: string, // string
         *      bi?: string, // bigint
         *      d?: string, // date in ISO 8601 format
         *      a?: array<mixed>, // array
         *      o?: array<array{ k: string, v: mixed }>, // object
         *  }  $value
         */
        // Handle primitive values
        if (isset($value['v'])) {
            return match ($value['v']) {
                'null' => null,
                'undefined' => null,
                'NaN' => NAN,
                'Infinity' => INF,
                '-Infinity' => -INF,
                default => $value['v'],
            };
        }

        if (isset($value['b'])) {
            return $value['b'];
        }

        if (isset($value['n'])) {
            return $value['n'];
        }

        if (isset($value['s'])) {
            return $value['s'];
        }

        if (isset($value['bi'])) {
            return $value['bi'];
        }

        if (isset($value['d'])) {
            return new DateTimeImmutable($value['d']);
        }

        // Handle arrays
        if (isset($value['a'])) {
            return array_map(self::parseValue(...), $value['a']);
        }

        // Handle objects
        if (isset($value['o'])) {
            $result = [];
            foreach ($value['o'] as $item) {
                $result[$item['k']] = self::parseValue($item['v']);
            }

            return $result;
        }

        return $value;
    }
}
