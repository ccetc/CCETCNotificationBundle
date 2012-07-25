<?php

namespace CCETC\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class TaskController extends Controller
{    
    public function taskListAction()
    {
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
       
        $deliveryHelper = $this->container->get('ccetc.notification.delivery');
        $utilityHelper = $this->container->get('ccetc.notification.utility');
        $notificationAdmin = $this->container->get('ccetc.notification.admin.notification');
        
        $tasks = $deliveryHelper->findInstancesByUser($user, true, null, 'task');
        $activeTaskCount = $deliveryHelper->getActiveTaskCountByUser($user);
        
        return $this->render('CCETCNotificationBundle:Task:_task_list.html.twig', array(
            'tasks' => $tasks,
            'activeTaskCount' => $activeTaskCount[0][1],
            'notificationAdmin' => $notificationAdmin,
        ));        
    }
   
}
