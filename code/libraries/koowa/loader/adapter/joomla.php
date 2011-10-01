<?php 

class KLoaderAdapterJoomla extends KLoaderAdapterAbstract
{

  protected $_prefix = 'J';
  
  protected function _pathFromClassname($classname)
  {
    $path = false; 
    
    $word  = preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $classname);
    $parts = explode('_', $word);
      
    if(array_shift($parts) == $this->_prefix)
    {
      $class = strtolower($classname); //force to lower case

      if (class_exists($class)) return;
         
      $classes = method_exists('JLoader','getClassList') ? JLoader::getClassList() : JLoader::register();
      if(array_key_exists( $class, $classes)) {
        $path = $classes[$class];
      }
    } 
    
    return $path;
  }
  
  protected function _pathFromIdentifier($identifier)
  {
    $path = false;
    
    if($identifier->type == 'lib' && $identifier->package == 'joomla')
    {
      if(count($identifier->path)) $path .= implode('.',$identifier->path);

      if(!empty($identifier->name)) $path .= '.'.$identifier->name;
        
      $path = JLoader::import('joomla.'.$path, $this->_basepath );
    }

    return $path;
  }
}