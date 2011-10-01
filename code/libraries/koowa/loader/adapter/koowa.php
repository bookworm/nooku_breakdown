<?php

class KLoaderAdapterKoowa extends KLoaderAdapterAbstract
{
	protected $_prefix = 'K';

	protected function _pathFromClassname($classname)
	{
		$path     = false;
		
		$word  = preg_replace('/(?<=\\w)([A-Z])/', '_\\1',  $classname);
		$parts = explode('_', $word);
		
		if(array_shift($parts) == $this->_prefix)
		{	
			$path = strtolower(implode('/', $parts));
				
			if(count($parts) == 1) $path = $path.'/'.$path;
			
			if(!is_file($this->_basepath.'/'.$path.'.php')) $path = $path.'/'.strtolower(array_pop($parts));

			$path = $this->_basepath.'/'.$path.'.php';
		}
		
		return $path;
	}	

	protected function _pathFromIdentifier($identifier)
	{
		$path = false;
		
		if($identifier->type == 'lib' && $identifier->package == 'koowa')
		{
			if(count($identifier->path)) $path .= implode('/',$identifier->path);

			if(!empty($identifier->name)) $path .= '/'.$identifier->name;
				
			$path = $this->_basepath.'/'.$path.'.php';
		}
		
		return $path;
	}
}