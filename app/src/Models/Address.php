<?php


namespace Firesphere\HIBP\Models;


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
        'Employee.Name'
    ];

    public static function findOrCreate($alias)
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


            $employee = Employee::get()->filter(['Email' => $email . '@catalyst.net.nz'])->first();

            if (!$employee) {
                $employee = Employee::create(['Email' => $email . '@catalyst.net.nz']);
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

    public function canView($member = null)
    {
        return true;
    }

}