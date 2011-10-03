<?php

define('DATE_FORMAT_ISO', 1);
define('DATE_FORMAT_ISO_BASIC', 2);
define('DATE_FORMAT_ISO_EXTENDED', 3);
define('DATE_FORMAT_ISO_EXTENDED_MICROTIME', 6);
define('DATE_FORMAT_TIMESTAMP', 4);
define('DATE_FORMAT_UNIXTIME', 5);
define('SECONDS_IN_HOUR', 3600);
define('SECONDS_IN_DAY', 86400);        

class KDate extends KObject
{
  public $year;
  public $month;
  public $day;
  public $hour;
  public $minute;
  public $second;
  public $partsecond; 
  
  public function __construct( KConfig $config = null)
  { 
    if(!isset($config)) $config = new KConfig();
  
    parent::__construct($config);

    if($config->date instanceof KDate)
      $this->copy( $config->date );
    else 
      $this->setDate( $config->date );  
      
  }    
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'date'  => date( 'Y-m-d H:i:s' )
    ));

    parent::_initialize($config);  
  } 
  
  public function setDate( $date, $format = DATE_FORMAT_ISO )
  {
    $regex = '/^(\d{4})-?(\d{2})-?(\d{2})([T\s]?(\d{2}):?(\d{2}):?(\d{2})(\.\d+)?(Z|[\+\-]\d{2}:?\d{2})?)?$/i';

    if (preg_match($regex, $date, $regs) && $format != DATE_FORMAT_UNIXTIME)
    {
      $this->year       = $regs[1];
      $this->month      = $regs[2];
      $this->day        = $regs[3];
      $this->hour       = isset( $regs[5] ) ? $regs[5] : 0;
      $this->minute     = isset( $regs[6] ) ? $regs[6] : 0;
      $this->second     = isset( $regs[7] ) ? $regs[7] : 0;
      $this->partsecond = (float) isset( $regs[8] ) ? $regs[8] : 0;
    }
    elseif(is_numeric($date)) {
      $this->setDate( date( 'Y-m-d H:i:s', $date ) );
    }
    else
    {
      $this->year       = 0;
      $this->month      = 1;
      $this->day        = 1;
      $this->hour       = 0;
      $this->minute     = 0;
      $this->second     = 0;
      $this->partsecond = (float)0;
    }

    return $this; 
  }  
  
  public function getDate($format = DATE_FORMAT_ISO)
  {
    switch ($format)
    {
      case DATE_FORMAT_ISO:
       return $this->format( '%Y-%m-%d %T' );
       break;

      case DATE_FORMAT_ISO_BASIC:
       $format = '%Y%m%dT%H%M%S';
       return $this->format($format);
       break;

      case DATE_FORMAT_ISO_EXTENDED:
       $format = '%Y-%m-%dT%H:%M:%S';
       return $this->format($format);
       break;

      case DATE_FORMAT_ISO_EXTENDED_MICROTIME:
       $format = '%Y-%m-%dT%H:%M:%s';
       return $this->format($format);
       break;

      case DATE_FORMAT_TIMESTAMP:
       return $this->format( '%Y%m%d%H%M%S' );
       break;

      case DATE_FORMAT_UNIXTIME:
       return mktime( $this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year );
       break;

      default:
       return $this->format( $format );
       break;    
    }       
  }  
 
  public function copy($date)
  {
    $this->year   = $date->year;
    $this->month  = $date->month;
    $this->day    = $date->day;
    $this->hour   = $date->hour;
    $this->minute = $date->minute;
    $this->second = $date->second; 
  }  
  
  public function format($format)
  {
    $timestamp = mktime( $this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year );
    return strftime( $format, $timestamp );    
  }

  public function getTimestamp()
  {
    $timestamp = mktime( $this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year );
    return $timestamp;  
  }    
  
  public function year($value = null)
  {
    if($value !== null)
    {
      if($value < 0 || $value > 9999)
        $this->year = 0;
      else 
        $this->year = $value;   
    }
    return $this->year; 
  } 
  
  public function month($value = null)
  {
    if($value !== null)
    {
      if ($value < 1 || $value > 12) 
        $this->month = 1;
      else
        $this->month = $value;    
    }
    return $this->month;    
  }   
  
  public function day($value = null)
  {
    if($value !== null)
    {
      if($value > 31 || $value < 1)
        $this->day = 1;
      else 
        $this->day = $value;  
    }
    return $this->day;  
  }  
  
  public function hour($value = null)
  {
    if($value !== null)
    {
      if($value > 23 || $value < 0)
        $this->hour = 0;
      else
        $this->hour = $value; 
    }
    return $this->hour;    
  }
  
  public function minute($value = null)
  {
    if($value !== null)
    {
      if($value > 59 || $value < 0)
        $this->minute = 0;
      else 
        $this->minute = $value;
    }
    return $this->minute;    
  }
  
  public function second($value = null)
  {
    if($value !== null)
    {
      if($value > 59 || $value < 0)
        $this->second = 0;
      else
        $this->second = $value;  
    }
    return $this->second;
  }  
  
  public function addYears($n)
  {
    $this->year += $n;
    return $this; 
  }
  
  public function addMonths($n)
  {
    $an     = abs( $n );
    $years  = floor( $an / 12 );
    $months = $an % 12;

    if($n < 0)
    {
      $this->year  -= $years;
      $this->month -= $months;
      if($this->month < 1) {
        $this->year--;
        $this->month = 12 + $this->month; 
      }
    }
    else
    {
      $this->year  += $years;
      $this->month += $months;
      if($this->month > 12) {
        $this->year++;
        $this->month -= 12; 
      }
    }

    return $this;
  } 
  
  public function addDays($n)
  {
    $this->setDate($this->getTimestamp() + SECONDS_IN_DAY * $n, DATE_FORMAT_UNIXTIME);
    return $this;  
  }

  public function addHours($n)
  {
    $this->setDate($this->getTimestamp() + SECONDS_IN_HOUR * $n, DATE_FORMAT_UNIXTIME);
    return $this;   
  }
  
  public function addMinutes( $n )
  {
    $this->setDate($this->getTimestamp() + 60 * $n, DATE_FORMAT_UNIXTIME);
    return $this;
  }

  public function addSeconds( $n )
  {
    $this->setDate($this->getTimestamp() + $n, DATE_FORMAT_UNIXTIME);
    return $this;
  }     
  
  public function toDays(KDate $date = null)
  {
    $year  = isset($date) ? $date->year  : $this->year;
    $month = isset($date) ? $date->month : $this->month;
    $day   = isset($date) ? $date->day   : $this->day;

    $century = (int) substr( $year, 0, 2 );
    $year    = (int) substr( $year, 2, 2 );

    if($month > 2) {
      $month -= 3;
    } 
    else 
    {
      $month += 9;
      if($year) {
        $year--;
      } 
      else {
        $year = 99;
        $century--; 
      }
    }

    return (
      floor( (146097 * $century) / 4 ) +
      floor( (1461 * $year) / 4 ) +
      floor( (153 * $month + 2) / 5 ) +
      $day + 1721119);   
  }      
  
  public function getDayOfWeek(KDate $date = null)
  {
    $year   = isset($date) ? $date->year  : $this->year;
    $month  = isset($date) ? $date->month : $this->month;
    $day    = isset($date) ? $date->day   : $this->day;

    if($month > 2) {
      $month -= 2;
    } 
    else {
      $month += 10;
      $year--;
    }  

    $day = (floor((13 * $month - 1) / 5) +
      $day + ($year % 100) +
      floor(($year % 100) / 4) +
      floor(($year / 100) / 4) - 2 *
      floor($year / 100) + 77);   

    $weekday_number = $day - 7 * floor($day / 7);
    return $weekday_number;       
  }  
  
  public static function getWeekdayFullname($day = null)
  {
    if($day === null) {
      $day = new KDate();
    }
    if($day instanceof KDate) {
      $weekday = self::getDayOfWeek( $day );
    } 
    elseif(is_int( $day )) {
      $weekday = $day;
    }
    $names = self::getWeekDays();
    return $names[$weekday]; 
  }      
  
  public static function getWeekdayAbbrname($day, $length = 3)
  {
    return substr(self::getWeekdayFullname($day), 0, $length);
  } 
  
  public static function getMonthFullname($month)
  {
    $month = (int) $month;
    $names = self::getMonthNames();
    return $names[$month];         
  } 
  
  public static function getMonthAbbrname($month, $length = 3)
  {
    $month = (int) $month;
    return substr(self::getMonthFullname($month), 0, $length);
  }   
  
  public static function getMonthNames()
  {
    static $months;
    if(!isset($months))
    {
      $months = array();
      for($i = 1; $i < 13; $i++) {
        $months[$i] = strftime('%B', mktime(0, 0, 0, $i, 1, 2001));
      }
    }
    return $months; 
  }   
  
  public static function getWeekDays()
  {
    static $weekdays = null;
    if ($weekdays == null)
    {
      $weekdays   = array();
      for($i = 0; $i < 7; $i++) {
        $weekdays[$i] = strftime('%A', mktime(0, 0, 0, 1, $i, 2001));
      }  
    }
    return $weekdays;  
  } 
  
  public static function getDaysInMonth($month, $year)
  {
    if($year == 1582 && $month == 10) 
      return 21; 

    if($month == 2) 
    {
      if(self::isLeapYear($year))
        return 29;
      else 
        return 28;
    } 
    elseif ($month == 4 or $month == 6 or $month == 9 or $month == 11) {
      return 30;
    } 
    else {
      return 31;
    }     
  }   
  
  public static function isLeapYear($year)
  {
    if(preg_match('/\D/', $year))
      return false;
    if($year < 1000)
      return false;
    if($year < 1582)
      return ($year % 4 == 0);
    else 
      return (($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0);
  }   
  
  public static function isToday(KDate $date)
  {
    static $today;
    if(!isset($today))
      $today  = new KDate;
    return ($today->day == $date->day && $today->month == $date->month && $today->year == $date->year); 
  }        
}