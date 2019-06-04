<?php

namespace DomainMonitor;


class Exception extends \Exception
{
    public function errorMessage()
    {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile();

        return $errorMsg;
    }
}
