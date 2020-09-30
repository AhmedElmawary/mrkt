<?php
namespace Firebase\JWT;

class ExpiredException extends \Exception
{
    protected $message;

    public function __construct(String $var = "Expired token"  ) {
        return $this->message = $var ;
    }
}
