<?php

namespace CCETC\NotificationBundle\Builder;

/**
 * A helper for easy Notification creation
 */
class NotificationBuilder {
    
    protected $container;
    protected $notificationInstanceAdmin;
    
    public function __construct($container)
    {
        $this->container = $container;
        $this->notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');
        
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
        $user = $this->container->get('security.context')->getToken()->getUser();
        
        foreach($options['values'] as $field => $value)
        {
            $method = 'set'.ucfirst($field);
            $notification->$method($value);
        }
           
        if(!array_key_exists('userCreatedBy', $options['values'])) { // check for the array key and not the value, so we can send null to create an anonymous notification
            $notification->setUserCreatedBy($user);
        }        
                        
        $notificationAdmin = $this->container->get('ccetc.notification.admin.notification');        	    
        $notificationAdmin->create($notification);

        if(isset($options['users'])) {
            if(is_array($options['users']) || get_class($options['users']) == "Doctrine\ORM\PersistentCollection") {
                foreach($options['users'] as $user)
                {
                    $this->createNotificationInstance($notification, $user);
                }
            } else {
                $this->createNotificationInstance($notification, $options['users']);
            }
        }
        if(isset($options['user_ids'])) {
            foreach($options['user_ids'] as $user_id)
            {
                $this->createNotificationInstance($notification->getId(), $user_id, true);
            }            
        }
        if(isset($options['user'])) {
            $this->createNotificationInstance($notification, $options['user']);
        }

        return $notification;
    }
    
    /**
     * Create an instance of $notification for $user
     * @param type $notification
     * @param type $user 
     */
    public function createNotificationInstance($notification, $user, $byId = false)
    {
        $instance = new \CCETC\NotificationBundle\Entity\NotificationInstance();

        if($byId) {
            $em = $this->container->get('doctrine')->getEntityManager();
            $instance->setUser($em->getReference('ApplicationSonataUserBundle:User', $user));
            $instance->setNotification($em->getReference('CCETCNotificationBundle:Notification', $notification));            
        } else {
            $instance->setUser($user);
            $instance->setNotification($notification);                        
        }
        
        $this->notificationInstanceAdmin->create($instance);
        
        return $instance;
    }
}