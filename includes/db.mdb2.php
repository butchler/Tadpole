<?php

set_include_path(get_include_path() . PATH_SEPARATOR . INCLUDE_PATH . '/mdb2');
require_once('MDB2.php');

class db
{
   public static $mdb2;
   public static $error;   // Holds the most recent MDB2 error object
   private static $lastTableName;   // Used for lastInsertId()

   public static function connect($driver, $host, $user = false, $password = false, $database = false)
   {
      $dsn = array();
      $dsn['phptype'] = $driver;
      $dsn['username'] = $user;
      $dsn['password'] = $password;
      $dsn['hostspec'] = $host;
      $dsn['database'] = $database;

      self::$mdb2 = &MDB2::connect($dsn);
      if (MDB2::isError(self::$mdb2))
      {
         self::$error = self::$mdb2;
         return false;
      }

      self::$mdb2->setFetchMode(MDB2_FETCHMODE_OBJECT);
      self::$error = NULL;
      self::$lastTableName = NULL;

      return true;
   }

   public static function get($tableName, $where = array())
   {
      $where['limit'] = 1;
      $results = self::getResults($tableName, $where);

      if (MDB2::isError($results))
      {
         self::$error = $results;
         return NULL;
      }

      $row = $results->fetchRow();

      if (MDB2::isError($row))
      {
         self::$error = $row;
         return NULL;
      }

      return $row;
   }

   public static function getAll($tableName, $where = array())
   {
      $results = self::getResults($tableName, $where);

      if (MDB2::isError($results))
      {
         self::$error = $results;
         return array();
      }

      $rows = $results->fetchAll();

      if (MDB2::isError($rows))
      {
         self::$error = $rows;
         return array();
      }

      return $rows;
   }

   private static function getResults($tableName, $where = array())
   {
      // Build SQL query
      $sql = 'SELECT ';

      if (isset($where['columns']))
      {
         // Get only certain columns
         foreach ($where['columns'] as &$field)
         {
            $field = self::quoteName($field);
         }
         $sql .= implode(',', $where['columns']);
      }
      else
      {
         $sql .= '*';
      }

      $sql .= ' FROM ' . self::quoteName($tableName);
      $sql .= ' WHERE ' . self::makeWhereClause($where);

      $results = self::$mdb2->query($sql);

      return $results;
   }

   public static function getCount($tableName, $where = array())
   {
      $sql  = 'SELECT COUNT(*) AS count FROM ' . self::quoteName($tableName);
      $sql .= ' WHERE ' . self::makeWhereClause($where);

      $results = self::$mdb2->query($sql);

      if (!MDB2::isError($results))
      {
         $row = $results->fetchRow();

         if (!MDB2::isError($row) && isset($row->count))
         {
            return (int)($row->count);
         }
      }

      $sql = 'SELECT * FROM ' . self::quoteName($tableName) . ' WHERE ' . self::makeWhereClause($where);
      $results = self::getResults($sql);

      if (!MDB2::isError($results))
      {
         $count = $results->numRows();

         if (!MDB2::isError($count))
         {
            return $count;
         }
         else
         {
            self::$error = $count;
            return false;
         }
      }
      else
      {
         self::$error = $results;
         return false;
      }
   }

   function add($tableName, $fields)
   {
      // Check for auto-increment field
      $result = self::$mdb2->query('SELECT * FROM ' . self::quoteName($tableName . '_autoincrement'));
      if (!MDB2::isError($result))
      {
         $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);

         if (!MDB2::isError($row))
         {
            // This loop should only run once, since there is only one column in the autoincrement table
            foreach ($row as $name => $value)
            {
               $autoIncrementFieldName = $name;
               $autoIncrementFieldValue = $value + 1;
            }

            if (!isset($fields[$autoIncrementFieldName]))
            {
               $numRows = self::update("{$tableName}_autoincrement", array($autoIncrementFieldName => $autoIncrementFieldValue), array());
               if (MDB2::isError($numRows))
               {
                  self::$error = $numRows;
                  return false;
               }

               $fields[$autoIncrementFieldName] = $autoIncrementFieldValue;
            }
         }
      }

