# Machine Translator Service

The Machine Translator service provides:

- A unified abstraction for translating text across multiple [translators](#translator)  
- A consistent API for single and bulk translations  
- Provider-specific error handling (including quota detection)  
- Interchangeable translators without changing application code  

It ships with multiple translator implementations, each following the same interface while preserving provider-specific capabilities and behaviors.

## Table of Contents

- [Getting started](#getting-started)
    - [Requirements](#requirements)
    - [Highlights](#highlights)
- [Documentation](#documentation)
    - [Basic Usage](#basic-usage)
        - [Translating Text](#translating-text)
    - [Translator](#translator)
        - [Azure Translator](#azure-translator)
        - [DeepL Translator](#deepl-translator)
        - [Google Translator](#google-translator)
        - [Null Translator](#null-translator)
        - [Rotating Translator](#rotating-translator)
    - [Translators](#translators)
        - [Default Translators](#default-translators)
        - [Lazy Translators](#lazy-translators)
    - [Non-Translatable Strategies](#non-translatable-strategies)
        - [Composite Strategy](#composite-strategy)
        - [Null Strategy](#null-strategy)
        - [Placeholder Strategy](#placeholder-strategy)
        - [Using Strategies with Translators](#using-strategies-with-translators)
    - [Adapters](#adapters)
        - [Event Machine Translator Adapter](#event-machine-translator-adapter)
    - [Events](#events)
- [Credits](#credits)
___

# Getting started

Add the latest version of the Machine Translator service project running this command.

```
composer require tobento/service-machine-translator
```

## Requirements

- PHP 8.4 or above

## Highlights

- Framework-agnostic, will work with any project
- Decoupled design

# Documentation

## Basic Usage

### Translating Text

To translate text, use the `translate()` or `translateMany()` method:

```php
use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

class SomeService
{
    public function translate(MachineTranslatorInterface $translator): void
    {
        try {
            $translated = $translator->translate(
                text: 'Hello World',
                locale: 'de'
            );

            // "Hallo Welt"
        } catch (TranslateException $e) {
            // handle
        }
    }
    
    public function translateMany(MachineTranslatorInterface $translator): void
    {
        try {
            $translated = $translator->translateMany(
                texts: ['Hello', 'How are you?'],
                locale: 'de'
            );

            // [
            //     "Hallo",
            //     "Wie geht es dir?"
            // ]
        } catch (TranslateException $e) {
            // handle
        }
    }
}
```

See the available [Translator](#translator) implementations to learn more about Azure, DeepL, Google, and the Null translator.

See the [Translators](#translators) section to learn how to manage multiple translators or lazy-load them.

## Translator

### Azure Translator

#### Translator

The Azure Translator provides machine translation using the Azure Cognitive Services Translator API.  
It supports single and bulk translations, detects quota-related errors, and returns consistent results through the shared `MachineTranslatorInterface`.

When using `translateMany()`, all texts are translated in **one API request**, reducing latency and helping avoid rate-limit issues.

You can obtain your API key by creating a Translator resource in the Azure Portal.  
Official documentation: https://learn.microsoft.com/azure/ai-services/translator/

#### Translator Factory

The Azure translator can be created using the `Azure\TranslatorFactory`.  
It requires an HTTP client, PSR-17 factories, and your Azure Translator configuration.

```php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\MachineTranslator\Azure;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

$factory = new Azure\TranslatorFactory(
    client: $client, // ClientInterface
    requestFactory: $requestFactory, // RequestFactoryInterface
    streamFactory: $streamFactory, // StreamFactoryInterface
    
    // Optional (defaults shown)
    defaults: [
        //'endpoint' => 'https://api.cognitive.microsofttranslator.com',
        //'region'   => 'westeurope',
    ],
);

var_dump($factory instanceof MachineTranslatorFactoryInterface);
// bool(true)

$translator = $factory->createTranslator(
    name: 'azure',
    config: [
        'apiKey'   => 'YOUR_API_KEY',
        
        // Optional (defaults shown)
        //'endpoint' => 'https://api.cognitive.microsofttranslator.com',
        //'region'   => 'westeurope',
    ]
);

var_dump($translator instanceof MachineTranslatorInterface);
```

> This translator supports non-translatable strategies.  
> See [Using Strategies with Translators](#using-strategies-with-translators).

### DeepL Translator

#### Translator

The DeepL Translator provides machine translation using the DeepL Translation API.  
It supports single and bulk translations, handles DeepL-specific error responses, and returns consistent results through the shared `MachineTranslatorInterface`.

When using `translateMany()`, all texts are translated in **one API request**, reducing latency and helping avoid rate-limit issues.

You can obtain your API key by creating a DeepL API account.  
Official documentation: https://developers.deepl.com/

#### Translator Factory

The DeepL translator can be created using the `DeepL\TranslatorFactory`.  
It requires an HTTP client, PSR-17 factories, and your DeepL API configuration.

```php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\MachineTranslator\DeepL;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

$factory = new DeepL\TranslatorFactory(
    client: $client, // ClientInterface
    requestFactory: $requestFactory, // RequestFactoryInterface
    streamFactory: $streamFactory, // StreamFactoryInterface

    // Optional (defaults shown)
    defaults: [
        //'endpoint' => 'https://api.deepl.com/v2/translate',
    ],
);

var_dump($factory instanceof MachineTranslatorFactoryInterface);
// bool(true)

$translator = $factory->createTranslator(
    name: 'deepl',
    config: [
        'apiKey' => 'YOUR_API_KEY',

        // Optional (defaults shown)
        //'endpoint' => 'https://api.deepl.com/v2/translate',
    ]
);

var_dump($translator instanceof MachineTranslatorInterface);
// bool(true)
```

> This translator supports non-translatable strategies.  
> See [Using Strategies with Translators](#using-strategies-with-translators).

### Google Translator

#### Translator

The Google Translator provides machine translation using the Google Cloud Translation API.  
It supports single and bulk translations, handles Google-specific error responses, and returns consistent results through the shared `MachineTranslatorInterface`.

When using `translateMany()`, all texts are translated in **one API request**, reducing latency and helping avoid rate-limit issues.

You can obtain your API key by creating a Translation API resource in the Google Cloud Console.  
Official documentation: https://cloud.google.com/translate/docs

#### Translator Factory

The Google translator can be created using the `Google\TranslatorFactory`.  
It requires an HTTP client, PSR-17 factories, and your Google Cloud Translation configuration.

```php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\MachineTranslator\Google;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

$factory = new Google\TranslatorFactory(
    client: $client, // ClientInterface
    requestFactory: $requestFactory, // RequestFactoryInterface
    streamFactory: $streamFactory, // StreamFactoryInterface

    // Optional (defaults shown)
    defaults: [
        //'endpoint' => 'https://translation.googleapis.com/language/translate/v2',
    ],
);

var_dump($factory instanceof MachineTranslatorFactoryInterface);
// bool(true)

$translator = $factory->createTranslator(
    name: 'google',
    config: [
        'apiKey' => 'YOUR_API_KEY',

        // Optional (defaults shown)
        //'endpoint' => 'https://translation.googleapis.com/language/translate/v2',
    ]
);

var_dump($translator instanceof MachineTranslatorInterface);
// bool(true)
```

> This translator supports non-translatable strategies.  
> See [Using Strategies with Translators](#using-strategies-with-translators).

### Null Translator

The Null Translator is a no-operation translator implementation.  
It returns the original text unchanged and is useful for testing, development, or scenarios where translation is optional or disabled.

It supports both `translate()` and `translateMany()` and always returns the input as-is.

```php
use Tobento\Service\MachineTranslator\NullMachineTranslator;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

$translator = new NullMachineTranslator();

var_dump($translator instanceof MachineTranslatorInterface);
// bool(true)

// Examples:
var_dump($translator->translate(text: 'Hello', locale: 'de'));
// string(5) "Hello"

var_dump($translator->translateMany(texts: ['A', 'B'], locale: 'fr'));
// array(2) { [0]=> string(1) "A" [1]=> string(1) "B" }
```

### Rotating Translator

The `RotatingMachineTranslator` allows you to combine multiple translators into a **single, unified translator** that automatically delegates translation to other translators based on configurable rotation rules.

This is useful when you want:

- **Failover**: if one translator fails, try the next one  
- **Load balancing**: distribute translation requests across providers  
- **Quota balancing**: rotate when one provider hits rate limits  
- **Selective participation**: include or exclude specific translators  
- **Fallback**: use a final translator if all others fail  

`RotatingMachineTranslator` implements `MachineTranslatorInterface`, so it can be used anywhere a normal translator is expected.

#### Using with Lazy Translators

When using [Lazy Translators](#lazy-translators), the callable receives:

- the `translator` registry (`MachineTranslatorsInterface`)  
- the translator `name`  
- everything else resolved by autowiring

This allows you to construct a rotating translator directly:

Example:

```php
use Tobento\Service\MachineTranslator\RotatingMachineTranslator;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorsInterface;
use Tobento\Service\MachineTranslator\Exception\TranslateException;

'rotator' => function (
    string $name,
    MachineTranslatorInterface $translators,
): MachineTranslatorInterface {

    return new RotatingMachineTranslator(
        translators: $translators,
        name: $name,

        // Only use these translators (optional)
        only: ['azure', 'google'],

        // Exclude these translators (optional)
        except: [],

        // Fallback translator name (optional)
        fallback: 'null',
        // or:
        //fallback: null, // will throw if all fail

        // Rotation strategy
        strategy: RotatingMachineTranslator::STRATEGY_FAILOVER,
        // or:
        //strategy: RotatingMachineTranslator::STRATEGY_ROUND_ROBIN,
    );
},
```

The rotation strategy controls how translators are selected.  
`STRATEGY_FAILOVER` tries translators in order until one succeeds, while  
`STRATEGY_ROUND_ROBIN` cycles through translators on each request.

The fallback translator is used only if all selected translators fail.  
If `fallback` is set to `null`, a `TranslateException` will be thrown instead.
If the fallback translator is defined but also fails, a `TranslateException` will be thrown.  
The fallback is the final step in the rotation chain.

## Translators

### Default Translators

The `MachineTranslators` class provides a simple, eager registry for machine translators.  
Unlike `LazyMachineTranslators`, all translators are **fully constructed upfront** and registered directly through the constructor.

This is ideal when:

- you want predictable, immediate initialization  
- your translators are already created through factories  
- you prefer explicit, ready-to-use translator instances  
- you only need a small number of translators

`MachineTranslators` implements both `MachineTranslatorsInterface` and `MachineTranslatorInterface`, allowing it to be used either as a **registry** or as a **default translator** (delegating to the first registered translator).

**Example**

```php
use Tobento\Service\MachineTranslator\MachineTranslators;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorsInterface;

// Create translator instances (typically via factories):
$azure = $azureFactory->createTranslator(name: 'azure', config: [
    'apiKey' => 'azure-key',
]);

$deepl = $deeplFactory->createTranslator(name: 'deepl', config: [
    'apiKey' => 'deepl-key',
]);

// Register them:
$translators = new MachineTranslators(
    $azure,
    $deepl,
);

var_dump($translators instanceof MachineTranslatorsInterface);
// bool(true)

// Retrieve a translator by name:
$azureTranslator = $translators->get('azure');
// will throw MachineTranslatorNotFoundException if the translator does not exist

// Check if a translator exists:
if ($translators->has('azure')) {
    // translator is available
}

// Get all registered translator names:
$names = $translators->names();
// e.g. ['azure', 'deepl']

// Use Translators as the default translator:
$translated = $translators->translate(
    text: 'Hello World',
    locale: 'de'
);
```

### Lazy Translators

The `LazyMachineTranslators` class allows you to register machine translators without creating them upfront.  
Translators are instantiated **only when first accessed**, which is useful when using factories, callables, or configuration‑based setups.

`LazyMachineTranslators` implements both `MachineTranslatorsInterface` and `MachineTranslatorInterface`, so it can be used as a **registry** or as a **default translator** (delegating to the first registered translator).

**Example**

```php
use Psr\Container\ContainerInterface;
use Tobento\Service\MachineTranslator\LazyMachineTranslators;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorsInterface;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;

$translators = new LazyMachineTranslators(
    container: $container,
    translators: [

        // 1. Direct instance:
        'azure-direct' => $azureFactory->createTranslator(
            name: 'azure-direct',
            config: ['apiKey' => 'azure-key']
        ),

        // 2. Callable definition:
        'deepl-callable' => function (
            string $name,
            MachineTranslatorsInterface $translators,
            // (everything else resolved by autowiring)
            ContainerInterface $c,
        ): MachineTranslatorInterface {
            return $c->get(\Tobento\Service\MachineTranslator\DeepL\TranslatorFactory::class)
                ->createTranslator(
                    name: 'deepl-callable',
                    config: ['apiKey' => 'deepl-key']
                );
        },

        // 3. Factory definition:
        'google-factory' => [
            'factory' => \Tobento\Service\MachineTranslator\Google\TranslatorFactory::class,
            'config' => [
                'apiKey' => 'google-key',
            ],
        ],
    ]
);

var_dump($translators instanceof MachineTranslatorsInterface);
// bool(true)

// Retrieve a translator by name:
$azure = $translators->get('azure-direct');
// will throw MachineTranslatorNotFoundException if the translator does not exist

// Check if a translator exists:
if ($translators->has('azure-direct')) {
    // translator is available
}

// Get all registered translator names:
$names = $translators->names();
// e.g. ['azure-direct', 'deepl-callable', 'google-factory']

// Use LazyMachineTranslators as a default translator:
$translated = $translators->translate(
    text: 'Hello World',
    locale: 'de'
);
```

## Non-Translatable Strategies

Non-translatable strategies let you protect parts of a text from being altered during translation. This is essential when working with placeholders, template variables, IDs, or any content that must remain exactly as it is. A strategy wraps these segments before sending the text to the translation API and restores them afterward, ensuring your dynamic values stay intact across all translators.

### Composite Strategy

The Composite strategy allows you to combine multiple non-translatable strategies and apply them in sequence. Each strategy performs its own protection logic, making it easy to mix behaviors. For example, protecting both indicator-based placeholders like `:name` and wrapper-based placeholders like `{id}`. Protection is applied in order, while unprotection runs in reverse order to safely restore the original text.

```php
use Tobento\Service\MachineTranslator\NonTranslatableStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

$strategy = new NonTranslatableStrategy\Composite(
    new NonTranslatableStrategy\Placeholder(),
);

var_dump($strategy instanceof NonTranslatableStrategyInterface);
// bool(true)
```

### Null Strategy

The Null strategy performs no protection at all. It simply returns the text unchanged during both protection and unprotection phases. This is useful when you want to disable placeholder handling entirely or when working with translators that do not require any special processing.

```php
use Tobento\Service\MachineTranslator\NonTranslatableStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

$strategy = new NonTranslatableStrategy\NullStrategy();

var_dump($strategy instanceof NonTranslatableStrategyInterface);
// bool(true)
```

### Placeholder Strategy

The Placeholder strategy protects placeholder segments such as `:name`, `{id}`, or `[[tag]]` from being translated. It detects both indicator-based placeholders (e.g. `:name`) and wrapper-based placeholders (e.g. `{name}`, `[[name]]`) and wraps them in configurable markers during translation. After translation, the markers are removed to restore the original placeholders exactly as they were.

```php
use Tobento\Service\MachineTranslator\NonTranslatableStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

$strategy = new NonTranslatableStrategy\Placeholder(
    indicators: [':'],
    wrappers: [['{', '}']],
    marker: ['<nt>', '</nt>'],
);

var_dump($strategy instanceof NonTranslatableStrategyInterface);
// bool(true)
```

### Using Strategies with Translators

In most applications, translators and non-translatable strategies are created through factories rather than being instantiated manually. A strategy's `protect()` method runs before the text is sent to the translation API, and `unprotect()` runs after the translated text is received. This ensures that placeholders, variables, or other protected segments remain unchanged throughout the translation process.

You can configure a non-translatable strategy either in the factory defaults or per translator.  
Defaults are merged with the configuration passed to `createTranslator()`.

```php
use Tobento\Service\MachineTranslator\Azure\MachineTranslatorFactory;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;

// Create a factory with your HTTP client and PSR-17 factories
$factory = new MachineTranslatorFactory(
    client: $client, // ClientInterface
    requestFactory: $requestFactory, // RequestFactoryInterface
    streamFactory: $streamFactory, // StreamFactoryInterface

    // Optional defaults (you may override any of them)
    defaults: [
        'nonTranslatableStrategy' => new NonTranslatableStrategy\Placeholder(),
        // 'endpoint' => 'https://api.cognitive.microsofttranslator.com',
        // 'region' => 'westeurope',
    ],
);

// Create a translator (only apiKey is required)
$translator = $factory->createTranslator(
    name: 'azure',
    config: [
        'apiKey' => 'YOUR_API_KEY',

        // Optional overrides (defaults shown)
        //'nonTranslatableStrategy' => new NonTranslatableStrategy\NullStrategy(),
        // 'endpoint' => 'https://api.cognitive.microsofttranslator.com',
        // 'region' => 'westeurope',
    ]
);

echo $translator->translate('Hello :name', 'de');
// "Hallo :name"
```

## Adapters

### Event Machine Translator Adapter

The **Event Machine Translator Adapter** decorates any translator and dispatches events whenever a translation succeeds or fails.  
This allows you to hook into translation activity for logging, monitoring, debugging, analytics, or custom workflows.

#### Example

```php
use Psr\EventDispatcher\EventDispatcherInterface;
use Tobento\Service\MachineTranslator\Adapter;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

$translator = new Adapter\EventMachineTranslator(
    translator: $translator, // MachineTranslatorInterface
    events: $eventDispatcher, // EventDispatcherInterface
);
```

The adapter behaves exactly like the wrapped translator and does **not** modify translation results or error handling.  
It simply emits events after each translation attempt.

See the [Events](#events) section for a full list of dispatched events and their descriptions.

#### Notes

- The adapter dispatches events **after** translation attempts.
- Exceptions are **not swallowed** - they are rethrown after the failure event is dispatched.
- Both single and multiple translations are normalized to arrays for consistent event payloads.

## Events

```php
use Tobento\Service\MachineTranslator\Event;
```

| Event Class | Description |
|-------------|-------------|
| `Event\TranslationSuccess::class` | Dispatched after one or more texts have been successfully translated. |
| `Event\TranslationFailed::class` | Dispatched after a translation attempt (single or many) has failed. |


# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)