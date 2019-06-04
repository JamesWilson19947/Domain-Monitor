<?php

namespace DomainMonitor\Modules;

use Punkstar\Ssl\Reader;
use DomainMonitor\SendAlert as Alert;
use Carbon\Carbon;


class Ssl
{
    private $domainName;
    private $config;
    private $expiry;
    private $db;

    function __construct($domainName, $config)
    {
        $this->config = $config;

        $disallowed = array('http://', 'https://');
        foreach ($disallowed as $d) {
            if (strpos($domainName, $d) === 0) {
                $domainName = str_replace($d, '', $domainName);
            }
        }

        $this->domainName = $domainName;

        $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/../../');
        $dotenv->load();

        $this->db = new \Filebase\Database([
            'dir'            => $_ENV['STORAGE_LOCATION'],
            'backupLocation' => $_ENV['BACKUPS_LOCATION'],
            'format'         => \Filebase\Format\Json::class,
            'cache'          => true,
            'cache_expires'  => 1800,
            'pretty'         => true,
            'safe_filename'  => true,
            'read_only'      => false,
            'validate'       => [
                'domain' => [
                    'valid.type'     => 'string',
                    'valid.required' => true,
                ],
            ],
        ]);

        if (!$this->config) {
            throw new \DomainMonitor\Exception("Config not defined correctly.");
        }
    }

    function returnStartAndEndDate()
    {
        $reader = new Reader();
        $item = $this->db->get($this->clean($this->domainName));
        $item->domain = $this->domainName;

        $certificate = $reader->readFromUrl($this->domainName);

        $array = [];

        $array['to'] = $certificate->validTo()->format('Y-m-d h:i:s');
        $array['from'] = $certificate->validFrom()->format('Y-m-d h:i:s');

        $item->SSLExpiry = $array['to'];
        $item->SSLStart = $array['from'];

        $item->save();

        $this->expiry = $array;

        return $array;
    }

    function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    function checkAlert($days)
    {
        $to = Carbon::parse($this->expiry['to']);

        if (Carbon::now() >= $to->subdays($days)) {
            return true;
        } else {
            return false;
        }
    }

    function sendAlert()
    {
        foreach ($this->config['alert']['emails'] as $email) {
            $subject = 'The SSL for ' . $this->domainName . ' expires on ' . $this->expiry['to'];
            $body = "Hello, the expiry for " . $this->domainName . ' expires on ' . $this->expiry['to'];
            Alert::sendMail($email, $subject, $body);
        }
    }

    function returnCachedData()
    {
        $item = $this->db->get($this->clean($this->domainName));

        if (!$item->SSLExpiry) {
            $this->returnStartAndEndDate();
            $item = $this->db->get($this->clean($this->domainName));
        }

        return $item->SSLExpiry;
    }
}
