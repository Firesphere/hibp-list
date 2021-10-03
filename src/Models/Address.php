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

    private static $summary_fields = [
        'Employee.Name',
        'Employee.Surname',
        'Extended',
        'Employee.Email',
        'Breaches.Count',
        'Pastes.Count'
    ];

    private static $has_one = [
        'Employee' => Employee::class
    ];

    private static $many_many = [
        'Breaches' => Breach::class,
        'Pastes'   => Paste::class
    ];

    private static $field_labels = [
        'Employee.Surname' => 'Surname',
        'Extended'         => 'Mail extension (x+y@example.com)',
        'Employee.Email'   => 'Email',
        'Employee.Name'    => 'First name',
        'Breaches.Count'   => 'Amount of breaches',
        'Pastes.Count'     => 'Amount of pastes'
    ];

    private static $indexes = [
        'Name'     => true,
        'Extended' => true
    ];

    private static $default_sort = 'Name ASC';

    public static function findOrCreate($alias, $domain)
    {
        $email = $alias;
        $extended = null;
        if (strpos($alias, '+') !== false) {
            list($email, $extended) = explode('+', $alias);
        }
        $address = static::get()->filter(['Name' => $email, 'Extended' => $extended])->first();

        if (!$address) {
            $address = static::create([
                'Name'     => $email,
                'Extended' => $extended
            ]);

            $employee = Employee::get()->filter(['Email' => $email . '@' . $domain])->first();

            if (!$employee) {
                $employee = Employee::create([
                    'Email' => $email . '@' . $domain,
                    'Name'  => ucfirst($email)
                ]);
                $employee->write();
            }
            $address->EmployeeID = $employee->ID;

            $address->write();
        }

        return $address;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['EmployeeID']);
        $fields->addFieldToTab(
            'Root.Main',
            ReadonlyField::create(
                'EmptyField',
                'Employee',
                $this->Employee()->Name . ' (' . $this->Employee()->Email . ')'
            )
        );

        return $fields;
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
