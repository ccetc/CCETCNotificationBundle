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
    * @var smallint $hasBeenShownOnDashboard
    *
    * @ORM\Column(name="hasBeenShownOnDashboard", type="boolean", nullable="true")
    */
    private $hasBeenShownOnDashboard;

    /**
    * @var smallint $hasBeenEmailed
    *
    * @ORM\Column(name="hasBeenEmailed", type="boolean", nullable="true")
    */
    private $hasBeenEmailed;

   /**
    * @var smallint $activeForDashboard
    *
    * @ORM\Column(name="activeForDashboard", type="boolean", nullable="true")
    */
    private $activeForDashboard;
    
   /**
    * @var smallint $activeForEmail
    *
    * @ORM\Column(name="activeForEmail", type="boolean", nullable="true")
    */
    private $activeForEmail;
    
   /**
    * @var smallint $hasAssociatedObject
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
        if(!isset($this->hasBeenEmailed)) $this->hasBeenEmailed = false;
        if(!isset($this->hasBeenShownOnDashboard)) $this->hasBeenShownOnDashboard = false;
        if(!isset($this->activeForEmail)) $this->activeForEmail = true;
        if(!isset($this->activeForDashboard)) $this->activeForDashboard = true;
    }
    
    public function getActive()
    {
        return $this->activeForDashboard || $this->activeForEmail;
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
     * Set hasBeenShownOnDashboard
     *
     * @param boolean $hasBeenShownOnDashboard
     */
    public function setHasBeenShownOnDashboard($hasBeenShownOnDashboard)
    {
        if(!$this->hasAssociatedObject && $hasBeenShownOnDashboard) {
            $this->activeForDashboard = false;
        }
        $this->hasBeenShownOnDashboard = $hasBeenShownOnDashboard;
    }

    /**
     * Get hasBeenShownOnDashboard
     *
     * @return boolean 
     */
    public function getHasBeenShownOnDashboard()
    {
        return $this->hasBeenShownOnDashboard;
    }

    /**
     * Set hasBeenEmailed
     *
     * @param boolean $hasBeenEmailed
     */
    public function setHasBeenEmailed($hasBeenEmailed)
    {
        if(!$this->hasAssociatedObject && $hasBeenEmailed) {
            $this->activeForEmail = false;
        }
        $this->hasBeenEmailed = $hasBeenEmailed;
    }

    /**
     * Get hasBeenEmailed
     *
     * @return boolean 
     */
    public function getHasBeenEmailed()
    {
        return $this->hasBeenEmailed;
    }


    /**
     * Set activeForDashboard
     *
     * @param boolean $activeForDashboard
     */
    public function setActiveForDashboard($activeForDashboard)
    {
        $this->activeForDashboard = $activeForDashboard;
    }

    /**
     * Get activeForDashboard
     *
     * @return boolean 
     */
    public function getActiveForDashboard()
    {
        return $this->activeForDashboard;
    }

    /**
     * Set activeForEmail
     *
     * @param boolean $activeForEmail
     */
    public function setActiveForEmail($activeForEmail)
    {
        $this->activeForEmail = $activeForEmail;
    }

    /**
     * Get activeForEmail
     *
     * @return boolean 
     */
    public function getActiveForEmail()
    {
        return $this->activeForEmail;
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
}