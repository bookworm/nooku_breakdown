<?php

class KFactoryAdapterPlugin extends KFactoryAdapterAbstract
{
  public function instantiate($identifier, KConfig $config)
  {
    $classname = false;
    
    if($identifier->type == 'plg') 
    {     
      $classpath = KInflector::camelize(implode('_', $identifier->path));
      $classname = 'Plg'.ucfirst($identifier->package).$classpath.ucfirst($identifier->name);
      
      if (!class_exists( $classname, false ))
      {
        if($path = KLoader::load($identifier)) {
          if (!class_exists( $classname, false )) 
            throw new KFactoryAdapterException("Class [$classname] not found in file [".$path."]" );
        }
      }
    }

    return $classname;
  }
}