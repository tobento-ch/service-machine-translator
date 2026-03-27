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

namespace Tobento\Service\MachineTranslator\Test\Exception;

use PHPUnit\Framework\TestCase;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorException;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorCreationException;

class MachineTranslatorCreationExceptionTest extends TestCase
{
    public function testException()
    {
        $previous = new \RuntimeException();
        $e = new MachineTranslatorCreationException(message: 'msg', code: 1, previous: $previous);
        
        $this->assertInstanceof(MachineTranslatorException::class, $e);
        $this->assertSame('msg', $e->getMessage());
        $this->assertSame(1, $e->getCode());
        $this->assertSame($previous, $e->getPrevious());
    }
}