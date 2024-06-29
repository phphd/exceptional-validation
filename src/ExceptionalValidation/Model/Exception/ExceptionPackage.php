<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception;

use PhPhD\ExceptionalValidation\Model\Rule\CaptureExceptionRule;
use Throwable;

final class ExceptionPackage
{
    /** @var array<int,Throwable> */
    private array $remainingExceptions;

    /** @var list<ProcessedException> */
    private array $processedExceptions;

    public function __construct(
        /** @var list<Throwable> */
        private readonly array $thrownExceptions,
    ) {
        $this->remainingExceptions = $this->thrownExceptions;
    }

    /** @return list<ProcessedException> */
    public function getProcessedExceptions(): array
    {
        return $this->processedExceptions;
    }

    public static function fromTheException(Throwable $exception): self
    {
        return new self([$exception]);
    }

    public function isProcessed(): bool
    {
        return [] === $this->remainingExceptions;
    }

    public function processRule(CaptureExceptionRule $rule): void
    {
        foreach ($this->remainingExceptions as $index => $exception) {
            if ($rule->matches($exception)) {
                $this->processException($index, $exception, $rule);

                return;
            }
        }
    }

    private function processException(int $index, Throwable $exception, CaptureExceptionRule $rule): void
    {
        unset($this->remainingExceptions[$index]);

        $this->processedExceptions[] = new ProcessedException($exception, $rule);
    }
}
