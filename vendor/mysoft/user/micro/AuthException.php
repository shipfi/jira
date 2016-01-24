<?php
namespace mysoft\user\micro;

class AuthException extends \Exception {
    
    const ERR_CODE = 987654321;
    
    public function __construct($message, $code = self::ERR_CODE, $previous = null) {
        return parent::__construct($message, $code, $previous);
    }
}