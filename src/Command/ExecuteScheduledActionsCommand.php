<?php 
namespace App\Command;

use App\Service\ActionScheduler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:execute-scheduled-actions',
    description: 'Exécute les actions planifiées qui sont prêtes.'
)]
class ExecuteScheduledActionsCommand extends Command
{
    private ActionScheduler $actionScheduler;

    public function __construct(ActionScheduler $actionScheduler)
    {
        parent::__construct();
        $this->actionScheduler = $actionScheduler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->actionScheduler->executeScheduledActions();
        $output->writeln('<info>Actions planifiées exécutées avec succès.</info>');
        return Command::SUCCESS;
    }
}
