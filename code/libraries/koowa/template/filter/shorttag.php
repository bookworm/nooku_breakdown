<?php

class KTemplateFilterShorttag extends KTemplateFilterAbstract implements KTemplateFilterRead
{
	public function read(&$text)
	{
    if (!ini_get('short_open_tag')) 
    {
      $find = '/\<\?\s*=\s*(.*?)/';
      $replace = "<?php echo \$1";
      $text = preg_replace($find, $replace, $text);

      $find = '/\<\?(?:php)?\s*(.*?)/';
      $replace = "<?php \$1";
      $text = preg_replace($find, $replace, $text);    
    }

    return $this;   
	}
}