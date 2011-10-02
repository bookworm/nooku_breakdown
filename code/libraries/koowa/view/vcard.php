<?php 

class KViewVcard extends KViewFile
{
  protected $_properties;
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'mimetype' => 'text/x-vcard',
    ));

    parent::_initialize($config);  
  }   
  
  public function display()
  {
    $filename = KFactory::tmp('lib.koowa.filter.filename')->sanitize($this->_properties['FN']);
    $this->filename = $filename.'.vcf';

    $data  = 'BEGIN:VCARD';
    $data .= "\r\n";
    $data .= 'VERSION:2.1';
    $data .= "\r\n";

    foreach($this->_properties as $key => $value) {
      $data   .= "$key:$value";
      $data   .= "\r\n";  
    }

    $data .= 'REV:'. date( 'Y-m-d' ) .'T'. date( 'H:i:s' ). 'Z';
    $data .= "\r\n";
    $data .= 'END:VCARD';
    $data .= "\r\n";

    $this->output = $data;

    parent::display(); 
  }  
  
  public function setName($family = '', $first = '', $additional = '', $prefix = '', $suffix = '') 
  {
    $this->_properties["N"]     = "$family;$first;$additional;$prefix;$suffix";
    $this->setFormattedName( trim( "$prefix $first $additional $family $suffix" ) );
    return $this;                
  }    
  
  public function setFormattedName($name) 
  {
    $this->_properties['FN'] = $this->_quoted_printable_encode($name);
    return $this; 
  }  
  
  public function setOrg($org) 
  {
    $this->_properties['ORG'] =  trim( $org );
    return $this;
  }   
  
  public function setTitle( $title ) 
  {
    $this->_properties['TITLE'] = trim( $title );
    return $this;  
  } 
  
  public function setRole( $role ) 
  {
    $this->_properties['ROLE'] = trim( $role );
    return $this;
  }
  
  public function setPhoneNumber($number, $type = 'PREF;WORK;VOICE') 
  {
    $this->_properties['TEL;'.$type] = $number;
    return $this;  
  }  
  
  public function setAddress($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = 'WORK;POSTAL') 
  {
    $data = $this->_encode( $postoffice );
    $data .= ';' . $this->_encode( $extended );
    $data .= ';' . $this->_encode( $street );
    $data .= ';' . $this->_encode( $city );
    $data .= ';' . $this->_encode( $region);
    $data .= ';' . $this->_encode( $zip );
    $data .= ';' . $this->_encode( $country );

    $this->_properties['ADR;'.$type] = $data;
    return $this;  
  }   
  
  public function setLabel($postoffice = '', $extended = '', $street = '', $city = '', $region = '', $zip = '', $country = '', $type = 'WORK;POSTAL') 
  {
    $label = '';
    if ($postoffice != '') {
      $label.= $postoffice;
      $label.= "\r\n";
    }

    if ($extended != '') {
      $label.= $extended;
      $label.= "\r\n";
    }

    if ($street != '') {
      $label.= $street;
      $label.= "\r\n";
    }

    if ($zip != '') {
      $label.= $zip .' ';
    }

    if ($city != '') {
      $label.= $city;
      $label.= "\r\n";
    }

    if ($region != '') {
      $label.= $region;
      $label.= "\r\n";
    }

    if ($country != '') {
      $country.= $country;
      $label.= "\r\n";
    }

    $this->_properties["LABEL;$type;ENCODING=QUOTED-PRINTABLE"] = $this->_quoted_printable_encode($label);
    return $this;
  } 
  
  public function setEmail($address) 
  {
    $this->_properties['EMAIL;PREF;INTERNET'] = $address;
    return $this; 
  }
  
  public function setURL($url, $type = 'WORK') 
  {
    $this->_properties['URL;'.$type] = $url;
    return $this;  
  }

  public function setPhoto($photo, $type = 'JPEG') 
  { 
    $this->_properties["PHOTO;TYPE=$type;ENCODING=BASE64"] = base64_encode($photo);
    return $this;     
  }

  public function setBirthday($date) 
  { 
    $this->_properties['BDAY'] = $date;
    return $this;   
  }

  public function setNote($note) 
  {
    $this->_properties['NOTE;ENCODING=QUOTED-PRINTABLE'] = $this->_quoted_printable_encode($note);
    return $this;   
  }

  protected function _encode($string) 
  {
    return $this->escape($this->_quoted_printable_encode($string));
  }

  protected function _escape($string) 
  {
    return str_replace(';',"\;",$string);
  } 
  
  protected function _quoted_printable_encode($input, $line_max = 76) 
  {
    $hex        = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
    $lines      = preg_split("/(?:\r\n|\r|\n)/", $input);
    $eol        = "\r\n";
    $linebreak  = '=0D=0A';
    $escape     = '=';
    $output     = '';

    for ($j = 0; $j < count($lines); $j++) 
    {
      $line    = $lines[$j];
      $linlen  = strlen($line);
      $newline = '';

      for($i = 0; $i < $linlen; $i++) 
      {
        $c   = substr($line, $i, 1);
        $dec = ord($c);

        if(($dec == 32) && ($i == ($linlen - 1))) 
          $c = '=20';
        elseif(($dec == 61) || ($dec < 32 ) || ($dec > 126)) 
        { 
          $h2 = floor($dec/16);
          $h1 = floor($dec%16);
          $c  = $escape.$hex["$h2"] . $hex["$h1"];
        }

        if((strlen($newline) + strlen($c)) >= $line_max) { 
          $output .= $newline.$escape.$eol;
          $newline = "    ";
        }
        $newline .= $c;  
      }
  
      $output .= $newline;
      if($j<count($lines)-1) $output .= $linebreak;
    }

    return trim($output);   
  }
}