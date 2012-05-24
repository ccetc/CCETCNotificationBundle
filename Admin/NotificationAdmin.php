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
    protected $entityIconPath = 'bundles/ccetcnotification/images/world.png';
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('datetimeCreated')
                ->add('shortMessage')
                ->add('class')		
                ->add('sendEmail')		
                ->add('showOnDashboard')		
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
        $formMapper
                ->add('shortMessage')		
                ->add('longMessage')		
                ->add('class')		
                ->add('sendEmail')		
                ->add('showOnDashboard')		
        ;
               
    }
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('shortMessage')		
                ->add('longMessage')		
                ->add('class')		
                ->add('sendEmail')		
                ->add('showOnDashboard')		
                ->add('userCreatedBy')		
                ->add('datetimeCreated')		
                ->add('instances')		
                ->add('dashboardStateMethod')		
                ->add('dashboardStateMethodService')		
                ->add('dashboardStateMethodParameters')		
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
