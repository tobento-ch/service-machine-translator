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
 * A non-translatable strategy that protects placeholder segments
 * from being translated by wrapping them in configurable markers.
 *
 * Supports both indicator-based placeholders (e.g. :name)
 * and wrapper-based placeholders (e.g. {name}, [[name]]).
 *
 * After translation, the markers are removed to restore the
 * original placeholder format.
 */
class Placeholder implements NonTranslatableStrategyInterface
{
    /**
     * Create a new instance.
     *    
     * @param array $indicators Prefix indicators like [':', '%'].
     * @param array $wrappers Wrapper pairs like [['{', '}'], ['[[', ']]']].
     * @param array $marker A two-element array defining the start and end protection markers, e.g. ['<nt>', '</nt>'].
     */
    public function __construct(
        protected array $indicators = [':'],
        protected array $wrappers = [['{', '}']],
        protected array $marker = ['<nt>', '</nt>'],
    ) {
        if (count($this->marker) !== 2) {
            throw new \InvalidArgumentException('Marker must be an array with [start, end].');
        }
    }
    
    /**
     * Protects non-translatable segments in the given text.
     *
     * @param string $text The original text.
     * @return string The text with protection markers applied.
     */
    public function protect(string $text): string
    {
        $patterns = [];

        // Indicator patterns: :name, %count, $price
        foreach ($this->indicators as $indicator) {
            $indicator = preg_quote($indicator, '/');
            $patterns[] = $indicator . '[a-zA-Z_][a-zA-Z0-9_]*';
        }

        // Wrapper patterns: {name}, [[name]], <name>
        foreach ($this->wrappers as $pair) {
            if (count($pair) !== 2) {
                continue;
            }

            [$open, $close] = $pair;

            $open  = preg_quote($open, '/');
            $close = preg_quote($close, '/');

            $patterns[] = $open . '[a-zA-Z_][a-zA-Z0-9_]*' . $close;
        }

        if ($patterns === []) {
            return $text;
        }

        $regex = '/(' . implode('|', $patterns) . ')/';

        $result = preg_replace_callback(
            $regex,
            fn($m) => $this->marker[0] . $m[0] . $this->marker[1],
            $text
        );
        
        return $result ?? '';
    }

    /**
     * Removes protection markers from the translated text.
     *
     * @param string $text The translated text.
     * @return string The cleaned text without protection markers.
     */
    public function unprotect(string $text): string
    {
        return strtr($text, [
            $this->marker[0] => '',
            $this->marker[1] => '',
        ]);
    }
}