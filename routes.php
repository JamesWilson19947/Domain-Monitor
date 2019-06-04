<?php

namespace Routes;

use Jenssegers\Blade\Blade;
use Config;
use Modules\SSL;
use Carbon\Carbon;

$dotenv = \Dotenv\Dotenv::create(__DIR__ . '../');
$dotenv->load();

$router = new \Klein\Klein();

$router->respond('GET', '/', function () {
	$blade = new Blade('../views', '../cache');

	$domainArray = [];
	
	foreach(Config\Domains::$domains as $domain => $config){

		$domainArray[$domain]['domain'] = $domain;

		if(in_array('ssl', $config['checks'])){

			$ssl = new \Modules\SSL\SSLCheck($domain, $config);
			$domainArray[$domain]['SSLExpiry'] = Carbon::parse($ssl->returnCachedData());
		}
		
		if(in_array('domainexpiry', $config['checks'])){
			$domainexpiry = new \Modules\DomainExpiry\DomainCheck($domain, $config);
			$domainArray[$domain]['DomainExpiry'] = $domainexpiry->returnCachedData();
		}
	}

	return $blade->make('homepage')->with('data', $domainArray);
});

$router->dispatch();