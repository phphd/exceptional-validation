<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\Dto\ThrownExceptionPackage;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;

/** @internal */
final class PropertyRuleSet implements CaptureRule
{
    public function __construct(
        private readonly CaptureRule $parent,
        private readonly string $name,
        private readonly mixed $value,
        private readonly CaptureRule $ruleSet,
    ) {
    }

    public function capture(ThrownExceptionPackage $exceptions): array
    {
        return $this->ruleSet->capture($exceptions);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath()->with($this->name);
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
        return $this->value;
    }
}
