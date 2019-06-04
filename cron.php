<?php

namespace CRON;

require __DIR__ . '/vendor/autoload.php';

foreach (\Config\Domains::$domains as $domain => $config) {

    if (empty($config['alert'])) {
        continue;
    }

    if (!$config['alert']['days']) {
        continue;
    }

    # Check SSL
    if (in_array('ssl', $config['checks'])) {
        $ssl = new \DomainMonitor\Modules\SSL($domain, $config);
        $expiry = $ssl->returnStartAndEndDate();
        $alert = $ssl->checkAlert($config['alert']['days']);
        if ($alert) {
            $ssl->sendAlert();
        }
    }

    # Check Domain Expiry
    if (in_array('domainexpiry', $config['checks'])) {
        $domain = new \DomainMonitor\Modules\DomainExpiry($domain, $config);
        $expiry = $domain->returnStartAndEndDate();
        $alert = $domain->checkAlert($config['alert']['days']);
        if ($alert) {
            $domain->sendAlert();
        }
    }
}