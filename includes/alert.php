<?php

if (!isset($_SESSION))
{
   session_start();
}

class alert
{
   public static function addAlert($message)
   {
      if (!isset($_SESSION['alerts']))
      {
         $_SESSION['alerts'] = array();
      }

      $_SESSION['alerts'][] = $message;
   }

   public static function addError($message)
   {
      if (!isset($_SESSION['errors']))
      {
         $_SESSION['errors'] = array();
      }

      $_SESSION['errors'][] = $message;
   }

   public static function hasAlerts()
   {
      if (!isset($_SESSION['alerts']))
      {
         return false;
      }

      return !empty($_SESSION['alerts']);
   }

   public static function hasErrors()
   {
      if (!isset($_SESSION['errors']))
      {
         return false;
      }

      return !empty($_SESSION['errors']);
   }

   public static function getAlerts()
   {
      if (!isset($_SESSION['alerts']))
      {
         return array();
      }

      $alerts = $_SESSION['alerts'];
      $_SESSION['alerts'] = array();

      return $alerts;
   }

   public static function getErrors()
   {
      if (!isset($_SESSION['errors']))
      {
         return array();
      }

      $errors = $_SESSION['errors'];
      $_SESSION['errors'] = array();

      return $errors;
   }
}

?>
