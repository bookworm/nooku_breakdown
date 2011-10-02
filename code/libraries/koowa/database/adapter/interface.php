<?php

interface KDatabaseAdapterInterface
{
  public function getQuery(KConfig $config = null);
  public function connect();
  public function reconnect();
  public function disconnect();
  public function getConnection();
  public function setConnection($resource);
  public function isConnected();
  public function getInsertId();
  public function getTableSchema($table);
  public function select($sql, $mode = KDatabase::RESULT_STORE);
  public function insert($table, array $data);
  public function update($table, array $data, $where = null);
  public function delete($table, $where);
  public function execute($sql, $mode = KDatabase::RESULT_STORE );
  public function setTablePrefix($prefix);
  public function getTablePrefix();
  public function replaceTablePrefix( $sql, $replace = null, $needle = '#__' );
  public function quoteValue($value);
  public function quoteName($spec);   
}