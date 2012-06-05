<?php

namespace CCETC\NotificationBundle\Builder;

/**
 * A helper for easy Notification creation
 */
class NotificationBuilder {
    
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
     * Create a new Notification
     * 
     * Options:
     *  - values: associative array of field/value pairs for the notification entity
     *  - users: array of users to create instances for (also takes a single user)
     *  - user: single user to create instance for 
     * 
     * @param type $options 
     */
    public function createNotification($options)
    {
        $notification = new \CCETC\NotificationBundle\Entity\Notification();
        
        foreach($options['values'] as $field => $value)
        {
            $method = 'set'.ucfirst($field);
            $notification->$method($value);
        }
                
        $notificationAdmin = $this->container->get('ccetc.notification.admin.notification');        	    
        $notificationAdmin->create($notification);

        if(isset($options['hasAssociatedObject'])) {
            $hasAssociatedObject = $options['hasAssociatedObject'];
        } else {
            $hasAssociatedObject = false;
        }
        
        if(isset($options['users'])) {
            if(is_array($options['users'])) {
                foreach($users as $user)
                {
                    $this->createNotificationInstance($notification, $user, $hasAssociatedObject);
                }
            } else {
                $this->createNotificationInstance($notification, $options['users'], $hasAssociatedObject);
            }
        }
        if(isset($options['user'])) {
            $this->createNotificationInstance($notification, $options['user'], $hasAssociatedObject);
        }

        return $notification;
    }
    
    /**
     * Create an instance of $notification for $user
     * @param type $notification
     * @param type $user 
     */
    public function createNotificationInstance($notification, $user, $hasAssociatedObject = null)
    {
        $instance = new \CCETC\NotificationBundle\Entity\NotificationInstance();
        $instance->setUser($user);
        $instance->setNotification($notification);            
        if(isset($hasAssociatedObject)) $instance->setHasAssociatedObject($hasAssociatedObject);
        $notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');
        $notificationInstanceAdmin->create($instance);
        
        return $instance;
    }
    
}