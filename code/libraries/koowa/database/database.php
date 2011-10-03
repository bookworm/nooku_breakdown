<?php 

class KDatabase
{
  const OPERATION_SELECT = 1;
  const OPERATION_INSERT = 2;
  const OPERATION_UPDATE = 4;
  const OPERATION_DELETE = 8;
  const OPERATION_SHOW   = 16;
  
  const RESULT_STORE = 0;
  const RESULT_USE   = 1;
  
  const FETCH_ROW         = 0;
  const FETCH_ROWSET      = 1;
  const FETCH_ARRAY       = 0;
  const FETCH_ARRAY_LIST  = 1;
  const FETCH_FIELD       = 2;
  const FETCH_FIELD_LIST  = 3;
  const FETCH_OBJECT      = 4;
  const FETCH_OBJECT_LIST = 5;
  
  const STATUS_LOADED   = 'loaded';
  const STATUS_DELETED  = 'deleted';
  const STATUS_CREATED  = 'created';
  const STATUS_UPDATED  = 'updated';
  const STATUS_FAILED   = 'failed';    
}