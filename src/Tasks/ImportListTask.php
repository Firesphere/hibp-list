<?php


namespace Firesphere\HIBP\Tasks;

use Exception;
use Firesphere\HIBP\Classes\BreachNotification;
use Firesphere\HIBP\Extensions\SiteConfigExtension;
use Firesphere\HIBP\Models\Address;
use Firesphere\HIBP\Models\Breach;
use Firesphere\HIBP\Models\Paste;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class ImportListTask extends BuildTask
{
    private static $segment = 'ImportHIBP';
    protected $description = 'Import all breaches from JSON file';
    protected $title = 'Import HIBP data';
    protected $breachCount = 0;
    protected $pasteCount = 0;
    protected $breachMailCount = 0;
    protected $pasteMailCount = 0;

    /**
     * @var ArrayList
     */
    protected $sendMails;

    /**
     * @param HTTPRequest $request
     * @throws Exception
     */
    public function run($request)
    {
        $this->sendMails = ArrayList::create();
        foreach (glob(Director::baseFolder() . '/datafiles/*.json') as $file) {
            $data = file_get_contents($file);

            $decoded = json_decode($data, 1);

            $this->findBreaches($decoded);
            $this->findPastes($decoded);
        }

        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();
        if ($config->NotifyBreachedAccounts) {
            // Filtering to get the latest made in the last hour. It's not ideal, but it works~ish
            $filter = ['Created:PartialMatch' => date('Y-m-d H')];
            /** @var DataList|Breach[] $recentBreaches */
            $recentBreaches = Breach::get()->filter($filter);
            $this->getAddressesToSend($recentBreaches);
            /** @var DataList|Paste[] $recentPastes */
            $recentPastes = Paste::get()->filter($filter);
            $this->getAddressesToSend($recentPastes);
            new BreachNotification($this->sendMails);
        }

        $newLine = Director::is_cli() ? PHP_EOL : "<br />";
        echo "New Emails in Breaches: $this->breachMailCount" . $newLine;
        echo "New Breaches: $this->breachCount" . $newLine;
        echo "New Emails in Pastes: $this->pasteMailCount" . $newLine;
        echo "New Pastes: $this->pasteCount" . $newLine;
    }

    /**
     * @param $decoded
     * @throws Exception
     */
    protected function findBreaches($decoded)
    {
        foreach ($decoded['BreachSearchResults'] as $result) {
            $address = Address::findOrCreate($result['Alias'], $result['DomainName']);
            $this->addItem($result, $address, 'Breaches', Breach::class);
            $address->destroy();
        }
    }

    /**
     * @param $results array of results
     * @param $address Address Address to add or fix
     * @param $name string Name of the method
     * @param $object string Object to create
     */
    protected function addItem($results, $address, $name, $object)
    {
        /** @var Breach|Paste $item */
        foreach ($results[$name] as $item) {
            $item = $object::findOrCreate($item);
            $address->$name()->add($item);
        }

    }

    /**
     * @param $decoded
     * @throws Exception
     */
    protected function findPastes($decoded)
    {
        foreach ($decoded['PasteSearchResults'] as $result) {
            $address = Address::findOrCreate($result['Alias'], $result['DomainName']);
            $this->addItem($result, $address, 'Pastes', Paste::class);
            $address->destroy();
        }
    }

    /**
     * @param Address $address
     * @param Breach|Paste $recentBreach
     */
    protected function updateSendEmails(Address $address, $recentBreach): void
    {
        $existing = $this->sendMails->find('Email', $address->Employee()->Email);
        if (!$existing) {
            $this->sendMails->push(ArrayData::create(
                [
                    'Email'    => $address->Employee()->Email,
                    'Name'     => $address->Employee()->Name,
                    'Breaches' => sprintf('<li>%s</li>', $recentBreach->Title)
                ]
            ));
        } else {
            $existing->Breaches .= sprintf('<li>%s</li>', $recentBreach->Title);
        }
    }

    /**
     * @param DataList $recentBreaches
     */
    protected function getAddressesToSend(DataList $recentBreaches): void
    {
        foreach ($recentBreaches as $recentBreach) {
            foreach ($recentBreach->Addresses() as $address) {
                $this->updateSendEmails($address, $recentBreach);
            }
        }
    }
}
