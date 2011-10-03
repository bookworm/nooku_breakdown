<?php

KRequest::instantiate();

class KRequest
{
  protected static $_url = null;
  protected static $_base = null;
  protected static $_root = null;
  protected static $_referrer = null;
  protected static $_content = null;
  protected static $_accept = null;     
  
  final private function __construct(KConfig $config)
  {
    $content = self::content();

    if(self::type() == 'HTTP')
    {
      $authorization = KRequest::get('server.HTTP_AUTHORIZATION', 'url');
      if(strstr($authorization,"Basic"))
      {
        $parts = explode(':',base64_decode(substr($authorization, 6)));

        if(count($parts) == 2) {
          KRequest::set('server.PHP_AUTH_USER', $parts[0]);
          KRequest::set('server.PHP_AUTH_PW'  , $parts[1]);
        } 
      }     
    }

    if(!empty($content['data']))
    {
      if($content['type'] == 'application/x-www-form-urlencoded')
      {
        if(in_array(self::method(), array('PUT', 'DELETE'))) {
          parse_str($content['data'], $GLOBALS['_'.self::method()]);
          $GLOBALS['_REQUEST'] = array_merge($GLOBALS['_REQUEST'],  $GLOBALS['_'.self::method()]);
        }
      }

      if($content['type'] == 'application/json')
      {
        if(in_array(self::method(), array('POST', 'PUT', 'DELETE'))) {
          $GLOBALS['_'.self::method()] = json_decode($content['data'], true);
          $GLOBALS['_REQUEST'] = array_merge($GLOBALS['_REQUEST'],  $GLOBALS['_'.self::method()]);
        }  
      } 
    }  
  }  
  
  final private function __clone() { }
  
  public static function instantiate($config = array())
  {
    static $instance;

    if($instance === NULL) {
      if(!$config instanceof KConfig) $config = new KConfig($config);
      $instance = new self($config);
    }

    return $instance; 
  } 
  
  public static function get($identifier, $filter, $default = null)
  {
    list($hash, $keys) = self::_parseIdentifier($identifier);

    $result = null;
    if(isset($GLOBALS['_'.$hash]))
    {
      $result = $GLOBALS['_'.$hash];
      foreach($keys as $key)
      {
        if(array_key_exists($key, $result))
          $result = $result[$key];
        else {
          $result = null;
          break;
        }
      }  
    }

    if(is_null($result)) return $default;

    if (get_magic_quotes_gpc() && !in_array($hash, array('FILES', 'SESSION'))) 
      $result = self::_stripSlashes( $result );

    if(!($filter instanceof KFilterInterface)) 
      $filter = KFilter::factory($filter);

    return $filter->sanitize($result); 
  }
  
  public static function set($identifier, $value)
  {
    list($hash, $keys) = self::_parseIdentifier($identifier);

    if(in_array($hash, array('GET', 'POST', 'COOKIE'))) 
      self::set('request.'.implode('.', $keys), $value);

    if($hash == 'COOKIE')
    {
      $ckeys = $keys; 
      $name = array_shift($ckeys);       
      
      foreach($ckeys as $ckey) {
        $name .= '['.$ckey.']';
      }

      if(!setcookie($name, $value))
        throw new KRequestException("Couldn't set cookie, headers already sent."); 
    }

    foreach(array_reverse($keys, true) as $key) {
      $value = array($key => $value);
    }
  
    if(!isset($GLOBALS['_'.$hash])) { 
      $GLOBALS['_'.$hash] = array(); 
    } 

    $GLOBALS['_'.$hash] = KHelperArray::merge($GLOBALS['_'.$hash], $value);  
  }      
  
  public static function has($identifier)
  {
    list($hash, $keys) = self::_parseIdentifier($identifier);

    foreach($keys as $key) {
      if(array_key_exists($key, $GLOBALS['_'.$hash])) return true;
    }

    return false;
  }   
  
  public static function content($key = null)
  {
    $result = '';

    if(!isset(self::$_content) && isset($_SERVER['CONTENT_TYPE']))
    {
      $type = $_SERVER['CONTENT_TYPE'];

      if(is_string($type)) {
        if (preg_match('/^([^,\;]*)/', $type, $matches)) 
          $type = $matches[1];
      }

      self::$_content['type'] = $type;

      $data = '';
      if(isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0)
      {
        $input = fopen('php://input', 'r');
        while ($chunk = fread($input, 1024)) {
          $data .= $chunk;
        }

        fclose($input);    
      }

      self::$_content['data'] = $data;     
    }

    return isset($key) ? self::$_content[$key] : self::$_content;  
  }   
  
