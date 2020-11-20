<?php


namespace Firesphere\HIBP\Tasks;

use Exception;
use Firesphere\HIBP\Models\Address;
use Firesphere\HIBP\Models\Breach;
use Firesphere\HIBP\Models\Paste;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;

class ImportListTask extends BuildTask
{
    protected $breachCount = 0;
    protected $pasteCount = 0;
    protected $breachMailCount = 0;
    protected $pasteMailCount = 0;

    /**
     * @param HTTPRequest $request
     * @throws Exception
     */
    public function run($request)
    {
        foreach (glob(Director::baseFolder() . '/datafiles/*.json') as $file) {
            $data = file_get_contents($file);

            $decoded = json_decode($data, 1);

            foreach ($decoded['BreachSearchResults'] as $result) {
                $address = Address::findOrCreate($result['Alias'], $result['DomainName']);
                if ($address->new) {
                    $this->breachMailCount++;
                }
                foreach ($result['Breaches'] as $breach) {
                    $breachItem = Breach::findOrCreate($breach);
                    if ($breachItem->new) {
                        $this->breachCount++;
                    }
                    $address->Breaches()->add($breachItem);
                }
            }
            foreach ($decoded['PasteSearchResults'] as $result) {
                $address = Address::findOrCreate($result['Alias'], $result['DomainName']);
                if ($address->new) {
                    $this->pasteMailCount++;
                }
                foreach ($result['Pastes'] as $paste) {
                    $paste = Paste::findOrCreate($paste);
                    if ($paste->new) {
                        $this->pasteMailCount++;
                    }

                    $address->Pastes()->add($paste);
                }
            }
        }

        $newLine = Director::is_cli() ? PHP_EOL : "<br />";
        echo "New Emails in Breaches: $this->breachMailCount" . $newLine;
        echo "New Breaches: $this->breachCount" . $newLine;
        echo "New Emails in Pastes: $this->pasteMailCount" . $newLine;
        echo "New Pastes: $this->pasteCount" . $newLine;
    }
}
