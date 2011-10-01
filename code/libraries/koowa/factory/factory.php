<?php 

KFactory::instantiate();

class KFactory
{
  protected static $_registry = null;
  protected static $_chain = null;
  protected static $_identifier_map = array();
  protected static $_mixin_map = array();  
  
  final private function __construct(KConfig $config) 
	{ 
		self::$_registry = new ArrayObject();
    self::$_chain    = new KFactoryChain();
	}        
	
	final private function __clone() { }
	
	public static function instantiate($config = array())
	{
		static $instance;
		
		if ($instance === NULL) 
		{
  		if(!$config instanceof KConfig) $config = new KConfig($config);
  		$instance = new self($config);    
		} 
		
		return $instance;
	}         
	
	public static function identify($identifier)
	{		
		if(!is_string($identifier)) {
			if($identifier instanceof KObjectIdentifiable) 
  			$identifier = $identifier->getIdentifier();
		} 
		
		$alias = (string) $identifier;
		if(array_key_exists($alias, self::$_identifier_map))
  		$identifier = self::$_identifier_map[$alias];
		
		if(is_string($identifier)) 
  		$identifier = new KIdentifier($identifier);
		
		return $identifier;
	}  
	
	public static function get($identifier, array $config = array())
	{
		$objIdentifier = self::identify($identifier);
		$strIdentifier = (string) $objIdentifier;
		
		if(!self::$_registry->offsetExists($strIdentifier))
		{
			$instance = self::_instantiate($objIdentifier, $config);
		
			self::_mixin($strIdentifier, $instance);
			self::$_registry->offsetSet($strIdentifier, $instance);
		}
		
		return self::$_registry->offsetGet($strIdentifier);
	}     
	
	public static function tmp($identifier, array $config = array())
	{
		$objIdentifier = self::identify($identifier);
		$strIdentifier = (string) $objIdentifier;
	          
		$instance = self::_instantiate($objIdentifier, $config);   
		
		self::_mixin($strIdentifier, $instance);    

		return $instance;
	}  
	
	public static function set($identifier, $object)
	{
		$objIdentifier = self::identify($identifier);
		$strIdentifier = (string) $objIdentifier;
		
		self::$_registry->offsetSet($strIdentifier, $object);
	} 
	
	public static function del($identifier)
	{
		$objIdentifier = self::identify($identifier);
		$strIdentifier = (string) $objIdentifier;

		if(self::$_registry->offsetExists($strIdentifier)) {
			self::$_registry->offsetUnset($strIdentifier);
			return true;
		}

		return false;
	}  
	
	public static function has($identifier)
	{
    try 
    {
      $objIdentifier = self::identify($identifier);
      $strIdentifier = (string) $objIdentifier;
      $result = (bool) self::$_registry->offsetExists($strIdentifier);  
    } 
    catch (KIdentifierException $e) {
      $result = false;
    }

    return $result; 
	}   
	
	public static function map($alias, $identifier)
	{		
		$identifier = self::identify($identifier);
		
		self::$_identifier_map[$alias] = $identifier;
	}    
	
	
	public static function mix($identifiers, $mixins)
  {
    settype($identifiers, 'array');
    settype($mixins,      'array');

    foreach($identifiers as $identifier) 
    {
      $objIdentifier = self::identify($identifier);
      $strIdentifier = (string) $objIdentifier;
  
      if (!isset(self::$_mixin_map[$strIdentifier])) 
        self::$_mixin_map[$strIdentifier] = array();

      self::$_mixin_map[$strIdentifier] = array_unique(array_merge(self::$_mixin_map[$strIdentifier], $mixins));

      if (self::$_registry->offsetExists($strIdentifier)) {
        $instance = self::$_registry->offsetGet($strIdentifier);
        self::_mixin($strIdentifier, $instance);
      }
    }  
  }  
  
  public static function addAdapter(KFactoryAdapterInterface $adapter)
  {
    self::$_chain->enqueue($adapter);
  } 
  
  protected static function _mixin($identifier, $instance)
  {
    if(isset(self::$_mixin_map[$identifier]) && $instance instanceof KObject)
    {
      $mixins = self::$_mixin_map[$identifier];
      foreach($mixins as $mixin) {
        $mixin = KFactory::tmp($mixin, array('mixer'=> $instance));
        $instance->mixin($mixin);
      }
    }    
  }  
  
  protected static function _instantiate($identifier, array $config = array())
  {                 
    $context = self::$_chain->getContext();
    $config  = new KConfig($config);
    $context->config = $config;
  
    $result = self::$_chain->run($identifier, $context);   
  
    if(is_string($result)) 
    {
      $identifier->classname = $result;
    
      $identifier->filepath = KLoader::path($identifier);
    
      if(array_key_exists('KObjectIdentifiable', class_implements($identifier->classname))) 
        $config->identifier = $identifier;
                    
      if(is_callable(array($identifier->classname, 'instantiate'), false)) 
        $result = call_user_func(array($identifier->classname, 'instantiate'), $config);
      else 
        $result = new $identifier->classname($config);
            
    }
  
    if(!is_object($result)) 
      throw new KFactoryException('Cannot create object from identifier : '.$identifier);
          
    return $result;         
  }
}