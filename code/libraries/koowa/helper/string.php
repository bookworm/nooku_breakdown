<?php

if(extension_loaded('mbstring') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('mbstring.so'))))
{
  @ini_set('mbstring.internal_encoding', 'UTF-8');
  @ini_set('mbstring.http_input', 'UTF-8');
  @ini_set('mbstring.http_output', 'UTF-8');   
}                                                   

if(function_exists('iconv') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('iconv.so'))))
{
  iconv_set_encoding("internal_encoding", "UTF-8");
  iconv_set_encoding("input_encoding", "UTF-8");
  iconv_set_encoding("output_encoding", "UTF-8");   
}

class KHelperString
{
  public static function strpos($str, $search, $offset = FALSE)
  {
    if(strlen($str) && strlen($search))
    {
      if($offset === FALSE)
        return mb_strpos($str, $search);
      else 
        return mb_strpos($str, $search, $offset);
    } 
    else return FALSE;      
  }  
  
  public static function strrpos($str, $search)
  {
    if($offset === FALSE)
    {
      if(empty($str)) return FALSE;
      return mb_strrpos($str, $search);
    }
    else
    {
      if(!is_int($offset)) {
        trigger_error('utf8_strrpos expects parameter 3 to be long',E_USER_WARNING);
        return FALSE;
      }

      $str = mb_substr($str, $offset);

      if(FALSE !== ($pos = mb_strrpos($str, $search)))
        return $pos + $offset;

      return FALSE;
    }      
  }   
  
  public static function substr($str, $offset, $length = FALSE)
  {
    if($length === FALSE)
      return mb_substr($str, $offset);
    else
      return mb_substr($str, $offset, $length);
  }
     
  public static function strtolower($str)
  {
    return mb_strtolower($str);
  }    
  
  public static function strtoupper($str)
  {
    return mb_strtoupper($str);
  }

  public static function strlen($str)
  {
    return mb_strlen($str);
  }       
  
  public static function str_ireplace($search, $replace, $str, $count = NULL)
  {
    if(!is_array($search))
    {
      $slen   = strlen($search);
      $lendif = strlen($replace) - $slen;
      if ($slen == 0 ) return $str;

      $search = KHelperString::strtolower($search);

      $search = preg_quote($search, '/');
      $lstr = KHelperString::strtolower($str);
      $i = 0;
      $matched = 0;
      while (preg_match('/(.*)'.$search.'/Us',$lstr, $matches)) 
      {
        if($i === $count) break;
        $mlen = strlen($matches[0]);
        $lstr = substr($lstr, $mlen);
        $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
        $matched += $mlen + $lendif;
        $i++;  
      }
      return $str;

    } 
    else 
    {
      foreach(array_keys($search) as $k)
      {
        if(is_array($replace))
        {
          if(array_key_exists($k,$replace)) 
            $str = KHelperString::str_ireplace($search[$k], $replace[$k], $str, $count);
          else
            $str = KHelperString::str_ireplace($search[$k], '', $str, $count);
        } 
        else 
          $str = KHelperString::str_ireplace($search[$k], $replace, $str, $count);
      }   
      return $str;
    }
  }     
  
  public static function str_split($str, $split_len = 1)
  {
    if(!preg_match('/^[0-9]+$/',$split_len) || $split_len < 1) 
      return FALSE;


    $len = KHelperString::strlen($str);
    if($len <= $split_len )
      return array($str);

    preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
    return $ar[0];
  }
  
  public static function strcasecmp($str1, $str2)
  {
    $strX = KHelperString::strtolower($strX);
    $strY = KHelperString::strtolower($strY);
    return strcmp($strX, $strY);   
  }     
  
  public static function strcspn($str, $mask, $start = NULL, $length = NULL)
  {
    if(empty($mask) || strlen($mask) == 0)
      return NULL;

    $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);

    if($start !== NULL || $length !== NULL)
      $str = KHelperString::substr($str, $start, $length);

    preg_match('/^[^'.$mask.']+/u',$str, $matches);

    if(isset($matches[0])) 
      return utf8_strlen($matches[0]);

