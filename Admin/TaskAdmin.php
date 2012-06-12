<?php

namespace CCETC\NotificationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TaskAdmin extends Admin
{

    protected $entityIcon = 'icon-signal';

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->add('myTasks', 'my-tasks');
    }

    public $userAdmin;
    public $entityHeading = "Tasks";

    public function initialize()
    {
        parent::initialize();
        $this->userAdmin = $this->getConfigurationPool()->getContainer()->get('sonata.user.admin.user');
    }

    public function getActionMenuItems($action, $object = null)
    {
        return array();
    }

    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
                ->add('shortMessage', null, array('label' => 'Notification', 'template' => 'CCETCNotificationBundle:Notification:_list_notification.html.twig'))
                ->add('instances', null, array('label' => $this->trans('users', array(), 'SonataUserBundle') . ' sent to', 'template' => 'CCETCNotificationBundle:Notification:_list_users.html.twig'))
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    ),
                    'label' => 'Actions'
                ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('type')
        ;

        $this->filterDefaults = array(
            'type' => 'task'
        );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('shortMessage')
                ->add('longMessage', null, array('label' => 'Long Message (optional)', 'attr' => array('class' => 'tinymce'), 'required' => false))
        ;
    }

    protected function configureShowField(ShowMapper $showMapper)
    {

        $showMapper
                ->add('shortMessage')
                ->add('longMessage', null, array('label' => 'Long Message (optional)', 'attr' => array('class' => 'tinymce'), 'required' => false))
        ;
    }

    public function prePersist($object)
    {
        $object->setDatetimeCreated(new \DateTime());

        if( $this->configurationPool->getContainer()->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->configurationPool->getContainer()->get('security.context')->getToken()->getUser();
            $object->setUserCreatedBy($user);
        }        
                
        parent::prePersist($object);
    } 

}
