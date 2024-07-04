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

The first thing necessary is to mark your message with `#[ExceptionalValidation]` attribute. It is used to indicate that
the message should be processed by the middleware.

Then you can define `#[Capture]` attributes on the properties of the message. These attributes are used to map thrown
exceptions to the corresponding properties of the class and specify the error message translation keys.

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

In this example, when `LoginAlreadyTakenException` or `WeakPasswordException` is thrown, it will be captured and mapped
to the `login` or `password` property with the corresponding message translation.

Eventually when `phd_exceptional_validation` middleware processes the exception, it
throws an `ExceptionalValidationFailedException`. 

Therefore, it's possible to catch it and process it as needed:

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

### Nested message exception mapping

`#[ExceptionalValidation]` attribute is working side-by-side with symfony validator `#[Valid]` attribute. Once you have
defined these, `#[Capture]` attribute can be defined on nested objects to handle exceptions at different levels of
hierarchy.

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class OrderProductCommand
{
    #[Valid]
    private ProductDetails $productDetails;
}

#[ExceptionalValidation]
final class ProductDetails
{
    #[Capture(InsufficientStockException::class, 'product_purchase.insufficient_stock')]
    private string $quantity;

    // ...
}
```

In this example, whenever `InsufficientStockException` is thrown, it will be captured and mapped to the
`productDetails.quantity` property with the corresponding message translation.

### Conditional Exception Capturing with Callbacks

`#[Capture]` attribute accepts the callback function to determine whether particular exception instance should
be captured for given property or not, allowing more dynamic handling scenarios:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;

#[ExceptionalValidation]
final class YourMessage
{
    #[Capture(ConditionallyCapturedException::class, 'oops', when: [self::class, 'firstPropertyMatchesException'])]
    private int $firstProperty;

    #[Capture(ConditionallyCapturedException::class, 'oops', when: [self::class, 'secondPropertyMatchesException'])]
    private int $secondProperty;

    public function firstPropertyMatchesException(ConditionallyCapturedException $exception): bool
    {
        return $exception->getValue() === $this->firstProperty;
    }

    public function secondPropertyMatchesException(ConditionallyCapturedException $exception): bool
    {
        return $exception->getValue() === $this->secondProperty;
    }
}
```

In this example `when: ` option of the `#[Capture]` attribute is used to specify a callback
functions (`firstPropertyMatchesException` and `secondPropertyMatchesException`) that are called when exception is
processed. If the callback returns `true`, then exception is captured; if it returns `false`, it won't be captured for
this property.

### Nested exception mapping for iterable items

You are perfectly allowed to map the violations for the nested array items given that you have `#[Valid]` attribute
on the iterable property. Here's an example:

```php
use PhPhD\ExceptionalValidation;
use PhPhD\ExceptionalValidation\Capture;
use Symfony\Component\Validator\Constraints\Valid;

#[ExceptionalValidation]
final class ParentMessage
{
    #[Valid]
    private array $nestedItems;
}

#[ExceptionalValidation]
final class NestedItem
{
    #[Capture(NestedItemException::class, 'nested_item_error')]
    private string $itemProperty;
}
```

Thus, whenever `NestedItemException` is thrown, it will be captured and mapped to the `nestedItems[*].itemProperty`,
where `*` stands for the index of the item where exception landed.

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

