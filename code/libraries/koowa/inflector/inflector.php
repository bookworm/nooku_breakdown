<?php

class KInflector
{
	protected static $_rules = array
	(
		'pluralization' => array(
			'/move$/i' 				             => 'moves',
			'/sex$/i' 				             => 'sexes',
			'/child$/i' 			             => 'children',
			'/man$/i' 				             => 'men',
			'/foot$/i' 				             => 'feet',
			'/person$/i' 			             => 'people',
			'/taxon$/i' 			             => 'taxa',
			'/(quiz)$/i' 			             => '$1zes',
			'/^(ox)$/i' 			             => '$1en',
			'/(m|l)ouse$/i' 			         => '$1ice',
			'/(matr|vert|ind|suff)ix|ex$/i'=> '$1ices',
			'/(x|ch|ss|sh)$/i' 			       => '$1es',
			'/([^aeiouy]|qu)y$/i' 	       => '$1ies',
			'/(?:([^f])fe|([lr])f)$/i' 	   => '$1$2ves',
			'/sis$/i' 					           => 'ses',
			'/([ti]|addend)um$/i' 		     => '$1a',
      '/(alumn|formul)a$/i'          => '$1ae',
			'/(buffal|tomat|her)o$/i' 	   => '$1oes',
			'/(bu)s$/i' 				           => '$1ses',
			'/(alias|status)$/i' 	         => '$1es',
			'/(octop|vir)us$/i' 		       => '$1i',
      '/(gen)us$/i'                  => '$1era',
			'/(ax|test)is$/i'	 		         => '$1es',
			'/s$/i' 					             => 's',
			'/$/' 						             => 's',
		),

		'singularization' => array(
			'/cookies$/i' 		          	=> 'cookie',
			'/moves$/i' 		              => 'move',
			'/sexes$/i' 		              => 'sex',
			'/children$/i' 	         	    => 'child',
			'/men$/i' 			              => 'man',
			'/feet$/i' 			              => 'foot',
			'/people$/i' 		              => 'person',
			'/taxa$/i' 			              => 'taxon',
			'/databases$/i'			          => 'database',
			'/(quiz)zes$/i' 		          => '\1',
			'/(matr|suff)ices$/i'         => '\1ix',
			'/(vert|ind)ices$/i'          => '\1ex',
			'/^(ox)en/i' 			            => '\1',
			'/(alias|status)es$/i' 	      => '\1',
      '/(tomato|hero|buffalo)es$/i' => '\1',
			'/([octop|vir])i$/i'          => '\1us',
      '/(gen)era$/i'                => '\1us',
		  '/(cris|^ax|test)es$/i'       => '\1is', 
			'/(shoe)s$/i' 		            => '\1',
			'/(o)es$/i' 		              => '\1',
			'/(bus)es$/i' 		            => '\1',
			'/([m|l])ice$/i' 	            => '\1ouse',
			'/(x|ch|ss|sh)es$/i'          => '\1',
			'/(m)ovies$/i' 			          => '\1ovie',
			'/(s)eries$/i' 			          => '\1eries',
			'/([^aeiouy]|qu)ies$/i'       => '\1y',
			'/([lr])ves$/i' 	            => '\1f',
			'/(tive)s$/i' 		            => '\1',
			'/(hive)s$/i' 		            => '\1',
			'/([^f])ves$/i' 	            => '\1fe',
			'/(^analy)ses$/i' 	         	=> '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/([ti]|addend)a$/i' 	=> '\1um',
     '/(alumn|formul)ae$/i' => '$1a',
			'/(n)ews$/i' 			    => '\1ews',
			'/(.*)ss$/i'          => '\1ss',       
			'/(.*)s$/i' 		      => '\1',
		),

		'countable' => array(
			'aircraft',
			'cannon',
			'deer',
			'equipment',
			'fish',
			'information',
			'money',
			'moose',
			'rice',
			'series',
			'sheep',
			'species',
			'swine',
		)
	);
	protected static $_cache = array(
		'singularized' => array(),
		'pluralized'   => array()
	);

	private function __construct() {}

	public static function addWord($singular, $plural)
	{
		self::$_cache['pluralized'][$singular]	= $plural;
		self::$_cache['singularized'][$plural] 	= $singular;
	}  
	
	public static function pluralize($word)
	{
   	if(isset(self::$_cache['pluralized'][$word]))
			return self::$_cache['pluralized'][$word];

		if(in_array($word, self::$_rules['countable'])) {
			$_cache['pluralized'][$word] = $word;
			return $word;
		}

		foreach(self::$_rules['pluralization'] as $regexp => $replacement)
		{
			$matches = null;
			$plural = preg_replace($regexp, $replacement, $word, -1, $matches);
			
			if($matches > 0) {
				$_cache['pluralized'][$word] = $plural;
				return $plural;
			}
		}

		return $word;
	}  
	
	public static function singularize($word)
	{
   	if(isset(self::$_cache['singularized'][$word]))
  		return self::$_cache['singularized'][$word];   

		if(in_array($word, self::$_rules['countable'])) {
			$_cache['singularized'][$word] = $word;
			return $word;
		}     
		
		foreach (self::$_rules['singularization'] as $regexp => $replacement)
		{
			$matches = null;
			$singular = preg_replace($regexp, $replacement, $word, -1, $matches);
			
			if($matches > 0) {
				$_cache['singularized'][$word] = $singular;
				return $singular;
			}
		}

 	   return $word;
	} 
	
	public static function camelize($word)
	{
		$word = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $word);
		$word = str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $word))));
		return $word;
	} 
	
	public static function underscore($word)
	{
		$word = preg_replace('/(\s)+/', '_', $word);
		$word = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
		return $word;
	}
	
	public static function explode($word)
	{
		$result = explode('_', self::underscore($word));
		return $result;
	}

	public static function implode($words)
	{
		$result = self::camelize(implode('_', $words));
		return $result;
	}

	public static function humanize($word)
	{
		$result = ucwords(strtolower(str_replace("_", " ", $word)));
		return $result;
	}
	
	public static function tableize($className)
	{
		$result = self::underscore($className);

		if(!self::isPlural($className))
  		$result = self::pluralize($result);
		return $result;
	}  
	
	public static function classify($tableName)
	{
		$result = self::camelize(self::singularize($tableName));
		return $result;
	}
	
	public static function variablize($string)
	{
		$string   = self::camelize(self::underscore($string));
		$result   = strtolower(substr($string, 0, 1));
		$variable = preg_replace('/\\w/', $result, $string, 1);
		return $variable;
	}   
	
	public static function isSingular($string) 
	{
		$singular = isset(self::$_cache['singularized'][$string]) ? self::$_cache['singularized'][$string] : null;
		$plural   = $singular && isset(self::$_cache['pluralized'][$singular]) ? self::$_cache['pluralized'][$singular] : null;
		
		if($singular && $plural)
			return $plural != $string;
		
		return self::singularize(self::pluralize($string)) == $string;
	}   
	
	public static function isPlural($string) 
	{
		$plural   = isset(self::$_cache['pluralized'][$string]) ? self::$_cache['pluralized'][$string] : null;
		$singular = $plural && isset(self::$_cache['singularized'][$plural]) ? self::$_cache['singularized'][$plural] : null;
		
		if($plural && $singular)
			return $singular != $string;
		
		return self::pluralize(self::singularize($string)) == $string;
	}
	
	public static function getPart($string, $index, $default = null)
  {
    $parts = self::explode($string);

    if($index < 0) 
      $index = count($parts) + $index;

    return isset($parts[$index]) ? $parts[$index] : $default;  
  }
}