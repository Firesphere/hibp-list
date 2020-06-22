<?php


namespace Firesphere\HIBP\Models;


use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\HIBP\Models\Employee
 *
 * @property string $Name
 * @property string $Email
 * @property string $Location
 * @property boolean $Active
 * @method DataList|Address[] Addresses()
 */
class Employee extends DataObject
{

    private static $table_name = 'Employee';

    private static $db = [
        'Name'     => 'Varchar(255)',
        'Email'    => 'Varchar(255)',
        'Location' => 'Varchar(255)',
        'Active'   => 'Boolean(true)'
    ];

    private static $has_many = [
        'Addresses' => Address::class
    ];

    private static $summary_fields = [
        'Name',
        'Email',
        'Location'
    ];

    private static $field_labels = [
        'Location' => 'Location within employer premises',
        'Active'   => 'Current employee'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->dataFieldByName('Addresses')->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);

        return $fields;
    }

    public function canEdit($member = null)
    {
        return true;
    }

    public function canCreate($member = null, $context = array())
    {
        return false;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canDelete($member = null)
    {
        return false;
    }

}