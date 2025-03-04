<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Operations\AssertDontSee;
use Pest\Browser\Operations\AssertHostIs;
use Pest\Browser\Operations\AssertHostIsNot;
use Pest\Browser\Operations\AssertPathBeginsWith;
use Pest\Browser\Operations\AssertPathContains;
use Pest\Browser\Operations\AssertPathEndsWith;
use Pest\Browser\Operations\AssertPathIs;
use Pest\Browser\Operations\AssertPathIsNot;
use Pest\Browser\Operations\AssertQueryStringHas;
use Pest\Browser\Operations\AssertQueryStringMissing;
use Pest\Browser\Operations\AssertSchemeIs;
use Pest\Browser\Operations\AssertSchemeIsNot;
use Pest\Browser\Operations\AssertSee;
use Pest\Browser\Operations\AssertTitle;
use Pest\Browser\Operations\AssertTitleContains;
use Pest\Browser\Operations\AssertUrlIs;
use Pest\Browser\Operations\Click;
use Pest\Browser\Operations\DoubleClick;
use Pest\Browser\Operations\Refresh;
use Pest\Browser\Operations\Screenshot;
use Pest\Browser\Operations\Visit;

/**
 * @internal
 */
final class PendingTest
{
    use AssertDontSee,
        AssertHostIs,
        AssertHostIsNot,
        AssertPathBeginsWith,
        AssertPathContains,
        AssertPathEndsWith,
        AssertPathIs,
        AssertPathIsNot,
        AssertQueryStringHas,
        AssertQueryStringMissing,
        AssertSchemeIs,
        AssertSchemeIsNot,
        AssertSee,
        AssertTitle,
        AssertTitleContains,
        AssertUrlIs,
        Click,
        DoubleClick,
        Refresh,
        Screenshot,
        Visit;
}
