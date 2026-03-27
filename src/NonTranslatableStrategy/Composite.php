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

namespace Tobento\Service\MachineTranslator\NonTranslatableStrategy;

use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

/**
 * A non-translatable strategy that combines multiple strategies
 * and applies them in sequence.
 *
 * Each contained strategy performs its own protection logic during
 * translation, and unprotection is applied in reverse order to
 * correctly restore the original placeholders.
 *
 * Useful for composing complex protection behavior, such as combining
 * placeholder protection with HTML or Markdown protection.
 */
class Composite implements NonTranslatableStrategyInterface
{
    /**
     * @var NonTranslatableStrategyInterface[]
     */
    protected array $strategies = [];

    /**
     * Create a new Composite strategy.
     *
     * @param NonTranslatableStrategyInterface ...$strategies
     */
    public function __construct(NonTranslatableStrategyInterface ...$strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * Add a strategy to the composite.
     *
     * @param NonTranslatableStrategyInterface $strategy
     * @return static
     */
    public function addStrategy(NonTranslatableStrategyInterface $strategy): static
    {
        $this->strategies[] = $strategy;
        return $this;
    }

    /**
     * Protects non-translatable segments by applying each strategy in order.
     *
     * @param string $text
     * @return string
     */
    public function protect(string $text): string
    {
        foreach ($this->strategies as $strategy) {
            $text = $strategy->protect($text);
        }

        return $text;
    }

    /**
     * Removes protection markers by applying each strategy in reverse order.
     *
     * @param string $text
     * @return string
     */
    public function unprotect(string $text): string
    {
        for ($i = count($this->strategies) - 1; $i >= 0; $i--) {
            $text = $this->strategies[$i]->unprotect($text);
        }

        return $text;
    }
}