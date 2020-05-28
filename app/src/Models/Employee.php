<?php


namespace Firesphere\HIBP\Models;


use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\HIBP\Models\Employee
 *
 * @property string $Name
 * @property string $Email
 * @property string $Location
 * @method DataList|Address[] Addresses()
 */
class Employee extends DataObject
{

    private static $table_name = 'Employee';

    private static $db = [
        'Name'     => 'Varchar(255)',
        'Email'    => 'Varchar(255)',
        'Location' => 'Varchar(255)',
    ];

    private static $has_many = [
        'Addresses' => Address::class
    ];

    private static $summary_fields = [
        'Name',
        'Email',
        'Location'
    ];

    public function canView($member = null)
    {
        return true;
    }

}