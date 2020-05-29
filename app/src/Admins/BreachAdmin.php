<?php


namespace Firesphere\HIBP\Admins;


use Firesphere\HIBP\Models\Address;
use Firesphere\HIBP\Models\Breach;
use Firesphere\HIBP\Models\DataType;
use Firesphere\HIBP\Models\Employee;
use Firesphere\HIBP\Models\Paste;
use SilverStripe\Admin\ModelAdmin;

/**
 * Class \Firesphere\HIBP\Admins\BreachAdmin
 *
 */
class BreachAdmin extends ModelAdmin
{

    private static $managed_models = [
        Breach::class,
        Paste::class,
        DataType::class,
        Address::class,
        Employee::class,
    ];

    private static $url_segment = 'breachadmin';

    private static $menu_title = 'Breaches';
}