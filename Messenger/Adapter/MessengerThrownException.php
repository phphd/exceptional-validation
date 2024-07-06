<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Messenger\Exception;

use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;
use RuntimeException;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
use Throwable;

/** @internal */
final class MessengerThrownException extends RuntimeException implements ThrownException
{
    public function __construct(
        private readonly WrappedExceptionsInterface&Throwable $exception,
    ) {
        parent::__construct(previous: $this->exception);
    }

    public function getExceptions(): array
    {
        return array_values($this->exception->getWrappedExceptions());
    }
}
