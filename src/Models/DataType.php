<?php


namespace Firesphere\HIBP\Models;


use SilverStripe\ORM\DataObject;

/**
 * Class \Firesphere\HIBP\Models\DataType
 *
 * @property string $Title
 * @method ManyManyList|Breach[] Breaches()
 */
class DataType extends DataObject
{

    private static $table_name = 'DataType';

    private static $db = [
        'Title' => 'Varchar(255)',
    ];

    private static $belongs_many_many = [
        'Breaches' => Breach::class,
    ];

    private static $summary_fields = [
        'Title',
        'Breaches.Count'
    ];

    private static $field_labels = [
        'Breaches.Count' => 'Total breaches with this type',
    ];

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