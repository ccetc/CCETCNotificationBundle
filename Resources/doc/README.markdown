# CCETCNotifcationBundle - README

Provides tools to generating, storing, and delivery application notificatons via e-mail and a "dashboard".

## Note
* **This bundle is a work in progress and has not yet been used in production. **
* This bundle is developed alongside our own forks of SonataAdminBundle, SonataUserBundle, FOSUserBundle, and SonataDoctrineORMAdminBundle.  It has not been testing with the original versions of those bundles.

## Features
- two types of messages: notifications & tasks
- tasks are actionable items
- notifications are just notifications
- SonataAdmin Dashboard Block for notifications
- SonataAdmin Dashboard Block for tasks
- email digest for all types of notifications
- configurable email reminders for tasks
- "My Notifications" and "My Tasks" pages


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

	every minute: php app/console ccetc:notification:sendemails instantly
	every hour: php app/console ccetc:notification:sendemails hourly
	every day: php app/console ccetc:notification:sendemails daily
	every day: php app/console ccetc:notification:cleanup
	every day: php app/console ccetc:notification:updatereminders


## Use
Notifications are primarily shown on the dashboard.  Once they are shown they are considered "inactive", but can still be seen on the "My Notifications" page.  The provided commands for email notifications send digest emails of inactive notifications according to the frequency a user has selected to receive them.  This ensures they only get e-mailed about notifications they haven't seen.

### Creating Notifications
        $this->configurationPool->getContainer()->get('ccetc.notification.builder')->createNotification(array(
            'values' => array(
                'shortMessage' => 'Error Report Submitted',
                'longMessage' => 'A an Error Report has been submitted: <a href="'.$this->generateObjectUrl('show', $object).'">'.$object->__toString().'</a>',
                'class' => 'icon-bell',
            ),
            'users' => $this->configurationPool->getContainer()->get('security.context')->getToken()->getUser(),
        ));

**Options**
- values
	- shortMessage - displayed in large text on dashboard, and in email subject
	- longMessage - displayed in small text on dashboard, and in email body (optional)
	- class - fontello font icon class given (optional)
		- set up to use 'icon-globe green', 'icon-attention red', 'icon-bell orange', 'icon-info-circle lightBlue'
	- type - 'task' or 'notification' (default: notification)
	- dateTaskDue - optional
        - taskReminderDays - optional, comma separated list of integers, tasks will be re-emailed in the digest x,y,z days before the task is due
- users
	- Array of users the notification is for (also takes a single user)
	
### Custom Active States
To have more control over the active/inactive state of an instance, you can attach an object to an instance.  This should be done entirely in your external entity/bundle.  If you do this, you should set ``$instance->hasAssociatedObject`` to ``true``.  If this value is true, $instance->active will not be set to false when shown on the dashboard, and the bundle will assume that your bundle will handle the inactivation of this notification.

## Documentation
All ISSUES, IDEAS, and FEATURES are documented on the [trello board](https://trello.com/board/notificationbundle/4fbb871762bd30482a494fe0).