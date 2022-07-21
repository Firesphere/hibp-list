<?php


namespace Firesphere\HIBP\Extensions;

use Firesphere\HIBP\Tasks\ImportListTask;
use SilverStripe\Control\Director;
use SilverStripe\Control\NullHTTPRequest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextareaField;
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
 * @property string $LastImport
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = [
        'NotifyBreachedAccounts' => 'Boolean(false)',
        'NotificationFrom'       => 'Varchar(255)',
        'NotificationReplyTo'    => 'Varchar(255)',
        'NotificationSubject'    => 'Varchar(255)',
        'NotificationContent'    => 'HTMLText',
        'LastImport'             => 'DBDatetime'
    ];

    protected $DataURL = null;
    protected $DataSet = null;

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('LastImport');
        $fields->addFieldsToTab('Root.HIBPImport', [
            DatetimeField::create('LastImport', 'Last import date')->setReadonly(true)->setDisabled(true),
            TextField::create('DataURL', 'Paste the url to download the JSON from'),
            TextareaField::create('DataSet', 'Paste the JSON from HIBP to import'),
        ]);
        $fields->addFieldsToTab('Root.HIBPNotifications', [
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

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->DataURL || $this->DataSet) {
            $targetFile = sprintf('%s/datafiles/hibp-%s.json', Director::baseFolder(), date('Y-m-d'));
            $content = $this->DataSet;
            if ($this->DataURL) {
                $content = file_get_contents($this->DataURL);
            }
            file_put_contents($targetFile, $content);
            (new ImportListTask())->doImport(new NullHTTPRequest());
            unlink($targetFile);
        }
    }
}
