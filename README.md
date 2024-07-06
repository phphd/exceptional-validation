# PhdExceptionalValidationBundle

🧰 Provides Exception Mapper component enabled as [Symfony Messenger](https://symfony.com/doc/current/messenger.html)
middleware. It captures thrown exceptions, mapping them
into [Symfony Validator](https://symfony.com/doc/current/validation.html) violations format.

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exceptional-validation-bundle/ci.yaml?branch=main)](https://github.com/phphd/exceptional-validation-bundle/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exceptional-validation-bundle/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exceptional-validation-bundle)
[![Psalm coverage](https://shepherd.dev/github/phphd/exceptional-validation-bundle/coverage.svg)](https://shepherd.dev/github/phphd/exceptional-validation-bundle)
[![Psalm level](https://shepherd.dev/github/phphd/exceptional-validation-bundle/level.svg)](https://shepherd.dev/github/phphd/exceptional-validation-bundle)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exceptional-validation-bundle.svg)](https://packagist.org/packages/phphd/exceptional-validation-bundle)
[![Licence](https://img.shields.io/github/license/phphd/exceptional-validation-bundle.svg)](https://github.com/phphd/exceptional-validation-bundle/blob/main/LICENSE)

## Installation 📥

1. Install via composer

    ```sh
    composer require phphd/exceptional-validation-bundle
    ```

2. Enable the bundle in the `bundles.php`

    ```php
    PhPhD\ExceptionalValidationBundle\PhdExceptionalValidationBundle::class => ['all' => true],
    ```

## Configuration ⚒️

The recommended way to use this package is via Symfony Messenger.

To leverage features of this bundle, you should add `phd_exceptional_validation` middleware to the list:

```diff
framework:
    messenger:
        buses:
            command.bus:
                middleware:
                    - validation
+                   - phd_exceptional_validation
                    - doctrine_transaction
```

## Usage 🚀

The first thing necessary is to mark your message with `#[ExceptionalValidation]` attribute. It is used to include the
message for processing by the middleware.

Then you define `#[Capture]` attributes on the properties of the message. These attributes are used to specify mapping
of the thrown exceptions to the corresponding properties of the class with the corresponding error message translation.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class RegisterUserCommand
{
    #[Capture(LoginAlreadyTakenException::class, 'auth.login.already_taken')]
    private string $login;

    #[Capture(WeakPasswordException::class, 'auth.password.weak')]
    private string $password;
}
```

In this example, whenever `LoginAlreadyTakenException` or `WeakPasswordException` is thrown, it will be captured and
mapped to the `login` or `password` property with the corresponding message translation.

Eventually when `phd_exceptional_validation` middleware has processed the exception, it will
throw `ExceptionalValidationFailedException` so that it can be caught and processed as needed:

```php
$command = new RegisterUserCommand($login, $password);

try {
    $this->commandBus->dispatch($command);
} catch (ExceptionalValidationFailedException $exception) {
    $constraintViolationList = $exception->getViolations();

    return $this->render('registrationForm.html.twig', ['errors' => $constraintViolationList]);
} 
```

The `$exception` object enfolds constraint violations with respectively mapped error messages. This
violation list may be used for example to render errors into html-form or to return them as a json-response.

## Advanced usage ⚙️

`#[ExceptionalValidation]` and `#[Capture]` attributes allow you to implement very flexible mappings.
Here are just few examples of how you can use them.

### Exception mapping on nested objects

`#[ExceptionalValidation]` attribute works side-by-side with Symfony Validator `#[Valid]` attribute. Once you have
defined these, the `#[Capture]` attribute can be defined on the nested objects.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class OrderProductCommand
{
    #[Valid]
    private ProductDetails $product;
}

#[ExceptionalValidation]
final class ProductDetails
{
    private int $id;

    #[Capture(InsufficientStockException::class, 'order.insufficient_stock')]
    private string $quantity;

    // ...
}
```

In this example, whenever `InsufficientStockException` is thrown, it will be captured and mapped to the
`product.quantity` property with the corresponding message translation.

### Capture Conditions

`#[Capture]` attribute accepts the callback function to determine whether particular exception instance should
be captured for the given property or not. It allows more dynamic exception handling scenarios:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class TransferMoneyCommand
{
    #[Capture(
        BlockedCardException::class,
        'wallet.blocked_card',
        when: [self::class, 'isWithdrawalCardBlocked']
    )]
    private int $withdrawalCardId;

    #[Capture(
        BlockedCardException::class,
        'wallet.blocked_card',
        when: [self::class, 'isDepositCardBlocked']
    )]
    private int $depositCardId;

    #[Capture(InsufficientFundsException::class, 'wallet.insufficient_funds')]
    private int $unitAmount;

    public function isWithdrawalCardBlocked(BlockedCardException $exception): bool
    {
        return $exception->getCardId() === $this->withdrawalCardId;
    }

    public function isDepositCardBlocked(BlockedCardException $exception): bool
    {
        return $exception->getCardId() === $this->depositCardId;
    }
}
```

In this example, `when: ` option of the `#[Capture]` attribute is used to specify the callback functions that are called
when exception is processed. If `isWithdrawalCardBlocked` callback returns `true`, then exception is captured for
`withdrawalCardId` property; if `isDepositCardBlocked` callback returns `true`, then exception is captured for
`depositCardId` property. If neither of the callbacks return `true`, then exception is re-thrown upper in the stack.

### Exception mapping on nested arrays

You are perfectly allowed to map the violations for the nested array items given that you have `#[Valid]` attribute
on the iterable property. For example:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints as Assert;

#[ExceptionalValidation]
final class CreateOrderCommand
{
    /** @var ProductDetails[] */
    #[Assert\Valid]
    private array $products;
}

#[ExceptionalValidation]
final class ProductDetails
{
    private int $id;

    #[Capture(
        InsufficientStockException::class, 
        'order.insufficient_stock', 
        when: [self::class, 'isStockExceptionForThisProduct']
    )]
    private string $quantity;

    public function isStockExceptionForThisProduct(InsufficientStockException $exception): bool
    {
        return $exception->getProductId() === $this->id;
    }
}
```

In this example, when `InsufficientStockException` is captured, it will be mapped to the `products[*].quantity`
property, where `*` stands for the index of the particular `ProductDetails` instance from the `products` array on which
the exception was captured.

### Capturing multiple exceptions at once

Typically, validation involves evaluating multiple conditions simultaneously, allowing user to see all the validation
errors in one go, rather than seeing just the first error as in case of standard exception handling.

Current component partially mitigates this issue by allowing to capture multiple exceptions at once.
The key idea involves using some kind of `CompositeException` that represents an array of other exceptions.

Here is an example of how you can achieve this:

Then, your handler could throw any kind of composite exception:

```php
throw new CompositeException([
    new EmailAlreadyExistsException('test@test.com'),
    new PasswordTooShortException('test'),
])
```

This way, all these exceptions will be captured and mapped to the corresponding properties on `RegisterUserCommand`.

If any of wrapped exceptions are not processed, then original `CompositeException` will be re-thrown, regardless of how
many exceptions were successfully mapped.

> Since this bundle integrates with Symfony Messenger component, you can use `HandlerFailedException` as well

