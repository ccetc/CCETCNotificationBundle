<?php

namespace CCETC\NotificationBundle\Delivery;

/**
 * Methods for delivering notifications
 */
class DeliveryHelper {
    
    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * Send an email for $instance.
     * 
     * NOTE: this will send an e-mail even if $instance->notification->sendEmail is false
     * 
     * @param type $instance 
     */
    public function sendNotificationEmail($instance)
    {
        $mailer = $this->container->get('mailer');
        
        $fromEmail = $this->container->getParameter('fos_user.registration.confirmation.from_email');
        $applicationTitle = $this->container->getParameter('fos_user.settings.application_title');
        
        $message = \Swift_Message::newInstance()
                ->setSubject($applicationTitle.' - '.$instance->getNotification()->getShortMessage())
                ->setFrom($fromEmail)
                ->setTo($instance->getUser()->getEmail())
                ->setContentType('text/html')
                ->setBody('<html>'.$instance->getNotification()->getLongMessage().'</html>')
        ;
        $mailer->send($message);
    }
    
    /**
     * Send all notification instance emails that need to be sent
     * @return int 
     */
    public function processAndSendNotificationEmails()
    {
        $notificationRepository = $this->container->get('doctrine')->getRepository('CCETCNotificationBundle:Notification');
        $notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');

        $notifications = $notificationRepository->findBy(array('sendEmail' => true));
        
        $emailsSent = 0;
        
        foreach($notifications as $notification)
        {
            foreach($notification->getInstances() as $instance)
            {
                if(!$instance->getHasBeenEmailed()) {
                    $this->sendNotificationEmail($instance);
                    $instance->setHasBeenEmailed(true);
                    $notificationInstanceAdmin->update($instance);
                    $emailsSent++;
                }
            }
        }
        
        return $emailsSent;
    }
    
    /**
     * Find all active instances for $user
     * 
     * @param type $user
     * @return type 
     */
    public function findActiveByUser($user)
    {  
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getEntityManager();
        $stateHelper = $this->container->get('ccetc.notification.state');        
        
        $query = $entityManager->createQuery(
            "SELECT   ni, u, n
            FROM     CCETCNotificationBundle:NotificationInstance ni
            JOIN     ni.user u
            JOIN     ni.notification n
            WHERE u.id = '".$user->getId()."'
            ORDER BY n.datetimeCreated DESC
        ");

        $instances = $query->getResult();
        $activeInstances = array();
                
        foreach($instances as $instance)
        {
            if($stateHelper->instanceIsActive($instance)) {
                $activeInstances[] = $instance;
            }
        }

        return $activeInstances;
    }

    
}