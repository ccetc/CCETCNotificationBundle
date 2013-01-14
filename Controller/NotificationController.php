<?php

namespace CCETC\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CCETC\NotificationBundle\Entity\Notification;

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
        
        $notifyWhoChoices = $deliveryHelper->getNotifyWhoChoices();
        $notifyWhoText = $deliveryHelper->getNotifyWhoText();
        $showNotifyWhoText = count($notifyWhoChoices) == 1;
        
        $utilityHelper->batchSetActive($instances, false);
        $utilityHelper->batchSetNeedsToBeEmailed($instances, false);
        
        return $this->render('CCETCNotificationBundle:Feed:_feed.html.twig', array(
            'instances' => $instances,
            'feedForm' => $feedForm->createView(),
            'notifyWhoChoices' => $deliveryHelper->getNotifyWhoChoices(),
            'notifyWhoText' => $notifyWhoText,
            'showNotifyWhoText' => $showNotifyWhoText
        ));
    }
    
    public function processFeedFormAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        $feedForm = $this->getFeedForm();
        $deliveryHelper = $this->container->get('ccetc.notification.delivery');
        
        if(count($deliveryHelper->getNotifyWhoChoices()) == 1) {
            $feedForm->setData(array('notifyWho' => $deliveryHelper->getNotifyWhoText()));
        }
        
        $userRepository = $this->container->get('doctrine')->getRepository('ApplicationSonataUserBundle:User');
        $myCCEHelper = $this->container->get('my_cce.app.helper.myccehelper');        

        $feedForm->bindRequest($request); 
        
        if($request->getMethod() == 'POST')
        {            
            if($feedForm->isValid())
            {
                $formData = $feedForm->getData();
                
                $message = Notification::autoLinkText($formData['message']);
                
                switch(true)
                {
                    case strstr($formData['notifyWho'], 'allUsers'):
                        $users = $userRepository->findAll();
                        break;
                    case strstr($formData['notifyWho'], 'supervisees'):
                        $users = $user->getSupervisees();
                        break;
                    case strstr($formData['notifyWho'], 'childSupervisees'):
                        $users = $user->getChildSupervisees();
                        break;
                    case strstr($formData['notifyWho'], 'county'):
                        $users = $this->getCountyByNameIdString($formData['notifyWho'])->getUsers();
                        break;
                    case strstr($formData['notifyWho'], 'region'):
                        $users = $this->getRegionByNameIdString($formData['notifyWho'])->getUsers();
                        break;
                    default:
                        $users = null;
                }
                $userIds = $myCCEHelper->getUserIds($users);
                $notification = $this->container->get('ccetc.notification.builder')->createNotification(array(
                    'values' => array(
                        'shortMessage' => $message,
                        'userCreatedBy' => $user,
                        'datetimeCreated' => new \DateTime()
                    ),
                    'user_ids' => $userIds
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
    
    public function getCountyByNameIdString($countyString) {
        $countyRepository = $this->container->get('doctrine')->getRepository('MyCCEAppBundle:County');
        $countyStringParts = explode('-', $countyString);
        $countyId = $countyStringParts[1];

        return $countyRepository->findOneById($countyId);
    }
    
    public function getRegionByNameIdString($regionString) {
        $regionRepository = $this->container->get('doctrine')->getRepository('MyCCEAppBundle:Region');
        $regionStringParts = explode('-', $regionString);
        $regionId = $regionStringParts[1];

        return $regionRepository->findOneById($regionId);
    }
    
    public function getFeedForm() {
        $deliveryHelper = $this->container->get('ccetc.notification.delivery');        
        $notifyWhoChoices = $deliveryHelper->getNotifyWhoChoices();
        
        $builder = $this->createFormBuilder();
        
        if(count($notifyWhoChoices) > 1) {
            $builder->add('notifyWho', 'choice', array('choices' => $notifyWhoChoices, 'label' => ''));
        } else {
            $builder->add('notifyWho', 'hidden');
        }
        
        $builder->add('message', 'textarea', array('label' => ''));
        return $builder->getForm();
    }
   
}
