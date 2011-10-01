<?php

class KFactoryAdapterModule extends KFactoryAdapterAbstract
{
  public function instantiate($identifier, KConfig $config)
  {
    $classname = false;
    
    if($identifier->type == 'mod') 
    {     
      $path = KInflector::camelize(implode('_', $identifier->path));
      $classname = 'Mod'.ucfirst($identifier->package).$path.ucfirst($identifier->name);
        
      if(!class_exists( $classname, false ))
      {
        if($path = KLoader::load($identifier)) {
          if(!class_exists( $classname, false )) 
            throw new KFactoryAdapterException("Class [$classname] not found in file [".$path."]" );
        }
        else 
        {
          $classpath = $identifier->path;
          $classtype = !empty($classpath) ? array_shift($classpath) : $identifier->name;
          
          $path = ($classtype != 'view') ? KInflector::camelize(implode('_', $classpath)) : '';
          
          if(class_exists('Mod'.ucfirst($identifier->package).ucfirst($identifier->name)))
            $classname = 'Mod'.ucfirst($identifier->package).ucfirst($identifier->name);
          elseif(class_exists('ModDefault'.ucfirst($identifier->name)))
            $classname = 'ModDefault'.ucfirst($identifier->name);
          elseif(class_exists( 'K'.ucfirst($classtype).$path.ucfirst($identifier->name)))
            $classname = 'K'.ucfirst($classtype).$path.ucfirst($identifier->name);
          elseif(class_exists('K'.ucfirst($classtype).'Default'))
            $classname = 'K'.ucfirst($classtype).'Default';
          else $classname = false;
          
        }
      }
    }

    return $classname;
  }
}