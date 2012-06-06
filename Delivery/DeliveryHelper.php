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
        $utilityHelper = $this->container->get('ccetc.notification.utility');
        $mailer = $this->container->get('mailer');
        
        $fromEmail = $this->container->getParameter('fos_user.registration.confirmation.from_email');
        $applicationTitle = $this->container->getParameter('fos_user.settings.application_title');
        
        $instances = $utilityHelper->splitInstancesByType($instances);
        
        $body = '<html>';

        $body .= "You have ";
        
        $totals = "";
        
        foreach($instances as $type => $typeInstances) {
            if(count($typeInstances) == 1) $noun = $type;
            else $noun = $type.'s';
            
            if($totals != "") $totals .= " and ";
            $totals .= count($typeInstances).' new '.$noun;
        }
        
        $body .= $totals;
            
        $body .= ':<br/><br/>';
            
        foreach($instances as $type => $typeInstances) {
            if(count($instances) > 1) $body .= '<b>'.ucfirst($type).'s</b><br/><br/>';
            $body .= '<div style="width: 600px; margin: 0 auto; border: 1px solid #ddd;">';
            
            
            foreach($typeInstances as $instance)
            {
                $notification = $instance->getNotification();

                $body .= '<div style="border-bottom: 1px solid #ddd; padding: 6px 8px; overflow: auto;">';
                $body .= '<div style="overflow: auto;"><div style="float: left; font-weight: bold;">'.$notification->getShortMessage().'</div>';
                
                if($notification->getType() == "task") {
                    $body .= '<div style="float: right; color: #666;">'.$notification->getDateTaskDueNice().'</div></div>';                    
                } else {
                    $body .= '<div style="float: right; color: #666;">'.$notification->getDateTimeCreatedNice().'</div></div>';
                }
                
                if($notification->getLongMessage()) $body .= '<div style="float: left;">'.$notification->getLongMessage().'</div>';
                $body .= '</div>';
            }

            $body .= '</div><br/><br/>';
        }
        
        
        $body .= '<span style="color: #666;">Control how often you receive notification emails on your <a href="'.$this->getContainer()->getParameter('my_cce_app.server_address').$this->getContainer()->get('router')->generate('fos_user_profile_show').'">Profile Page</a></span>';
        
        $message = \Swift_Message::newInstance()
                ->setSubject($applicationTitle.' - Notification Digest')
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
    public function findInstancesByUser($user, $active = null, $hasBeenEmailed = null, $type = null)
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getEntityManager();
        
        // get all of user's instances
        $query = "
            SELECT   ni, u, n
            FROM     CCETCNotificationBundle:NotificationInstance ni
            JOIN     ni.user u
            JOIN     ni.notification n
            WHERE u.id = '".$user->getId()."'";
        
        if(isset($active)) {
            if($active) $active = 1;
            else $active = 0;
            $query .= " AND ni.active=".$active;
        }
        if(isset($hasBeenEmailed)) {
            if($hasBeenEmailed) $hasBeenEmailed = 1;
            else $hasBeenEmailed = 0;
            $query .= " AND ni.hasBeenEmailed=".$hasBeenEmailed;
        }
        if(isset($type)) {
            $query .= " AND n.type='".$type."'";
        }

        $query .= " ORDER BY n.datetimeCreated DESC";
        
        return $entityManager->createQuery($query)->getResult();
    }

    public function getActiveTaskCountByUser($user)
    {
        $doctrine = $this->container->get('doctrine');
        $entityManager = $doctrine->getEntityManager();
        
        // get all of user's instances
        $query = "
            SELECT   count(ni)
            FROM     CCETCNotificationBundle:NotificationInstance ni
            JOIN     ni.user u
            JOIN     ni.notification n
            WHERE u.id = '".$user->getId()."' AND ni.active=1 AND n.type='task'";
        
        return $entityManager->createQuery($query)->getResult();        
    }
    
}