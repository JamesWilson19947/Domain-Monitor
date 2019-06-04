<?php

namespace Error;


class Exception
{
    public function errorMessage()
    {
        //error message
        $errorMsg = 'Error on line ' . $this->getLine() . ' in ' . $this->getFile();

        return $errorMsg;
    }
}
