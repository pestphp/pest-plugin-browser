<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

use Closure;
use Pest\Factories\TestCaseMethodFactory;
use Pest\TestSuite;
use ReflectionException;
use ReflectionFunction;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final readonly class BrowserTestIdentifier
{
    /**
     * Checks if the given closure uses the "page" function.
     */
    public static function isBrowserTest(TestCaseMethodFactory $factory): bool
    {
        if (self::usesBrowserFolder($factory)) {
            return true;
        }

        return self::usesFunction($factory->closure ?? fn (): null => null, 'visit');
    }

    public static function isDebugTest(TestCaseMethodFactory $factory): bool
    {
        return self::usesFunction($factory->closure ?? fn (): null => null, 'debug');
    }

    /**
     * Checks if the given factory is in the "browser" folder.
     */
    private static function usesBrowserFolder(TestCaseMethodFactory $factory): bool
    {
        $filename = $factory->filename;

        return str_starts_with($filename, implode('', [
            TestSuite::getInstance()->rootPath,
            DIRECTORY_SEPARATOR,
            TestSuite::getInstance()->testPath,
            DIRECTORY_SEPARATOR,
            'Browser',
            DIRECTORY_SEPARATOR,
        ]));
    }

    private static function usesFunction(Closure $closure, string $functionName): bool
    {
        try {
            $ref = new ReflectionFunction($closure);
        } catch (ReflectionException) {
            return false;
        }

        $file = $ref->getFileName();

        if ($file === false) {
            return false;
        }

        $startLine = $ref->getStartLine();
        $endLine = $ref->getEndLine();
        $lines = file($file);

        if (is_array($lines) === false || $startLine < 1 || $endLine > count($lines)) {
            return false;
        }

        // @phpstan-ignore-next-line
        $code = implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $tokens = token_get_all('<?php '.$code);
        $tokensCount = count($tokens);

        for ($i = 0; $i < $tokensCount - 1; $i++) {
            if (is_array($tokens[$i]) &&
            in_array($tokens[$i][0], [T_STRING, T_NAME_FULLY_QUALIFIED], true) &&
            mb_strtolower(mb_ltrim($tokens[$i][1], '\\')) === $functionName &&
            $tokens[$i + 1] === '(') {
                if ($functionName === 'debug') {
                    return true;
                }

                if (($tokens[$i - 1][1] ?? '') === '::' && ($tokens[$i - 2][1] ?? '') === 'Livewire') {
                    return true;
                }

                return $tokens[$i - 1][0] === T_WHITESPACE;
            }
        }

        return false;
    }
}
