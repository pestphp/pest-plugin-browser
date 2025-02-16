<?php

declare(strict_types=1);

namespace Pest\Browser\Operations;

use Closure;
use Pest\Browser\Contracts\Condition;
use Pest\Browser\Contracts\Operation;
use Pest\Browser\PendingTest;

final readonly class When implements Operation
{
    /**
     * Creates an operation instance.
     */
    public function __construct(
        private Condition $condition,
        private Closure $then,
        private ?Closure $else = null
    ) {
        //
    }

    /**
     * Compile the operation.
     */
    public function compile(): string
    {
        $conditionContent = $this->condition->compile();

        $thenContent = $this->compileCallbackOperations($this->then);

        if (is_null($this->else)) {
            return <<<JS
                if (({$conditionContent})) {
                {$thenContent}
                    }
                JS;
        }

        $elseContent = $this->compileCallbackOperations($this->else);

        return <<<JS
                if (({$conditionContent})) {
                {$thenContent}
                    } else {
                {$elseContent}
                    }
                JS;
    }

    /**
     * Compile the operations in callback.
     */
    private function compileCallbackOperations(Closure $closure): string
    {
        $operations = $this->collectCallbackOperations($closure);

        return implode(
            "\n",
            array_map(
                static fn (Operation $operation): string => "\t\t{$operation->compile()}",
                $operations,
            ),
        );
    }

    /**
     * Collect the operations in callback.
     *
     * @return array<int, Operation>
     */
    private function collectCallbackOperations(Closure $closure): array
    {
        $thenTest = new PendingTest(compileTest: false);

        ($closure)($thenTest);

        return $thenTest->operations;
    }
}
