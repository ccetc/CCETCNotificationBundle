<?php

namespace CCETC\NotificationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Datagrid\ORM\ProxyQuery;

class TaskAdminController extends Controller
{
    public function myTasksAction()
    {
        $deliveryHelper = $this->container->get('ccetc.notification.delivery');
        $user = $this->container->get('security.context')->getToken()->getUser();

        $instances = $deliveryHelper->findInstancesByUser($user, null, null, 'task');

        return $this->render('CCETCNotificationBundle:Notification:my_tasks.html.twig', array(
                    'instances' => $instances,
                    'action' => 'myTasks'
                ));
    }


}