<?php

class KFactoryAdapterComponent extends KFactoryAdapterAbstract
{
  public function instantiate($identifier, KConfig $config)
  {
    $classname = false;
    
    if($identifier->type == 'com') 
    {     
      $path      = KInflector::camelize(implode('_', $identifier->path));
      $classname = 'Com'.ucfirst($identifier->package).$path.ucfirst($identifier->name);
                      
      if(!class_exists($classname, false))
      {
        if($path = KLoader::load($identifier)) {
          if (!class_exists($classname, false))
            throw new KFactoryAdapterException("Class [$classname] not found in file [".$path."]" );
        }
        else 
        {
          $classpath = $identifier->path;
          $classtype = !empty($classpath) ? array_shift($classpath) : '';

          $path = ($classtype != 'view') ? KInflector::camelize(implode('_', $classpath)) : '';

          if(class_exists('Com'.ucfirst($identifier->package).ucfirst($classtype).$path.ucfirst($identifier->name)))
            $classname = 'Com'.ucfirst($identifier->package).ucfirst($classtype).$path.ucfirst($identifier->name);
          elseif(class_exists('Com'.ucfirst($identifier->package).ucfirst($classtype).$path.'Default')) 
            $classname = 'Com'.ucfirst($identifier->package).ucfirst($classtype).$path.'Default'; 
          elseif(class_exists('ComDefault'.ucfirst($classtype).$path.ucfirst($identifier->name)))
            $classname = 'ComDefault'.ucfirst($classtype).$path.ucfirst($identifier->name);
          elseif(class_exists('ComDefault'.ucfirst($classtype).$path.'Default'))
            $classname = 'ComDefault'.ucfirst($classtype).$path.'Default';
          elseif(class_exists( 'K'.ucfirst($classtype).$path.ucfirst($identifier->name)))
            $classname = 'K'.ucfirst($classtype).$path.ucfirst($identifier->name);
          elseif(class_exists('K'.ucfirst($classtype).$path.'Default'))
            $classname = 'K'.ucfirst($classtype).$path.'Default';
          else $classname = false; 
        } 
      }
    }

    return $classname;
  }
}