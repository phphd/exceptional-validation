<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler;

use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Collector\ExceptionPackageCollector;
use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationListFormatter;
use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use Throwable;

/** @internal */
final class ExceptionalHandler implements ExceptionHandler
{
    public function __construct(
        private readonly ObjectRuleSetAssembler $ruleSetAssembler,
        private readonly ExceptionPackageCollector $exceptionPackageCollector,
        private readonly ExceptionViolationListFormatter $violationsFormatter,
    ) {
    }

    public function capture(object $message, Throwable $exception): never
    {
        $ruleSet = $this->ruleSetAssembler->assemble($message);

        if (null === $ruleSet) {
            throw $exception;
        }

        $thrownExceptions = $this->exceptionPackageCollector->collect($exception);

        $caughtExceptions = $ruleSet->capture($thrownExceptions);

        if (count($thrownExceptions) === count($caughtExceptions)) {
            // the condition is always false
        }

        if ([] === $caughtExceptions) {
            throw $exception;
        }

        $violationList = $this->violationsFormatter->formatViolations($caughtExceptions);

        throw new ExceptionalValidationFailedException($message, $violationList, $exception);
    }
}
