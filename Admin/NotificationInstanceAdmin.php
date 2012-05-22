<?php

namespace CCETC\NotificationBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;


class NotificationInstanceAdmin extends Admin
{    
    protected $entityIconPath = 'bundles/ccetcnotification/images/world.png';
    
    protected $entityLabelPlural = "Instances";
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('user')
                ->add('notification')		
                ->add('hasBeenEmailed')		
                ->add('hasBeenShownOnDashboard')
//                ->add('isActive?', 'string', array('template' => 'CCETCNotificationBundle:NotificationInstance:_listIsActive.html.twig'))
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
                ->add('user')
                ->add('notification')		
                ->add('hasBeenEmailed')		
                ->add('hasBeenShownOnDashboard')		
        ;
    }
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('user')
                ->add('notification')		
                ->add('hasBeenEmailed')		
                ->add('hasBeenShownOnDashboard')		
        ;
               
    }
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('user')
                ->add('notification')		
                ->add('hasBeenEmailed')		
                ->add('hasBeenShownOnDashboard')		
		;
    }
}
