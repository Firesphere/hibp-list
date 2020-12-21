<?php


namespace Firesphere\HIBP\Models;

use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\HIBP\Models\Employee
 *
 * @property string $Name
 * @property string $Surname
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
        'Surname'  => 'Varchar(255)',
        'Email'    => 'Varchar(255)',
        'Location' => 'Varchar(255)',
        'Active'   => 'Boolean(true)'
    ];

    private static $has_many = [
        'Addresses' => Address::class
    ];

    private static $summary_fields = [
        'Name',
        'Surname',
        'Email',
        'Location',
        'Addresses.Breaches.Count',
        'Active.Nice'
    ];

    private static $field_labels = [
        'Addresses.Breaches.Count' => 'Times breached',
        'Active.Nice'              => 'Current employee'
    ];

    public static function findOrCreate($data)
    {
        /** @var static|Employee $existing */
        $existing = self::get()->filter(['Email' => $data['Email']])->first();
        if (!$existing) {
            $empl = self::create($data);
            $empl->Active = $data['Active'] === 'active';
            $empl->write();
        } else {
            $data['Active'] = $data['Active'] === 'active';
            $existing->update($data)->write();
        }
    }

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
