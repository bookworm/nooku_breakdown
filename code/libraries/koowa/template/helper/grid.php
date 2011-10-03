<?php

class KTemplateHelperGrid extends KTemplateHelperAbstract
{
  public function checkbox($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'row'     => null,
    ));

    if($config->row->isLockable() && $config->row->locked())
    {
      $html = '<span class="editlinktip hasTip" title="'.$config->row->lockMessage() .'">
          <img src="media://lib_koowa/images/locked.png"/>
        </span>';
    }
    else
    {
      $column = $config->row->getIdentityColumn();
      $value  = $config->row->{$column};
      $html   = '<input type="checkbox" class="-koowa-grid-checkbox" name="'.$column.'[]" value="'.$value.'" />';
    }

    return $html;
  }    
  
  public function search($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'search' => null
    ));    
      
    $html = '<input name="search" id="search" value="'.$config->search.'" />';
    $html .= '<button>'.JText::_('Go').'</button>';
    $html .= '<button onclick="document.getElementById(\'search\').value=\'\';this.form.submit();">'.JText::_('Reset').'</button>';

    return $html;     
  }     
           
  public function checkall($config = array())
  {
    $config = new KConfig($config);

    $html = '<input type="checkbox" class="-koowa-grid-checkall" />';
    return $html;
  } 

  public function sort( $config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'title'     => '',
      'column'    => '',
      'direction' => 'asc',
      'sort'    => ''
    ));

    if(empty($config->title)) 
      $config->title = ucfirst($config->column);

    $direction  = strtolower($config->direction);
    $direction  = in_array($direction, array('asc', 'desc')) ? $direction : 'asc';

    $class = '';
    if($config->column == $config->sort) {
      $direction = $direction == 'desc' ? 'asc' : 'desc'; // toggle
      $class = 'class="-koowa-'.$direction.'"';
    }

    $url = clone KRequest::url();

    $query              = $url->getQuery(1);
    $query['sort']      = $config->column;
    $query['direction'] = $direction;
    $url->setQuery($query);

    $html  = '<a href="'.JRoute::_('index.php?'.$url->getQuery()).'" title="'.JText::_('Click to sort by this column').'"  '.$class.'>';
    $html .= JText::_($config->title);
    $html .= '</a>';

    return $html;
  }   
  
  public function enable($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'row'   => null,
      'field' => 'enabled'
    ))->append(array(
      'data' => array($config->field => $config->row->{$config->field})
    ));

    $img  = $config->row->{$config->field} ? 'enabled.png' : 'disabled.png';
    $alt  = $config->row->{$config->field} ? JText::_( 'Enabled' ) : JText::_( 'Disabled' );
    $text = $config->row->{$config->field} ? JText::_( 'Disable Item' ) : JText::_( 'Enable Item' );

    $config->data->{$config->field} = $config->row->{$config->field} ? 0 : 1;
    $data = str_replace('"', '&quot;', $config->data);

    $html = '<img src="media://lib_koowa/images/'. $img .'" border="0" alt="'. $alt .'" data-action="edit" data-data="'.$data.'" title='.$text.' />';

    return $html;
  } 
  
  public function order($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'row'   => null,
      'total' => null,
      'field' => 'ordering',
      'data'  => array('order' => 0)     
    ));

    $up   = 'media://lib_koowa/images/arrow_up.png';
    $down = 'media://lib_koowa/images/arrow_down.png';

    $config->data->order = -1;
    $updata  = str_replace('"', '&quot;', $config->data);

    $config->data->order = +1;
    $downdata = str_replace('"', '&quot;', $config->data);

    $html = '';

    if($config->row->{$config->field} > 1)
      $html .= '<img src="'.$up.'" border="0" alt="'.JText::_('Move up').'" data-action="edit" data-data="'.$updata.'" />';

    $html .= $config->row->{$config->field};

    if($config->row->{$config->field} != $config->total) 
      $html .= '<img src="'.$down.'" border="0" alt="'.JText::_('Move down').'" data-action="edit" data-data="'.$downdata.'"/>';

    return $html;    
  }  
  
  public function access($config = array())
  {
    $config = new KConfig($config);
    $config->append(array(
      'row'   => null,
      'field' => 'access'
    ))->append(array(
      'data' => array($config->field => $config->row->{$config->field})
    ));

    switch($config->row->{$config->field})
    {
      case 0: {
        $color   = 'green';
        $group   = JText::_('Public');
        $access  = 1;
      } break;

      case 1: {
        $color   = 'red';
        $group   = JText::_('Registered');
        $access  = 2;
      } break;

      case 2: {
        $color   = 'black';
        $group   = JText::_('Special');
        $access  = 0;
      } break;

    }

    $config->data->{$config->field} = $access;
    $data = str_replace('"', '&quot;', $config->data);

    $html = '<span style="color:'.$color.'" data-action="edit" data-data="'.$data.'">'.$group.'</span>';

    return $html; 
  }              
}