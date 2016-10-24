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
class RunWorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cmq:worker:run')
            ->setDescription('Runs a worker process. Specific worker has to be registered by driver or application.')
            ->addArgument('name', InputArgument::REQUIRED, 'Worker name.');
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        /* @var $workerRunnerService WorkerRunnerManager */
        $workerRunnerService = $this->getContainer()->get('gendoria_command_queue.runner_manager');
        if (!$workerRunnerService->has($name)) {
            $runners = $workerRunnerService->getRunners();
            $runnersFormatted = array_map(array($this, 'formatRunnerName'), $runners);
            $output->writeln(sprintf('<error>Worker "%s" not registered.</error>', $name));
            $output->writeln('Registered workers:');
            $output->writeln($runnersFormatted);
            return 1;
        }
        $workerRunnerService->run($name, $output);
    }
    
    public function formatRunnerName($name)
    {
        return sprintf("  * <info>%s</info>", $name);
    }
}
