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
            $instancesToEmail = $this->findInstancesByUser($user, true, "email");

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
     * NOTE: this will send an e-mail even if $instance->notification->sendEmail is false
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
        
        $body .= 'You have '.count($instances).' new '.$noun.':<br/><br/> ';
        
        foreach($instances as $instance)
        {
            $notification = $instance->getNotification();
            
            $body .= '<b>'.$notification->getShortMessage().'</b><br/>';
            if($notification->getLongMessage()) $body .= $notification->getLongMessage().'<br/>';
            $body .= '<br/><br/>';
        }
        
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
     * @param type $active only include inactive or active instances
     * @param type $type only include notifications of a certain type (email|dashboard)
     * @return type 
     */
    public function findInstancesByUser($user, $active = null, $type = null)
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getEntityManager();
        $stateHelper = $this->container->get('ccetc.notification.state');        
        
        // get all of user's instances
        $query = "
            SELECT   ni, u, n
            FROM     CCETCNotificationBundle:NotificationInstance ni
            JOIN     ni.user u
            JOIN     ni.notification n
            WHERE u.id = '".$user->getId()."'";
        
        if(isset($type) && $type == "email") {
            $query .= " AND n.sendEmail=1";
        }
        if(isset($type) && $type == "dashboard") {
            $query .= " AND n.showOnDashboard=1";
        }

        $query .= " ORDER BY n.datetimeCreated DESC";
        $instances = $entityManager->createQuery($query)->getResult();


        if(isset($active)) {
            if(isset($type) && $type == "dashboard") {
                $stateMethod = "instanceIsActiveForDashboard";
            } else if(isset($type) && $type == "email") {
                $stateMethod = "instanceIsActiveForEmail";
            } else {
                $stateMethod = "instanceIsActive";
            }

            $instancesToReturn = array();            
            
            foreach($instances as $instance)
            {
                if(($active && $stateHelper->$stateMethod($instance)) || (!$active && !$stateHelper->$stateMethod($instance))) {
                    $instancesToReturn[] = $instance;
                }
            }

            return $instancesToReturn;
        } else {
            return $instances;
        }
    }

    
}