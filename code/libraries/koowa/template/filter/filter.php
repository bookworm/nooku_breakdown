<?php

class KTemplateFilter
{
	const MODE_READ  = 1;
	const MODE_WRITE = 2; 
	
	public static function factory($filter, $config = array())
	{		
    if(!($filter instanceof KTemplateFilterInterface))
    {   
      if(is_string($filter) && strpos($filter, '.') === false)
        $filter = 'com.default.template.filter.'.trim($filter);

      $filter = KFactory::tmp($filter, $config);

      if(!($filter instanceof KTemplateFilterInterface)) {
        $identifier = $filter->getIdentifier();
        throw new KDatabaseBehaviorException("Template filter $identifier does not implement KTemplateFilterInterface");
      }         
    }

    return $filter; 
	}
}