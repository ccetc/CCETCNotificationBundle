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

    public function __toString()
    {
        if(isset($this->id)) return $this->user->__toString().' - '.$this->notification->__toString();
        else return '';
    }
    
    public function __construct()
    {
        if(!isset($this->hasBeenEmailed)) $this->hasBeenEmailed = false;
        if(!isset($this->hasBeenShownOnDashboard)) $this->hasBeenShownOnDashboard = false;
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
     * @param CCETC\NotificationBundle\Entity\notification $notification
     */
    public function setNotification(\CCETC\NotificationBundle\Entity\notification $notification)
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

}