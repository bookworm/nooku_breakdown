<?php

class KTemplateHelper 
{
  public static function factory($helper, $config = array())
  {       
    if(!($helper instanceof KTemplateHelperInterface))
    {   
      $identifier = $helper;
      if(is_string($identifier) && strpos($identifier, '.') === false) 
        $identifier = 'com.default.template.helper.'.trim($identifier);

      $helper = KFactory::get($identifier, $config);

      if(!($helper instanceof KTemplateHelperInterface)) 
        throw new KTemplateHelperException("Template helper $identifier does not implement KTemplateHelperInterface");
    }

    return $helper;
  }                
}