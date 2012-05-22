<?php
namespace CCETC\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ccetc:notification:cleanup')
            ->setDescription('remove notifications that are inactive')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $utilityHelper = $this->getContainer()->get('ccetc.notification.utility');
                
        $count = $utilityHelper->removeInactiveNotifications();

        $output->writeln($count.' Notifications Removed');
    }
}