<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Exception\Adapter;

use Throwable;

/** @api */
interface ThrownException extends Throwable
{
    /** @return list<Throwable> */
    public function getExceptions(): array;
}
