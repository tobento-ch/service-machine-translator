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

namespace Tobento\Service\MachineTranslator\Azure;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorCreationException;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

class  MachineTranslatorFactory implements MachineTranslatorFactoryInterface
{
    protected array $defaults = [
        'endpoint' => 'https://api.cognitive.microsofttranslator.com',
        'region' => 'westeurope',
        'apiKey' => null, // must be provided by user
    ];
    
    /**
     * Create a new instance.
     *
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param array<string, mixed> $defaults
     */
    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        array $defaults = [],
    ) {
        $this->defaults = array_replace($this->defaults, $defaults);
    }
    
    /**
     * Creates and returns a new MachineTranslator instance.
     *
     * @param string $name The translator name used for identification.
     * @param array $config  Provider-specific configuration values used to construct the translator instance.
     * @return MachineTranslatorInterface
     * @throws MachineTranslatorCreationException If the translator cannot be created.
     */
    public function createTranslator(string $name, array $config = []): MachineTranslatorInterface
    {
        try {
            // Merge defaults with user config
            $config = array_replace($this->defaults, $config);
            
            // Validate required config
            if (empty($config['apiKey'])) {
                throw new MachineTranslatorCreationException(
                    message: sprintf('Azure translator "%s" requires a non-empty "apiKey" config value.', $name)
                );
            }

            return new MachineTranslator(
                client: $this->client,
                requestFactory: $this->requestFactory,
                streamFactory: $this->streamFactory,
                endpoint: $config['endpoint'],
                region: $config['region'],
                apiKey: $config['apiKey'],
                name: $name,
            );

        } catch (\Throwable $e) {
            throw new MachineTranslatorCreationException(
                message: sprintf('Failed creating Azure translator "%s": %s', $name, $e->getMessage()),
                code: 0,
                previous: $e,
            );
        }
    }
}