<?php

namespace Modules\SSL;

use Punkstar\Ssl\Reader;
use Alert\SendAlert as Alert;
use Carbon\Carbon;

class SSLCheck {

	private $domainName;
	private $config;
	private $expiry;

	function __construct($domainName, $config){
		$this->domainName = $domainName;
		$this->config = $config;

		if(!$this->config){
			throw new Error\Exception("Config not defined correctly.");
		}
	}

	function returnStartAndEndDate(){
		$reader = new Reader();
		
		$certificate = $reader->readFromUrl($this->domainName);
		
		$array = [];

		$array['to'] = $certificate->validTo()->format('Y-m-d h:i:s');
		$array['from'] = $certificate->validFrom()->format('Y-m-d h:i:s');

		$this->expiry = $array;

		return $array;
	}

	function checkAlert($days){

		$to = Carbon::parse($this->expiry['to']);

		if(Carbon::now() >= $to->subdays($days)){
			return true;
		}else{
			return false;
		}

	}

	function sendAlert(){

		foreach($this->config['alert']['emails'] as $email){
			$subject = 'The SSL for '. $this->domainName .' expires on ' . $this->expiry['to']; 
			$body = "Hello, the expiry for " . $this->domainName .' expires on ' . $this->expiry['to']; 
			Alert::sendMail($email, $subject, $body);
		}

	}
}
