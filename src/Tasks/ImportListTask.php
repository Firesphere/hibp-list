<?php


namespace Firesphere\HIBP\Tasks;


use Firesphere\HIBP\Models\Address;
use Firesphere\HIBP\Models\Breach;
use Firesphere\HIBP\Models\Paste;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;

class ImportListTask extends BuildTask
{

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        foreach (glob(Director::baseFolder() . '/datafiles/*.json') as $file) {

            $data = file_get_contents($file);

            $decoded = json_decode($data, 1);

            foreach ($decoded['BreachSearchResults'] as $result) {
                $address = Address::findOrCreate($result['Alias'], $result['DomainName']);
                foreach ($result['Breaches'] as $breach) {
                    $breachItem = Breach::findOrCreate($breach);
                    $address->Breaches()->add($breachItem);
                }
            }
            foreach ($decoded['PasteSearchResults'] as $result) {
                $address = Address::findOrCreate($result['Alias'], $result['DomainName']);
                foreach ($result['Pastes'] as $paste) {
                    $paste = Paste::findOrCreate($paste);
                    $address->Pastes()->add($paste);
                }

            }
        }
    }
}