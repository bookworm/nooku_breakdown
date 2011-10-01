<?php 
  
class KDispatcherDefault extends KDispatcherAbstract 
{
  public static function instantiate($config = array())
  {
    static $instance;
  
    if ($instance === NULL) 
    {
      $classname = $config->identifier->classname;
      $instance = new $classname($config);
  
      KFactory::map('dispatcher', $config->identifier);  
    }
  
    return $instance;  
  }        
}