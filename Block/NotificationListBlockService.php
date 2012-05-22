<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCETC\NotificationBundle\Block;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

class NotificationListBlockService extends BaseBlockService
{
    protected $container;
    
    /**
     * @param $name
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Sonata\AdminBundle\Admin\Pool $pool
     */
    public function __construct($name, EngineInterface $templating, $container)
    {
        parent::__construct($name, $templating);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        $entityManager = $this->container->get('doctrine')->getEntityManager();
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        $user = $this->container->get('security.context')->getToken()->getUser();

        $deliveryHelper = $this->container->get('ccetc.notification.delivery');
        
        $notifications = $deliveryHelper->findActiveByUser($user);
        
        foreach($notifications as $instance)
        {
            $instance->setHasBeenShownOnDashboard(true);
            $entityManager->persist($instance);
        }
        
        $entityManager->flush();
        
        return $this->renderResponse('CCETCNotificationBundle:Block:block_notification_list.html.twig', array(
            'block'     => $block,
            'settings'  => $settings,
            'notifications' => $notifications
        ), $response);
    }

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Notification List';
    }

    /**
     * {@inheritdoc}
     */
    function getDefaultSettings()
    {
        return array();
    }
    
    public function getHelper()
    {
        return $this->helper;
    }
}