<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use Throwable;

/** @internal */
final class CaptureExceptionRule implements CaptureRule, MatchCondition
{
    public function __construct(
        private readonly CaptureRule $parent,
        private readonly MatchCondition $condition,
        private readonly string $messageTemplate,
        private readonly string $formatterId,
    ) {
    }

    public function process(ExceptionPackage $package): bool
    {
        $package->processRule($this);

        return $package->isProcessed();
    }

    public function matches(Throwable $exception): bool
    {
        return $this->condition->matches($exception);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRoot(): object
    {
        return $this->parent->getRoot();
    }

    public function getValue(): mixed
    {
        return $this->parent->getValue();
    }

    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    public function getFormatterId(): string
    {
        return $this->formatterId;
    }
}
