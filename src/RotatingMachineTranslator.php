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

use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorsInterface;

/**
 * RotatingMachineTranslator
 *
 * Delegates translation to multiple translators using configurable
 * rotation rules (failover, round-robin, etc.).
 */
class RotatingMachineTranslator implements MachineTranslatorInterface
{
    public const STRATEGY_FAILOVER = 'failover';
    public const STRATEGY_ROUND_ROBIN = 'round-robin';

    /**
     * @var array<int, string>
     */
    protected array $names = [];

    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * Create a new RotatingMachineTranslator.
     *
     * @param MachineTranslatorsInterface $translators
     * @param string $name
     * @param array $only Only use these translators (optional)
     * @param array $except Exclude these translators (optional)
     * @param null|string $fallback Translator name to use if all fail
     * @param string $strategy Rotation strategy
     */
    public function __construct(
        protected MachineTranslatorsInterface $translators,
        protected string $name,
        protected array $only = [],
        protected array $except = [],
        protected null|string $fallback = null,
        protected string $strategy = self::STRATEGY_FAILOVER,
    ) {
        $this->names = $this->filterNames($translators->names());
    }
    
    /**
     * Returns the translator name (e.g. "google", "deepl", "azure").
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Translates the given text into the specified locale.
     *
     * @param string $text The text to translate.
     * @param string $locale The target locale (e.g. "de", "fr", "es").
     * @return string The translated text.
     * @throws TranslateException If translation fails.
     */
    public function translate(string $text, string $locale): string
    {
        foreach ($this->rotationOrder() as $name) {
            try {
                return $this->translators->get($name)->translate($text, $locale);
            } catch (TranslateException $e) {
                // Try next translator
            }
        }

        return $this->handleFallback($text, $locale);
    }

    /**
     * Translates multiple texts into the specified locale.
     *
     * @param array<int, string> $texts
     * @param string $locale
     * @return array<int, string> Translated texts in the same order.
     * @throws TranslateException
     */
    public function translateMany(array $texts, string $locale): array
    {
        foreach ($this->rotationOrder() as $name) {
            try {
                return $this->translators->get($name)->translateMany($texts, $locale);
            } catch (TranslateException $e) {
                // Try next translator
            }
        }

        return $this->handleFallbackMany($texts, $locale);
    }

    /**
     * Determine the rotation order based on strategy.
     *
     * @return array<string>
     */
    protected function rotationOrder(): array
    {
        if ($this->strategy === self::STRATEGY_ROUND_ROBIN) {
            $name = $this->names[$this->position] ?? null;
            $this->position = ($this->position + 1) % count($this->names);
            return $name ? [$name] : [];
        }

        // Default: failover
        return $this->names;
    }

    /**
     * Filter translator names using only/exclude rules.
     *
     * @param array<string> $names
     * @return array<string>
     */
    protected function filterNames(array $names): array
    {
        if (!empty($this->only)) {
            $names = array_values(array_intersect($names, $this->only));
        }

        if (!empty($this->except)) {
            $names = array_values(array_diff($names, $this->except));
        }

        return $names;
    }

    /**
     * Handle fallback for translate().
     *
     * @throws TranslateException
     */
    protected function handleFallback(string $text, string $locale): string
    {
        if ($this->fallback === null) {
            throw new TranslateException('All translators failed and no fallback defined.');
        }

        return $this->translators->get($this->fallback)->translate($text, $locale);
    }

    /**
     * Handle fallback for translateMany().
     *
     * @throws TranslateException
     */
    protected function handleFallbackMany(array $texts, string $locale): array
    {
        if ($this->fallback === null) {
            throw new TranslateException('All translators failed and no fallback defined.');
        }

        return $this->translators->get($this->fallback)->translateMany($texts, $locale);
    }
}