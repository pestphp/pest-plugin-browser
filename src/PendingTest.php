<?php

declare(strict_types=1);

namespace Pest\Browser;

use Pest\Browser\Operations\AssertAttribute;
use Pest\Browser\Operations\AssertAttributeContains;
use Pest\Browser\Operations\AssertAttributeDoesntContain;
use Pest\Browser\Operations\AssertAttributeMissing;
use Pest\Browser\Operations\AssertChecked;
use Pest\Browser\Operations\AssertDontSee;
use Pest\Browser\Operations\AssertHostIs;
use Pest\Browser\Operations\AssertHostIsNot;
use Pest\Browser\Operations\AssertMissing;
use Pest\Browser\Operations\AssertNotChecked;
use Pest\Browser\Operations\AssertNotPresent;
use Pest\Browser\Operations\AssertPathBeginsWith;
use Pest\Browser\Operations\AssertPathContains;
use Pest\Browser\Operations\AssertPathEndsWith;
use Pest\Browser\Operations\AssertPathIs;
use Pest\Browser\Operations\AssertPathIsNot;
use Pest\Browser\Operations\AssertPresent;
use Pest\Browser\Operations\AssertQueryStringHas;
use Pest\Browser\Operations\AssertQueryStringMissing;
use Pest\Browser\Operations\AssertSchemeIs;
use Pest\Browser\Operations\AssertSchemeIsNot;
use Pest\Browser\Operations\AssertSee;
use Pest\Browser\Operations\AssertTitle;
use Pest\Browser\Operations\AssertTitleContains;
use Pest\Browser\Operations\AssertUrlIs;
use Pest\Browser\Operations\AssertVisible;
use Pest\Browser\Operations\Back;
use Pest\Browser\Operations\Check;
use Pest\Browser\Operations\Click;
use Pest\Browser\Operations\ClickLink;
use Pest\Browser\Operations\DoubleClick;
use Pest\Browser\Operations\Forward;
use Pest\Browser\Operations\Refresh;
use Pest\Browser\Operations\Screenshot;
use Pest\Browser\Operations\Uncheck;
use Pest\Browser\Operations\Visit;

/**
 * @internal
 */
final class PendingTest
{
    use AssertAttribute,
        AssertAttributeContains,
        AssertAttributeDoesntContain,
        AssertAttributeMissing,
        AssertChecked,
        AssertDontSee,
        AssertHostIs,
        AssertHostIsNot,
        AssertMissing,
        AssertNotChecked,
        AssertNotPresent,
        AssertPathBeginsWith,
        AssertPathContains,
        AssertPathEndsWith,
        AssertPathIs,
        AssertPathIsNot,
        AssertPresent,
        AssertQueryStringHas,
        AssertQueryStringMissing,
        AssertSchemeIs,
        AssertSchemeIsNot,
        AssertSee,
        AssertTitle,
        AssertTitleContains,
        AssertUrlIs,
        AssertVisible,
        Back,
        Check,
        Click,
        ClickLink,
        DoubleClick,
        Forward,
        Refresh,
        Screenshot,
        Uncheck,
        Visit;
}
