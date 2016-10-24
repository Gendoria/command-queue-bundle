<?php

namespace Gendoria\CommandQueueBundle\Tests\Command;

use Gendoria\CommandQueueBundle\Command\RunWorkerCommand;
use Gendoria\CommandQueueBundle\Worker\WorkerRunnerInterface;
use Gendoria\CommandQueueBundle\Worker\WorkerRunnerManager;
use PHPUnit_Framework_TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Test run worker command.
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class RunWorkerCommandTest extends PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $container = new ContainerBuilder();
        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $kernel->expects($this->any())->method('getBundles')->will($this->returnValue(array()));
        $kernel->expects($this->any())->method('getContainer')->will($this->returnValue($container));
        
        $manager = new WorkerRunnerManager($container);
        $container->set('gendoria_command_queue.runner_manager', $manager);
        $runner = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $container->set('test', $runner);
        $manager->addRunnerService('test', 'test');
        $container->compile();
        
        $application = new Application($kernel);
        $application->add(new RunWorkerCommand());

        $command = $application->find('cmq:worker:run');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'name'    => 'test',
            )
        );
    }
    
    public function testExecuteNoWorker()
    {
        $container = new ContainerBuilder();
        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $kernel->expects($this->any())->method('getBundles')->will($this->returnValue(array()));
        $kernel->expects($this->any())->method('getContainer')->will($this->returnValue($container));
        
        $manager = new WorkerRunnerManager($container);
        $container->set('gendoria_command_queue.runner_manager', $manager);
        $runner = $this->getMockBuilder(WorkerRunnerInterface::class)->getMock();
        $container->set('test_svc', $runner);
        $manager->addRunnerService('different', 'test_svc');
        $container->compile();
        
        $application = new Application($kernel);
        $application->add(new RunWorkerCommand());

        $command = $application->find('cmq:worker:run');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(
            array(
                'name'    => 'test',
            )
        );
        $this->assertEquals(1, $exitCode);
        $this->assertContains('Worker "test" not registered.', $commandTester->getDisplay());
        $this->assertContains('different', $commandTester->getDisplay());
    }    
}
