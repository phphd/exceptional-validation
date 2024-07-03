<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception;

use RuntimeException;
use Throwable;

/** @api */
final class CompositeException extends RuntimeException implements ThrownException
{
    public function __construct(
        /** @var list<Throwable> */
        private readonly array $exceptions,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getExceptions(): array
    {
        return $this->exceptions;
    }
}
