<?php

class KLoaderAdapterPlugin extends KLoaderAdapterAbstract
{
  protected $_prefix = 'Plg';

  protected function _pathFromClassname($classname)
  { 
    $path = false; 
    
    if (strpos($classname, $this->_prefix) === 0) 
    { 
      $word  = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $classname));
      $parts = explode('_', $word);
      
      if (array_shift($parts) == 'plg') 
      { 
        $type = array_shift($parts);
        
        if(count($parts) > 1) $path = array_shift($parts).'/'.implode('/', $parts);
        else $path = array_shift($parts);
          
        if (is_file($this->_basepath.'/plugins/'.$type.'/'.$path.'/'.$path.'.php')) {
          $path = $this->_basepath.'/plugins/'.$type.'/'.$path.'/'.$path.'.php';
        } else {
          $path = $this->_basepath.'/plugins/'.$type.'/'.$path.'.php';
        }
      }
    }
    
    return $path;
  }

  protected function _pathFromIdentifier($identifier)
  {
    $path = false;
    
    if($identifier->type == 'plg')
    {   
      $parts = $identifier->path;
      
      $name  = array_shift($parts);
      $type  = $identifier->package;
      
      if(!empty($identifier->name))
      {
        if(count($parts)) 
        {
          $path    = array_shift($parts).
          $path   .= count($parts) ? '/'.implode('/', $parts) : '';
          $path   .= DS.strtolower($identifier->name);  
        } 
        else $path = strtolower($identifier->name); 
      }
        
      if (is_file($this->_basepath.'/plugins/'.$type.'/'.$path.'/'.$path.'.php')) {
        $path = $this->_basepath.'/plugins/'.$type.'/'.$path.'/'.$path.'.php';
      } else {
        $path = $this->_basepath.'/plugins/'.$type.'/'.$path.'.php';
      }
    } 
    
    return $path;
  }
}