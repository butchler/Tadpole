<?php

include_once(INCLUDE_PATH . '/db.php');

class page
{
   public static function findPageByUrl($url)
   {
      $url = trim($url, '/ ');

      $page = self::getRootPage();
      foreach (explode('/', $url) as $slug)
      {
         $parentPage = $page;
         $page = self::findPageBySlug($slug, $parentPage);
         if (!$page)
         {
            return NULL;
         }
      }

      return $page;
   }

   public static function findPageBySlug($slug, $parentPage)
   {
      $page = db::get('page', array('parent_id' => $parentPage->id, 'slug' => $slug));

      if (!$page)
      {
         return NULL;
      }

      $page = new Page($page);
      $page->parentPage = $parentPage;

      return $page;
   }

   public static function getRootPage()
   {
      $rootPage = db::get('page', array('parent_id' => NULL));

      if (!$rootPage)
      {
         return NULL;
      }

      return new Page($rootPage);
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

   public function getParent($cache = 'cache')
   {
      if ($this->parent_id == NULL)
      {
         return NULL;
      }

      if (isset($this->parentPage) && $cache == 'cache')
      {
         return $this->parentPage;
      }
      else
      {
         $this->parentPage = db::get('page', array('id' => $this->parent_id));

         if (!$this->parentPage)
         {
            return NULL;
         }

         $this->parentPage = new Page($this->parentPage);

         return $this->parentPage;
      }
   }

   public function getChildren()
   {
      $children = db::getAll('page', array('parent_id' => $this->id));
      foreach ($children as &$child)
      {
         $child = new Page($child);
      }

      return $children;
   }

   public function getSiblings()
   {
      $siblings = db::getAll('page', array('parent_id' => $this->parent_id, 'and' => 'id<>' . db::quoteValue($this->id)));
      foreach ($siblings as &$sibling)
      {
         $sibling = new Page($sibling);
      }

      return $siblings;
   }

   public function getNextSibling()
   {
      $siblings = db::getAll('page', array('parent_id' => $this->parent_id));

      for ($i = 0; $i < count($siblings); $i += 1)
      {
         $sibling = $siblings[$i];
         if ($sibling->id == $this->id)
         {
            if (isset($siblings[$i + 1]))
            {
               return new Page($siblings[$i + 1]);
            }
            else
            {
               return NULL;
            }
         }
      }

      return NULL;
   }

   public function getPreviousSibling()
   {
      $siblings = db::getAll('page', array('parent_id' => $this->parent_id));

      for ($i = 0; $i < count($siblings); $i += 1)
      {
         $sibling = $siblings[$i];
         if ($sibling->id == $this->id)
         {
            if (isset($siblings[$i - 1]))
            {
               return new Page($siblings[$i - 1]);
            }
            else
            {
               return NULL;
            }
         }
      }

      return NULL;
   }

   public function getPath()
   {
      $path = '';
      $page = $this;
      while ($page)
      {
         $path = $page->slug . '/' . $path;
         $page = $page->getParent();
      }

      return '/' . trim($path, '/');
   }

   // Is this useful?
   /*public function link($text = NULL)
   {
      if ($text == NULL)
      {
         $text = $this->title;
      }

      return '<a href="'. ROOT_URL . $this->getPath() . '">' . $text . '</a>';
   }*/
}

?>
