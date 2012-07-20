<?php

namespace CCETC\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NotificationController extends Controller
{    
    public function feedAction()
    {
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $deliveryHelper = $this->container->get('ccetc.notification.delivery');
        $utilityHelper = $this->container->get('ccetc.notification.utility');
        $notificationAdmin = $this->container->get('ccetc.notification.admin.notification');
                
        $newNotificationInstances = $deliveryHelper->findInstancesByUser($user, true, null, 'notification');
        $oldNotificationInstances = $deliveryHelper->findInstancesByUser($user, false, null, 'notification');
        
        if(count($oldNotificationInstances) > 0) {
            $hasOldNotifications = true;
        } else {
            $hasOldNotifications = false;
        }
        
    //    $utilityHelper->batchSetInactive($newNotificationInstances);
        return $this->render('CCETCNotificationBundle:Feed:_feed.html.twig', array(
            'instances' => $newNotificationInstances,
        ));
    }
   
}
