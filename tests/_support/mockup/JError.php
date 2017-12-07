<?php
abstract class JError
{
    public static function raiseError($code, $message)
    {
        if (404 == $code) {
            throw new Exception('COM_OSDOWNLOADS_ERROR_NOT_FOUND');
        }
    }
}
