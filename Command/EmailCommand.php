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
            ->setDescription('send all outstanding notification emails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deliveryHelper = $this->getContainer()->get('ccetc.notification.delivery');
                
        $count = $deliveryHelper->processAndSendNotificationEmails();

        $output->writeln($count.' Emails Sent');
    }
}