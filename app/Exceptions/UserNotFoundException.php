<?php

namespace App\Exceptions;

use Exception;
use App\Traits\HttpResponses;

class UserNotFoundException extends Exception
{

    use HttpResponses;
    
    public function report(){

    }

    public function render(){
        return $this->sendError('Account does not exist', 404);
    }
}
