<?php
namespace bxkj_module\exception;


class ApiException extends Exception
{
    public function __construct($message = "", $code = 1, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}