      $sql = 'INSERT INTO ' . self::quoteName($tableName);

      $names = array();
      $values = array();
      foreach ($fields as $name => $value)
      {
         $names[] = self::quoteName($name);
         $values[] = self::quoteValue($value);
      }

      $sql .= ' (' . implode(',', $names) . ')';
      $sql .= ' VALUES';
      $sql .= ' (' . implode(',', $values) . ')';

      $numRows = self::$mdb2->exec($sql);

      if (MDB2::isError($numRows))
      {
         self::$error = $numRows;
         return false;
      }

      self::$lastTableName = $tableName;

      return ($numRows >= 1);
   }

   public static function lastInsertId()
   {
      if (self::$lastTableName == NULL)
      {
         return false;
      }

      $result = self::$mdb2->query('SELECT * FROM ' . self::quoteName(self::$lastTableName . '_autoincrement') );

      if (MDB2::isError($result))
      {
         self::$error = $result;
         return false;
      }

      $row = $result->fetchRow(MDB2_FETCHMODE_ORDERED);

      if (MDB2::isError($row))
      {
         self::$error = $row;
         return false;
      }

      $id = $row[0];

      return $id;

      /*$id = self::$mdb2->lastInsertID($tableName);

      if (MDB2::isError($id))
      {
         self::$error = $id;
         return false;
      }

      return $id;*/
   }

   public static function remove($tableName, $where = array())
   {
      $sql = 'DELETE FROM ' . self::quoteName($tableName);
      $sql .= ' WHERE ' . self::makeWhereClause($where);
      
      $numRows = self::$mdb2->exec($sql);

      if (MDB2::isError($numRows))
      {
         self::$error = $numRows;
         return false;
      }

      return $numRows;
   }

   public static function update($tableName, $fields, $where = array())
   {
      $sql = 'UPDATE ' . self::quoteName($tableName) . ' SET ';

      $fieldAssignments = array();
      foreach ($fields as $name => $value)
      {
         $fieldAssignments[] = self::quoteName($name) . '=' . self::quoteValue($value);
      }
      $sql .= implode(', ', $fieldAssignments);
      
      $sql .= ' WHERE ' . self::makeWhereClause($where);
     
      $numRows = self::$mdb2->exec($sql);

      if (MDB2::isError($numRows))
      {
         self::$error = $numRows;
         return false;
      }
      
      return $numRows;
   }

   public static function createTable($tableName, $fields, $primaryKeyField = NULL, $saveForExport = true)
   {
      if (!isset(self::$mdb2->manager))
      {
         $ok = self::$mdb2->loadModule('Manager', null, true);

         if (PEAR::isError($ok))
         {
            self::$error = $ok;
            return false;
         }
      }

      $fieldDefinitions = array();
      $autoincrementField = NULL;
      foreach ($fields as $fieldName => $fieldType)
      {
         if ($fieldType == 'string')
         {
            $fieldDefinitions[$fieldName] = array(
               'type' => 'text',
               'length' => '255'
            );
         }
         else if ($fieldType == 'text')
         {
            $fieldDefinitions[$fieldName] = array('type' => 'text');
         }
         else if ($fieldType == 'boolean')
         {
            $fieldDefinitions[$fieldName] = array('type' => 'boolean');
         }
         else if ($fieldType == 'integer')
         {
            $fieldDefinitions[$fieldName] = array('type' => 'integer');
         }
         else if ($fieldType == 'float')
         {
            $fieldDefinitions[$fieldName] = array('type' => 'float');
         }
         else if ($fieldType == 'autoincrement')
         {
            $fieldDefinitions[$fieldName] = array('type' => 'integer');
            $autoincrementField = $fieldName;
         }
      }

      $ok = self::$mdb2->manager->createTable(self::quoteName($tableName), $fieldDefinitions);

      if (MDB2::isError($ok))
      {
         self::$error = $ok;
         return false;
      }

      // Set up primary key
      if ($primaryKeyField && isset($fields[$primaryKeyField]))
      {
         $constraintDefinition = array(
            'primary' => true,
            'fields' => array(
               $primaryKeyField => array()
            )
         );

         $ok = self::$mdb2->manager->createConstraint(self::quoteName($tableName), 'primary_key', $constraintDefinition);

         if (MDB2::isError($ok))
         {
            self::$error = $ok;
         }
      }

      // Set up the autoincrement field if any
      if ($autoincrementField != NULL)
      {
         $success = self::createTable("{$tableName}_autoincrement", array($autoincrementField => 'integer'), NULL, false);
         if ($success)
         {
            // Set it to zero by default
            $success = self::add("{$tableName}_autoincrement", array($autoincrementField => 0));
            if (!$success)
            {
               return false;
            }
         }
         else
         {
            return false;
         }
      }

      // If we successfully created table, save it's information so it can be exported later
      if ($saveForExport == true)
      {
         foreach ($fields as $fieldName => $fieldType)
         {
            $ok = self::add('table-field', array('table_name' => $tableName, 'name' => $fieldName, 'type' => $fieldType));

            if (MDB2::isError($ok))
            {
               self::$error = $ok;
               return false;
            }
         }

         if ($primaryKeyField)
         {
            $ok = self::add('table-primary-key', array('table_name' => $tableName, 'name' => $primaryKeyField));

            if (!$ok)
            {
               return false;
            }
         }
      }

      return true;
   }

   public static function getErrorMessage()
   {
      if (self::$error)
      {
         return (string)(self::$error);
      }
      else
      {
         return false;
      }
   }

   public static function makeWhereClause($params = array())
   {
      // TODO: Make 'IN' clause instead of an '=' if they pass in an array as a value

      $sql = '';

      if (isset($params['sql']))
      {
         $sql = $params['sql'];
      }
      else
      {
         $keywords = array('columns', 'sql', 'limit', 'offset', 'order_by', 'and', 'or');
         
         $filters = array();
         foreach ($params as $fieldName => $fieldValue)
         {
            // Skip keywords
            if (in_array($fieldName, $keywords))
            {
               continue;
            }
            
            if (is_null($fieldValue))
            {
               $filters[] = self::quoteName($fieldName) . ' IS NULL';
            }
            else if (is_array($fieldValue) && !empty($fieldValue))
            {
               $filters[] = self::quoteName($fieldName) . ' IN (' . implode(', ', array_map('self::quoteValue', $fieldValue)) . ')';
            }
            else
            {
               $filters[] = self::quoteName($fieldName) . '=' . self::quoteValue($fieldValue);
            }
         }
         
         if (!empty($filters))
         {
            $sql .= implode(' AND ', $filters);
         }
         else
         {
            $sql .= self::quoteValue(true);
         }
      }

      if (isset($params['and']))
      {
         $sql .= ' AND ' . $params['and'];
      }

      if (isset($params['or']))
      {
         $sql .= ' OR ' . $params['or'];
      }
      
      if (isset($params['order_by']))
      {
         $sql .= ' ORDER BY ' . $params['order_by'];
      }
      
      if (isset($params['limit']))
      {
         //$sql .= ' LIMIT ' . $params['limit'];
         self::$mdb2->setLimit($params['limit']);
      }
      
      if (isset($params['offset']))
      {
         $sql .= ' OFFSET ' . $params['offset'];
      }
      
      return $sql;
   }

   public static function quoteName($name)
   {
      return self::$mdb2->quoteIdentifier($name);
   }

   public static function quoteValue($value)
   {
      return self::$mdb2->quote($value);
   }
}

?>
