<?php

namespace Gendoria\CommandQueueBundle\Tests\DependencyInjection\Pass;

use Gendoria\CommandQueue\Worker\WorkerRunnerInterface;
use Gendoria\CommandQueueBundle\DependencyInjection\Pass\WorkerRunnersPass;
use Gendoria\CommandQueueBundle\Worker\WorkerRunnerManager;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Description of WorkersPassTest
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class WorkerRunnersPassTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $container = new ContainerBuilder();
        $runnerManager = new Definition(WorkerRunnerManager::class, array('@service_container'));
        $container->addDefinitions(array('gendoria_command_queue.runner_manager' => $runnerManager));
        $pass = new WorkerRunnersPass();
        $pass->process($container);
    }
    
    public function testPass()
    {
        $options = array('1' => '1');
        $runerMock = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $container = new ContainerBuilder();
        $runnerManager = new Definition(WorkerRunnerManager::class, array('@service_container'));
        $workerRunner = new Definition(get_class($runerMock));
        $workerRunner->addTag(WorkerRunnersPass::WORKER_RUNNER_TAG, array('name' => 'test', 'options' => json_encode($options)));
        $container->addDefinitions(array('test_svc' => $workerRunner, WorkerRunnersPass::MANAGER_ID => $runnerManager));
        
        $pass = new WorkerRunnersPass();
        $pass->process($container);
        
        $this->assertTrue($runnerManager->hasMethodCall('addRunnerService'));
        $calls = $runnerManager->getMethodCalls();
        $this->assertEquals('addRunnerService', $calls[0][0]);
        $this->assertEquals(array('test', 'test_svc', $options), $calls[0][1]);
    }
    
    public function testPassNoName()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Tag '.WorkerRunnersPass::WORKER_RUNNER_TAG.' has to contain "name" parameter.');
        $runerMock = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $container = new ContainerBuilder();
        $runnerManager = new Definition(WorkerRunnerManager::class, array('@service_container'));
        $workerRunner = new Definition(get_class($runerMock));
        $workerRunner->addTag(WorkerRunnersPass::WORKER_RUNNER_TAG, array());
        $container->addDefinitions(array('test_svc' => $workerRunner, 'gendoria_command_queue.runner_manager' => $runnerManager));
        $pass = new WorkerRunnersPass();
        $pass->process($container);
    }
    
    public function testPassIncorrectService()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Runner service has to implement WorkerRunnerInterface.');
        $runerMock = $this->getMockBuilder(stdClass::class)->getMock();
        $container = new ContainerBuilder();
        $runnerManager = new Definition(WorkerRunnerManager::class, array('@service_container'));
        $workerRunner = new Definition(get_class($runerMock));
        $workerRunner->addTag(WorkerRunnersPass::WORKER_RUNNER_TAG, array('name' => 'test'));
        $container->addDefinitions(array('test_svc' => $workerRunner, 'gendoria_command_queue.runner_manager' => $runnerManager));
        $pass = new WorkerRunnersPass();
        $pass->process($container);
    }
    
    public function testPassIncorrectOptions()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Options parameter has to be a valid JSON.');
        $runerMock = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $container = new ContainerBuilder();
        $runnerManager = new Definition(WorkerRunnerManager::class, array('@service_container'));
        $workerRunner = new Definition(get_class($runerMock));
        $workerRunner->addTag(WorkerRunnersPass::WORKER_RUNNER_TAG, array('name' => 'test', 'options' => '--'));
        $container->addDefinitions(array('test_svc' => $workerRunner, 'gendoria_command_queue.runner_manager' => $runnerManager));
        $pass = new WorkerRunnersPass();
        $pass->process($container);
    }
}
