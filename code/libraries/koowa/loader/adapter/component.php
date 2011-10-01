<?php

class KLoaderAdapterComponent extends KLoaderAdapterAbstract
{
  protected $_prefix = 'Com';
  
  protected function _pathFromClassname($classname)
  {
    $path = false; 
    
    if (strpos($classname, $this->_prefix) === 0) 
    {
      $word  = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $classname));
      $parts = explode('_', $word);
      
      if (array_shift($parts) == 'com') 
      {
        $component = 'com_'.strtolower(array_shift($parts));
        $file      = array_pop($parts);
        
        if(count($parts)) 
        {
          foreach($parts as $key => $value) {
            $parts[$key] = KInflector::pluralize($value);
          }
          
          $path = implode('/', $parts);
          $path = $path.'/'.$file;
        } 
        else $path = $file;
      
        $path = $this->_basepath.'/components/'.$component.'/'.$path.'.php';
      }
    }
    
    return $path;
  }

  protected function _pathFromIdentifier($identifier)
  {
    $path = false;
    
    if($identifier->type == 'com')
    {
      $parts = $identifier->path;
        
      $component = 'com_'.strtolower($identifier->package);
      
      if($identifier->basepath) $this->_basepath = $identifier->basepath;

      if(!empty($identifier->name))
      {
        if(count($parts)) 
        {
          $path    = KInflector::pluralize(array_shift($parts));
          $path   .= count($parts) ? '/'.implode('/', $parts) : '';
          $path   .= '/'.strtolower($identifier->name); 
        } 
        else $path = strtolower($identifier->name); 
      }
        
      $path = $this->_basepath.'/components/'.$component.'/'.$path.'.php';
    } 
    
    return $path;
  }
}