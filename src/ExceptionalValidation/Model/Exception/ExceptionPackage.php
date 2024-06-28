<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception;

use PhPhD\ExceptionalValidation\Model\Rule\CaptureExceptionRule;
use Throwable;

final class ExceptionPackage
{
    /** @var array<int,Throwable> */
    private array $remainingExceptions;

    /** @var list<CaughtException> */
    private array $caughtExceptions;

    public function __construct(
        /** @var list<Throwable> */
        private readonly array $thrownExceptions,
    ) {
        $this->remainingExceptions = $this->thrownExceptions;
    }

    /** @return list<CaughtException> */
    public function getCaughtExceptions(): array
    {
        return $this->caughtExceptions;
    }

    public static function fromTheException(Throwable $exception): self
    {
        return new self([$exception]);
    }

    public function isProcessed(): bool
    {
        return [] === $this->remainingExceptions;
    }

    public function captureWith(CaptureExceptionRule $rule): void
    {
        foreach ($this->remainingExceptions as $index => $exception) {
            if ($rule->matches($exception)) {
                $this->catch($index, $exception, $rule);

                return;
            }
        }
    }

    private function catch(int $index, Throwable $exception, CaptureExceptionRule $rule): void
    {
        unset($this->remainingExceptions[$index]);

        $this->caughtExceptions[] = new CaughtException($exception, $rule);
    }
}
