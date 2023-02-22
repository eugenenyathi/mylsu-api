<?php

namespace App\Exceptions;

use Exception;
use App\Traits\HttpResponses;

class AccountExistsException extends Exception
{
    use HttpResponses;

    public function report(){

    }

    public function render(){
       return $this->sendError('Account already exists');
    }
}
