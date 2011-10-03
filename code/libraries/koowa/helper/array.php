<?php

class KHelperArray
{
  public static function settype(array $array, $type, $recursive = true)
  {
    foreach($array as $k => $v)
    {
      if($recursive && is_array($v))
        $array[$k] = self::settype($v, $type, $recursive);
      else
        settype($array[$k], $type);   
    }
    return $array;  
  }   
  
  public static function count(array $array)
  {
    $count = 0;

    foreach($array as $v)
    {
      if(is_array($v))
        $count += self::count($v);
      else 
        $count++;
    }
    return $count;     
  }
  
  public static function merge(array &$array1, array &$array2)
  {
    $args   = func_get_args();
    $merged = array_shift($args);

    foreach($args as $array)
    {
      foreach ($array as $key => &$value)
      {
        if (is_array($value) && isset ($merged [$key]) && is_array ($merged [$key])) 
          $merged [$key] = self::merge ( $merged [$key], $value );
        else
          $merged [$key] = $value;   
      } 
    }

    return $merged;  
  }
  
  public static function getColumn(array $array, $index)
  {
    $result = array();

    foreach($array as $k => $v)
    {
      if(is_object($v))
        $result[$k] = $v->$index;
      else
        $result[$k] = $v[$index];  
    }

    return $result; 
  }
  
  public static function toString($array = null, $inner_glue = '=', $outer_glue = ' ', $keepOuterKey = false)
  {
    $output = array();

    if($array instanceof KConfig)
    {
      $data = array();
      foreach($array as $key => $item) {
        $data[$key] = (string) $item;
      }
      $array = $data; 
    }    
      
    if(is_object($array))
      $array = (array) KConfig::toData($array);  

    if(is_array($array))
    {
      foreach($array as $key => $item)
      {
        if(is_array($item))
        {
          if($keepOuterKey) 
            $output[] = $key;

          $output[] = KHelperArray::toString($item, $inner_glue, $outer_glue, $keepOuterKey);  
        }
        else $output[] = $key.$inner_glue.'"'.str_replace('"', '&quot;', $item).'"';        
      }   
    }

    return implode($outer_glue, $output);   
  }
}