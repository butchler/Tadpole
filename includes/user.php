<?php

include_once(INCLUDE_PATH . '/db.php');

if (!isset($_SESSION))
{
   session_start();
}

class user
{
   private static $user;

   public static function logInUser($username, $passwordHash)
   {
      $user = db::get('user', array('username' => $username));

      if ($passwordHash == $user->password_hash)
      {
         // Add user to session
         $_SESSION['username'] = $username;
         return true;
      }

      return false;
   }

   public static function logOutUser()
   {
      unset($_SESSION['username']);
   }

   public static function isUserLoggedIn()
   {
      return isset($_SESSION['username']);
   }

   public static function getCurrentUser($cache = true)
   {
      if (isset(self::$user) && $cache == true)
      {
         return self::$user;
      }
      else
      {
         if (!self::isUserLoggedIn())
         {
            return NULL;
         }

         self::$user = new User(db::get('user', array('username' => $_SESSION['username'])));
         return self::$user;
      }
   }

   // Non-static functions
   public function __construct($rowObject)
   {
      // Copy object's properties
		foreach ($rowObject as $key => $value)
		{
			$this->$key = $value;
		}
   }

   public function getRoles()
   {
      return explode(',', $this->roles);
   }

   public function hasRole($role)
   {
      return in_array($role, $this->getRoles());
   }

   public function addRole($role)
   {
      if ($this->hasRole($role))
      {
         return true;
      }

      $roles = $this->getRoles();
      $roles[] = $role;
      $this->roles = implode(',', $roles);

      return db::update('user',
         array('roles' => $this->roles),
         array('username' => $this->username)
      );
   }

   public function removeRole($role)
   {
      $roles = $this->getRoles();

      $index = array_search($role, $roles);
      if ($index !== false)
      {
         unset($roles[$index]);

         $this->roles = implode(',', $roles);

         return db::update('user',
            array('roles' => $this->roles),
            array('username' => $this->username)
         );
      }

      return true;
   }
}

?>
