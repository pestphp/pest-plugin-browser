<?php

declare(strict_types=1);

namespace Pest\Browser\Enums;

enum City: string
{
    case AMSTERDAM = 'amsterdam';
    case BERLIN = 'berlin';
    case CHICAGO = 'chicago';
    case HOUSTON = 'houston';
    case LONDON = 'london';
    case LOS_ANGELES = 'losAngeles';
    case MIAMI = 'miami';
    case NEW_YORK = 'newYork';
    case PARIS = 'paris';
    case TOKYO = 'tokyo';
    case TORONTO = 'toronto';
    case SAN_FRANCISCO = 'sanFrancisco';
    case SYDNEY = 'sydney';

    /**
     * Get the timezone for the city.
     */
    public function timezone(): string
    {
        return match ($this) {
            self::AMSTERDAM => 'Europe/Amsterdam',
            self::BERLIN => 'Europe/Berlin',
            self::CHICAGO => 'America/Chicago',
            self::HOUSTON => 'America/Chicago',
            self::LONDON => 'Europe/London',
            self::LOS_ANGELES => 'America/Los_Angeles',
            self::MIAMI => 'America/New_York',
            self::NEW_YORK => 'America/New_York',
            self::PARIS => 'Europe/Paris',
            self::TOKYO => 'Asia/Tokyo',
            self::TORONTO => 'America/Toronto',
            self::SAN_FRANCISCO => 'America/Los_Angeles',
            self::SYDNEY => 'Australia/Sydney',
        };
    }

    /**
     * Get the locale for the city.
     */
    public function locale(): string
    {
        return match ($this) {
            self::AMSTERDAM => 'nl-NL',
            self::BERLIN => 'de-DE',
            self::CHICAGO => 'en-US',
            self::HOUSTON => 'en-US',
            self::LONDON => 'en-GB',
            self::LOS_ANGELES => 'en-US',
            self::MIAMI => 'en-US',
            self::NEW_YORK => 'en-US',
            self::PARIS => 'fr-FR',
            self::TOKYO => 'ja-JP',
            self::TORONTO => 'en-CA',
            self::SAN_FRANCISCO => 'en-US',
            self::SYDNEY => 'en-AU',
        };
    }

    /**
     * Get the geolocation (latitude and longitude) for the city.
     *
     * @return array{latitude: float, longitude: float}
     */
    public function geolocation(): array
    {
        return match ($this) {
            self::AMSTERDAM => ['latitude' => 52.3676, 'longitude' => 4.9041],
            self::BERLIN => ['latitude' => 52.5200, 'longitude' => 13.4050],
            self::CHICAGO => ['latitude' => 41.8781, 'longitude' => -87.6298],
            self::HOUSTON => ['latitude' => 29.7604, 'longitude' => -95.3698],
            self::LONDON => ['latitude' => 51.5074, 'longitude' => -0.1278],
            self::LOS_ANGELES => ['latitude' => 34.0522, 'longitude' => -118.2437],
            self::MIAMI => ['latitude' => 25.7617, 'longitude' => -80.1918],
            self::NEW_YORK => ['latitude' => 40.7128, 'longitude' => -74.0060],
            self::PARIS => ['latitude' => 48.8566, 'longitude' => 2.3522],
            self::TOKYO => ['latitude' => 35.6895, 'longitude' => 139.6917],
            self::TORONTO => ['latitude' => 43.651070, 'longitude' => -79.347015],
            self::SAN_FRANCISCO => ['latitude' => 37.7749, 'longitude' => -122.4194],
            self::SYDNEY => ['latitude' => -33.8688, 'longitude' => 151.2093],
        };
    }
}
