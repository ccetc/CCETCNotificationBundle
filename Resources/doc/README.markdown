# CCETCNotifcationBundle - README

Provides tools to generating, storing, and delivery application notificatons via e-mail and a "dashboard".

## Note
This bundle is developed alongside our own forks of SonataAdminBundle, SonataUserBundle, FOSUserBundle, and SonataDoctrineORMAdminBundle.  It has not been testing with the original versions of those bundles.

## Features
- Admin Interface for managing Notifications
- SonataAdmin Dashboard Block
- email notifications

## Installation
To install as a Symfony vendor, add the following lines to the file ``deps``:

        [CCETCNotificationBundle]
                git=https://github.com/CCETC/NotificationBundle.git
                target=/bundles/CCETC/NotificationBundle
                

If you are using git, you can instead add them as submodules:

        git submodule add git@github.com:CCETC/NotificationBundle.git vendor/bundles/CCETC/NotificationBundle

Add to autoload and AppKernal.

Install assets:

        bin/vendors install

Add the user side of the 2 notification relations to your user class:

        <one-to-many field="notificationsCreated" target-entity="CCETC\NotificationBundle\Entity\Notification" mapped-by="userCreatedBy" />
        
        <one-to-many field="notifications" target-entity="CCETC\NotificationBundle\Entity\NotificationInstance" mapped-by="user" />
        
Add the notificationEmailFrequency field to your User Entity.  Add it to the entity, your admin class, and the profile form/templates.  If should have hourly/daily/instantly/never options and is required.

Add block to Sonata Dashboard:

	sonata_admin:
        dashboard:
			blocks:
				- { position: left, type: ccetc.notification.block.notificationlist }
				- { position: right,  type: sonata.admin.block.admin_list }

Create cronjobs to run the cleanup and email commands:

	php app/console ccetc:notification:sendemails instantly
	php app/console ccetc:notification:sendemails hourly
	php app/console ccetc:notification:sendemails daily
	php app/console ccetc:notification:cleanup


## Use
### Creating Notifications
        $this->configurationPool->getContainer()->get('ccetc.notification.builder')->createNotification(array(
            'values' => array(
                'shortMessage' => 'Error Report Submitted',
                'longMessage' => 'A an Error Report has been submitted: <a href="'.$this->generateObjectUrl('show', $object).'">'.$object->__toString().'</a>',
                'showOnDashboard' => true,
                'sendEmail' => true,
                'class' => 'new-farmer',
            ),
            'users' => $this->configurationPool->getContainer()->get('security.context')->getToken()->getUser(),
        ));

**Options**
- values
	- shortMessage - displayed in large text on dashboard, and in email subject
	- longMessage - displayed in small text on dashboard, and in email body (optional)
	- showOnDashboard - bool (default: true)
	- sendEmail - bool (default: true)
	- class - string added as a class name on the dashboard (optional)
		- warning, reminder, information, newspaper, clock-red, clock-blue will include different icons
- users
	- Array of users the notification is for (also takes a single user)
	
### Custom State Methods
You can specify a method to be used to determine the active/inactive state of a particular dashboard notification.

When creating a notification specify the following values:

- dashboardStateMethod - method name
- dashboardStateMethodService - service containing `` dashboardStateMethod``
- dashboardStateMethodParamater - parameters to send to `` dashboardStateMethod`` (will be eval'd as an array)
	
**NOTE**
 - `` dashboardStateMethod`` must return a bool
	
### Custom Dashboard Icon
Add a style to match your custom notifcation classes:

	.notification-list-conatiner .notification-container .your-class {
		background-image: url(your-icon);
	}



## Documentation
All ISSUES, IDEAS, and FEATURES are documented on the [trello board](https://trello.com/board/notificationbundle/4fbb871762bd30482a494fe0).