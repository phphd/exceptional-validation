<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Dto;

use PhPhD\ExceptionalValidation\Model\Condition\MatchCondition;
use Throwable;

final class ThrownExceptionPackage
{
    public function __construct(
        /** @var array<int,Throwable> */
        private array $exceptions,
    ) {
    }

    public static function fromTheException(Throwable $exception): self
    {
        return new self([$exception]);
    }

    public function ejectWith(MatchCondition $condition): ?Throwable
    {
        foreach ($this->exceptions as $key => $exception) {
            if ($condition->matches($exception)) {
                unset($this->exceptions[$key]);

                return $exception;
            }
        }

        return null;
    }
}
