<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception;

use Throwable;

/** @api */
interface ThrownException extends Throwable
{
    /** @return list<Throwable> */
    public function getExceptions(): array;
}
