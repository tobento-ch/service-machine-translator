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

use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;
use Tobento\Service\MachineTranslator\Exception\TranslateException;

class MachineTranslators implements MachineTranslatorsInterface, MachineTranslatorInterface
{
    /**
     * @var array<string, MachineTranslatorInterface>
     */
    protected array $translators = [];

    /**
     * Create a new instance.
     *
     * @param MachineTranslatorInterface ...$translators
     */
    public function __construct(
        MachineTranslatorInterface ...$translators,
    ) {
        foreach ($translators as $translator) {
            $this->translators[$translator->name()] = $translator;
        }
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
        if (!isset($this->translators[$name])) {
            throw new MachineTranslatorNotFoundException(
                sprintf('Machine translator "%s" not found.', $name)
            );
        }

        return $this->translators[$name];
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
     * @throws TranslateException If translation fails.
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