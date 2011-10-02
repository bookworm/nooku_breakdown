<?php

class KTemplateFilterScript extends KTemplateFilterAbstract implements KTemplateFilterWrite
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_LOW,
    ));

    parent::_initialize($config);
  }             
  
  public function write(&$text)
	{
    $scripts = $this->_parseScripts($text);
    $text = $scripts.$text;

    return $this;   
	}  
	
	protected function _parseScripts(&$text)
	{
    $scripts = '';

    $matches = array();    
    
    if(preg_match_all('#<script(?!\s+inline\s*)\s+src="([^"]+)"(.*)/>#siU', $text, $matches))
    {
    foreach(array_unique($matches[1]) as $key => $match) {
      $attribs = $this->_parseAttributes( $matches[2][$key]);
      $scripts .= $this->_renderScript($match, true, $attribs);
    }

    $text = str_replace($matches[0], '', $text);
    }

    $matches = array();        
    
    if(preg_match_all('#<script(?!\s+inline\s*)(.*)>(.*)</script>#siU', $text, $matches))
    {
      foreach($matches[2] as $key => $match) {
        $attribs = $this->_parseAttributes( $matches[1][$key]);
        $scripts .= $this->_renderScript($match, false, $attribs);
      }

      $text = str_replace($matches[0], '', $text);   
    }

    // get rid of inline and inline="true" in script tags
    $text = preg_replace('#<script\s*(?:inline="true"|inline)\s*#siU', '<script', $text);

    return $scripts; 
	}
	
	protected function _renderScript($script, $link, $attribs = array())
	{
    $attribs = KHelperArray::toString($attribs);

    if(!$link)
    {
      $html  = '<script type="text/javascript" '.$attribs.'>'."\n";
      $html .= trim($script);
      $html .= '</script>'."\n"; 
    }
    else $html = '<script type="text/javascript" src="'.$script.'" '.$attribs.'></script>'."\n";

    return $html; 
	}
}