<?php

declare(strict_types=1);

use Pest\Browser\Support\JavaScriptSerializer;

it('serializes primitive values correctly', function (): void {
    expect(JavaScriptSerializer::serializeValue(null))->toBe(['v' => 'null']);
    expect(JavaScriptSerializer::serializeValue(true))->toBe(['b' => true]);
    expect(JavaScriptSerializer::serializeValue(false))->toBe(['b' => false]);
    expect(JavaScriptSerializer::serializeValue(42))->toBe(['n' => 42]);
    expect(JavaScriptSerializer::serializeValue(3.14))->toBe(['n' => 3.14]);
    expect(JavaScriptSerializer::serializeValue('hello'))->toBe(['s' => 'hello']);
});

it('serializes special numeric values correctly', function (): void {
    expect(JavaScriptSerializer::serializeValue(NAN))->toBe(['v' => 'NaN']);
    expect(JavaScriptSerializer::serializeValue(INF))->toBe(['v' => 'Infinity']);
    expect(JavaScriptSerializer::serializeValue(-INF))->toBe(['v' => '-Infinity']);
});

it('serializes arrays correctly', function (): void {
    $array = JavaScriptSerializer::serializeValue([1, 2, 3]);

    expect($array)->toHaveKey('a');
    expect($array['a'])->toHaveCount(3);
    expect($array['a'][0])->toBe(['n' => 1]);
    expect($array['a'][1])->toBe(['n' => 2]);
    expect($array['a'][2])->toBe(['n' => 3]);
});

it('serializes associative arrays as objects correctly', function (): void {
    $assoc = JavaScriptSerializer::serializeValue(['name' => 'John', 'age' => 30]);

    expect($assoc)->toHaveKey('o');
    expect($assoc['o'])->toBeArray();

    $hasName = false;
    $hasAge = false;

    foreach ($assoc['o'] as $item) {
        if ($item['k'] === 'name' && $item['v'] === ['s' => 'John']) {
            $hasName = true;
        }
        if ($item['k'] === 'age' && $item['v'] === ['n' => 30]) {
            $hasAge = true;
        }
    }

    expect($hasName)->toBeTrue();
    expect($hasAge)->toBeTrue();
});

it('serializes objects correctly', function (): void {
    $obj = new stdClass();
    $obj->name = 'Jane';
    $obj->active = true;

    $result = JavaScriptSerializer::serializeValue($obj);

    expect($result)->toHaveKey('o');
    expect($result['o'])->toBeArray();

    $hasName = false;
    $hasActive = false;

    foreach ($result['o'] as $item) {
        if ($item['k'] === 'name' && $item['v'] === ['s' => 'Jane']) {
            $hasName = true;
        }
        if ($item['k'] === 'active' && $item['v'] === ['b' => true]) {
            $hasActive = true;
        }
    }

    expect($hasName)->toBeTrue();
    expect($hasActive)->toBeTrue();
});

it('serializes nested structures correctly', function (): void {
    $nested = [
        'user' => [
            'name' => 'Alice',
            'tags' => ['developer', 'tester'],
        ],
    ];

    $result = JavaScriptSerializer::serializeValue($nested);

    expect($result)->toHaveKey('o');
    expect($result['o'][0]['k'])->toBe('user');
    expect($result['o'][0]['v'])->toHaveKey('o');

    $user = $result['o'][0]['v']['o'];
    expect($user)->toContain(['k' => 'name', 'v' => ['s' => 'Alice']]);

    $tags = null;
    foreach ($user as $prop) {
        if ($prop['k'] === 'tags') {
            $tags = $prop['v'];
            break;
        }
    }

    expect($tags)->not->toBeNull();
    expect($tags)->toHaveKey('a');
    expect($tags['a'])->toContain(['s' => 'developer']);
    expect($tags['a'])->toContain(['s' => 'tester']);
});

it('creates argument structure correctly', function (): void {
    $arg = ['test' => true];
    $result = JavaScriptSerializer::serializeArgument($arg);

    expect($result)->toHaveKeys(['value', 'handles']);
    expect($result['value'])->toHaveKey('o');
    expect($result['handles'])->toBeArray();
    expect($result['handles'])->toBeEmpty();
});

