<?php

namespace CCETC\NotificationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;


class NotificationAdmin extends Admin
{    
    protected $entityIcon = 'icon-signal';
    
    public $userAdmin;
    
    public function initialize()
    {
        parent::initialize();
        $this->userAdmin = $this->getConfigurationPool()->getContainer()->get('sonata.user.admin.user');
    }
    
    protected function configureListFields(ListMapper $listMapper)
    {
        
        $listMapper
                ->add('shortMessage', null, array('label' => 'Notification','template' => 'CCETCNotificationBundle:Notification:_list_notification.html.twig'))
                ->add('instances', null, array('label' => $this->trans('users', array(), 'SonataUserBundle').' sent to','template' => 'CCETCNotificationBundle:Notification:_list_users.html.twig'))                
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
        
    }
    protected function configureFormFields(FormMapper $formMapper)
    {
        $userRepository = $this->configurationPool->getContainer()->get('doctrine')->getRepository("ApplicationSonataUserBundle:User");
        $users = $userRepository->findAll();
        $userChoices = array();
        
        foreach($users as $user)
        {
            $userChoices[$user->getId()] = $user->__toString();
        }

        
        $formMapper
                ->add('shortMessage')		
                ->add('longMessage', null, array('label' => 'Long Message (optional)', 'attr' => array('class' => 'tinymce'), 'required' => false))	
                ->add('users', 'choice', array(
                    'label' => $this->trans('users', array(), 'SonataUserBundle').' to send to',
                    'property_path' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'choices' => $userChoices
                ))
                ->add('datetimeCreated', null, array('label' => 'Date Created', 'required' => false))
                ->add('dateTaskDue', null, array('label' => 'Date Task Due', 'required' => false))
                ->add('class')
                ->add('type', 'choice', array('choices' => array('notification' => 'notification', 'task' => 'task')))
                ->add('userCreatedBy', null, array('required' => false))
        ;
    }
    
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('shortMessage')		
                ->add('longMessage', null, array('template' => 'CCETCNotificationBundle:Notification:_show_long_message.html.twig'))
                ->add('datetimeCreated')		
                ->add('instances', null, array('label' => $this->trans('users', array(), 'SonataUserBundle').' sent to','template' => 'CCETCNotificationBundle:Notification:_show_users.html.twig'))                
		;
    }
    public function prePersist($object)
    {
        if(!$object->getDatetimeCreated()) {
            $object->setDatetimeCreated(new \DateTime());
        }

        if( !$object->getUserCreatedBy() && $this->configurationPool->getContainer()->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->configurationPool->getContainer()->get('security.context')->getToken()->getUser();
            $object->setUserCreatedBy($user);
        }        
                
        parent::prePersist($object);
    }    
}
