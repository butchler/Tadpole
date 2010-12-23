<?php

db::createTable('layout', array(
   'id' => 'autoincrement',
   'name' => 'string',
   'body' => 'text',
   'parent_id' => 'integer',
), 'id');

function addLayout($fields)
{
   $result = db::add('layout', $fields);

   if (!$result)
   {
      echo "<strong>Error creating layout '{$fields['name']}' (" . db::getErrorMessage() . ").</strong><br />\n";
      $success = false;
   }
   else
   {
      echo "Created layout '{$fields['name']}'.<br />\n";
   }
}

addLayout(array(
   'name' => 'inherit',
   'body' => <<<EODEOD
This is a dummy layout that doesn't actually do anything, the actual inheriting functionality is implemented in the Loader page.
EODEOD
   ,
   'parent_id' => NULL,
));

addLayout(array(
   'name' => 'Admin Authentication',
   'body' => <<<EODEOD
<?php
require_once(INCLUDE_PATH . '/user.php');

if (!user::isUserLoggedIn())
{
   header('Location: ' . ROOT_URL . '/admin/login');
   return;
}

\$user = user::getCurrentUser();
if (!\$user->hasRole('administrator'))
{
   echo 'You must be logged in as an administrator to view this page.';
   return;
}

echo content();

?>
EODEOD
   ,
   'parent_id' => NULL,
));

addLayout(array(
   'name' => 'Admin',
   'body' => <<<EODEOD
<html>
  <head>
    <title><?php echo \$self->title; ?> - Tadpole CMS</title>
    <script type="text/javascript" src="<?php echo SITE_URL; ?>/files/jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/files/admin-style.css" />
<?php eval('?>' . \$self->head); ?>
  </head>
  <body>
    <div id="header">
<?php \$user = user::getCurrentUser(); ?>
      <div id="login-information">Logged in as <a href="<?php echo ROOT_URL; ?>/admin/edit/user/<?php echo \$user->username; ?>"><?php echo \$user->username; ?></a>. <a href="<?php echo ROOT_URL; ?>/admin/logout">Log out</a></div>

      <div id="site-title">Tadpole CMS</div>

      <ul id="tabs">
<?php
\$adminPage = page::findPageByUrl('/admin/list');
\$adminPages = \$adminPage->getChildren();
foreach (\$adminPages as \$page)
{
  echo '<li><a href="' . ROOT_URL . \$page->getPath() . '"';
  \$url = explode('/', trim(\$_GET['q'], '/'));
  \$path = explode('/', trim(\$page->getPath(), '/'));
  if (count(\$url) >= 3 && count(\$path) >= 3 && \$url[2] == \$path[2])
  {
    echo ' class="current"';
  }
  echo '>' . \$page->title . '</a></li>';
}
?>
        <li style="float: right;"><a href="<?php echo ROOT_URL; ?>/admin/export">Export</a></li>
        <li style="float: right;"><a href="<?php echo ROOT_URL; ?>/admin/create-type">Create new type</a></li>
      </ul>
    </div>

    <div id="main">
      <div id="page-title"><?php echo \$self->title; ?></div>

<?php include_once(INCLUDE_PATH . '/alert.php'); ?>
<?php if (alert::hasAlerts()) { ?>
      <div id="alerts">
        <ul>
<?php
foreach (alert::getAlerts() as \$message)
{
  echo '<li>' . \$message . '</li>';
}
?>
        </ul>
      </div>
<?php } ?>

<?php if (alert::hasErrors()) { ?>
      <div id="errors">
<?php
foreach (alert::getErrors() as \$message)
{
  echo '<li>' . \$message . '</li>';
}
?>
      </div>
<?php } ?>

<?php echo content(); ?>
    </div>
  </body>
</html>
EODEOD
   ,
   'parent_id' => 2,
));

addLayout(array(
   'name' => 'Simple',
   'body' => <<<EODEOD
<html>
  <head>
   <title><?php echo \$self->title; ?></title>
<?php echo \$self->head; ?>
  </head>
  <body>
<?php echo content(); ?>
  </body>
</html>
EODEOD
   ,
   'parent_id' => NULL,
));

addLayout(array(
   'name' => 'Admin Authentication Backup',
   'body' => <<<EODEOD
<?php
require_once(INCLUDE_PATH . '/user.php');

if (!user::isUserLoggedIn())
{
   header('Location: ' . ROOT_URL . '/admin-backup/login');
   return;
}

\$user = user::getCurrentUser();
if (!\$user->hasRole('administrator'))
{
   echo 'You must be logged in as an administrator to view this page.';
   return;
}

echo content();

?>
EODEOD
   ,
   'parent_id' => NULL,
));

addLayout(array(
   'name' => 'Admin Backup',
   'body' => <<<EODEOD
<html>
  <head>
    <title><?php echo \$self->title; ?> - Tadpole CMS</title>
    <script type="text/javascript" src="<?php echo SITE_URL; ?>/files/jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/files/admin-style.css" />
<?php eval('?>' . \$self->head); ?>
  </head>
  <body>
    <div id="header">
<?php \$user = user::getCurrentUser(); ?>
      <div id="login-information">Logged in as <a href="<?php echo ROOT_URL; ?>/admin-backup/edit/user/<?php echo \$user->username; ?>"><?php echo \$user->username; ?></a>. <a href="<?php echo ROOT_URL; ?>/admin-backup/logout">Log out</a></div>

      <div id="site-title">Tadpole CMS</div>

      <ul id="tabs">
<?php
\$adminPage = page::findPageByUrl('/admin-backup/list');
\$adminPages = \$adminPage->getChildren();
foreach (\$adminPages as \$page)
{
  echo '<li><a href="' . ROOT_URL . \$page->getPath() . '"';
  \$url = explode('/', trim(\$_GET['q'], '/'));
  \$path = explode('/', trim(\$page->getPath(), '/'));
  if (count(\$url) >= 3 && count(\$path) >= 3 && \$url[2] == \$path[2])
  {
    echo ' class="current"';
  }
  echo '>' . \$page->title . '</a></li>';
}
?>
      </ul>
    </div>

    <div id="main">
      <div id="page-title"><?php echo \$self->title; ?></div>

<?php include_once(INCLUDE_PATH . '/alert.php'); ?>
<?php if (alert::hasAlerts()) { ?>
      <div id="alerts">
        <ul>
<?php
foreach (alert::getAlerts() as \$message)
{
  echo '<li>' . \$message . '</li>';
}
?>
        </ul>
      </div>
<?php } ?>

<?php if (alert::hasErrors()) { ?>
      <div id="errors">
<?php
foreach (alert::getErrors() as \$message)
{
  echo '<li>' . \$message . '</li>';
}
?>
      </div>
<?php } ?>

<?php echo content(); ?>
    </div>
  </body>
</html>
EODEOD
   ,
   'parent_id' => 5,
));

?>
