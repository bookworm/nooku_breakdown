<?php 

class KTemplateFilterLink extends KTemplateFilterAbstract implements KTemplateFilterWrite
{
	public function write(&$text)
	{
    $scripts = $this->_parseLinks($text);
    $text = $scripts.$text; 

    return $this;
	}
	
	protected function _parseLinks(&$text)
	{
		$scripts = '';
		
		$matches = array();
		if(preg_match_all('#<link\ href="([^"]+)"(.*)\/>#iU', $text, $matches))
		{
			foreach(array_unique($matches[1]) as $key => $match) {
				$attribs = $this->_parseAttributes( $matches[2][$key]);
				$scripts .= $this->_renderScript($match, $attribs);
			}
			
			$text = str_replace($matches[0], '', $text);
		}
			
		return $scripts;
	}

	protected function _renderLink($link, $attribs = array())
	{
		$attribs = KHelperArray::toString($attribs);
		
		$html = '<link href="'.$link.'" '.$attribs.'/>'."\n";
		return $html;
	}
}