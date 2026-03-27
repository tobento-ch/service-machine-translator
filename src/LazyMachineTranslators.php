<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\MachineTranslator;

use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Autowire;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorCreationException;
use Tobento\Service\MachineTranslator\Exception\TranslateException;

class LazyMachineTranslators implements MachineTranslatorsInterface, MachineTranslatorInterface
{
    /**
     * @var Autowire
     */
    protected Autowire $autowire;

    /**
     * @var array<string, MachineTranslatorInterface>
     */
    protected array $createdTranslators = [];

    /**
     * Create a new instance.
     *
     * @param ContainerInterface $container
     * @param array $translators Definitions for translators.
     */
    public function __construct(
        ContainerInterface $container,
        protected array $translators,
    ) {
        $this->autowire = new Autowire($container);
    }

    /**
     * Returns the machine translator registered under the given name.
     *
     * @param string $name The translator name.
     * @return MachineTranslatorInterface
     * @throws MachineTranslatorNotFoundException If no translator is registered with the given name.
     */
    public function get(string $name): MachineTranslatorInterface
    {
        // Already created?
        if (isset($this->createdTranslators[$name])) {
            return $this->createdTranslators[$name];
        }

        // Not registered?
        if (!isset($this->translators[$name])) {
            throw new MachineTranslatorNotFoundException(
                sprintf('Machine translator "%s" not found.', $name)
            );
        }

        $definition = $this->translators[$name];

        // Direct instance
        if ($definition instanceof MachineTranslatorInterface) {
            return $this->createdTranslators[$name] = $definition;
        }

        // Callable factory
        if (is_callable($definition)) {
            return $this->createdTranslators[$name] = $this->autowire->call(
                $definition,
                ['name' => $name, 'translators' => $this]
            );
        }

        // Array definition with factory
        if (!isset($definition['factory'])) {
            throw new MachineTranslatorNotFoundException(
                sprintf('Missing "factory" for machine translator "%s".', $name)
            );
        }

        $factory = $this->autowire->resolve($definition['factory']);

        if (!$factory instanceof MachineTranslatorFactoryInterface) {
            throw new MachineTranslatorNotFoundException(
                sprintf('Invalid factory for machine translator "%s".', $name)
            );
        }

        $config = $definition['config'] ?? [];

        return $this->createdTranslators[$name] = $factory->createTranslator($name, $config);
    }

    /**
     * Checks whether a machine translator with the given name exists.
     *
     * @param string $name The translator name.
     * @return bool True if the translator exists, otherwise false.
     */
    public function has(string $name): bool
    {
        return isset($this->translators[$name]);
    }

    /**
     * Returns all registered translator names.
     *
     * @return array<int, string> A list of translator names.
     */
    public function names(): array
    {
        return array_keys($this->translators);
    }

    /**
     * Returns the translator name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->getFirstTranslator()->name();
    }

    /**
     * Translates the given text using the first registered translator.
     *
     * @param string $text
     * @param string $locale
     * @return string
     * @throws TranslateException
     */
    public function translate(string $text, string $locale): string
    {
        return $this->getFirstTranslator()->translate($text, $locale);
    }
    
    /**
     * Translates multiple texts using the first registered translator.
     *
     * @param array<int, string> $texts
     * @param string $locale
     * @return array<int, string>
     * @throws TranslateException
     */
    public function translateMany(array $texts, string $locale): array
    {
        return $this->getFirstTranslator()->translateMany($texts, $locale);
    }

    /**
     * Returns the first registered translator.
     *
     * @return MachineTranslatorInterface
     * @throws MachineTranslatorNotFoundException
     */
    protected function getFirstTranslator(): MachineTranslatorInterface
    {
        $firstKey = array_key_first($this->translators);

        if ($firstKey === null) {
            throw new MachineTranslatorNotFoundException('No machine translators registered.');
        }

        return $this->get($firstKey);
    }
}