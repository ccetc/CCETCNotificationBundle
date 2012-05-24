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
     * 
     * @param type $instance
     * @return type 
     */
    public function instanceIsActive($instance)
    {        
        if( $this->instanceIsActiveForDashboard($instance) || $this->instanceIsActiveForEmail($instance)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function instanceIsActiveForDashboard($instance)
    {
        $notification = $instance->getNotification();
        $dashboardStateMethodServiceName = $notification->getDashboardStateMethodService();
        $dashboardStateMethod = $notification->getDashboardStateMethod();
        $dashboardStateMethodParameters = $notification->getDashboardStateMethodParameters();
        
        if($dashboardStateMethodServiceName && $dashboardStateMethod) {
            $dashboardStateMethodService = $this->container->get($dashboardStateMethodServiceName);
            return call_user_func_array(array($dashboardStateMethodService, $dashboardStateMethod), eval("return ".$dashboardStateMethodParameters.";"));
        } else {
            return $instance->getNotification()->getShowOnDashboard() && !$instance->getHasBeenShownOnDashboard();
        }
    }
    
    public function instanceIsActiveForEmail($instance)
    {
        return $instance->getNotification()->getSendEmail() && !$instance->getHasBeenEmailed();
    }
}