<?php
namespace CCETC\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ccetc:notification:sendemails')
            ->setDescription('send all outstanding notification emails for users with a specific frequency')
            ->addArgument('frequency', InputArgument::REQUIRED, 'At what frequency?')                
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $frequency = $input->getArgument('frequency');
        
        $deliveryHelper = $this->getContainer()->get('ccetc.notification.delivery');
                
        $count = $deliveryHelper->processAndSendNotificationEmails($frequency);

        $output->writeln($count.' Emails Sent');
    }
}