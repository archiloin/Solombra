<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\ResourceUpdaterService;

#[AsCommand(
    name: 'app:update-resources',
    description: 'Met à jour les ressources de tous les empires.'
)]
class UpdateResourcesCommand extends Command
{
    private ResourceUpdaterService $resourceUpdater;

    public function __construct(ResourceUpdaterService $resourceUpdater)
    {
        $this->resourceUpdater = $resourceUpdater;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->resourceUpdater->updateResources();
        $output->writeln('Les ressources ont été mises à jour avec succès.');

        return Command::SUCCESS;
    }
}
