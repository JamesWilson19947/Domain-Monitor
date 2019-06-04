<?php

namespace DomainMonitor\Modules;

use DomainMonitor\SendAlert as Alert;
use Carbon\Carbon;


class DomainExpiry
{
    private $domainName;
    private $config;
    private $expiry;

    function __construct($domainName, $config)
    {
        $disallowed = array('http://', 'https://');
        foreach ($disallowed as $d) {
            if (strpos($domainName, $d) === 0) {
                $domainName = str_replace($d, '', $domainName);
            }
        }

        $this->domainName = $domainName;
        $this->config = $config;
    }

    function returnStartAndEndDate()
    {
        $whois = new \Whois();
        $query = $this->domainName;
        $result = $whois->lookup($query, false);

        $registered = isset($result['regrinfo']['domain']['created']);

        if (!$registered) {
            return false;
        } else {
            if (isset($result['regrinfo']['domain']['expires'])) {
                $date = Carbon::parse($result['regrinfo']['domain']['expires']);

                return $date;
            } else {
                foreach ($result['rawdata'] as $raw) {
                    if (strpos($raw, 'Expiry') !== false) {
                        $date = Carbon::parse(trim(explode(':', $raw)[1]));
                        $this->expiry = $date;

                        return $date;
                    }
                }
            }
        }
    }

    function checkAlert($days)
    {
        $to = Carbon::parse($this->expiry);

        if (Carbon::now() >= $to->subdays($days)) {
            return true;
        } else {
            return false;
        }

    }

    function sendAlert()
    {
        foreach ($this->config['alert']['emails'] as $email) {
            $subject = 'The Domain for ' . $this->domainName . ' expires on ' . $this->expiry;
            $body = "Hello, the expiry for " . $this->domainName . ' expires on ' . $this->expiry;
            Alert::sendMail($email, $subject, $body);
        }
    }
}
