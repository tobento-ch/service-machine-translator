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

use Throwable;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

final class TranslationFailed
{
    /**
     * Create a new instance.
     *
     * @param MachineTranslatorInterface $translator
     * @param array<int, string> $texts
     * @param string $locale
     * @param Throwable $exception
     */
    public function __construct(
        private MachineTranslatorInterface $translator,
        private array $texts,
        private string $locale,
        private Throwable $exception,
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
     * Returns the exception.
     *
     * @return Throwable
     */
    public function exception(): Throwable
    {
        return $this->exception;
    }
}