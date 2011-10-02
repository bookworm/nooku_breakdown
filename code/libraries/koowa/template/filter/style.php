<?php

class KTemplateFilterStyle extends KTemplateFilterAbstract implements KTemplateFilterWrite
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority' => KCommand::PRIORITY_LOW,
    ));

    parent::_initialize($config); 
  }      
  
  public function write(&$text)
	{
		$styles = $this->_parseStyles($text); 
		$text = $styles.$text; 
		
		return $this;
	}   
	
	protected function _parseStyles(&$text)
	{
    $styles = '';

    $matches = array();
    if(preg_match_all('#<style\s*src="([^"]+)"(.*)\/>#iU', $text, $matches))
    {
      foreach(array_unique($matches[1]) as $key => $match) {
      	$attribs = $this->_parseAttributes( $matches[2][$key]);
      	$styles .= $this->_renderStyle($match, true, $attribs);
      }

      $text = str_replace($matches[0], '', $text);     
    }

    $matches = array();
    if(preg_match_all('#<style(.*)>(.*)<\/style>#siU', $text, $matches))
    {
      foreach($matches[2] as $key => $match) {
      	$attribs = $this->_parseAttributes( $matches[1][$key]);
      	$styles .= $this->_renderStyle($match, false, $attribs);
      }

      $text = str_replace($matches[0], '', $text);  
    }

    return $styles; 
	}  
	
	protected function _renderStyle($style, $link, $attribs = array())
	{
		$attribs = KHelperArray::toString($attribs);
		
		if(!$link) 
		{
			$html  = '<style type="text/css" '.$attribs.'>'."\n";
			$html .= trim($style['data']);
			$html .= '</style>'."\n";
		}
		else $html = '<link type="text/css" rel="stylesheet" href="'.$style.'" '.$attribs.' />'."\n";
		
		return $html;
	}
}