<?php
namespace Alledia\OSDownloads\Free;

abstract class Factory
{
    protected static $container;

    public static function getContainer()
    {
        return static::container;
    }
}
