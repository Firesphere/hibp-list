<?php


namespace Firesphere\HIBP\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ManyManyList;

/**
 * Class \Firesphere\HIBP\Models\Paste
 *
 * @property string $Title
 * @property string $Source
 * @property string $PasteId
 * @property string $Date
 * @method ManyManyList|Address[] Addresses()
 */
class Paste extends DataObject
{
    private static $table_name = 'Paste';

    private static $db = [
        'Title'   => 'Varchar(255)',
        'Source'  => 'Varchar(255)',
        'PasteId' => 'Varchar(255)',
        'Date'    => 'Date',
    ];

    private static $belongs_many_many = [
        'Addresses' => Address::class
    ];

    private static $summary_fields = [
        'Title',
        'Date',
        'PasteId',
        'Addresses.Count'
    ];

    private static $field_labels = [
        'Addresses.Count' => 'ISP Addresses found in breach'
    ];

    public $new = false;

    public static function findOrCreate($breachData)
    {
        $existing = static::get()->filter(['PasteId' => $breachData['Id']])->first();

        if (!$existing) {
            $pasteID = $breachData['Id'];
            unset($breachData['Id']);
            $existing = static::create(
                $breachData
            );
            $existing->ID = null;
            $existing->PasteId = $pasteID;

            $existing->write();
            $existing->New = true;
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
