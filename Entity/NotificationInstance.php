<?php

namespace CCETC\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CCETC\NotificationBundle\Entity\NotificationInstance
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CCETC\NotificationBundle\Entity\NotificationInstanceRepository")
 */
class NotificationInstance
{
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
   /** @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User", inversedBy="notifications")
    */
    protected $user;

    /** @ORM\ManyToOne(targetEntity="CCETC\NotificationBundle\Entity\Notification", inversedBy="instances") */
    protected $notification;

    /**
     * @var boolean $needsToBeEmailed
     *
     * @ORM\Column(name="needsToBeEmailed", type="boolean", nullable="true")
     */
    private $needsToBeEmailed;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean", nullable="true")
     */
    private $active;
    
    /**
     * @var boolean $hasAssociatedObject
     *
     * @ORM\Column(name="hasAssociatedObject", type="boolean", nullable="true")
     */
    private $hasAssociatedObject;
    
    
    public function __toString()
    {
        if(isset($this->id)) return $this->user->__toString().' - '.$this->notification->__toString();
        else return '';
    }
    
    public function __construct()
    {
        if(!isset($this->needsToBeEmailed)) $this->needsToBeEmailed = false;
        if(!isset($this->active)) $this->active = true;
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user
     *
     * @param Application\Sonata\UserBundle\Entity\User $user
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Application\Sonata\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set notification
     *
     * @param CCETC\NotificationBundle\Entity\Notification $notification
     */
    public function setNotification(\CCETC\NotificationBundle\Entity\Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get notification
     *
     * @return CCETC\NotificationBundle\Entity\notification 
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set hasAssociatedObject
     *
     * @param boolean $hasAssociatedObject
     */
    public function setHasAssociatedObject($hasAssociatedObject)
    {
        $this->hasAssociatedObject = $hasAssociatedObject;
    }

    /**
     * Get hasAssociatedObject
     *
     * @return boolean 
     */
    public function getHasAssociatedObject()
    {
        return $this->hasAssociatedObject;
    }

    /**
     * Set active.
     * 
     * Make sure to only set this within the bundle if instance does not $hasAssociatedObject
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set needsToBeEmailed
     *
     * @param boolean $needsToBeEmailed
     */
    public function setNeedsToBeEmailed($needsToBeEmailed)
    {
        $this->needsToBeEmailed = $needsToBeEmailed;
    }

    /**
     * Get needsToBeEmailed
     *
     * @return boolean 
     */
    public function getNeedsToBeEmailed()
    {
        return $this->needsToBeEmailed;
    }
}