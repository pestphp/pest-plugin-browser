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

        if (self::usesBrowserGroup($factory)) {
            return true;
        }

        return self::usesPageFunction($factory->closure ?? fn (): null => null);
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

    /**
     * Checks if the given factory uses the specified group.
     */
    private static function usesBrowserGroup(TestCaseMethodFactory $factory): bool
    {
        return in_array('browser', $factory->groups, true);
    }

    /**
     * Checks if the given closure uses the "page" function.
     */
    private static function usesPageFunction(Closure $closure): bool
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

        // Tokenize and search for "page" function usage
        $tokens = token_get_all('<?php '.$code);
        $tokensCount = count($tokens);

        for ($i = 0; $i < $tokensCount - 1; $i++) {
            if (
                is_array($tokens[$i]) &&
                $tokens[$i][0] === T_STRING &&
                mb_strtolower($tokens[$i][1]) === 'page' &&
                $tokens[$i + 1] === '('
            ) {
                return true;
            }
        }

        return false;
    }
}
