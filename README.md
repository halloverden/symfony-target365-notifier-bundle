# symfony-target365-notifier-bundle
Target365 (Strex) SMS transport for Symfony notifier

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require halloverden/target365-notifier-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require halloverden/target365-notifier-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    HalloVerden\Target365NotifierBundle\HalloVerdenTarget365NotifierBundle::class => ['all' => true],
];
```

Configuration
============

DSN example:
```
TARGET365_DSN=target365://<keyName>:<privateKey>@default/?from=<sender>&allowUnicode=true
```

notifier config:
```yaml
framework:
  notifier:
    texter_transports:
      target365: '%env(TARGET365_DSN)%'
```
