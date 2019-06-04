<?php

namespace Modules\DomainExpiry;

use phpWhois\Whois;
use Alert\SendAlert as Alert;
use Carbon\Carbon;

class DomainCheck {

	private $domainName;
	private $config;
	private $expiry;
	private $db;

	function __construct($domainName, $config){
		 
	   $disallowed = array('http://', 'https://');
	   foreach($disallowed as $d) {
	      if(strpos($domainName, $d) === 0) {
	         $domainName = str_replace($d, '', $domainName);
	      }
	   }

		$this->domainName = $domainName;
		$this->config = $config;

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
		    'validate' => [
		        'domain'   => [
		            'valid.type' => 'string',
		            'valid.required' => true
		        ]
		    ]
		]);
	}

	function returnStartAndEndDate(){



		$item = $this->db->get($this->clean($this->domainName));
		$item->domain = $this->domainName;

		$whois = new \Whois();
		$query = $this->domainName;
		$result = $whois->lookup($query,false);
		
		$registered = isset($result['regrinfo']['domain']['created']);


		if (!$registered) {
		    return false;
		} else {
		    if (isset($result['regrinfo']['domain']['expires'])) {
		        $date = Carbon::parse($result['regrinfo']['domain']['expires']);
		        $item->domainExpiry = $date;
		        $item->save();
		        return $date;
		    } else {
		        foreach ($result['rawdata'] as $raw) {
		            if (strpos($raw, 'Expiry') !== false) {
		                $date = Carbon::parse(trim(explode(':', $raw)[1]));
		                $this->expiry = $date;
		                $item->domainExpiry = $date;
		                $item->save();
		                return $date;
		            }
		        }
		    }
		}
	}

	function checkAlert($days){

		$to = Carbon::parse($this->expiry);

		if(Carbon::now() >= $to->subdays($days)){
			return true;
		}else{
			return false;
		}

	}
	function returnCachedData(){
		
	    $item = $this->db->get($this->clean($this->domainName));

	    if(!$item->domainExpiry){
	    	$this->returnStartAndEndDate();
	    	$item = $this->db->get($this->clean($this->domainName));
	    }

	    return $item->domainExpiry;

	}

	function sendAlert(){

		foreach($this->config['alert']['emails'] as $email){
			$subject = 'The Domain for '. $this->domainName .' expires on ' . $this->expiry; 
			$body = "Hello, the expiry for " . $this->domainName .' expires on ' . $this->expiry; 
			Alert::sendMail($email, $subject, $body);
		}

	}

	function clean($string) {
	   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

	   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
	}

}
