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
     * remove all notifications that are inactive
     * @return int 
     */
    public function removeInactiveNotifications()
    {
        $notificationRepository = $this->container->get('doctrine')->getRepository('CCETCNotificationBundle:Notification');
        $notifications = $notificationRepository->findAll();
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        $stateHelper = $this->container->get('ccetc.notification.state');
        
        $notificationsRemoved = 0;
        
        foreach($notifications as $notification)
        {
            if(!$stateHelper->notificationIsActive($notification)) {
                $entityManager->remove($notification);
                $notificationsRemoved++;
            }
        }
        
        $entityManager->flush();
        
        return $notificationsRemoved;
    }
    
    
}