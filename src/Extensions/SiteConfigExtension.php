<?php


namespace Firesphere\HIBP\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class \Firesphere\HIBP\Extensions\SiteConfigExtension
 *
 * @property SiteConfig|SiteConfigExtension $owner
 * @property boolean $NotifyBreachedAccounts
 * @property string $NotificationFrom
 * @property string $NotificationReplyTo
 * @property string $NotificationSubject
 * @property string $NotificationContent
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = [
        'NotifyBreachedAccounts' => 'Boolean(false)',
        'NotificationFrom'       => 'Varchar(255)',
        'NotificationReplyTo'    => 'Varchar(255)',
        'NotificationSubject'    => 'Varchar(255)',
        'NotificationContent'    => 'HTMLText',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.HIBP', [
            CheckboxField::create('NotifyBreachedAccounts'),
            TextField::create('NotificationFrom'),
            TextField::create('NotificationReplyTo'),
            TextField::create('NotificationSubject'),
            $content = HTMLEditorField::create('NotificationContent')
        ]);

        $content->setDescription('Available variables:<br />
         - $Name for the employee name<br />
         - $Email for the employee email address<br />
         - $Breaches.RAW for listing out the breaches and their description (Note, the .RAW part is required)');

        return $fields;
    }
}
