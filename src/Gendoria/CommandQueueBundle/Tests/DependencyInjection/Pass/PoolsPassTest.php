<?php

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection\Pass;

use Gendoria\CommandQueue\QueueManager\MultipleQueueManagerInterface;
use Gendoria\CommandQueue\QueueManager\SingleQueueManagerInterface;
use Gendoria\CommandQueue\SendDriver\SendDriverInterface;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\PoolsPass;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Tests of pools pass
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 * @group CommandQueue
 * @group legacy
 */
class PoolsPassTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSingleQueueManagerDefinition
     */
    public function testSingleQueueManager(Definition $definition, $poolName, $poolValid = true, $hasValidTags = true)
    {
        if (!$poolValid) {
            $this->setExpectedException(InvalidArgumentException::class, sprintf(
                'Service "%s" requests non existing queue pool "%s".',
                'single',
                $definition->getTag(PoolsPass::QUEUE_MANAGER_TAG)[0]['pool']
            ));
        }
        if (!$hasValidTags) {
            $this->setExpectedException(InvalidArgumentException::class, 'Only single ' . PoolsPass::QUEUE_MANAGER_TAG . ' tag possible on service single.');
        }
        $container = new ContainerBuilder();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $pools = array(
            'default' => array(
                'send_driver' => '@dummy',
            ),
        );
        if ($poolName !== 'default' && $poolValid) {
            $pools[$poolName] = array(
                'send_driver' => '@dummy1',
            );
            $container->addDefinitions(array(
                'dummy1' => new Definition(get_class($sendDriverMock)),
            ));
        }
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
            'single' => $definition,
        ));
        $container->setParameter('gendoria_command_queue.pools', $pools);
        $poolsPass = new PoolsPass();
        $poolsPass->process($container);
    }
    
    /**
     * Get definitions for various queue manager passes
     */
    public function getSingleQueueManagerDefinition()
    {
        $singleQueueManager = $this->getMockBuilder(SingleQueueManagerInterface::class)->getMock();
        $definitionDefaultPool = new Definition(get_class($singleQueueManager));
        $definitionDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionNonDefaultPool = new Definition(get_class($singleQueueManager));
        $definitionNonDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG, array('pool' => 'newpool'));
        $definitionInvalidTags = new Definition(get_class($singleQueueManager));
        $definitionInvalidTags->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionInvalidTags->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        return array(
            array($definitionDefaultPool, 'default'),
            array($definitionNonDefaultPool, 'newpool'),
            array($definitionNonDefaultPool, 'newpool', false),
            array($definitionInvalidTags, 'default', true, false)
        );
    }
    
    /**
     * @dataProvider getMultipleQueueManagerDefinition
     */
    public function testMultipleQueueManager(Definition $definition, $poolsValid = true)
    {
        $tags = $definition->getTag(PoolsPass::QUEUE_MANAGER_TAG);
        if (!$poolsValid) {
            $invalidPool = '';
            foreach ($tags as $tag) {
                if (!empty($tag['pool']) && $tag['pool'] !== 'default') {
                    $invalidPool = $tag['pool'];
                    break;
                }
            }
            $this->setExpectedException(InvalidArgumentException::class, sprintf(
                'Service "%s" requests non existing queue pool "%s".',
                'single',
                $invalidPool
            ));
        }
        $container = new ContainerBuilder();
        $sendDriverMock = $this->getMockBuilder(SendDriverInterface::class)->getMock();
        $pools = array(
            'default' => array(
                'send_driver' => '@dummy_default',
            )
        );
        $container->addDefinitions(array(
            'dummy_default' => new Definition(get_class($sendDriverMock)),
        ));
        if ($poolsValid) {
            foreach ($tags as $tag) {
                $poolName = !empty($tag['pool']) ? $tag['pool'] : 'default';
                if ($poolName == 'default') {
                    continue;
                }
                $pools[$poolName] = array(
                    'send_driver' => '@dummy_'.$poolName,
                );
                $container->addDefinitions(array(
                    'dummy_'.$poolName => new Definition(get_class($sendDriverMock)),
                ));
            }
        }
        $container->setParameter('gendoria_command_queue.pools', $pools);
        $container->addDefinitions(array(
            'single' => $definition,
        ));
        $poolsPass = new PoolsPass();
        $poolsPass->process($container);
    }
    
    /**
     * Get definitions for various queue manager passes
     */
    public function getMultipleQueueManagerDefinition()
    {
        $multipleQueueManager = $this->getMockBuilder(MultipleQueueManagerInterface::class)->getMock();
        $definitionDefaultPool = new Definition(get_class($multipleQueueManager));
        $definitionDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionNonDefaultPool = new Definition(get_class($multipleQueueManager));
        $definitionNonDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG);
        $definitionNonDefaultPool->addTag(PoolsPass::QUEUE_MANAGER_TAG, array('pool' => 'newpool'));
        return array(
            array($definitionDefaultPool),
            array($definitionNonDefaultPool),
            array($definitionNonDefaultPool, false),
        );
    }    
    
    public function testInvalidSendDriverNoService()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Non existing send driver service provided: @dummy.');
        $container = new ContainerBuilder();
        $container->setParameter('gendoria_command_queue.pools', array(
            'default' => array(
                'send_driver' => '@dummy',
            )
        ));
        $poolsPass = new PoolsPass();
        $poolsPass->process($container);
    }
    
    public function testInvalidSendDriverInvalidInterface()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Service "dummy" does not implement interface "'.SendDriverInterface::class.'".');
        $container = new ContainerBuilder();
        $sendDriverMock = $this->getMockBuilder(stdClass::class)->getMock();
        $container->addDefinitions(array(
            'dummy' => new Definition(get_class($sendDriverMock)),
        ));
        $container->setParameter('gendoria_command_queue.pools', array(
            'default' => array(
                'send_driver' => '@dummy',
            )
        ));
        $poolsPass = new PoolsPass();
        $poolsPass->process($container);
    }
}
