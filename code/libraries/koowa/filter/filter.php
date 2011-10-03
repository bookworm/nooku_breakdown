<?php

class KFilter
{
  public static function factory($identifier, $config = array())
  {   
    $filters = (array) $identifier;

    $filter = array_shift($filters);
    $filter = self::_createFilter($filter, $config);
  
    foreach($filters as $name) {
      $filter->addFilter(self::_createFilter($name, $config));
    }
  
    return $filter;   
  }
  
  protected static function _createFilter($filter, $config)
  {
    try 
    {
      if(is_string($filter) && strpos($filter, '.') === false )
        $filter = 'com.default.filter.'.trim($filter);

      $filter = KFactory::tmp($filter, $config);
    } 
    catch(KFactoryAdapterException $e) {
      throw new KFilterException('Invalid filter: '.$filter);
    }

    if(!($filter instanceof KFilterInterface)) {
      $identifier = $filter->getIdentifier();
      throw new KFilterException("Filter $identifier does not implement KFilterInterface");
    }

    return $filter;
  }
}