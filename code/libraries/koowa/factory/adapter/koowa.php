<?php

class KFactoryAdapterKoowa extends KFactoryAdapterAbstract
{
  public function instantiate($identifier, KConfig $config)
  {
    $classname = false;

    if($identifier->type == 'lib' && $identifier->package == 'koowa')
    {
      $classname = 'K'.KInflector::implode($identifier->path).ucfirst($identifier->name);
      $filepath  = KLoader::path($identifier);
      
      if(!class_exists($classname))
      {
        $classname = 'K'.KInflector::implode($identifier->path).'Default';
        
        if (!class_exists($classname)) 
          throw new KFactoryAdapterException("Class [$classname] not found in file [".basename($filepath)."]" );
      }
    }

    return $classname;
  }
}