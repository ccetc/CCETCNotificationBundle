<?php
namespace CCETC\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReminderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ccetc:notification:updatereminders')
            ->setDescription('set instances as needsToBeEmailed if a reminder is set')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deliveryHelper = $this->getContainer()->get('ccetc.notification.delivery');
                
        $count = $deliveryHelper->updateTaskReminders();

        $output->writeln($count.' instances updated');
    }
}