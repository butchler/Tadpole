<?php

require_once('./config.php');
require_once(INCLUDE_PATH . '/db.php');
require_once(INCLUDE_PATH . '/page.php');

if (is_dir('./install'))
{
   die("You must remove the 'install' folder before using Tadpole!");
}

if (!db::connect(DB_DRIVER, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME))
{
   die('Could not connect to database!');
}

if (isset($_GET['q']) &&strpos(ltrim($_GET['q'], '/'), 'admin-backup') === 0)
{
   $loaderPage = page::findPageByUrl('/admin-backup/loader');
}
else
{
   $loaderPage = page::findPageByUrl('/admin/loader');
}

if (!$loaderPage)
{
   die('Could not find loader page!');
}

eval('?>' . $loaderPage->body);

?>
