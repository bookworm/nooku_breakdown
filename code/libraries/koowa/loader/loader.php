<?php

require_once Koowa::getPath().'/exception/interface.php';
require_once Koowa::getPath().'/exception/exception.php';

require_once Koowa::getPath().'/identifier/interface.php';
require_once Koowa::getPath().'/identifier/identifier.php';
require_once Koowa::getPath().'/identifier/exception.php';

require_once Koowa::getPath().'/loader/adapter/interface.php';
require_once Koowa::getPath().'/loader/adapter/exception.php';
require_once Koowa::getPath().'/loader/adapter/abstract.php';
require_once Koowa::getPath().'/loader/adapter/koowa.php';  

KLoader::instantiate(); 

class KLoader
{        
  protected static $_registry = null;
  protected static $_adapters = null;   
  
  final private function __construct() 
  { 
    self::$_adapters  = array();
    self::$_registry = new ArrayObject();

    spl_autoload_register(array(__CLASS__, 'load'));

    if (function_exists('__autoload')) {
      spl_autoload_register('__autoload');
    }    
  } 
  
  final private function __clone() { }
  
  public static function instantiate()
  {
    static $instance;

    if ($instance === NULL) $instance = new self();

    return $instance; 
  }  
  
  public static function load($class)
  {
    if((ctype_upper(substr($class, 0, 1)) || (strpos($class, '.') !== false)))
    {

      if (class_exists($class, false) || interface_exists($class, false))
        return true;  
 
      $result = self::path( $class );

      if ($result !== false && !in_array($result, get_included_files()) && file_exists($result))
      {
        $mask = E_ALL ^ E_WARNING;
        if (defined('E_DEPRECATED')) $mask = $mask ^ E_DEPRECATED;

        $old = error_reporting($mask);
        $included = include $result;
        error_reporting($old);

        if ($included) return $result; 
      }  
    }

    return false;    
  }   
  
  public static function path($class)
  {
    if(self::$_registry->offsetExists((string)$class))
      return self::$_registry->offsetGet((string)$class);

    $result = false;

    if(ctype_upper(substr($class, 0, 1)))
    {
      $word  = preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class);
      $parts = explode('_', $word);
  
      if(isset(self::$_adapters[$parts[0]])) $result = self::$_adapters[$parts[0]]->path( $class );
    } 
    else 
    {
      if(!($class instanceof KIdentifier)) $class = new KIdentifier($class);
  
      $adapters = array_reverse(self::$_adapters);
      foreach($adapters as $adapter) {
        if($result = $adapter->path( $class )) break;
      } 
    }

    if ($result !== false) 
    {
      $path = realpath($result);
      $result = $path !== false ? $path : $result;
  
      if($result !== false)
        self::$_registry->offsetSet((string) $class, $result);
    }

    return $result; 
  }

  public static function addAdapter(KLoaderAdapterInterface $adapter)
  {
    self::$_adapters[$adapter->getPrefix()] = $adapter;
  }
}