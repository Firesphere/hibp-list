<?php


namespace Firesphere\HIBP\Tasks;

use Exception;
use Firesphere\HIBP\Classes\BreachNotification;
use Firesphere\HIBP\Extensions\SiteConfigExtension;
use Firesphere\HIBP\Models\Address;
use Firesphere\HIBP\Models\Breach;
use Firesphere\HIBP\Models\Paste;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;

class ImportListTask extends BuildTask
{
    private static $segment = 'ImportHIBP';
    protected $description = 'Import all breaches from JSON file';
    protected $title = 'Import HIBP data';

    /**
     * @var ArrayList
     */
    protected $sendMails;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct()
    {
        parent::__construct();
        $this->logger = Injector::inst()->get(LoggerInterface::class);
    }

    public function run($request)
    {
        $this->doImport($request);
    }

    /**
     * @param HTTPRequest $request
     * @throws Exception
     */
    public function doImport($request)
    {
        $maxBreach = Breach::get()->max('ID') ?? 0;
        $maxPaste = Paste::get()->max('ID') ?? 0;
        foreach (glob(Director::baseFolder() . '/datafiles/*.json') as $file) {
            $data = file_get_contents($file);

            $decoded = json_decode($data, 1);

            $this->findBreaches($decoded);
            $this->findPastes($decoded);
        }
        // Filtering to get the latest made with an ID higher than the last known ID.
        // Not ideal for loading an entire new set, but works for subsequent sets.
        $breachCount = Breach::get()->filter(['ID:GreaterThan' => $maxBreach]);
        $pasteCount = Paste::get()->filter(['ID:GreaterThan' => $maxPaste]);

        $this->logger->info(sprintf('Found/created %s new Breaches', $breachCount->count()));
        $this->logger->info(sprintf('Found/created %s new Pastes', $pasteCount->count()));

        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();
        if ($config->NotifyBreachedAccounts) {
            $this->sendNotification($breachCount, $pasteCount);
        }
        $config->LastImport = DBDatetime::now();
        $config->write();
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
     * @param $object string|Breach|Paste Object to create
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
        if (!$this->sendMails) {
            $this->sendMails = ArrayList::create();
        }
        $existing = $this->sendMails->find('Email', $address->Employee()->Email);
        if (!$existing) {
            $this->sendMails->push(ArrayData::create(
                [
                    'Email'    => $address->Employee()->Email,
                    'Name'     => $address->Employee()->Name,
                    'Breaches' => sprintf('<li>%s<br />%s</li>', $recentBreach->Title, $recentBreach->Description)
                ]
            ));
        } else {
            $existing->Breaches .= sprintf('<li>%s<br />%s</li>', $recentBreach->Title, $recentBreach->Description);
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

    protected function sendNotification($breaches, $pastes): void
    {
        $this->getAddressesToSend($breaches);

        $this->getAddressesToSend($pastes);
        new BreachNotification($this->sendMails);
    }
}
