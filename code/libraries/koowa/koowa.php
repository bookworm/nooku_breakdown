<?php

define('KOOWA', 1);

class Koowa
{
  const VERSION = '0.7.0-alpha-3';

  protected static $_path;

  public static function getVersion()
  {
    return self::VERSION;
  }

  public static function getPath()
  {
    if(!isset(self::$_path)) self::$_path = dirname(__FILE__);
    
    return self::$_path;
  }  
}