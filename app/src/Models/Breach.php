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
 * @property string $Data
 * @method ManyManyList|Address[] Addresses()
 */
class Breach extends DataObject
{

    private static $table_name = 'Breach';

    private static $db = [
        'Title'       => 'Varchar(255)',
        'Name'        => 'Varchar(255)',
        'BreachDate'  => 'Date',
        'Domain'      => 'Varchar(255)',
        'Description' => 'HTMLText',
        'Data'        => 'Text',
    ];

    private static $belongs_many_many = [
        'Addresses' => Address::class
    ];

    private static $summary_fields = [
        'Title',
        'Name',
        'BreachDate',
        'Domain',
    ];

    public static function findOrCreate($breachData)
    {
        $existing = static::get()->filter(['Name' => $breachData['Name']])->first();

        if (!$existing) {
            $existing = static::create(
                $breachData
            );

            $existing->Data = implode(', ', $breachData['DataClasses']);
            $existing->write();
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

}