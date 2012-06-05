<?php

namespace CCETC\NotificationBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Datagrid\ORM\ProxyQuery;

class NotificationAdminController extends Controller
{

    public function myNotificationsAction()
    {
        $deliveryHelper = $this->container->get('ccetc.notification.delivery');
        $user = $this->container->get('security.context')->getToken()->getUser();

        $instances = $deliveryHelper->findInstancesByUser($user);

        return $this->render('CCETCNotificationBundle:Notification:my_notifications.html.twig', array(
                    'instances' => $instances,
                    'action' => 'myNotifications'
                ));
    }

    public function createAction()
    {
        if(false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');

        $object = $this->admin->getNewInstance();

        $object = $this->processUrlFormValues($object);

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();
        $form->setData($object);

        $this->processFormFieldHooks($object);

        if($this->get('request')->getMethod() == 'POST') {
            $form->bindRequest($this->get('request'));

            if($form->isValid()) {
                if(isset($this->admin->fieldGroupsToCheckForDuplicates)) {
                    $itemMayBeInDB = $this->checkForDuplicates($object);
                }

                if(isset($itemMayBeInDB) && $itemMayBeInDB) {
                    $this->getRequest()->getSession()->setFlash('sonata_flash_error', 'This ' . $this->admin->getEntityLabel() . ' may already by in the database.  Please check the list before creating it.
                    If you are sure that this ' . $this->admin->getEntityLabel() . ' is not already in the database, click "Create" again.');
                } else {

                    $this->admin->create($object);

                    foreach($form['users']->getData() as $userId) {
                        $userRepository = $this->getDoctrine()->getRepository("ApplicationSonataUserBundle:User");
                        $user = $userRepository->findOneById($userId);

                        $instance = new \CCETC\NotificationBundle\Entity\NotificationInstance();
                        $instance->setUser($user);
                        $instance->setNotification($object);
                        $notificationInstanceAdmin->create($instance);
                    }

                    if($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                                    'result' => 'ok',
                                    'objectId' => $this->admin->getNormalizedIdentifier($object)
                                ));
                    }

                    $this->get('session')->setFlash('sonata_flash_success', 'flash_create_success');
                    // redirect to edit mode
                    return $this->redirectTo($object);
                }
            } else {
                $this->get('session')->setFlash('sonata_flash_error', 'flash_create_error');
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getEditTemplate(), array(
                    'action' => 'create',
                    'form' => $view,
                    'object' => $object,
                ));
    }

    public function editAction($id = null)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if(!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if(false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();
        $form->setData($object);

        $users = array();

        foreach($object->getInstances() as $instance) {
            $users[] = $instance->getUser()->getId();
        }

        $form['users']->setData($users);

        $this->processFormFieldHooks($object);

        if($this->get('request')->getMethod() == 'POST') {
            $form->bindRequest($this->get('request'));

            if($form->isValid()) {
                $this->admin->update($object);

                $notificationInstanceAdmin = $this->container->get('ccetc.notification.admin.notificationinstance');
                $userRepository = $this->getDoctrine()->getRepository("ApplicationSonataUserBundle:User");
                $notificationInstanceRepository = $this->getDoctrine()->getRepository("CCETCNotificationBundle:NotificationInstance");

                // check for existing instances, and remove ones not included in the request
                $existingInstances = $notificationInstanceRepository->findBy(array('notification' => $object->getId()));
                $existingUsers = array();
                foreach($existingInstances as $instance) {
                    $existingUsers[] = $instance->getUser()->getId();
                                        
                    if(!in_array($instance->getUser()->getId(), $form['users']->getData())) {
                        $object->getInstances()->removeElement($instance);
                        $notificationInstanceAdmin->delete($instance);
                    }
                }
                
                // add any new instances
                foreach($form['users']->getData() as $userId) {
                    if(!in_array($userId, $existingUsers)) {
                        $user = $userRepository->findOneById($userId);
                        $instance = new \CCETC\NotificationBundle\Entity\NotificationInstance();
                        $instance->setUser($user);
                        $instance->setNotification($object);
                        $notificationInstanceAdmin->create($instance);
                    }
                    
                }

                $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');

                if($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                                'result' => 'ok',
                                'objectId' => $this->admin->getNormalizedIdentifier($object)
                            ));
                }

                // redirect to edit mode
                return $this->redirectTo($object);
            }

            $this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getEditTemplate(), array(
                    'action' => 'edit',
                    'form' => $view,
                    'object' => $object,
                ));
    }

}