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
 
namespace Tobento\Service\MachineTranslator\Event;

use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

final class TranslationSuccess
{
    /**
     * Create a new instance.
     *
     * @param MachineTranslatorInterface $translator
     * @param array<int, string> $texts
     * @param string $locale
     * @param array<int, string> $translated
     */
    public function __construct(
        private MachineTranslatorInterface $translator,
        private array $texts,
        private string $locale,
        private array $translated,
    ) {}

    /**
     * Returns the translator.
     *
     * @return MachineTranslatorInterface
     */
    public function translator(): MachineTranslatorInterface
    {
        return $this->translator;
    }
    
    /**
     * Returns the texts.
     *
     * @return array<int, string>
     */
    public function texts(): array
    {
        return $this->texts;
    }
    
    /**
     * Returns the locale.
     *
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }
    
    /**
     * Returns the translated.
     *
     * @return array<int, string>
     */
    public function translated(): array
    {
        return $this->translated;
    }
}