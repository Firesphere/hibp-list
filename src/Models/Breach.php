<?php


namespace Firesphere\HIBP\Models;


use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Class \Firesphere\HIBP\Models\Breach
 *
 * @property string $Title
 * @property string $Name
 * @property string $BreachDate
 * @property string $Domain
 * @property string $Description
 * @property boolean $IsSensitive
 * @method ManyManyList|DataType[] Types()
 * @method ManyManyList|Address[] Addresses()
 */
class Breach extends DataObject
{

    private static $table_name = 'Breach';

    private static $singular_name = 'Breach';

    private static $plural_name = 'Breaches';

    private static $db = [
        'Title'       => 'Varchar(255)',
        'Name'        => 'Varchar(255)',
        'BreachDate'  => 'Date',
        'Domain'      => 'Varchar(255)',
        'Description' => 'HTMLText',
        'IsSensitive' => 'Boolean'
    ];

    private static $many_many = [
        'Types' => DataType::class,
    ];

    private static $belongs_many_many = [
        'Addresses' => Address::class
    ];

    private static $summary_fields = [
        'Title',
        'Name',
        'BreachDate',
        'Domain',
        'Addresses.Count',
        'Types.Count',
        'IsSensitive.Nice'
    ];

    private static $field_labels = [
        'Addresses.Count'  => 'ISP Addresses in breach',
        'Types.Count'      => 'Datatypes breached',
        'IsSensitive.Nice' => 'Sensitive'
    ];

    public static function findOrCreate($breachData)
    {
        $existing = static::get()->filter(['Name' => $breachData['Name']])->first();

        if (!$existing) {
            $existing = static::create(
                $breachData
            );

            $existing->write();
        }
        foreach ($breachData['DataClasses'] as $data) {
            $type = DataType::get()->filter(['Title' => $data])->first();
            if (!$type) {
                $type = DataType::create(['Title' => $data]);
                $type->write();
            }
            $existing->Types()->add($type);
        }

        return $existing;
    }

    public function canEdit($member = null)
    {
        return false;
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