<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Messenger\Exception;

use PhPhD\ExceptionalValidation\Model\Exception\ThrownException;
use RuntimeException;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
use Throwable;

final class MessengerThrownExceptionAdapter extends RuntimeException implements ThrownException
{
    public function __construct(
        private readonly WrappedExceptionsInterface&Throwable $exceptions,
    ) {
        parent::__construct(previous: $this->exceptions);
    }

    public function getExceptions(): array
    {
        return array_values($this->exceptions->getWrappedExceptions());
    }
}
