<?php

namespace Juice\UploadBundle\Lib;

class Globals
{
    protected static $tmpUploadDir;
    protected static $finalUploadDir;
    protected static $absolutePath;

    public static function setTmpUploadDir($dir)
    {
        self::$tmpUploadDir = $dir;
    }

    public static function getTmpUploadDir()
    {
        return self::$tmpUploadDir;
    }

    public static function setFinalUploadDir($dir)
    {
        self::$finalUploadDir = $dir;
    }

    public static function getFinalUploadDir()
    {
        return self::$finalUploadDir;
    }

    public static function setAbsolutePath($flag)
    {
        self::$absolutePath = $flag;
    }

    public static function getAbsolutePath()
    {
        return self::$absolutePath;
    }

    public static function getRootFolder() {
        return __DIR__ . '/../../../../../../web/';
    }
}
