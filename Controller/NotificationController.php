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
                
        $instances = $deliveryHelper->findInstancesByUser($user, null, null, 'notification');
        
        $feedForm = $this->getFeedForm();
        
        $notifyWhoChoices = $this->getNotifyWhoChoices();
        $notifyWhoText = $this->getNotifyWhoText();
        $showNotifyWhoText = count($notifyWhoChoices) == 1;
        
        $utilityHelper->batchSetInactive($instances);
        
        return $this->render('CCETCNotificationBundle:Feed:_feed.html.twig', array(
            'instances' => $instances,
            'feedForm' => $feedForm->createView(),
            'notifyWhoChoices' => $this->getNotifyWhoChoices(),
            'notifyWhoText' => $notifyWhoText,
            'showNotifyWhoText' => $showNotifyWhoText
        ));
    }
    
    public function processFeedFormAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        $feedForm = $this->getFeedForm();
        
        if(count($this->getNotifyWhoChoices()) == 1) {
            $feedForm->setData(array('notifyWho' => $this->getNotifyWhoText()));
        }
        
        $userRepository = $this->container->get('doctrine')->getRepository('ApplicationSonataUserBundle:User');
        
        $feedForm->bindRequest($request); 
        
        if($request->getMethod() == 'POST')
        {            
            if($feedForm->isValid())
            {
                $formData = $feedForm->getData();
                
                switch($formData['notifyWho'])
                {
                    case 'allStaff':
                        $users = $userRepository->findAll();
                        break;
                    case 'supervisees':
                        $users = $user->getSupervisees();
                        break;
                    case 'childSupervisees':  
                        $users = $user->getChildSupervisees();
                        break;
                    default:
                        $users = null;
                }
                
                $notification = $this->container->get('ccetc.notification.builder')->createNotification(array(
                    'values' => array(
                        'shortMessage' => $formData['message'],
                        'userCreatedBy' => $user,
                        'datetimeCreated' => new \DateTime()
                    ),
                    'users' => $users
                ));
                
                if(isset($notification)) {
                    $this->getRequest()->getSession()->setFlash('sonata_flash_success', 'Your staff have been notified.');
                } else {
                    $error = true;
                }
            } else {
                $error = true;
            }
            
            if(isset($error) && $error) {
                $this->getRequest()->getSession()->setFlash('sonata_flash_error', 'Your staff could not be notified');
            }
        }        
        return $this->redirect($this->generateUrl('home'));
    }
    
    public function getFeedForm() {
        $notifyWhoChoices = $this->getNotifyWhoChoices();
        
        $builder = $this->createFormBuilder();
        
        if(count($notifyWhoChoices) > 1) {
            $builder->add('notifyWho', 'choice', array('choices' => $notifyWhoChoices, 'label' => ''));
        } else {
            $builder->add('notifyWho', 'hidden');
        }
        
        $builder->add('message', 'textarea', array('label' => ''));
        return $builder->getForm();
    }
    
    public function getNotifyWhoChoices()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $notifyWhoChoices = array();
        
        if( $user->isSuperAdmin() || $this->container->get('security.context')->isGranted('ROLE_SONATA_USER_ADMIN_USER_ADMIN') ) {
            $notifyWhoChoices['allStaff'] = 'All Staff';
        }

        if($user->hasSupervisees()) {
            $notifyWhoChoices['supervisees'] = 'Staff I supervise';
        }
         
        if($user->hasChildSupervisees()) {
            // if user has supervisees with supervisees
            $notifyWhoChoices['childSupervisees'] = 'Staff I supervise and staff they supervise';
        }
        
        return $notifyWhoChoices;
    }
    
    public function getNotifyWhoText()
    {
        $notifyWhoChoices = $this->getNotifyWhoChoices();
        if(count($notifyWhoChoices) > 0) return $notifyWhoChoices[key($notifyWhoChoices)];
        return "";
    }
        

}
