<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pest\Browser\Playwright\Dialog;
use PHPUnit\Framework\ExpectationFailedException;

it('can handle alert dialog with custom handler', function (): void {
    Route::get('/', fn (): string => '
        <button id="alert-btn" onclick="alert(\'Hello World!\'); document.getElementById(\'result\').textContent = \'Alert handled\';">Show Alert</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        expect($dialog->type())->toBe('alert');
        expect($dialog->message())->toBe('Hello World!');

        $dialog->accept();
    });

    $page->click('#alert-btn');

    expect($page->text('#result'))->toBe('Alert handled');
});

it('can auto dismiss dialog', function (): void {
    Route::get('/', fn (): string => '
        <button id="alert-btn" onclick="alert(\'Hello World!\');">Show Alert</button>
        <p>Normal text on page.</p>
    ');

    $page = visit('/');

    $page->assertSee('Normal text on page.');
});

it('can not auto dismiss dialog when interupted', function (): void {
    Route::get('/', fn (): string => '
        <button id="alert-btn" onclick="alert(\'Hello World!\');">Show Alert</button>
        <p>Normal text on page.</p>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        //
    });

    $page->click('#alert-btn');
})->throws(ExpectationFailedException::class);

it('can handle confirm dialog with acceptance', function (): void {
    Route::get('/', fn (): string => '
        <button id="confirm-btn" onclick="
            var result = confirm(\'Are you sure?\');
            document.getElementById(\'result\').textContent = result ? \'Confirmed\' : \'Cancelled\';
        ">Show Confirm</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        expect($dialog->message())->toBe('Are you sure?');

        $dialog->accept();
    });

    $page->click('#confirm-btn');

    expect($page->text('#result'))->toBe('Confirmed');
});

it('can handle confirm dialog with dismissal', function (): void {
    Route::get('/', fn (): string => '
        <button id="confirm-btn" onclick="
            var result = confirm(\'Are you sure?\');
            document.getElementById(\'result\').textContent = result ? \'Confirmed\' : \'Cancelled\';
        ">Show Confirm</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        expect($dialog->message())->toBe('Are you sure?');

        $dialog->dismiss();
    });

    $page->click('#confirm-btn');

    expect($page->text('#result'))->toBe('Cancelled');
});

it('can handle prompt dialog with custom input', function (): void {
    Route::get('/', fn (): string => '
        <button id="prompt-btn" onclick="
            var result = prompt(\'What is your name?\', \'Default Name\');
            document.getElementById(\'result\').textContent = result || \'No input\';
        ">Show Prompt</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        expect($dialog->message())->toBe('What is your name?');
        expect($dialog->defaultValue())->toBe('Default Name');

        $dialog->accept('John Doe');
    });

    $page->click('#prompt-btn');

    expect($page->text('#result'))->toBe('John Doe');
});

it('can handle prompt dialog with dismissal', function (): void {
    Route::get('/', fn (): string => '
        <button id="prompt-btn" onclick="
            var result = prompt(\'What is your name?\');
            document.getElementById(\'result\').textContent = result || \'No input\';
        ">Show Prompt</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        $dialog->dismiss();
    });

    $page->click('#prompt-btn');

    expect($page->text('#result'))->toBe('No input');
});

it('can auto-accept all dialogs', function (): void {
    Route::get('/', fn (): string => '
        <button id="multi-btn" onclick="
            alert(\'First alert\');
            var confirm_result = confirm(\'Continue?\');
            var prompt_result = prompt(\'Your name?\', \'Default\');
            document.getElementById(\'result\').textContent = confirm_result + \'-\' + prompt_result;
        ">Show Multiple</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->acceptAllDialogs('Test User');

    $page->click('#multi-btn');

    expect($page->text('#result'))->toBe('true-Test User');
});

it('can auto-dismiss all dialogs', function (): void {
    Route::get('/', fn (): string => '
        <button id="multi-btn" onclick="
            alert(\'First alert\');
            var confirm_result = confirm(\'Continue?\');
            var prompt_result = prompt(\'Your name?\');
            document.getElementById(\'result\').textContent = (confirm_result || \'false\') + \'-\' + (prompt_result || \'null\');
        ">Show Multiple</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->dismissAllDialogs();

    $page->click('#multi-btn');

    expect($page->text('#result'))->toBe('false-null');
});

it('can accept one and dismiss all dialogs', function (): void {
    Route::get('/', fn (): string => '
        <button id="multi-btn" onclick="
            alert(\'First alert\');
            var confirm_result = confirm(\'Continue?\');
            var prompt_result = prompt(\'Your name?\');
            document.getElementById(\'result\').textContent = (confirm_result || \'false\') + \'-\' + (prompt_result || \'null\');
        ">Show Multiple</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        if ($dialog->type() === 'alert') {
            $dialog->accept();
        }
    });

    $page->dismissAllDialogs();

    $page->click('#multi-btn');

    expect($page->text('#result'))->toBe('false-null');
});

it('can selectively accept only confirm dialogs', function (): void {
    Route::get('/', fn (): string => '
        <button id="mixed-btn" onclick="
            alert(\'Alert message\');
            var confirm_result = confirm(\'Confirm message\');
            document.getElementById(\'result\').textContent = \'confirm:\' + confirm_result;
        ">Show Mixed</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->acceptingConfirms();

    $page->click('#mixed-btn');

    expect($page->text('#result'))->toBe('confirm:true');
});

it('can selectively dismiss only confirm dialogs', function (): void {
    Route::get('/', fn (): string => '
        <button id="mixed-btn" onclick="
            alert(\'Alert message\');
            var confirm_result = confirm(\'Confirm message\');
            document.getElementById(\'result\').textContent = \'confirm:\' + confirm_result;
        ">Show Mixed</button>
        <div id="result"></div>
    ');

    $page = visit('/');

    $page->dismissingConfirms();

    $page->click('#mixed-btn');

    expect($page->text('#result'))->toBe('confirm:false');
});

it('can remove dialog handlers', function (): void {
    $page = visit('/');

    expect($page->hasDialogHandler())->toBeFalse();

    $page->acceptAllDialogs();
    expect($page->hasDialogHandler())->toBeTrue();

    $page->removeDialogHandler();
    expect($page->hasDialogHandler())->toBeFalse();
});

it('can handle multiple sequential dialogs with different types', function (): void {
    Route::get('/', fn (): string => '
        <button id="sequence-btn" onclick="
            alert(\'Step 1: Alert\');
            var confirm_result = confirm(\'Step 2: Confirm?\');
            if (confirm_result) {
                var prompt_result = prompt(\'Step 3: Your name?\', \'Anonymous\');
                document.getElementById(\'result\').textContent = \'Hello \' + (prompt_result || \'Anonymous\');
            } else {
                document.getElementById(\'result\').textContent = \'Process cancelled\';
            }
        ">Start Sequence</button>
        <div id="result"></div>
    ');

    $stepCount = 0;
    $page = visit('/');

    $page->onDialog(function (Dialog $dialog) use (&$stepCount): void {
        $stepCount++;

        if ($stepCount === 1) {
            expect($dialog->message())->toBe('Step 1: Alert');

            $dialog->accept();
        } elseif ($stepCount === 2) {
            expect($dialog->message())->toBe('Step 2: Confirm?');

            $dialog->accept();
        } elseif ($stepCount === 3) {
            expect($dialog->message())->toBe('Step 3: Your name?');

            $dialog->accept('Integration Test');
        }
    });

    $page->click('#sequence-btn');

    expect($stepCount)->toBe(3);
    expect($page->text('#result'))->toBe('Hello Integration Test');
});

it('can handle dialog expectation failure', function (): void {
    Route::get('/', fn (): string => '
        <button id="confirm-btn" onclick="confirm(\'Are you sure?\');">Show Confirm</button>
    ');

    $page = visit('/');

    $page->onDialog(function (Dialog $dialog): void {
        expect($dialog->message())->toBe('Wrong text');

        $dialog->accept();
    });

    $page->click('Show Confirm');
})->throws(ExpectationFailedException::class);
