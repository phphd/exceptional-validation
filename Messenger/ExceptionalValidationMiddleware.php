<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Messenger;

use Exception;
use PhPhD\ExceptionalValidation\Handler\ExceptionHandler;
use PhPhD\ExceptionalValidation\Model\Exception\SingleThrownExceptionAdapter;
use PhPhD\ExceptionalValidation\Model\Exception\ThrownException;
use PhPhD\ExceptionalValidationBundle\Messenger\Exception\MessengerThrownExceptionAdapter;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

/** @api */
final class ExceptionalValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ExceptionHandler $exceptionHandler,
    ) {
    }

    /** @throws Throwable */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (WrappedExceptionsInterface $exception) {
            /** @var WrappedExceptionsInterface&Throwable $exception */

            $thrownException = new MessengerThrownExceptionAdapter($exception);

            $this->exceptionHandler->capture($message, $thrownException);
        } catch (ThrownException $exception) {
            $this->exceptionHandler->capture($message, $exception);
        } catch (Exception $exception) {
            $thrownException = new SingleThrownExceptionAdapter($exception);

            $this->exceptionHandler->capture($message, $thrownException);
        }

        throw $exception;
    }
}