it('parses primitive values correctly', function (): void {
    expect(JavaScriptSerializer::parseValue(['v' => 'null']))->toBeNull();
    expect(JavaScriptSerializer::parseValue(['v' => 'undefined']))->toBeNull();
    expect(JavaScriptSerializer::parseValue(['b' => true]))->toBeTrue();
    expect(JavaScriptSerializer::parseValue(['b' => false]))->toBeFalse();
    expect(JavaScriptSerializer::parseValue(['n' => 42]))->toBe(42);
    expect(JavaScriptSerializer::parseValue(['n' => 3.14]))->toBe(3.14);
    expect(JavaScriptSerializer::parseValue(['s' => 'hello']))->toBe('hello');
    expect(JavaScriptSerializer::parseValue(['v' => 'NaN']))->toBeNan();
    expect(JavaScriptSerializer::parseValue(['v' => 'Infinity']))->toBe(INF);
    expect(JavaScriptSerializer::parseValue(['v' => '-Infinity']))->toBe(-INF);
});

it('parses arrays correctly', function (): void {
    $array = [
        'a' => [
            ['n' => 1],
            ['n' => 2],
            ['s' => 'test'],
        ],
    ];

    $result = JavaScriptSerializer::parseValue($array);

    expect($result)->toBeArray();
    expect($result)->toBe([1, 2, 'test']);
});

it('parses objects correctly', function (): void {
    $object = [
        'o' => [
            ['k' => 'name', 'v' => ['s' => 'John']],
            ['k' => 'age', 'v' => ['n' => 30]],
            ['k' => 'active', 'v' => ['b' => true]],
        ],
    ];

    $result = JavaScriptSerializer::parseValue($object);

    expect($result)->toBeArray();
    expect($result)->toBe([
        'name' => 'John',
        'age' => 30,
        'active' => true,
    ]);
});

it('parses nested structures correctly', function (): void {
    $nested = [
        'o' => [
            [
                'k' => 'user',
                'v' => [
                    'o' => [
                        ['k' => 'name', 'v' => ['s' => 'Alice']],
                        [
                            'k' => 'hobbies',
                            'v' => [
                                'a' => [
                                    ['s' => 'reading'],
                                    ['s' => 'coding'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $result = JavaScriptSerializer::parseValue($nested);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('user');
    expect($result['user'])->toHaveKeys(['name', 'hobbies']);
    expect($result['user']['name'])->toBe('Alice');
    expect($result['user']['hobbies'])->toBe(['reading', 'coding']);
});

it('serializes DateTimeImmutable objects correctly', function (): void {
    $date = new DateTimeImmutable('2023-01-01T12:00:00Z');
    $result = JavaScriptSerializer::serializeValue($date);

    expect($result)->toHaveKey('d');
    expect($result['d'])->toBe('2023-01-01T12:00:00+00:00');
});

it('parses DateTimeImmutable values correctly', function (): void {
    $dateValue = ['d' => '2023-01-01T12:00:00+00:00'];
    $result = JavaScriptSerializer::parseValue($dateValue);

    expect($result)->toBeInstanceOf(DateTimeImmutable::class);
    expect($result->format('Y-m-d\TH:i:s\Z'))->toBe('2023-01-01T12:00:00Z');
});

it('serializes big integers correctly', function (): void {
    $bigInt = 9007199254740993; // Greater than JavaScript's MAX_SAFE_INTEGER
    $result = JavaScriptSerializer::serializeValue($bigInt);

    expect($result)->toHaveKey('bi');
    expect($result['bi'])->toBe('9007199254740993');
});

it('parses big integer values correctly', function (): void {
    $bigIntValue = ['bi' => '9007199254740993'];
    $result = JavaScriptSerializer::parseValue($bigIntValue);

    expect($result)->toBe('9007199254740993');
});

it('serializes resources as strings (fallback)', function (): void {
    $resource = fopen('php://memory', 'r');
    $result = JavaScriptSerializer::serializeValue($resource);

    expect($result)->toHaveKey('s');
    expect($result['s'])->toBeString();
    expect($result['s'])->toContain('Resource');

    fclose($resource);
});

it('parses values that are not arrays as-is', function (): void {
    expect(JavaScriptSerializer::parseValue('plain string'))->toBe('plain string');
    expect(JavaScriptSerializer::parseValue(42))->toBe(42);
    expect(JavaScriptSerializer::parseValue(true))->toBeTrue();
    expect(JavaScriptSerializer::parseValue(null))->toBeNull();
});
