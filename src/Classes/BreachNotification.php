<?php


namespace Firesphere\HIBP\Classes;

use Firesphere\HIBP\Extensions\SiteConfigExtension;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;

class BreachNotification
{
    public function __construct($addressList)
    {
        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();
        $mail = Email::create();

        $mail->setFrom($config->NotificationFrom);
        $mail->setReplyTo($config->NotificationReplyTo);
        $mail->setSubject($config->NotificationSubject);

        foreach ($addressList as $address) {
            $mail->setTo($address->Email);
            $address->Breaches = sprintf('<ul>%s</ul>', $address->Breaches);
            $body = SSViewer::execute_string($config->NotificationContent, $address);
            $mail->setBody($body);
            $mail->send();
            // In dev mode, we don't want to send hundreds and hundreds of emails
            if (Director::isDev()) {
                exit;
            }
        }
    }
}
