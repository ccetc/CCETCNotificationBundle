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
        
        $users = $userRepository->findBy(array('notificationEmailFrequency' => $frequency, 'tester' => true));
                
        foreach($users as $user)
        {
            $instancesToEmail = $this->findInstancesByUser($user, true, true);

            if($instancesToEmail) {
                $this->sendNotificationDigestEmail($user, $instancesToEmail);
                $emailsSent++;
            
                foreach($instancesToEmail as $instance)
                {
                    $instance->setNeedsToBeEmailed(false);
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
        $applicationTitle = $this->container->getParameter('fos_user.options.application_title');
        
        $instances = $utilityHelper->splitInstancesByType($instances);
        
        $totals = "";
        
        foreach($instances as $type => $typeInstances) {
            if(count($typeInstances) == 1) $noun = $type;
            else $noun = $type.'s';
            
            if($totals != "") $totals .= " and ";
            $totals .= count($typeInstances).' '.$noun;
        }
        $body = $this->container->get('twig')->render('CCETCNotificationBundle:Email:digest.html.twig', array(
            'totals' => $totals,
            'instances' => $instances,
            'appHref' => $this->getContainer()->getParameter('my_cce_app.server_address'),
            'settingsHref' => $this->getContainer()->getParameter('my_cce_app.server_address').$this->getContainer()->get('router')->generate('fos_user_settings')
        ));        
        $message = \Swift_Message::newInstance()
                ->setSubject($applicationTitle.' - Notification & Task Digest')
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
    public function findInstancesByUser($user, $active = null, $needsToBeEmailed = null, $type = null)
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
        if(isset($needsToBeEmailed)) {
            if($needsToBeEmailed) $needsToBeEmailed = 1;
            else $needsToBeEmailed = 0;
            $query .= " AND ni.needsToBeEmailed=".$needsToBeEmailed;
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
    
    /**
     * For each task,
     * set each instance to needsToBeEmailed
     * if it is active, and today is one of the taskReminderDays from the dateDue
     */
    public function updateTaskReminders()
    {
        $notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');
        $notificationRepository = $this->container->get('doctrine')->getRepository('CCETCNotificationBundle:Notification');
        
        $notifications = $notificationRepository->findByType('task');
        
        $instancesUpdated = 0;
        
        foreach($notifications as $notification)
        {
            if($notification->getActive() && $notification->getTaskReminderDays() && $notification->getDateTaskDue()) {        
                $reminderDays = explode(',', $notification->getTaskReminderDays());
                foreach($reminderDays as $day) {
                    $dateToCheckFor = date('Y-m-d', time() + ($day * 24 * 60 * 60));
                    
                    $dateDue = $notification->getDateTaskDue()->format('Y-m-d');
                    
                    if($dateDue == $dateToCheckFor) {
                        foreach($notification->getInstances() as $instance)
                        {
                            if($instance->getActive()) {
                                $instance->setNeedsToBeEmailed(true);
                                $notificationInstanceAdmin->update($instance);
                                $instancesUpdated++;
                            }
                        }
                    }
                }
            }
        }
        
        return $instancesUpdated;
    }
    
    public function getNotifyWhoChoices()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $notifyWhoChoices = array();
        
        if( $user->isSuperAdmin()) {
            $notifyWhoChoices['allUsers'] = 'All Users';
        }
        
        if($this->container->get('security.context')->isGranted('ROLE_NOTIFY_REGION_STAFF')) {
            $notifyWhoChoices['region-'.$user->getWorkingRegion()->getId()] = 'Staff in '.$user->getWorkingRegion().' Region';
            foreach($user->getWorkingRegion()->getCounties() as $county)
            {
                $notifyWhoChoices['county-'.$county->getId()] = 'Staff in '.$county.' County';
            }
        }
        
        if($this->container->get('security.context')->isGranted('ROLE_NOTIFY_COUNTY_STAFF')) {
            $notifyWhoChoices['county-'.$user->getWorkingCounty()->getId()] = 'Staff in '.$user->getWorkingCounty().' County';
        }
        
        if($user->hasSupervisees()) {
            $notifyWhoChoices['supervisees'] = 'Staff I supervise';
        }
         
        if($user->hasChildSupervisees()) {
            $notifyWhoChoices['childSupervisees'] = 'Staff I supervise and staff they supervise';
        }
        
        return $notifyWhoChoices;
    }
    
    public function getNotifyWhoText()
    {
        $notifyWhoChoices = $this->getNotifyWhoChoices();
        if(count($notifyWhoChoices) > 0) return $notifyWhoChoices[key($notifyWhoChoices)];
        return "";
    }    
}