    return 0;  
  }     
  
  public static function stristr($str, $search)
  {
    if(strlen($search) == 0)
      return $str;

    $lstr = KHelperString::strtolower($str);
    $lsearch = KHelperString::strtolower($search);
    preg_match('|^(.*)'.preg_quote($lsearch).'|Us',$lstr, $matches);

    if(count($matches) == 2) 
      return substr($str, strlen($matches[1]));

    return FALSE; 
  }          
  
  public static function strrev($str)
  {
    preg_match_all('/./us', $str, $ar);
    return join('',array_reverse($ar[0]));      
  }  
  
  public static function strspn($str, $mask, $start = NULL, $length = NULL)
  {
    $mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);

    if($start !== NULL || $length !== NULL)
      $str = KHelperString::substr($str, $start, $length);

    preg_match('/^['.$mask.']+/u',$str, $matches);

    if(isset($matches[0]))
      return KHelperString::strlen($matches[0]);

    return 0;      
  }   
  
  public static function substr_replace($str, $repl, $start, $length = NULL)
  {
    preg_match_all('/./us', $str, $ar);
    preg_match_all('/./us', $repl, $rar);
    if($length === NULL)
        $length = KHelperString::strlen($str);  
      
    array_splice( $ar[0], $start, $length, $rar[0] );
    return join('',$ar[0]);       
  }    
  
  public static function ltrim($str, $charlist = FALSE)
  {
    if($charlist === FALSE) return ltrim($str);

    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);

    return preg_replace('/^['.$charlist.']+/u','',$str);    
  }
  
  public static function rtrim($str, $charlist = FALSE)
  {
    if($charlist === FALSE)
      return rtrim($str);

    $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);

    return preg_replace('/['.$charlist.']+$/u','',$str);  
  }   
  
  public static function trim($str, $charlist = FALSE)
  {
    if($charlist === FALSE)
      return trim($str);

    return KHelperString::ltrim(utf8_rtrim($str, $charlist), $charlist); 
  }     
  
  public static function ucfirst($str)
  {
    switch(KHelperString::strlen($str))
    {
      case 0:
        return '';
        break;
      case 1:
        return KHelperString::strtoupper($str);
        break;
      default:
        preg_match('/^(.{1})(.*)$/us', $str, $matches);
        return KHelperString::strtoupper($matches[1]).$matches[2];
        break;  
    }  
  }  
  
  public static function ucwords($str)
  {
    $pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';
    return preg_replace_callback($pattern, 'KHelperString::ucwords_callback',$str);  
  }    
  
  public static function ucwords_callback($matches)
  {
    $leadingws = $matches[2];
    $ucfirst = KHelperString::strtoupper($matches[3]);
    $ucword = KHelperString::substr_replace(ltrim($matches[0]),$ucfirst,0,1);
    return $leadingws . $ucword;  
  }
  
  public static function transcode($source, $from_encoding, $to_encoding)
  {
    if(is_string($source))
      return iconv($from_encoding, $to_encoding.'//TRANSLIT', $source);
  } 
  
  public static function valid($str)
  {
    $mState = 0;     
    $mUcs4  = 0;     
    $mBytes = 1;     

    $len = strlen($str);

    for($i = 0; $i < $len; $i++)
    {
      $in = ord($str{$i});

      if($mState == 0)
      {
          if(0 == (0x80 & ($in))) {
            $mBytes = 1;
          } 
          else if(0xC0 == (0xE0 & ($in))) 
          {
            $mUcs4 = ($in);
            $mUcs4 = ($mUcs4 & 0x1F) << 6;
            $mState = 1;
            $mBytes = 2;
          }
          else if(0xE0 == (0xF0 & ($in))) 
          {
            $mUcs4 = ($in);
            $mUcs4 = ($mUcs4 & 0x0F) << 12;
            $mState = 2;
            $mBytes = 3;
          } 
          else if(0xF0 == (0xF8 & ($in)))
          {
            $mUcs4 = ($in);
            $mUcs4 = ($mUcs4 & 0x07) << 18;
            $mState = 3;
            $mBytes = 4; 
          } 
          else if (0xF8 == (0xFC & ($in))) 
          {
            $mUcs4 = ($in);
            $mUcs4 = ($mUcs4 & 0x03) << 24;
            $mState = 4;
            $mBytes = 5;
          } 
          else if(0xFC == (0xFE & ($in))) 
          {
            $mUcs4 = ($in);
            $mUcs4 = ($mUcs4 & 1) << 30;
            $mState = 5;
            $mBytes = 6; 
          } 
          else {
            return FALSE;
          }
      }
      else
      {
        if(0x80 == (0xC0 & ($in)))
        {
          $shift = ($mState - 1) * 6;
          $tmp = $in;
          $tmp = ($tmp & 0x0000003F) << $shift;
          $mUcs4 |= $tmp;
          
          if(0 == --$mState)
          {
            if(((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
              ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
              ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
              (4 < $mBytes) ||
              (($mUcs4 & 0xFFFFF800) == 0xD800) ||
              ($mUcs4 > 0x10FFFF))    
            {
              return FALSE;
            }     

            $mState = 0;
            $mUcs4  = 0;
            $mBytes = 1; 
          }
        }
        else {
          return FALSE;
        }  
      }
    }
    return TRUE;  
  }           
  
  public static function compliant($str)
  {
    if(strlen($str) == 0)
      return TRUE;
    return (preg_match('/^.{1}/us',$str,$ar) == 1);    
  }       
}