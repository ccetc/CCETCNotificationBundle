<?php

namespace CCETC\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CCETC\NotificationBundle\Entity\Notification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CCETC\NotificationBundle\Entity\NotificationRepository")
 */
class Notification
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime $datetimeCreated
     *
     * @ORM\Column(name="datetimeCreated", type="datetime")
     */
    private $datetimeCreated;

    /**
     * @var string $shortMessage
     *
     * @ORM\Column(name="shortMessage", type="string", length=255)
     */
    private $shortMessage;
    
    /**
     * @var text $longMessage
     *
     * @ORM\Column(name="longMessage", type="text", nullable="true")
     */
    private $longMessage;

    /**
     * @var string $class
     *
     * @ORM\Column(name="class", type="string", length=255, nullable="true")
     */
    private $class;    
    
   /** @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User", inversedBy="NotificationsCreated")
    *  @ORM\JoinColumn(name="userCreatedBy_id", referencedColumnName="id", onDelete="SET NULL") 
    */
    protected $userCreatedBy;

    /** @ORM\OneToMany(targetEntity="CCETC\NotificationBundle\Entity\NotificationInstance", mappedBy="notification", cascade={"persist", "remove"}) */
    protected $instances;

    public function __toString()
    {
        if(isset($this->shortMessage)) return $this->shortMessage;
        else return '';
    }
    
    public function getDateTimeCreatedNice()
    {
        $date = $this->getDatetimeCreated()->format('d/m/Y');
        
        if($date == date('d/m/Y')) {
            return 'Today - '.$this->getDatetimeCreated()->format('g:ia');
        } else if($date == date('d/m/Y', time() - (24 * 60 * 60))) {
            return 'Yesterday - '.$this->getDatetimeCreated()->format('g:ia');
        } else {
            return $this->getDatetimeCreated()->format('M j');
        }
    }
    
    public function getActive()
    {
        foreach($this->instances as $instance)
        {
            if($instance->getActive())
            {
                return true;
            }
        }
        
        return false;
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
     * Set datetimeCreated
     *
     * @param datetime $datetimeCreated
     */
    public function setDatetimeCreated($datetimeCreated)
    {
        $this->datetimeCreated = $datetimeCreated;
    }

    /**
     * Get datetimeCreated
     *
     * @return datetime 
     */
    public function getDatetimeCreated()
    {
        return $this->datetimeCreated;
    }

    /**
     * Set shortMessage
     *
     * @param string $shortMessage
     */
    public function setShortMessage($shortMessage)
    {
        $this->shortMessage = $shortMessage;
    }

    /**
     * Get shortMessage
     *
     * @return string 
     */
    public function getShortMessage()
    {
        return $this->shortMessage;
    }

    /**
     * Set longMessage
     *
     * @param text $longMessage
     */
    public function setLongMessage($longMessage)
    {
        $this->longMessage = $longMessage;
    }

    /**
     * Get longMessage
     *
     * @return text 
     */
    public function getLongMessage()
    {
        return $this->longMessage;
    }

    /**
     * Set class
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set userCreatedBy
     *
     * @param Application\Sonata\UserBundle\Entity\User $userCreatedBy
     */
    public function setUserCreatedBy(\Application\Sonata\UserBundle\Entity\User $userCreatedBy)
    {
        $this->userCreatedBy = $userCreatedBy;
    }

    /**
     * Get userCreatedBy
     *
     * @return Application\Sonata\UserBundle\Entity\User 
     */
    public function getUserCreatedBy()
    {
        return $this->userCreatedBy;
    }
    public function __construct()
    {
        $this->instances = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add instances
     *
     * @param CCETC\NotificationBundle\Entity\NotificationInstance $instances
     */
    public function addNotificationInstance(\CCETC\NotificationBundle\Entity\NotificationInstance $instances)
    {
        $this->instances[] = $instances;
    }

    /**
     * Get instances
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInstances()
    {
        return $this->instances;
    }

  
}