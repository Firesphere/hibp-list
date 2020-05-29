<?php


namespace Firesphere\HIBP\Models;


use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Class \Firesphere\HIBP\Models\Address
 *
 * @property string $Name
 * @property string $Extended
 * @property int $EmployeeID
 * @method Employee Employee()
 * @method ManyManyList|Breach[] Breaches()
 * @method ManyManyList|Paste[] Pastes()
 */
class Address extends DataObject
{
    private static $table_name = 'Address';

    private static $db = [
        'Name'     => 'Varchar(255)',
        'Extended' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Employee' => Employee::class
    ];

    private static $many_many = [
        'Breaches' => Breach::class,
        'Pastes'   => Paste::class
    ];

    private static $summary_fields = [
        'Name',
        'Extended',
        'Employee.Email',
        'Employee.Name',
        'Breaches.Count',
        'Pastes.Count'
    ];

    private static $field_labels = [
        'Extended'       => 'Mail extension (x+y@example.com)',
        'Employee.Email' => 'Employee email',
        'Employee.Name'  => 'Employee name',
        'Breaches.Count' => 'Amount of breaches for this email',
        'Pastes.Count'   => 'Amount of pastes for this email'
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['EmployeeID']);
        $fields->addFieldToTab('Root.Main',
            ReadonlyField::create('EmptyField', 'Employee',
                $this->Employee()->Name . ' (' . $this->Employee()->Email . ')')
        );

        return $fields;
    }

    public static function findOrCreate($alias, $domain)
    {
        $email = $alias;
        $extended = null;
        if (strpos($alias, '+') !== false) {
            list($email, $extended) = explode('+', $alias);
        }
        $existing = static::get()->filter(['Name' => $email, 'Extended' => $extended])->first();

        if (!$existing) {
            $existing = static::create([
                'Name'     => $alias,
                'Extended' => $extended
            ]);

            $existing->write();


            $employee = Employee::get()->filter(['Email' => $email . '@' . $domain])->first();

            if (!$employee) {
                $employee = Employee::create(['Email' => $email . '@' . $domain]);
                $id = $employee->write();
            } else {
                $id = $employee->ID;
            }
            $existing->EmployeeID = $id;

            $existing->write();
        }

        return $existing;
    }

    public function canCreate($member = null, $context = array())
    {
        return false;
    }

    public function canEdit($member = null)
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