1. Initial Setup
- Scan and understand the entire project before starting.
- Get familiar with all existing conventions, coding styles, formats, and patterns.
- New code must strictly follow the same style — naming, structure, logic patterns, formatting, and architecture.
- Always add tests for new code, matching the structure and style of existing tests.


2. Tooling
- Respect all tools and dependencies in composer.json.
- Use tools like PHPStan, Rector, Pest, etc., as configured — no overrides.

3. PHP Standards
- Use PHP 8.4 syntax and features.
- Enforce strict typing: scalar types, return types, property types — everywhere.
- Strict array shapes only — no loose or untyped arrays.
- Use enums for fixed values.
- Never use mixed types — including in array shapes.
- Do not auto-format or touch unrelated code.

4. Code Quality
- Apply existing naming, formatting, and architectural patterns exactly.
- Do not deviate from established conventions.
- No commented-out code.
- Avoid magic strings and numbers.
- Keep classes/functions short, focused, and testable.

5. Testing
- When editing code, run only the related tests during development.
- Before finishing the task, run `composer test` to ensure the full suite passes.
- When using pest, always make sure to use chainable `expect()` methods:

```php
expect($value)->toBeTrue()
    ->and($anotherValue)->toBeFalse();
```

6. Other
- Prefer value objects over raw arrays when appropriate.
- Avoid over-engineering — keep things simple and pragmatic.
- Never leave TODOs or FIXMEs without clear context or a linked issue.
- Never leave comments within code blocks, only on methods.
