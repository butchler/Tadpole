<?php

include_once(INCLUDE_PATH . '/db.php');

class snippet
{
   public static function getSnippet($snippetName)
   {
      $snippet = db::get('snippet', array('name' => $snippetName));

      if (isset($snippet->body))
      {
         return $snippet->body;
      }
      else
      {
         return NULL;
      }
   }

   public static function includeSnippet($snippetName)
   {
      echo self::getSnippet($snippetName);
   }
}

?>
