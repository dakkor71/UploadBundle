<?php

namespace Juice\UploadBundle\Lib;

class Globals
{
    protected static $tmpUploadDir;
    protected static $finalUploadDir;

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
}
