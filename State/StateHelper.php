<?php

namespace CCETC\NotificationBundle\State;

/**
 * Utility methods to check the active/inactive state of Notifications and NotificationInstances
 */
class StateHelper {
    
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
     * A Notification is active if it has any active instances
     * 
     * @param type $notification
     * @return type 
     */
    public function notificationIsActive($notification)
    {
        $stateHelper = $this->container->get('ccetc.notification.state');        
        
        foreach($notification->getInstances() as $instance) {
            if($stateHelper->instanceIsActive($instance)) {
                return true;
            }
        }
    
        return false;
    }

    /**
     * The state of an instance is determined by the notification's state check method if defined.
     * If no method is defined, the following rules are used.
     * 
     * Instances without state check methods are active if
     *  - the notification should be shown on dashboard and hasn't been
     *  - OR the notification should be emailed and hasn't been
     *  - OR the notifation has a showUntil date specified
     * 
     * @param type $instance
     * @return type 
     */
    public function instanceIsActive($instance)
    {        
        $notification = $instance->getNotification();
        $stateMethodService = $notification->getStateMethodService();
        $stateMethod = $notification->getStateMethod();
        $stateMethodParameter = $notification->getStateMethodParameter();
        
        // if a contraint method is defined, return the result
        if($stateMethodService && $stateMethod) {
            return $this->container->get($stateMethodService)->$method($this->container->get('doctrine'), $stateMethodParameter);
        } else if($notification->getShowOnDashboard() && !$instance->getHasBeenShownOnDashboard() ) {
            return true;
        } else if($notification->getSendEmail() && !$instance->getHasBeenEmailed() ){
            return true;
        } else if($notification->getActiveUntilDateTime() && !$notification->activeUntilDateTimeReached()) {
            return true;
        } else {
            return false;
        }
    }
    
}