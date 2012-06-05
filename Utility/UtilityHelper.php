<?php

namespace CCETC\NotificationBundle\Utility;

class UtilityHelper {
    
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
     * remove all notifications that are inactive and over 30 days old
     * @return int 
     */
    public function removeOldNotifications()
    {
        $notificationRepository = $this->container->get('doctrine')->getRepository('CCETCNotificationBundle:Notification');
        $notifications = $notificationRepository->findAll();
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        
        $notificationsRemoved = 0;
        
        foreach($notifications as $notification)
        {
            $interval = $notification->getDatetimeCreated()->diff(new \DateTime());
            $daysOld = (int) $interval->format('%a');
            if(!$notification->getActive() && $daysOld > 30) {
                $entityManager->remove($notification);
                $notificationsRemoved++;
            }
        }
        
        $entityManager->flush();
        
        return $notificationsRemoved;
    }
    
    /**
     * Set each hasBeenShownOnDashboard for each $instance in $instances as true.
     * 
     * @param array of NotificationInstances $instances 
     */
    public function batchSetShownOnDashboard($instances)
    {
        $entityManager = $this->container->get('doctrine')->getEntityManager();

        foreach($instances as $instance)
        {
            $instance->setHasBeenShownOnDashboard(true);
            $entityManager->persist($instance);
        }
        
        $entityManager->flush();
    }
    
    
}