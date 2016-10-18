<?php

namespace Gendoria\CommandQueueBundle\Command;

use Gendoria\CommandQueueBundle\Worker\WorkerRunnerManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of RunWorkerCommand
 *
 * @author Tomasz Struczyński <t.struczynski@gmail.com>
 */
class ListWorkersCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('command-queue:list-workers')
            ->setDescription('List available workers');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $workerRunnerService WorkerRunnerManager */
        $workerRunnerService = $this->getContainer()->get('gendoria_command_queue.runner_manager');
        $runners = $workerRunnerService->getRunners();
        $runnersFormatted = array_map(array($this, 'formatRunnerName'), $runners);
        $output->writeln('Registered workers:');
        $output->writeln($runnersFormatted);
    }

    public function formatRunnerName($name)
    {
        return sprintf("  * <info>%s</info>", $name);
    }

}
