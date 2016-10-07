<?php

namespace Gendoria\CommandQueueBundle\Tests\Fixtures;

use Gendoria\CommandQueue\Command\CommandInterface;
use Gendoria\CommandQueue\CommandProcessor\CommandProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Description of DummyCommandProcessor
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class DummyCommandProcessor implements CommandProcessorInterface
{
    public function process(CommandInterface $command)
    {
        
    }

    public function setLogger(LoggerInterface $logger)
    {
        
    }

    public function supports(CommandInterface $command)
    {
        return true;
    }

}
