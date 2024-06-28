<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\Dto\ThrownExceptionPackage;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;

/** @internal */
final class ObjectRuleSet implements CaptureRule
{
    public function __construct(
        private readonly object $object,
        private readonly ?CaptureRule $parent,
        private readonly CaptureRule $ruleSet,
    ) {
    }

    public function capture(ThrownExceptionPackage $exceptions): array
    {
        return $this->ruleSet->capture($exceptions);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent?->getPropertyPath() ?? PropertyPath::empty();
    }

    public function getEnclosingObject(): object
    {
        return $this->object;
    }

    public function getRoot(): object
    {
        return $this->parent?->getRoot() ?? $this->object;
    }

    public function getValue(): object
    {
        return $this->object;
    }
}