  public static function accept($type = null)
  {
    if(!isset(self::$_accept) && isset($_SERVER['HTTP_ACCEPT']))
    {
      $accept = KRequest::get('server.HTTP_ACCEPT', 'string');
      self::$_accept['format'] = self::_parseAccept($accept);

      if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        $accept = KRequest::get('server.HTTP_ACCEPT_ENCODING', 'string');
        self::$_accept['encoding'] = self::_parseAccept($accept);
      }

      if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $accept = KRequest::get('server.HTTP_ACCEPT_LANGUAGE', 'string');
        self::$_accept['language'] = self::_parseAccept($accept);
      } 
    }

    return $type ? self::$_accept[$type] : self::$_accept;     
  }    
  
  public static function client()
  {
    return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
  }  
  
  public static function referrer($isInternal = true)
  {
    if(!isset(self::$_referrer))
    {
      if($referrer = KRequest::get('server.HTTP_REFERER', 'url'))
      {
        self::$_referrer = KFactory::get('lib.koowa.http.url', array('url' => $referrer));

        if($isInternal) {
          if(!KFactory::get('lib.koowa.filter.internalurl')->validate((string)self::$_referrer))
            return null;
        }  
      }
    }

    return self::$_referrer;    
  }         
  
  public static function url()
  {
    if(!isset(self::$_url))
    {
       if(!empty ($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI']))
         $url = self::protocol().'://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
       else
       {
        $url = self::protocol().'://'. $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

        if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) 
          $url .= '?' . $_SERVER['QUERY_STRING'];        
       }

       $url = KFactory::get('lib.koowa.filter.url')->sanitize($url);
       self::$_url = KFactory::tmp('lib.koowa.http.url', array('url' => $url));
    }

    return self::$_url; 
  } 
  
  public static function base()
  {
    if(!isset(self::$_base))
    {
      if(strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo')  && !empty($_SERVER['REQUEST_URI']))   
        $path = $_SERVER['PHP_SELF'];
      else $path = $_SERVER['SCRIPT_NAME'];
  
      $path = rtrim(dirname($path), '/\\');
      $path = KFactory::get('lib.koowa.filter.url')->sanitize($path);  
      
      self::$_base = KFactory::tmp('lib.koowa.http.url', array('url' => $path));
    }

    return self::$_base; 
  }      
  
  public static function root($path = null)
  {
    if(!is_null($path))
    {
      if(!$path instanceof KhttpUrl)
        $path = KFactory::tmp('lib.koowa.http.url', array('url' => $path));

      self::$_root = $path;
    }

    if(is_null(self::$_root))
      self::$_root = self::$_base;
      
    return self::$_root;   
  }       
  
  public static function protocol()
  {
    if (PHP_SAPI === 'cli') return NULL;

    if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) 
      return 'https';
    else 
      return 'http';    
  }        
  
  public static function method()
  {
    $method = '';

    if(PHP_SAPI != 'cli')
    {
      $method  =  strtoupper($_SERVER['REQUEST_METHOD']);

      if($method == 'POST')
      {
        if(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) 
          $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);

        if(self::has('post._method')) 
          $method = strtoupper(self::get('post._method', 'cmd'));   
      } 
    }

    return $method; 
  }  
  
  public static function type()
  {
    $type = 'HTTP';

    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
      $type = 'AJAX';
    if( isset($_SERVER['HTTP_X_FLASH_VERSION'])) 
      $type = 'FLASH';
    if(preg_match('/^(Shockwave|Adobe) Flash/', KRequest::client()) == 1) 
      $type = 'FLASH';

    return $type; 
  }         
  
  public static function token()
  {
    $token = null;

    if(self::has('server.HTTP_X_TOKEN')) 
      $token = self::get('server.HTTP_X_TOKEN', 'md5');

    if(self::has('request._token')) 
      $token = self::get('request._token', 'md5');

    return $token;          
  }    
  
  public static function format()
  {
    $format = null;

    if(count(self::accept('format')) == 1)
    {
      $mime   = explode('/', key(self::accept('format')));
      $format = $mime[1];

      if($pos = strpos($format, '+')) $format = substr($format, 0, $pos);

      if($format == '*') $format = null;      
    }

    if(!empty(self::url()->format) && self::url()->format != 'php') 
      $format = self::url()->format;

    if(self::has('request.format')) 
      $format = self::get('request.format', 'word');

    return $format;    
  }
  
  protected static function _parseIdentifier($identifier)
  {
    $parts = array();
    $hash  = $identifier;

    if(strpos($identifier, '.') !== false) {
      $parts = explode('.', $identifier);
      $hash   = array_shift($parts); 
    }

    $hash = strtoupper($hash);

    return array($hash, $parts);  
  }   
  
  protected static function _parseAccept( $accept, array $defaults = NULL)
  {
    if (!empty($accept))
    {
      $types = explode(',', $accept);

      foreach ($types as $type)
      {
        $parts = explode(';', $type);
        $type = trim(array_shift($parts));
        $quality = 1.0;

        foreach ($parts as $part)
        {
          if (strpos($part, '=') === FALSE) continue;
            
          list ($key, $value) = explode('=', trim($part));

          if ($key === 'q')
            $quality = (float) trim($value);    
        }

        $defaults[$type] = $quality; 
      }  
    }

    $accepts = (array) $defaults;

    arsort($accepts);
    return $accepts;     
  }           
  
  protected static function _stripSlashes( $value )
  {
    if(!is_object($value))
      $value = is_array( $value ) ? array_map( array( 'KRequest', '_stripSlashes' ), $value ) : stripslashes( $value );

    return $value;
  }
}