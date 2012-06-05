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
     * Send all notification instance emails that need to be sent to users that recieve emails at a specific $frequency
     * 
     * @param type $frequency - instantly|periodically|daily|never
     * @return int 
     */
    public function processAndSendNotificationEmails($frequency)
    {
        $userRepository = $this->container->get('doctrine')->getRepository('ApplicationSonataUserBundle:User');
        $notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');
        $emailsSent = 0;
        
        $users = $userRepository->findBy(array('notificationEmailFrequency' => $frequency));
        
        foreach($users as $user)
        {
            $instancesToEmail = $this->findInstancesByUser($user, true, false);

            if($instancesToEmail) {
                $this->sendNotificationDigestEmail($user, $instancesToEmail);
                $emailsSent++;
            
                foreach($instancesToEmail as $instance)
                {
                    $instance->setHasBeenEmailed(true);
                    $notificationInstanceAdmin->update($instance);
                }            
            }
        }
        
        return $emailsSent;
    }
    
    /**
     * Send an email to $user listing $instances.
     * 
     * @param type $instance 
     */
    public function sendNotificationDigestEmail($user, $instances)
    {
        $mailer = $this->container->get('mailer');
        
        $fromEmail = $this->container->getParameter('fos_user.registration.confirmation.from_email');
        $applicationTitle = $this->container->getParameter('fos_user.settings.application_title');
        
        if(count($instances) == 1) $noun = "notification";
        else $noun = "notifications";
        
        $body = '<html>';
        
        $body .= 'You have '.count($instances).' new '.$noun.' on <a href="'.$this->getContainer()->getParameter('my_cce_app.server_address').'">MyCCE</a>:<br/><br/> ';
        
        $body .= '<div style="width: 600px; margin: 0 auto; border: 1px solid #eee;">';
        foreach($instances as $instance)
        {
            $notification = $instance->getNotification();
            
            $body .= '<div style="border-bottom: 1px solid #eee; padding: 6px 8px; overflow: auto;">';
            $body .= '<div style="overflow: auto;"><div style="float: left; font-weight: bold;">'.$notification->getShortMessage().'</div>';
            $body .= '<div style="float: right; color: #666;">'.$notification->getDateTimeCreatedNice().'</div></div>';
            if($notification->getLongMessage()) $body .= '<div style="float: left;">'.$notification->getLongMessage().'</div>';
            $body .= '</div>';
        }
        
        $body .= '</div>';
        
        $body .= '<br/><br/><span style="color: #666;">Control how often you receive notification emails on your <a href="'.$this->getContainer()->getParameter('my_cce_app.server_address').$this->getContainer()->get('router')->generate('fos_user_profile_show').'">Profile Page</a></span>';
        
        $message = \Swift_Message::newInstance()
                ->setSubject($applicationTitle.' - '.count($instances).' '.ucfirst($noun))
                ->setFrom($fromEmail)
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody($body)
        ;
        $mailer->send($message);
    }
    
    
    /**
     * Fine all instances belonging to User.
     * 
     * @param type $user
     * @param bool $active only include inactive or active instances
     * @return type 
     */
    public function findInstancesByUser($user, $active = null, $hasBeenEmailed = null)
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getEntityManager();
        
        if(isset($active)) {
            if($active) $active = 1;
            else $active = 0;
        }
        if(isset($hasBeenEmailed)) {
            if($hasBeenEmailed) $hasBeenEmailed = 1;
            else $hasBeenEmailed = 0;
        }
        
        // get all of user's instances
        $query = "
            SELECT   ni, u, n
            FROM     CCETCNotificationBundle:NotificationInstance ni
            JOIN     ni.user u
            JOIN     ni.notification n
            WHERE u.id = '".$user->getId()."'";
        
        if(isset($active)) {
            $query .= "AND ni.active=".$active;
        }
        if(isset($hasBeenEmailed)) {
            $query .= "AND ni.hasBeenEmailed=".$hasBeenEmailed;
        }

        $query .= " ORDER BY n.datetimeCreated DESC";
        
        return $entityManager->createQuery($query)->getResult();
    }

    
}