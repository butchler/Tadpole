<?php

db::createTable('page', array(
   'id' => 'autoincrement',
   'title' => 'string',
   'slug' => 'string',
   'body' => 'text',
   'head' => 'text',
   'parent_id' => 'integer',
   'layout_id' => 'integer',
   'is_dynamic' => 'boolean',
   'created_time' => 'string',
   'last_modified_time' => 'string',
   'expanded' => 'boolean',
), 'id');

function addPage($fields)
{
   $result = db::add('page', $fields);

   if (!$result)
   {
      echo "<strong>Error creating page '{$fields['title']}' (" . db::getErrorMessage() . ").</strong><br />\n";
      $success = false;
   }
   else
   {
      echo "Created page '{$fields['title']}'.<br />\n";
   }
}

addPage(array(
   'title' => 'Home Page',
   'slug' => '/',
   'body' => <<<EODEOD
This is the home page.
EODEOD
   ,
   'head' => NULL,
   'parent_id' => NULL,
   'layout_id' => 4,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Admin',
   'slug' => 'admin',
   'body' => <<<EODEOD
<?php header('Location: ' . ROOT_URL . '/admin/list/page'); ?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 1,
   'layout_id' => 3,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Loader',
   'slug' => 'loader',
   'body' => <<<EODEOD
<?php

\$rootPage = page::getRootPage();

if (!isset(\$_GET['q']) || trim(\$_GET['q'], '/') == '')
{
   echo renderPage(\$rootPage);
}
else
{
   \$query = trim(\$_GET['q'], '/');

   \$page = \$rootPage;
   \$foundPage = true;
   foreach (explode('/', \$query) as \$slug)
   {
      \$childPage = page::findPageBySlug(\$slug, \$page);
      if (!\$childPage)
      {
         // Could not find page with the specified URL
         \$foundPage = false;
         break;
      }

      \$page = \$childPage;
   }

   if (\$foundPage)
   {
      echo renderPage(\$page);
   }
   else
   {
      // Go backwards and try to find a dynamic page
      \$foundDynamicPage = false;
      while (\$page)
      {
         if (\$page->is_dynamic)
         {
            \$foundDynamicPage = true;
            break;
         }

         \$page = \$page->getParent();
      }

      if (\$foundDynamicPage)
      {
         \$path = trim(\$page->getPath(), '/');
         // New query equals everything after the dynamic page's path in the original query
         // So /admin/edit/page/5 becomes just 5, since /admin/edit/page is a dynamic page
         // It's a bit confusing at first, but just think of the five as the ?query=5 part of /admin/edit/page?query=5
         \$query = substr(\$query, strlen(\$path) + 1);

         echo renderPage(\$page, \$query);
      }
      else
      {
         // Couldn't find the page or any dynamic page to handle the query, send a 404 error
         header('HTTP/1.0 404 Not Found');
         echo "Sorry, couldn't find the page!";
      }
   }
}

// Evaluates and returns the contents of a page, and handles layouts
function renderPage(\$page, \$query = '')
{
   if (\$page->layout_id == NULL)
   {
      \$GLOBALS['self'] = \$page;
      \$GLOBALS['query'] = \$query;
      return executePage();
   }
   else
   {
      \$layout = db::get('layout', array('id' => \$page->layout_id));

      if (\$layout->name == 'inherit')
      {
         // Go up chain of parent pages until we find one with a layout that isn't the 'inherit' layout too
         \$inheritId = \$layout->id;
         \$parentPage = \$page->getParent();
         while (\$parentPage != NULL && \$parentPage->layout_id == \$inheritId)
         {
            \$parentPage = \$parentPage->getParent();
         }

         if (\$parentPage == NULL)
         {
            \$page->layout_id = NULL;
         }
         else
         {
            \$page->layout_id = \$parentPage->layout_id;
         }

         return renderPage(\$page, \$query);
      }

      // Create chain of layouts
      while (\$layout->parent_id != NULL)
      {
         \$parentLayout = db::get('layout', array('id' => \$layout->parent_id));
         if (!\$parentLayout)
         {
            break;
         }
         \$parentLayout->childLayout = \$layout;
         \$layout = \$parentLayout;
      }

      \$GLOBALS['self'] = \$page;
      \$GLOBALS['query'] = \$query;
      \$GLOBALS['layout'] = \$layout;
      return executeLayout();
   }
}

// All layouts execute within this function
function executeLayout()
{
   global \$self, \$query, \$layout;

   ob_start();
   eval('?>' . \$layout->body);
   \$output = ob_get_clean();

   return \$output;
}

// All pages execute within this function
function executePage()
{
   global \$self, \$query;

   ob_start();
   eval('?>' . \$self->body);
   \$output = ob_get_clean();

   return \$output;
}

// Returns the contents of the current page, or the contents of the current layout's child layout if it has one
// Should only be called from within layouts
function content()
{
   global \$self, \$query, \$layout;

   if (isset(\$layout->childLayout))
   {
      \$layout = \$layout->childLayout;
      return executeLayout();
   }
   else
   {
      return executePage();
   }
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Log in',
   'slug' => 'login',
   'body' => <<<EODEOD
<?php
require_once(INCLUDE_PATH . '/user.php');

if (user::isUserLoggedIn())
{
   header('Location: ' . ROOT_URL . '/admin');
}
?>
<html>
<head>
<title>Tadpole Login</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/files/admin-style.css" />
</head>
<body style="text-align: center;">

<div style="margin: auto; margin-top: 150px; width: 400px; text-align: left; background-color: white; padding: 10px; padding-bottom: 5px; -webkit-box-shadow: 3px 3px 6px #222; -moz-box-shadow: 3px 3px 6px #222; box-shadow: 3px 3px 6px #222;">

<div id="page-title">Tadpole Login</div>

<?php
include_once(INCLUDE_PATH . '/alert.php');
if (alert::hasErrors())
{
?>
<div id="errors">
<?php
   foreach (alert::getErrors() as \$message)
   {
      echo \$message . '<br />';
   }
?>
</div>
<?php
}
?>

<form action="<?php echo ROOT_URL; ?>/admin/do-login" method="post">
  <div class="field string-field"><label for="username">Username:</label><input style="width: 250px;" name="username" id="username" type="text" /></div>
  <div class="field string-field"><label for="password">Password:</label><input style="width: 250px;" name="password" id="password" type="password" /></div>
  <input type="submit" value="Log in" />
</form>

</div>

</body>
</html>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => NULL,
   'is_dynamic' => true,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Do login',
   'slug' => 'do-login',
   'body' => <<<EODEOD
<?php

require_once(INCLUDE_PATH . '/user.php');

if (user::logInUser(\$_POST['username'], sha1(\$_POST['password'])))
{
   header('Location: ' . ROOT_URL . '/admin');
}
else
{
   include_once(INCLUDE_PATH . '/alert.php');
   alert::addError('Login failed!');

   header('Location: ' . ROOT_URL . '/admin/login');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Log out',
   'slug' => 'logout',
   'body' => <<<EODEOD
<?php

require_once(INCLUDE_PATH . '/user.php');

user::logOutUser();
header('Location: ' . ROOT_URL . '/admin/login');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Create type',
   'slug' => 'create-type',
   'body' => <<<EODEOD
<p style="font-size: small; color: #aaa;">
Note: The Type Name will become the table's name in the database and the field names will become the table's column names.
</p>

<form action="<?php echo ROOT_URL; ?>/admin/do-create-type" method="post">

<label for="type_name">Type Name</label><br />
<input type="text" name="type_name" id="type_name" />
<br /><br />

<table>
<thead>
  <tr>
    <th>Field Name</th>
    <th>Type</th>
  </tr>
</thead>
<tbody id="field-list">
  <tr>
    <td><input style="width: 100%;" name="fields[0][name]" type="text" /></td>
    <td><select name="fields[0][type]">
<?php
\$dataTypes = db::getAll('data-type');
foreach (\$dataTypes as \$dataType)
{
   echo '      <option value="' . \$dataType->name . '">' . \$dataType->name . "</option>\\n";
}
?>
    </select></td>
  </tr>
</tbody>
</table>

<a href="#" onclick="addField(); return false;">Add new field</a>
<br /><br />

<input type="checkbox" name="create_table" id="create_table" checked="checked" /> <label for="create_table">Create table in database</label><br />
<input type="checkbox" name="generate_pages" id="generate_pages" checked="checked" /> <label for="generate_pages">Generate list, edit, save, and remove page templates</label><br />
<input type="submit" value="Create" />
</form>
EODEOD
   ,
   'head' => <<<EODEOD
<script type="text/javascript">
var fieldIndex = 0;
function addField()
{
   fieldIndex += 1;
   var html = '\\
  <tr>\\
    <td><input style="width: 100%;" name="fields[' + fieldIndex + '][name]" type="text" /></td>\\
    <td><select name="fields[' + fieldIndex + '][type]">\\
<?php
\$dataTypes = db::getAll('data-type');
foreach (\$dataTypes as \$dataType)
{
   echo '      <option value="' . \$dataType->name . '">' . \$dataType->name . "</option>\\\\\\n";
}
?>
    </select></td>\\
  </tr>';
   jQuery("#field-list").append(html);
}
</script>
EODEOD
   ,
   'parent_id' => 2,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Do create type',
   'slug' => 'do-create-type',
   'body' => <<<EODEOD
<?php

include_once(INCLUDE_PATH . '/alert.php');

\$typeName = \$_POST['type_name'];
\$fields = \$_POST['fields'];

if (empty(\$typeName))
{
   alert::addError('No type name specified.');
   header('Location: ' . ROOT_URL . '/admin/create-type');
   return;
}

if (isset(\$_POST['create_table']))
{
   \$tableDefinition = array();
   \$tableDefinition['id'] = 'autoincrement';
   foreach (\$fields as \$field)
   {
      \$dataType = db::get('data-type', array('name' => \$field['type']));
      \$tableDefinition[\$field['name']] = \$dataType->database_type;
   }

   \$result = db::createTable(\$typeName, \$tableDefinition, 'id');
   if (\$result == true)
   {
      alert::addAlert("Table '\$typeName' created successfully.");
   }
   else
   {
      alert::addError('An error occured while trying to create the table.');
      header('Location: ' . ROOT_URL . '/admin/create-type');
      return;
   }
}

if (isset(\$_POST['generate_pages']))
{
   // Generate 'human readable' versions of all the field names
   for (\$i = 0; \$i < count(\$fields); \$i += 1)
   {
      \$fields[\$i]['name_human_readable'] = ucwords(str_replace('_', ' ', \$fields[\$i]['name']));
   }

   // Generate pages
   \$inheritLayout = db::get('layout', array('name' => 'inherit'));

   \$GLOBALS['typeName'] = \$typeName;
   \$typeVarName = explode('-', \$typeName);
   for (\$i = 1; \$i < count(\$typeVarName); \$i += 1)
   {
      \$typeVarName[\$i] = ucfirst(\$typeVarName[\$i]);
   }
   \$typeVarName = implode('', \$typeVarName);
   \$GLOBALS['typeVarName'] = \$typeVarName;
   \$GLOBALS['fields'] = \$fields;

   // ====== Generate list page ======
   \$listPage = page::findPageByUrl('/admin/list');
   \$listTemplate = page::findPageByUrl('/admin/templates/list');
   \$title = ucwords(str_replace('-', ' ', \$typeName)) . 's';
   \$body = executeTemplatePage(\$listTemplate);
   \$result = db::add('page', array(
      'title' => \$title,
      'slug' => \$typeName,
      'body' => \$body,
      'parent_id' => \$listPage->id,
      'layout_id' => \$inheritLayout->id,
      'is_dynamic' => false,
      'created_time' => time(),
      'last_modified_time' => time()
   ));

   if (\$result == true)
   {
      alert::addAlert("Created /admin/list/\$typeName page.");
   }
   else
   {
      alert::addError("Error creating /admin/list/\$typeName page.");
   }

   // ====== Generate edit page ======
   \$editPage = page::findPageByUrl('/admin/edit');
   \$editTemplate = page::findPageByUrl('/admin/templates/edit');
   \$title = 'Edit ' . str_replace('-', ' ', \$typeName);
   \$body = executeTemplatePage(\$editTemplate);
   \$result = db::add('page', array(
      'title' => \$title,
      'slug' => \$typeName,
      'body' => \$body,
      'parent_id' => \$editPage->id,
      'layout_id' => \$inheritLayout->id,
      'is_dynamic' => true,
      'created_time' => time(),
      'last_modified_time' => time()
   ));

   if (\$result == true)
   {
      alert::addAlert("Created /admin/edit/\$typeName page.");
   }
   else
   {
      alert::addError("Error creating /admin/edit/\$typeName page.");
   }

   // ====== Generate save page ======
   \$savePage = page::findPageByUrl('/admin/save');
   \$saveTemplate = page::findPageByUrl('/admin/templates/save');
   \$title = 'Save ' . str_replace('-', ' ', \$typeName);
   \$body = executeTemplatePage(\$saveTemplate);
   \$result = db::add('page', array(
      'title' => \$title,
      'slug' => \$typeName,
      'body' => \$body,
      'parent_id' => \$savePage->id,
      'layout_id' => \$inheritLayout->id,
      'is_dynamic' => true,
      'created_time' => time(),
      'last_modified_time' => time()
   ));

   if (\$result == true)
   {
      alert::addAlert("Created /admin/save/\$typeName page.");
   }
   else
   {
      alert::addError("Error creating /admin/save/\$typeName page.");
   }

   // ====== Generate remove page ======
   \$removePage = page::findPageByUrl('/admin/remove');
   \$removeTemplate = page::findPageByUrl('/admin/templates/remove');
   \$title = 'Remove ' . str_replace('-', ' ', \$typeName);
   \$body = executeTemplatePage(\$removeTemplate);
   \$result = db::add('page', array(
      'title' => \$title,
      'slug' => \$typeName,
      'body' => \$body,
      'parent_id' => \$removePage->id,
      'layout_id' => \$inheritLayout->id,
      'is_dynamic' => true,
      'created_time' => time(),
      'last_modified_time' => time()
   ));

   if (\$result == true)
   {
      alert::addAlert("Created /admin/remove/\$typeName page.");
   }
   else
   {
      alert::addError("Error creating /admin/remove/\$typeName page.");
   }
}

// All template pages execute within this function
function executeTemplatePage(\$self)
{
   global \$typeName, \$typeVarName, \$fields;

   ob_start();
   eval('?>' . \$self->body);
   return ob_get_clean();
}

function replaceTemplateNames(\$code, \$field)
{
   global \$typeName, \$typeVarName;

   \$code = str_replace('TYPE_NAME', \$typeName, \$code);
   \$code = str_replace('TYPE_VAR_NAME', \$typeVarName, \$code);
   \$code = str_replace('FIELD_NAME_HUMAN_READABLE', \$field['name_human_readable'], \$code);
   \$code = str_replace('FIELD_NAME', \$field['name'], \$code);
   return \$code;
}

// Redirect to the new type's list page when we're done
if (isset(\$_POST['generate_pages']))
{
   header('Location: ' . ROOT_URL . '/admin/list/' . \$_POST['type_name']);
}
else
{
   header('Location: ' . ROOT_URL . '/admin/list/page');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Template pages',
   'slug' => 'templates',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => false,
));

addPage(array(
   'title' => 'List page template',
   'slug' => 'list',
   'body' => <<<EODEOD
<?php

// \$typeName and \$fields are already defined by the create type script

\$typeNameHumanReadable = str_replace('-', ' ', \$typeName);

echo <<<EOD
<table id="\$typeName-list" class="type-list">
<thead>
  <tr>

EOD;

foreach (\$fields as \$field)
{
   echo "    <th>{\$field['name_human_readable']}</th>\\n";
}

echo <<<EOD
    <th>Actions</th>
  </tr>
</thead>
<tbody>

<?php
\\\${\$typeVarName}s = db::getAll('\$typeName');
foreach (\\\${\$typeVarName}s as \\\$\$typeVarName)
{
?>
  <tr>

EOD;

foreach (\$fields as \$field)
{
   \$dataType = db::get('data-type', array('name' => \$field['type']));
   echo '    ' . replaceTemplateNames(\$dataType->list_code, \$field) . "\\n";
}

echo <<<EOD
    <td class="action-links">
      <a href="<?php echo ROOT_URL; ?>/admin/edit/\$typeName/<?php echo \\\${\$typeVarName}->id; ?>">Edit</a> |
      <a href="<?php echo ROOT_URL; ?>/admin/remove/\$typeName/<?php echo \\\${\$typeVarName}->id; ?>" onclick="javascript:return confirm('Are you sure you want to delete this \$typeNameHumanReadable?');">Remove</a>
    </td>
  </tr>
<?php
}
?>

</tbody>
</table>

<a href="<?php echo ROOT_URL; ?>/admin/edit/\$typeName/new">Add new \$typeNameHumanReadable</a>
EOD;

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 9,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit page template',
   'slug' => 'edit',
   'body' => <<<EODEOD
<?php

// \$typeName and \$fields are already defined by the create type script

echo <<<EOD
<?php

if (empty(\\\$query))
{
   echo 'No \$typeName specified.';
   return;
}

if (\\\$query != 'new')
{
   \\\$\$typeVarName = db::get('\$typeName', array('id' => \\\$query));
   if (!\\\$\$typeVarName)
   {
      echo 'Could not find \$typeName.';
      return;
   }
}
else
{
   \\\$\$typeVarName = new stdClass();
   // Assign default values here

EOD;

foreach (\$fields as \$field)
{
   \$dataType = db::get('data-type', array('name' => \$field['type']));
   echo '   ' . replaceTemplateNames(\$dataType->default_value_code, \$field) . "\\n";
}

echo <<<EOD
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin/save/\$typeName/<?php echo \\\$query; ?>">

EOD;

foreach (\$fields as \$field)
{
   \$dataType = db::get('data-type', array('name' => \$field['type']));
   echo '  ' . replaceTemplateNames(\$dataType->edit_code, \$field) . "\\n";
}

echo <<<EOD

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin/list/\$typeName">Cancel</a>
  </div>
</form>
EOD;

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 9,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save page template',
   'slug' => 'save',
   'body' => <<<EODEOD
<?php

echo <<<EOD
<?php

// Parse query
if (empty(\\\$query))
{
   echo 'No \$typeName specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\\\$query != 'new')
{
   if (db::getCount('\$typeName', array('id' => \\\$query)) < 1)
   {
      alert::addError('Could not find \$typeName.');
      header('Location: ' . ROOT_URL . '/admin/list/\$typeName');
      return;
   }
}

// Validate fields
\\\$fields = array();

EOD;

foreach (\$fields as \$field)
{
   \$dataType = db::get('data-type', array('name' => \$field['type']));
   echo replaceTemplateNames(\$dataType->save_code, \$field) . "\\n";
}

echo <<<EOD

// Update database with new data
if (\\\$query == 'new')
{
   \\\$result = db::add('\$typeName', \\\$fields);
   if (\\\$result == false)
   {
      alert::addError('Error adding new \$typeName.');
   }
   else
   {
      alert::addAlert('\$typeName saved.');
      \\\$query = db::lastInsertId();
   }
}
else
{
   \\\$result = db::update('\$typeName', \\\$fields, array('id' => \\\$query));
   if (\\\$result == false)
   {
      alert::addError('Error saving \$typeName.');
   }
   else
   {
      alert::addAlert('\$typeName saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\\\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin/edit/\$typeName/' . \\\$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin/list/\$typeName');
}

?>
EOD;

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 9,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove page template',
   'slug' => 'remove',
   'body' => <<<EODEOD
<?php

echo <<<EOD
<?php

if (empty(\\\$query))
{
   echo 'No \$typeName specified.';
   return;
}

db::remove('\$typeName', array('id' => \\\$query));

header('Location: ' . ROOT_URL . '/admin/list/\$typeName');

?>
EOD;

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 9,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'List',
   'slug' => 'list',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => false,
));

addPage(array(
   'title' => 'Edit',
   'slug' => 'edit',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => false,
));

addPage(array(
   'title' => 'Save',
   'slug' => 'save',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => false,
));

addPage(array(
   'title' => 'Remove',
   'slug' => 'remove',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => false,
));

addPage(array(
   'title' => 'PHP Console',
   'slug' => 'console',
   'body' => <<<EODEOD
<?php

if (isset(\$_POST['code']))
{
   echo '<strong>Output:</strong><br />';
   ob_start();
   eval(\$_POST['code']);
   \$output = ob_get_clean();
   echo '<pre>' . htmlspecialchars(\$output) . '</pre>';
}

?>

<form action="<?php echo ROOT_URL; ?>/admin/console" method="post">
   <strong>PHP code to execute:</strong> <small>(&lt;?php and ?&gt; tags not required)</small><br />
   <textarea name="code" cols="80" rows="20"><?php 
if (isset(\$_POST['code']))
{
   echo htmlspecialchars(\$_POST['code']);
}
   ?></textarea><br />
   <input type="submit" value="Run" />
</form>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'About Page',
   'slug' => 'about',
   'body' => <<<EODEOD
This is an about page.<br /><br />

This is a list of  all the pages on this site:
<ul>
<?php

\$pages = db::getAll('page', array('order_by' => 'title'));
foreach (\$pages as \$page)
{
   echo "   <li>\$page->title</li>\\n";
}

?>
</ul>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 1,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Export',
   'slug' => 'export',
   'body' => <<<EODEOD
<?php
\$rows = db::getAll('table-field', array('columns' => array('table_name')));
\$tables = array();
foreach (\$rows as \$row)
{
   \$table = \$row->table_name;
   if (!in_array(\$table, \$tables))
   {
      \$tables[] = \$table;
   }
}

foreach (\$tables as \$table)
{
   echo \$table . ' <a href="' . ROOT_URL . '/admin/do-export/' . \$table . '">Export</a><br />';
}
?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Do export',
   'slug' => 'do-export',
   'body' => <<<EODEOD
<?php

\$tableName = \$query;

\$fields = db::getAll('table-field', array('table_name' => \$tableName));
\$primaryKey = db::get('table-primary-key', array('table_name' => \$tableName));

echo <<<EOD
<?php

db::createTable('\$tableName', array(

EOD;

foreach (\$fields as \$field)
{
   echo <<<EOD
   '\$field->name' => '\$field->type',

EOD;
}

if (\$primaryKey)
{
   echo <<<EOD
), '\$primaryKey->name');


EOD;
}
else
{
   echo <<<EOD
));


EOD;
}

\$addFunctionName = 'add' . implode('', array_map('ucfirst', explode('-', \$tableName)));

echo <<<EOD
function \$addFunctionName(\\\$fields)
{
   \\\$result = db::add('\$tableName', \\\$fields);

   if (!\\\$result)
   {
      echo "<strong>Error creating \$tableName '' (" . db::getErrorMessage() . ").</strong><br />\\\\n";
      \\\$success = false;
   }
   else
   {
      echo "Created \$tableName ''.<br />\\\\n";
   }
}


EOD;

\$rows = db::getAll(\$tableName);
foreach (\$rows as \$row)
{
   echo "\$addFunctionName(array(\\n";
   foreach (\$row as \$fieldName => \$fieldValue)
   {
      \$fieldType = NULL;
      foreach (\$fields as \$field)
      {
         if (\$field->name == \$fieldName)
         {
            \$fieldType = \$field->type;
            break;
         }
      }

      if (\$fieldType == 'autoincrement')
      {
         // We don't need to include autoincrement fields since they will be updated automatically
         continue;
      }

      echo "   '\$fieldName' => ";

      if (is_null(\$fieldValue))
      {
         echo "NULL,\\n";
      }
      else if (\$fieldType == 'integer' || \$fieldType == 'float')
      {
         echo "\$fieldValue,\\n";
      }
      else if (\$fieldType == 'boolean')
      {
         echo ((\$fieldValue) ? 'true' : 'false') . ",\\n";
      }
      else if (\$fieldType == 'string')
      {
         echo "'" . quoteString(\$fieldValue) . "',\\n";
      }
      else if (\$fieldType == 'text')
      {
         echo "<<<EODEOD\\n";
         echo quoteText(\$fieldValue);
         echo "\\nEODEOD\\n";
         echo "   ,\\n";
      }
   }
   echo "));\\n";
   echo "\\n";
}

echo "?>";

function quoteString(\$string)
{
   \$string = str_replace('\\\\', '\\\\\\\\', \$string);
   \$string = str_replace("'", "\\'", \$string);
   return \$string;
}

function quoteText(\$text)
{
   \$text = str_replace('\\\\', '\\\\\\\\', \$text);
   \$text = str_replace('\$', '\\\$', \$text);
   return \$text;
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => true,
   'created_time' => '1292891026',
   'last_modified_time' => '1292891026',
   'expanded' => true,
));

addPage(array(
   'title' => 'Users',
   'slug' => 'user',
   'body' => <<<EODEOD
<table id="user-list" class="type-list">
<thead>
  <tr>
    <th>Username</th>
    <th>Roles</th>
    <th>Actions</th>
  </tr>
</thead>
<tbody>

<?php
\$users = db::getAll('user');
foreach (\$users as \$user)
{
?>
  <tr>
    <td class="string-preview"><?php echo htmlspecialchars(\$user->username); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$user->roles, 0, 50)); ?></td>
    <td class="action-links">
      <a href="<?php echo ROOT_URL; ?>/admin/edit/user/<?php echo \$user->username; ?>">Edit</a> |
      <a href="<?php echo ROOT_URL; ?>/admin/remove/user/<?php echo \$user->username; ?>" onclick="javascript:return confirm('Are you sure you want to delete this user?');">Remove</a>
    </td>
  </tr>
<?php
}
?>

</tbody>
</table>

<a href="<?php echo ROOT_URL; ?>/admin/edit/user/new">Add new user</a>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 14,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891549',
   'last_modified_time' => '1292891549',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit user',
   'slug' => 'user',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No user specified.';
   return;
}

if (\$query != 'new')
{
   \$user = db::get('user', array('username' => \$query));
   if (!\$user)
   {
      echo 'Could not find user.';
      return;
   }
}
else
{
   \$user = new stdClass();
   // Assign default values here
   \$user->username = '';
   \$user->password_hash = '';
   \$user->roles = '';
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin/save/user/<?php echo \$query; ?>">
  <div class="field string-field"><label for="field-username">Username</label> <input type="text" name="username" id="field-username" value="<?php echo htmlspecialchars(\$user->username); ?>" /></div>
  <div class="field string-field"><label for="field-password_hash" style="width: 200px;">New Password:</label> <input type="password" name="new_password" id="field-new_password" /></div>
  <div class="field string-field"><label for="field-password_hash" style="width: 200px;">Confirm Password:</label> <input type="password" name="new_password2" id="field-new_password2" /></div>
  <div class="field textbox-field"><label for="field-roles">Roles</label> <textarea name="roles" id="field-roles" rows="10" cols="100"><?php echo htmlspecialchars(\$user->roles); ?></textarea></div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin/list/user">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 15,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891549',
   'last_modified_time' => '1292891549',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save user',
   'slug' => 'user',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No user specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('user', array('username' => \$query)) < 1)
   {
      alert::addError('Could not find user.');
      header('Location: ' . ROOT_URL . '/admin/list/user');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate username
if (isset(\$_POST['username']))
{
   // No validation for normal strings
   \$fields['username'] = \$_POST['username'];
}
// Validate new password
if (isset(\$_POST['new_password']))
{
   if (isset(\$_POST['new_password2']) && \$_POST['new_password2'] == \$_POST['new_password'])
   {
      \$fields['password_hash'] = sha1(\$_POST['new_password']);
   }
   else
   {
      alert::addError('New passwords do not match.');
   }
}
// Validate roles
if (isset(\$_POST['roles']))
{
   // No validation for normal textboxes
   \$fields['roles'] = \$_POST['roles'];
}

// Update database with new data
if (\$query == 'new')
{
   \$result = db::add('user', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new user.');
   }
   else
   {
      alert::addAlert('user saved.');
      \$query = \$fields['username'];
   }
}
else
{
   \$result = db::update('user', \$fields, array('username' => \$query));
   if (\$result == false)
   {
      alert::addError('Error saving user.');
   }
   else
   {
      alert::addAlert('user saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin/edit/user/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin/list/user');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 16,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891549',
   'last_modified_time' => '1292891549',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove user',
   'slug' => 'user',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No user specified.';
   return;
}

db::remove('user', array('username' => \$query));

header('Location: ' . ROOT_URL . '/admin/list/user');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 17,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891549',
   'last_modified_time' => '1292891549',
   'expanded' => true,
));

addPage(array(
   'title' => 'Data Types',
   'slug' => 'data-type',
   'body' => <<<EODEOD
<table id="data-type-list" class="type-list">
<thead>
  <tr>
    <th>Name</th>
    <th>Database type</th>
    <th>Default value code</th>
    <th>List code</th>
    <th>Edit code</th>
    <th>Save code</th>
    <th>Actions</th>
  </tr>
</thead>
<tbody>

<?php
\$datas = db::getAll('data-type');
foreach (\$datas as \$data)
{
?>
  <tr>
    <td class="string-preview"><?php echo htmlspecialchars(\$data->name); ?></td>
    <td class="string-preview"><?php echo htmlspecialchars(\$data->database_type); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$data->default_value_code, 0, 50)); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$data->list_code, 0, 50)); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$data->edit_code, 0, 50)); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$data->save_code, 0, 50)); ?></td>
    <td class="action-links">
      <a href="<?php echo ROOT_URL; ?>/admin/edit/data-type/<?php echo \$data->id; ?>">Edit</a> |
      <a href="<?php echo ROOT_URL; ?>/admin/remove/data-type/<?php echo \$data->id; ?>" onclick="javascript:return confirm('Are you sure you want to delete this data type?');">Remove</a>
    </td>
  </tr>
<?php
}
?>

</tbody>
</table>

<a href="<?php echo ROOT_URL; ?>/admin/edit/data-type/new">Add new data type</a>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 14,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891606',
   'last_modified_time' => '1292891606',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit data type',
   'slug' => 'data-type',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No data-type specified.';
   return;
}

if (\$query != 'new')
{
   \$data = db::get('data-type', array('id' => \$query));
   if (!\$data)
   {
      echo 'Could not find data-type.';
      return;
   }
}
else
{
   \$data = new stdClass();
   // Assign default values here
   \$data->name = '';
   \$data->database_type = '';
   \$data->default_value_code = '';
   \$data->list_code = '';
   \$data->edit_code = '';
   \$data->save_code = '';
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin/save/data-type/<?php echo \$query; ?>">
  <div class="field string-field"><label for="field-name">Name</label> <input type="text" name="name" id="field-name" value="<?php echo htmlspecialchars(\$data->name); ?>" /></div>
  <div class="field string-field"><label for="field-database_type">Database type</label> <input type="text" name="database_type" id="field-database_type" value="<?php echo htmlspecialchars(\$data->database_type); ?>" /></div>
  <div class="field textbox-field"><label for="field-default_value_code">Default value code</label> <textarea name="default_value_code" id="field-default_value_code" rows="10" cols="100"><?php echo htmlspecialchars(\$data->default_value_code); ?></textarea></div>
  <div class="field textbox-field"><label for="field-list_code">List code</label> <textarea name="list_code" id="field-list_code" rows="10" cols="100"><?php echo htmlspecialchars(\$data->list_code); ?></textarea></div>
  <div class="field textbox-field"><label for="field-edit_code">Edit code</label> <textarea name="edit_code" id="field-edit_code" rows="10" cols="100"><?php echo htmlspecialchars(\$data->edit_code); ?></textarea></div>
  <div class="field textbox-field"><label for="field-save_code">Save code</label> <textarea name="save_code" id="field-save_code" rows="10" cols="100"><?php echo htmlspecialchars(\$data->save_code); ?></textarea></div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin/list/data-type">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 15,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891606',
   'last_modified_time' => '1292891606',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save data type',
   'slug' => 'data-type',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No data-type specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('data-type', array('id' => \$query)) < 1)
   {
      alert::addError('Could not find data-type.');
      header('Location: ' . ROOT_URL . '/admin/list/data-type');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate name
if (isset(\$_POST['name']))
{
   // No validation for normal strings
   \$fields['name'] = \$_POST['name'];
}
// Validate database_type
if (isset(\$_POST['database_type']))
{
   // No validation for normal strings
   \$fields['database_type'] = \$_POST['database_type'];
}
// Validate default_value_code
if (isset(\$_POST['default_value_code']))
{
   // No validation for normal textboxes
   \$fields['default_value_code'] = \$_POST['default_value_code'];
}
// Validate list_code
if (isset(\$_POST['list_code']))
{
   // No validation for normal textboxes
   \$fields['list_code'] = \$_POST['list_code'];
}
// Validate edit_code
if (isset(\$_POST['edit_code']))
{
   // No validation for normal textboxes
   \$fields['edit_code'] = \$_POST['edit_code'];
}
// Validate save_code
if (isset(\$_POST['save_code']))
{
   // No validation for normal textboxes
   \$fields['save_code'] = \$_POST['save_code'];
}

// Update database with new data
if (\$query == 'new')
{
   \$result = db::add('data-type', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new data-type.');
   }
   else
   {
      alert::addAlert('data-type saved.');
      \$query = db::lastInsertId();
   }
}
else
{
   \$result = db::update('data-type', \$fields, array('id' => \$query));
   if (\$result === false)
   {
      alert::addError('Error saving data-type.');
   }
   else
   {
      alert::addAlert('data-type saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin/edit/data-type/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin/list/data-type');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 16,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891606',
   'last_modified_time' => '1292891606',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove data type',
   'slug' => 'data-type',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No data-type specified.';
   return;
}

db::remove('data-type', array('id' => \$query));

header('Location: ' . ROOT_URL . '/admin/list/data-type');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 17,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891606',
   'last_modified_time' => '1292891606',
   'expanded' => true,
));

addPage(array(
   'title' => 'Layouts',
   'slug' => 'layout',
   'body' => <<<EODEOD
<table id="layout-list" class="type-list">
<thead>
  <tr>
    <th>Name</th>
    <th>Body</th>
    <th>Parent Layout</th>
    <th>Actions</th>
  </tr>
</thead>
<tbody>

<?php
\$layouts = db::getAll('layout');
foreach (\$layouts as \$layout)
{
?>
  <tr>
    <td class="string-preview"><?php echo htmlspecialchars(\$layout->name); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$layout->body, 0, 50)); ?></td>
    <td class="string-preview">
<?php
if (\$layout->parent_id)
{
   \$parentLayout = db::get('layout', array('id' => \$layout->parent_id));
   if (isset(\$parentLayout->name))
   {
      echo \$parentLayout->name;
   }
}
?>
    </td>
    <td class="action-links">
      <a href="<?php echo ROOT_URL; ?>/admin/edit/layout/<?php echo \$layout->id; ?>">Edit</a> |
      <a href="<?php echo ROOT_URL; ?>/admin/remove/layout/<?php echo \$layout->id; ?>" onclick="javascript:return confirm('Are you sure you want to delete this layout?');">Remove</a>
    </td>
  </tr>
<?php
}
?>

</tbody>
</table>

<a href="<?php echo ROOT_URL; ?>/admin/edit/layout/new">Add new layout</a>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 14,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292891625',
   'last_modified_time' => '1292891625',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit layout',
   'slug' => 'layout',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No layout specified.';
   return;
}

if (\$query != 'new')
{
   \$layout = db::get('layout', array('id' => \$query));
   if (!\$layout)
   {
      echo 'Could not find layout.';
      return;
   }
}
else
{
   \$layout = new stdClass();
   // Assign default values here
   \$layout->name = '';
   \$layout->body = '';
   \$layout->parent_id = 0;
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin/save/layout/<?php echo \$query; ?>">
  <div class="field string-field"><label for="field-name">Name</label> <input type="text" name="name" id="field-name" value="<?php echo htmlspecialchars(\$layout->name); ?>" /></div>
  <div class="field textbox-field"><label for="field-body">Body</label> <textarea name="body" id="field-body" rows="10" cols="100"><?php echo htmlspecialchars(\$layout->body); ?></textarea></div>
  <div class="field"><label for="field-parent_id">Parent Layout</label>
  <select name="parent_id" id="field-parent_id">
    <option>None</option>
<?php
\$layouts = db::getAll('layout');
foreach (\$layouts as \$parentLayout)
{
   echo "<option value=\\"\$parentLayout->id\\"";
   if (\$parentLayout->id == \$layout->parent_id)
   {
      echo ' selected="selected"';
   }
   echo ">\$parentLayout->name</option>\\n";
}
?>
  <select>
  </div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin/list/layout">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 15,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891625',
   'last_modified_time' => '1292891625',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save layout',
   'slug' => 'layout',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No layout specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('layout', array('id' => \$query)) < 1)
   {
      alert::addError('Could not find layout.');
      header('Location: ' . ROOT_URL . '/admin/list/layout');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate name
if (isset(\$_POST['name']))
{
   // No validation for normal strings
   \$fields['name'] = \$_POST['name'];
}
// Validate body
if (isset(\$_POST['body']))
{
   // No validation for normal textboxes
   \$fields['body'] = \$_POST['body'];
}
// Validate parent_id
if (isset(\$_POST['parent_id']))
{
   if (\$_POST['parent_id'] == 0)
   {
      \$fields['parent_id'] = NULL;
   }
   else if (!is_numeric(\$_POST['parent_id']) || strpos(\$_POST['parent_id'], '.') != false)
   {

      alert::addError('Parent id must be an integer.');
   }
   else
   {
      \$fields['parent_id'] = \$_POST['parent_id'];
   }
}

// Update database with new data
if (\$query == 'new')
{
   \$result = db::add('layout', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new layout.');
   }
   else
   {
      alert::addAlert('layout saved.');
      \$query = db::lastInsertId();
   }
}
else
{
   \$result = db::update('layout', \$fields, array('id' => \$query));
   if (\$result === false)
   {
      alert::addError('Error saving layout.');
   }
   else
   {
      alert::addAlert('layout saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin/edit/layout/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin/list/layout');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 16,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891625',
   'last_modified_time' => '1292891625',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove layout',
   'slug' => 'layout',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No layout specified.';
   return;
}

db::remove('layout', array('id' => \$query));

header('Location: ' . ROOT_URL . '/admin/list/layout');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 17,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292891625',
   'last_modified_time' => '1292891625',
   'expanded' => true,
));

addPage(array(
   'title' => 'Pages',
   'slug' => 'page',
   'body' => <<<EODEOD
<ul id="page-list">
<?php

function listPageRecursively(\$page)
{
   \$childPages = \$page->getChildren();

   echo '<li>';
   echo '<div>';
   if (!empty(\$childPages))
   {
      \$plusOrMinus = ((\$page->expanded) ? '-' : '+');
      echo '  <a id="page-toggle-' . \$page->id . '" class="page-toggle" href="javascript:void(0);" onclick="toggleExpandPage(' . \$page->id . ');">' . \$plusOrMinus . '</a>';
   }
   echo '  <a class="page-title" href="' . ROOT_URL . \$page->getPath() . '">' . \$page->title . '</a>';
   echo '  <span style="float: right;">';
   echo '    <a  href="' . ROOT_URL . '/admin/edit/page/' . \$page->id . '">Edit Page</a> | ';
   echo '    <a href="' . ROOT_URL . '/admin/edit/page/new/' . \$page->id . '">Add Child</a> | ';
   echo '    <a href="' . ROOT_URL . '/admin/make-duplicate-page/' . \$page->id . '">Make Duplicate</a> | ';
   echo '    <a href="' . ROOT_URL . '/admin/remove/page/' . \$page->id . '" onclick="javascript:return confirm(\\'Are you sure you want to delete the page \\\\\\'' . \$page->title . '\\\\\\'?\\');">Delete Page</a>';
   echo '  </span>';
   echo '</div>';

   if (!empty(\$childPages))
   {
      echo '<ul id="page-' . \$page->id . '-children">';
      foreach (\$childPages as \$childPage)
      {
         listPageRecursively(\$childPage);
      }
      echo '</ul>';
   }

   echo '</li>';
}

listPageRecursively(page::getRootPage());

?>
</ul>
EODEOD
   ,
   'head' => <<<EODEOD
<style type="text/css">
#page-list li { list-style: none; }
#page-list li div { border-bottom: 1px solid #ccc; line-height: 25px; }
#page-list li div:hover { background: #eee; }
#page-list a.page-title { color: black; text-decoration: none; font-weight: bold; padding-left: 3px; }
#page-list a.page-toggle { font-family: monospace; text-decoration: none; padding: 0 3px; margin-left: -20px; }
</style>
<script type="text/javascript">
\$(document).ready(function() {
<?php
\$pages = db::getAll('page');
foreach (\$pages as \$page)
{
   if (!\$page->expanded)
   {
      echo '   \$("#page-' . \$page->id . '-children").hide();';
   }
}
?>
});

function toggleExpandPage(pageId)
{
   \$("#page-" + pageId + "-children").toggle();
   var plusOrMinus = \$("#page-toggle-" + pageId).html();
   if (plusOrMinus == '+')
   {
      \$.get('<?php echo ROOT_URL; ?>/admin/expand-page/' + pageId);
      plusOrMinus = '-';
   }
   else
   {
      \$.get('<?php echo ROOT_URL; ?>/admin/unexpand-page/' + pageId);
      plusOrMinus = '+';
   }
   \$("#page-toggle-" + pageId).html(plusOrMinus);
}
</script>
EODEOD
   ,
   'parent_id' => 14,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292904451',
   'last_modified_time' => '1292904451',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit page',
   'slug' => 'page',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No page specified.';
   return;
}

\$query = explode('/', \$query);
if (\$query[0] != 'new')
{
   \$page = db::get('page', array('id' => \$query[0]));
   if (!\$page)
   {
      echo 'Could not find page.';
      return;
   }
}
else
{
   \$page = new stdClass();
   // Assign default values here
   \$page->title = 'Page Title';
   \$page->slug = 'page-title';
   \$page->body = '';
   \$page->head = '';
   \$page->parent_id = (int)(\$query[1]);
   \$page->layout_id = NULL;
   \$page->is_dynamic = false;
   \$page->created_time = (string)(time());
   \$page->last_modified_time = \$page->created_time;
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin/save/page/<?php echo \$query[0]; ?>">
  <div class="field string-field"><label for="field-title">Title</label> <input type="text" name="title" id="field-title" value="<?php echo htmlspecialchars(\$page->title); ?>" /></div>
  <div class="field string-field"><label for="field-slug">Slug</label> <input type="text" name="slug" id="field-slug" value="<?php echo htmlspecialchars(\$page->slug); ?>" /></div>
  <div class="field textbox-field"><label id="head-label" for="field-head">Head</label> <textarea name="head" id="field-head" rows="10" cols="100"><?php echo htmlspecialchars(\$page->head); ?></textarea></div>
  <div class="field textbox-field"><label for="field-body">Body</label> <textarea name="body" id="field-body" rows="25" cols="100"><?php echo htmlspecialchars(\$page->body); ?></textarea></div>

  <div class="field" style="float: left; margin-right: 5px;"><label for="field-parent_id">Parent Page</label>
    <select name="parent_id" id="field-parent_id">
<?php
\$pages = db::getAll('page', array('order_by' => 'parent_id ASC'));
foreach (\$pages as \$parentPage)
{
   if (\$parentPage->id == \$page->id)
   {
      // We can't make a page a child of itself!, skip it
      continue;
   }

   \$parentPage = new Page(\$parentPage);
   echo '<option value="' . \$parentPage->id . '"';
   if (\$parentPage->id == \$page->parent_id)
   {
      echo ' selected="selected"';
   }
   echo ">" . \$parentPage->getPath() . "</option>n";
}
?>
    </select>
  </div>

  <div class="field" style="float: left; margin-right: 5px;"><label for="field-layout_id">Layout</label>
    <select name="layout_id" id="field-layout_id">
      <option value="0">None</option>
<?php
\$layouts = db::getAll('layout');
foreach (\$layouts as \$layout)
{
   echo '<option value="' . \$layout->id . '"';
   if (\$layout->id == \$page->layout_id)
   {
      echo ' selected="selected"';
   }
   echo ">\$layout->name</option>\\n";
}
?>
    </select>
  </div>

  <div class="field" style="float: left;"><label for="field-is_dynamic">Dynamic Page</label> <input type="checkbox" name="is_dynamic" id="field-is_dynamic" <?php if (\$page->is_dynamic) echo 'checked="checked" '; ?>/></div>

  <div style="clear: both;"></div>

  <div class="field string-field"><label for="field-created_time">Created On</label> <?php echo date('l, F j, Y, g:i:s A', \$page->created_time); ?></div>
  <div class="field string-field"><label for="field-last_modified_time">Last Modified</label> <?php echo date('l, F j, Y, g:i:s A', \$page->last_modified_time); ?></div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin/list/page">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => <<<EODEOD
<script type="text/javascript">
\$(document).ready(function() {
   \$("#field-head").hide();
   \$("#head-label").click(function() {
      \$("#field-head").toggle();
   });
   \$("#head-label").css('cursor', 'pointer');
});
</script>
EODEOD
   ,
   'parent_id' => 15,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292904451',
   'last_modified_time' => '1292904451',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save page',
   'slug' => 'page',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No page specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('page', array('id' => \$query)) < 1)
   {
      alert::addError('Could not find page.');
      header('Location: ' . ROOT_URL . '/admin/list/page');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate title
if (isset(\$_POST['title']))
{
   // No validation for normal strings
   \$fields['title'] = \$_POST['title'];
}
// Validate slug
if (isset(\$_POST['slug']))
{
   // No validation for normal strings
   \$fields['slug'] = \$_POST['slug'];
}
// Validate body
if (isset(\$_POST['body']))
{
   // No validation for normal textboxes
   \$fields['body'] = \$_POST['body'];
}
// Validate head
if (isset(\$_POST['head']))
{
   // No validation for normal textboxes
   \$fields['head'] = \$_POST['head'];
}
// Validate parent_id
if (isset(\$_POST['parent_id']))
{
   if (!is_numeric(\$_POST['parent_id']) || strpos(\$_POST['parent_id'], '.') != false)
   {
      alert::addError('Parent id must be an integer.');
   }
   else
   {
      \$fields['parent_id'] = \$_POST['parent_id'];
   }
}
// Validate layout_id
if (isset(\$_POST['layout_id']))
{
   if (\$_POST['layout_id'] == 0)
   {
      \$fields['layout_id'] = NULL;
   }
   else if (!is_numeric(\$_POST['layout_id']) || strpos(\$_POST['layout_id'], '.') != false)
   {
      alert::addError('Layout id must be an integer.');
   }
   else
   {
      \$fields['layout_id'] = \$_POST['layout_id'];
   }
}
// Validate is_dynamic
if (isset(\$_POST['is_dynamic']))
{
   \$fields['is_dynamic'] = 1;
}
else
{
   \$fields['is_dynamic'] = 0;
}
// Validate created_time
if (isset(\$_POST['created_time']))
{
   // No validation for normal strings
   \$fields['created_time'] = \$_POST['created_time'];
}
// Validate last_modified_time
if (isset(\$_POST['last_modified_time']))
{
   // No validation for normal strings
   \$fields['last_modified_time'] = \$_POST['last_modified_time'];
}

// Update database with new data
if (\$query == 'new')
{
   \$fields['created_time'] = (string)(time());
   \$fields['last_modified_time'] = \$fields['created_time'];

   \$result = db::add('page', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new page.');
   }
   else
   {
      alert::addAlert('page saved.');
      \$query = db::lastInsertId();
   }
}
else
{
   \$fields['last_modified_time'] = (string)(time());

   \$result = db::update('page', \$fields, array('id' => \$query));
   if (\$result === false)
   {
      alert::addError('Error saving page.');
   }
   else
   {
      alert::addAlert('page saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin/edit/page/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin/list/page');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 16,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292904451',
   'last_modified_time' => '1292904451',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove page',
   'slug' => 'page',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No page specified.';
   return;
}

db::remove('page', array('id' => \$query));

header('Location: ' . ROOT_URL . '/admin/list/page');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 17,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292904451',
   'last_modified_time' => '1292904451',
   'expanded' => true,
));

addPage(array(
   'title' => 'Make Duplicate Page',
   'slug' => 'make-duplicate-page',
   'body' => <<<EODEOD
<?php

include_once(INCLUDE_PATH . '/alert.php');

if (empty(\$query))
{
   alert::addError('No page specified.');
   header('Location: ' . ROOT_URL . '/admin/list/page');
   return;
}

\$page = db::get('page', array('id' => \$query));
if (!\$page)
{
   alert::addError('Could not find page.');
   header('Location: ' . ROOT_URL . '/admin/list/page');
   return;
}

\$page = (array)(\$page);
unset(\$page['id']);
\$page['title'] .= ' Copy';
\$page['slug'] .= '-copy';
\$page['created_time'] = (string)(time());
\$page['last_modified_time'] = \$page['created_time'];

\$ok = db::add('page', \$page);

if (!\$ok)
{
   alert::addError("Error creating duplicate page '{\$page['title']}' (" . db::getErrorMessage() . ").");
}
else
{
   alert::addAlert("Created duplicate page '{\$page['title']}'.");
}

header('Location: ' . ROOT_URL . '/admin/list/page');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => true,
   'created_time' => NULL,
   'last_modified_time' => NULL,
   'expanded' => true,
));

addPage(array(
   'title' => 'Loader',
   'slug' => 'loader',
   'body' => <<<EODEOD
<?php

\$rootPage = page::getRootPage();

if (!isset(\$_GET['q']) || trim(\$_GET['q'], '/') == '')
{
   echo renderPage(\$rootPage);
}
else
{
   \$query = trim(\$_GET['q'], '/');

   \$page = \$rootPage;
   \$foundPage = true;
   foreach (explode('/', \$query) as \$slug)
   {
      \$childPage = page::findPageBySlug(\$slug, \$page);
      if (!\$childPage)
      {
         // Could not find page with the specified URL
         \$foundPage = false;
         break;
      }

      \$page = \$childPage;
   }

   if (\$foundPage)
   {
      echo renderPage(\$page);
   }
   else
   {
      // Go backwards and try to find a dynamic page
      \$foundDynamicPage = false;
      while (\$page)
      {
         if (\$page->is_dynamic)
         {
            \$foundDynamicPage = true;
            break;
         }

         \$page = \$page->getParent();
      }

      if (\$foundDynamicPage)
      {
         \$path = trim(\$page->getPath(), '/');
         // New query equals everything after the dynamic page's path in the original query
         // So /admin/edit/page/5 becomes just 5, since /admin/edit/page is a dynamic page
         // It's a bit confusing at first, but just think of the five as the ?query=5 part of /admin/edit/page?query=5
         \$query = substr(\$query, strlen(\$path) + 1);

         echo renderPage(\$page, \$query);
      }
      else
      {
         // Couldn't find the page or any dynamic page to handle the query, send a 404 error
         header('HTTP/1.0 404 Not Found');
         echo "Sorry, couldn't find the page!";
      }
   }
}

// Evaluates and returns the contents of a page, and handles layouts
function renderPage(\$page, \$query = '')
{
   if (\$page->layout_id == NULL)
   {
      \$GLOBALS['self'] = \$page;
      \$GLOBALS['query'] = \$query;
      return executePage();
   }
   else
   {
      \$layout = db::get('layout', array('id' => \$page->layout_id));

      if (\$layout->name == 'inherit')
      {
         // Go up chain of parent pages until we find one with a layout that isn't the 'inherit' layout too
         \$inheritId = \$layout->id;
         \$parentPage = \$page->getParent();
         while (\$parentPage != NULL && \$parentPage->layout_id == \$inheritId)
         {
            \$parentPage = \$parentPage->getParent();
         }

         if (\$parentPage == NULL)
         {
            \$page->layout_id = NULL;
         }
         else
         {
            \$page->layout_id = \$parentPage->layout_id;
         }

         return renderPage(\$page, \$query);
      }

      // Create chain of layouts
      while (\$layout->parent_id != NULL)
      {
         \$parentLayout = db::get('layout', array('id' => \$layout->parent_id));
         if (!\$parentLayout)
         {
            break;
         }
         \$parentLayout->childLayout = \$layout;
         \$layout = \$parentLayout;
      }

      \$GLOBALS['self'] = \$page;
      \$GLOBALS['query'] = \$query;
      \$GLOBALS['layout'] = \$layout;
      return executeLayout();
   }
}

// All layouts execute within this function
function executeLayout()
{
   global \$self, \$query, \$layout;

   ob_start();
   eval('?>' . \$layout->body);
   \$output = ob_get_clean();

   return \$output;
}

// All pages execute within this function
function executePage()
{
   global \$self, \$query;

   ob_start();
   eval('?>' . \$self->body);
   \$output = ob_get_clean();

   return \$output;
}

// Returns the contents of the current page, or the contents of the current layout's child layout if it has one
// Should only be called from within layouts
function content()
{
   global \$self, \$query, \$layout;

   if (isset(\$layout->childLayout))
   {
      \$layout = \$layout->childLayout;
      return executeLayout();
   }
   else
   {
      return executePage();
   }
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292991745',
   'last_modified_time' => '1292991745',
   'expanded' => true,
));

addPage(array(
   'title' => 'Admin Backup',
   'slug' => 'admin-backup',
   'body' => <<<EODEOD
<?php header('Location: ' . ROOT_URL . '/admin-backup/list/page'); ?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 1,
   'layout_id' => 6,
   'is_dynamic' => false,
   'created_time' => '1292991740',
   'last_modified_time' => '1292991740',
   'expanded' => false,
));

addPage(array(
   'title' => 'Log in',
   'slug' => 'login',
   'body' => <<<EODEOD
<?php
require_once(INCLUDE_PATH . '/user.php');

if (user::isUserLoggedIn())
{
   header('Location: ' . ROOT_URL . '/admin-backup');
}
?>
<html>
<head>
<title>Tadpole Login</title>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/files/admin-style.css" />
</head>
<body style="text-align: center;">

<div style="margin: auto; margin-top: 150px; width: 400px; text-align: left; background-color: white; padding: 10px; padding-bottom: 5px; -webkit-box-shadow: 3px 3px 6px #222; -moz-box-shadow: 3px 3px 6px #222; box-shadow: 3px 3px 6px #222;">

<div id="page-title">Tadpole Login</div>

<?php
include_once(INCLUDE_PATH . '/alert.php');
if (alert::hasErrors())
{
?>
<div id="errors">
<?php
   foreach (alert::getErrors() as \$message)
   {
      echo \$message . '<br />';
   }
?>
</div>
<?php
}
?>

<form enctype="multipart/form-data" action="<?php echo ROOT_URL; ?>/admin/do-login" method="post">
  <div class="field string-field"><label for="username">Username:</label><input style="width: 250px;" name="username" id="username" type="text" /></div>
  <div class="field string-field"><label for="password">Password:</label><input style="width: 250px;" name="password" id="password" type="password" /></div>
  <input type="submit" value="Log in" />
</form>

</div>

</body>
</html>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => NULL,
   'is_dynamic' => true,
   'created_time' => '1292991747',
   'last_modified_time' => '1292991747',
   'expanded' => true,
));

addPage(array(
   'title' => 'Do login',
   'slug' => 'do-login',
   'body' => <<<EODEOD
<?php

require_once(INCLUDE_PATH . '/user.php');

if (user::logInUser(\$_POST['username'], sha1(\$_POST['password'])))
{
   header('Location: ' . ROOT_URL . '/admin-backup');
}
else
{
   include_once(INCLUDE_PATH . '/alert.php');
   alert::addError('Login failed!');

   header('Location: ' . ROOT_URL . '/admin-backup/login');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292991749',
   'last_modified_time' => '1292991749',
   'expanded' => true,
));

addPage(array(
   'title' => 'Log out',
   'slug' => 'logout',
   'body' => <<<EODEOD
<?php

require_once(INCLUDE_PATH . '/user.php');

user::logOutUser();
header('Location: ' . ROOT_URL . '/admin-backup/login');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => NULL,
   'is_dynamic' => false,
   'created_time' => '1292991751',
   'last_modified_time' => '1292991751',
   'expanded' => true,
));

addPage(array(
   'title' => 'List',
   'slug' => 'list',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292991759',
   'last_modified_time' => '1292991759',
   'expanded' => false,
));

addPage(array(
   'title' => 'Users',
   'slug' => 'user',
   'body' => <<<EODEOD
<table id="user-list" class="type-list">
<thead>
  <tr>
    <th>Username</th>
    <th>Roles</th>
    <th>Actions</th>
  </tr>
</thead>
<tbody>

<?php
\$users = db::getAll('user');
foreach (\$users as \$user)
{
?>
  <tr>
    <td class="string-preview"><?php echo htmlspecialchars(\$user->username); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$user->roles, 0, 50)); ?></td>
    <td class="action-links">
      <a href="<?php echo ROOT_URL; ?>/admin-backup/edit/user/<?php echo \$user->username; ?>">Edit</a> |
      <a href="<?php echo ROOT_URL; ?>/admin-backup/remove/user/<?php echo \$user->username; ?>" onclick="javascript:return confirm('Are you sure you want to delete this user?');">Remove</a>
    </td>
  </tr>
<?php
}
?>

</tbody>
</table>

<a href="<?php echo ROOT_URL; ?>/admin-backup/edit/user/new">Add new user</a>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 44,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292991770',
   'last_modified_time' => '1292991770',
   'expanded' => true,
));

addPage(array(
   'title' => 'Pages',
   'slug' => 'page',
   'body' => <<<EODEOD
<ul id="page-list">
<?php

function listPageRecursively(\$page)
{
   echo '<li>';
   echo '<div>';
   echo '  <a class="page-title" href="' . ROOT_URL . \$page->getPath() . '">' . \$page->title . '</a>';
   echo '  <span style="float: right;">';
   echo '    <a  href="' . ROOT_URL . '/admin-backup/edit/page/' . \$page->id . '">Edit Page</a> | ';
   echo '    <a href="' . ROOT_URL . '/admin-backup/edit/page/new/' . \$page->id . '">Add Child</a> | ';
   echo '    <a href="' . ROOT_URL . '/admin-backup/remove/page/' . \$page->id . '" onclick="javascript:return confirm(\\'Are you sure you want to delete this page?\\');">Delete Page</a>';
   echo '  </span>';
   echo '</div>';

   \$childPages = \$page->getChildren();
   if (!empty(\$childPages))
   {
      echo '<ul>';
      foreach (\$childPages as \$childPage)
      {
         listPageRecursively(\$childPage);
      }
      echo '</ul>';
   }

   echo '</li>';
}

listPageRecursively(page::getRootPage());

?>
</ul>
EODEOD
   ,
   'head' => <<<EODEOD
<style type="text/css">
#page-list li div { border-bottom: 1px solid #ccc; line-height: 25px; }
#page-list li div:hover { background: #eee; }
#page-list a.page-title { color: black; text-decoration: none; font-weight: bold; }
</style>
EODEOD
   ,
   'parent_id' => 44,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292991777',
   'last_modified_time' => '1292991777',
   'expanded' => true,
));

addPage(array(
   'title' => 'Layouts',
   'slug' => 'layout',
   'body' => <<<EODEOD
<table id="layout-list" class="type-list">
<thead>
  <tr>
    <th>Name</th>
    <th>Body</th>
    <th>Parent Layout</th>
    <th>Actions</th>
  </tr>
</thead>
<tbody>

<?php
\$layouts = db::getAll('layout');
foreach (\$layouts as \$layout)
{
?>
  <tr>
    <td class="string-preview"><?php echo htmlspecialchars(\$layout->name); ?></td>
    <td class="textbox-preview"><?php echo htmlspecialchars(substr(\$layout->body, 0, 50)); ?></td>
    <td class="string-preview">
<?php
if (\$layout->parent_id)
{
   \$parentLayout = db::get('layout', array('id' => \$layout->parent_id));
   if (isset(\$parentLayout->name))
   {
      echo \$parentLayout->name;
   }
}
?>
    </td>
    <td class="action-links">
      <a href="<?php echo ROOT_URL; ?>/admin-backup/edit/layout/<?php echo \$layout->id; ?>">Edit</a> |
      <a href="<?php echo ROOT_URL; ?>/admin-backup/remove/layout/<?php echo \$layout->id; ?>" onclick="javascript:return confirm('Are you sure you want to delete this layout?');">Remove</a>
    </td>
  </tr>
<?php
}
?>

</tbody>
</table>

<a href="<?php echo ROOT_URL; ?>/admin-backup/edit/layout/new">Add new layout</a>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 44,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292991779',
   'last_modified_time' => '1292991779',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit',
   'slug' => 'edit',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => 1,
   'is_dynamic' => false,
   'created_time' => '1292991784',
   'last_modified_time' => '1292991784',
   'expanded' => false,
));

addPage(array(
   'title' => 'Edit user',
   'slug' => 'user',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No user specified.';
   return;
}

if (\$query != 'new')
{
   \$user = db::get('user', array('username' => \$query));
   if (!\$user)
   {
      echo 'Could not find user.';
      return;
   }
}
else
{
   \$user = new stdClass();
   // Assign default values here
   \$user->username = '';
   \$user->password_hash = '';
   \$user->roles = '';
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin-backup/save/user/<?php echo \$query; ?>">
  <div class="field string-field"><label for="field-username">Username</label> <input type="text" name="username" id="field-username" value="<?php echo htmlspecialchars(\$user->username); ?>" /></div>
  <div class="field string-field"><label for="field-password_hash" style="width: 200px;">New Password:</label> <input type="password" name="new_password" id="field-new_password" /></div>
  <div class="field string-field"><label for="field-password_hash" style="width: 200px;">Confirm Password:</label> <input type="password" name="new_password2" id="field-new_password2" /></div>
  <div class="field textbox-field"><label for="field-roles">Roles</label> <textarea name="roles" id="field-roles" rows="10" cols="100"><?php echo htmlspecialchars(\$user->roles); ?></textarea></div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin-backup/list/user">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 48,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991795',
   'last_modified_time' => '1292991795',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit page',
   'slug' => 'page',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No page specified.';
   return;
}

\$query = explode('/', \$query);
if (\$query[0] != 'new')
{
   \$page = db::get('page', array('id' => \$query[0]));
   if (!\$page)
   {
      echo 'Could not find page.';
      return;
   }
}
else
{
   \$page = new stdClass();
   // Assign default values here
   \$page->title = 'Page Title';
   \$page->slug = 'page-title';
   \$page->body = '';
   \$page->head = '';
   \$page->parent_id = (int)(\$query[1]);
   \$page->layout_id = NULL;
   \$page->is_dynamic = false;
   \$page->created_time = (string)(time());
   \$page->last_modified_time = \$page->created_time;
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin-backup/save/page/<?php echo \$query[0]; ?>">
  <div class="field string-field"><label for="field-title">Title</label> <input type="text" name="title" id="field-title" value="<?php echo htmlspecialchars(\$page->title); ?>" /></div>
  <div class="field string-field"><label for="field-slug">Slug</label> <input type="text" name="slug" id="field-slug" value="<?php echo htmlspecialchars(\$page->slug); ?>" /></div>
  <div class="field textbox-field"><label id="head-label" for="field-head">Head</label> <textarea name="head" id="field-head" rows="10" cols="100"><?php echo htmlspecialchars(\$page->head); ?></textarea></div>
  <div class="field textbox-field"><label for="field-body">Body</label> <textarea name="body" id="field-body" rows="25" cols="100"><?php echo htmlspecialchars(\$page->body); ?></textarea></div>

  <div class="field" style="float: left; margin-right: 5px;"><label for="field-parent_id">Parent Page</label>
    <select name="parent_id" id="field-parent_id">
<?php
\$pages = db::getAll('page', array('order_by' => 'parent_id ASC'));
foreach (\$pages as \$parentPage)
{
   if (\$parentPage->id == \$page->id)
   {
      // We can't make a page a child of itself!, skip it
      continue;
   }

   \$parentPage = new Page(\$parentPage);
   echo '<option value="' . \$parentPage->id . '"';
   if (\$parentPage->id == \$page->parent_id)
   {
      echo ' selected="selected"';
   }
   echo ">" . \$parentPage->getPath() . "</option>n";
}
?>
    </select>
  </div>

  <div class="field" style="float: left; margin-right: 5px;"><label for="field-layout_id">Layout</label>
    <select name="layout_id" id="field-layout_id">
      <option value="0">None</option>
<?php
\$layouts = db::getAll('layout');
foreach (\$layouts as \$layout)
{
   echo '<option value="' . \$layout->id . '"';
   if (\$layout->id == \$page->layout_id)
   {
      echo ' selected="selected"';
   }
   echo ">\$layout->name</option>n";
}
?>
    </select>
  </div>

  <div class="field" style="float: left;"><label for="field-is_dynamic">Dynamic Page</label> <input type="checkbox" name="is_dynamic" id="field-is_dynamic" <?php if (\$page->is_dynamic) echo 'checked="checked" '; ?>/></div>

  <div style="clear: both;"></div>

  <div class="field string-field"><label for="field-created_time">Created On</label> <?php echo date('l, F j, Y, g:i:s A', \$page->created_time); ?></div>
  <div class="field string-field"><label for="field-last_modified_time">Last Modified</label> <?php echo date('l, F j, Y, g:i:s A', \$page->last_modified_time); ?></div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin-backup/list/page">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => <<<EODEOD
<script type="text/javascript">
\$(document).ready(function() {
   \$("#field-head").hide();
   \$("#head-label").click(function() {
      \$("#field-head").toggle();
   });
   \$("#head-label").css('cursor', 'pointer');
});
</script>
EODEOD
   ,
   'parent_id' => 48,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991799',
   'last_modified_time' => '1292991799',
   'expanded' => true,
));

addPage(array(
   'title' => 'Edit layout',
   'slug' => 'layout',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No layout specified.';
   return;
}

if (\$query != 'new')
{
   \$layout = db::get('layout', array('id' => \$query));
   if (!\$layout)
   {
      echo 'Could not find layout.';
      return;
   }
}
else
{
   \$layout = new stdClass();
   // Assign default values here
   \$layout->name = '';
   \$layout->body = '';
   \$layout->parent_id = 0;
}

?>

<form enctype="multipart/form-data" method="post" action="<?php echo ROOT_URL; ?>/admin-backup/save/layout/<?php echo \$query; ?>">
  <div class="field string-field"><label for="field-name">Name</label> <input type="text" name="name" id="field-name" value="<?php echo htmlspecialchars(\$layout->name); ?>" /></div>
  <div class="field textbox-field"><label for="field-body">Body</label> <textarea name="body" id="field-body" rows="10" cols="100"><?php echo htmlspecialchars(\$layout->body); ?></textarea></div>
  <div class="field"><label for="field-parent_id">Parent Layout</label>
  <select name="parent_id" id="field-parent_id">
    <option>None</option>
<?php
\$layouts = db::getAll('layout');
foreach (\$layouts as \$parentLayout)
{
   echo "<option value=\\"\$parentLayout->id\\"";
   if (\$parentLayout->id == \$layout->parent_id)
   {
      echo ' selected="selected"';
   }
   echo ">\$parentLayout->name</option>n";
}
?>
  <select>
  </div>

  <div id="submit-buttons">
    <input type="submit" name="save" value="Save and Close" />
    <input type="submit" name="continue" value="Save and Continue Editing" />
    or <a href="<?php echo ROOT_URL; ?>/admin-backup/list/layout">Cancel</a>
  </div>
</form>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 48,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991804',
   'last_modified_time' => '1292991804',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save',
   'slug' => 'save',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => 5,
   'is_dynamic' => false,
   'created_time' => '1292991806',
   'last_modified_time' => '1292991806',
   'expanded' => false,
));

addPage(array(
   'title' => 'Save user',
   'slug' => 'user',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No user specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('user', array('username' => \$query)) < 1)
   {
      alert::addError('Could not find user.');
      header('Location: ' . ROOT_URL . '/admin-backup/list/user');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate username
if (isset(\$_POST['username']))
{
   // No validation for normal strings
   \$fields['username'] = \$_POST['username'];
}
// Validate new password
if (isset(\$_POST['new_password']))
{
   if (isset(\$_POST['new_password2']) && \$_POST['new_password2'] == \$_POST['new_password'])
   {
      \$fields['password_hash'] = sha1(\$_POST['new_password']);
   }
   else
   {
      alert::addError('New passwords do not match.');
   }
}
// Validate roles
if (isset(\$_POST['roles']))
{
   // No validation for normal textboxes
   \$fields['roles'] = \$_POST['roles'];
}

// Update database with new data
if (\$query == 'new')
{
   \$result = db::add('user', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new user.');
   }
   else
   {
      alert::addAlert('user saved.');
      \$query = \$fields['username'];
   }
}
else
{
   \$result = db::update('user', \$fields, array('username' => \$query));
   if (\$result == false)
   {
      alert::addError('Error saving user.');
   }
   else
   {
      alert::addAlert('user saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin-backup/edit/user/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin-backup/list/user');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 52,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991814',
   'last_modified_time' => '1292991814',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save layout',
   'slug' => 'layout',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No layout specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('layout', array('id' => \$query)) < 1)
   {
      alert::addError('Could not find layout.');
      header('Location: ' . ROOT_URL . '/admin-backup/list/layout');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate name
if (isset(\$_POST['name']))
{
   // No validation for normal strings
   \$fields['name'] = \$_POST['name'];
}
// Validate body
if (isset(\$_POST['body']))
{
   // No validation for normal textboxes
   \$fields['body'] = \$_POST['body'];
}
// Validate parent_id
if (isset(\$_POST['parent_id']))
{
   if (\$_POST['parent_id'] == 0)
   {
      \$fields['parent_id'] = NULL;
   }
   else if (!is_numeric(\$_POST['parent_id']) || strpos(\$_POST['parent_id'], '.') != false)
   {

      alert::addError('Parent id must be an integer.');
   }
   else
   {
      \$fields['parent_id'] = \$_POST['parent_id'];
   }
}

// Update database with new data
if (\$query == 'new')
{
   \$result = db::add('layout', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new layout.');
   }
   else
   {
      alert::addAlert('layout saved.');
      \$query = db::lastInsertId();
   }
}
else
{
   \$result = db::update('layout', \$fields, array('id' => \$query));
   if (\$result === false)
   {
      alert::addError('Error saving layout.');
   }
   else
   {
      alert::addAlert('layout saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin-backup/edit/layout/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin-backup/list/layout');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 52,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991817',
   'last_modified_time' => '1292991817',
   'expanded' => true,
));

addPage(array(
   'title' => 'Save page',
   'slug' => 'page',
   'body' => <<<EODEOD
<?php

// Parse query
if (empty(\$query))
{
   echo 'No page specified.';
   return;
}

include_once(INCLUDE_PATH . '/alert.php');

if (\$query != 'new')
{
   if (db::getCount('page', array('id' => \$query)) < 1)
   {
      alert::addError('Could not find page.');
      header('Location: ' . ROOT_URL . '/admin-backup/list/page');
      return;
   }
}

// Validate fields
\$fields = array();
// Validate title
if (isset(\$_POST['title']))
{
   // No validation for normal strings
   \$fields['title'] = \$_POST['title'];
}
// Validate slug
if (isset(\$_POST['slug']))
{
   // No validation for normal strings
   \$fields['slug'] = \$_POST['slug'];
}
// Validate body
if (isset(\$_POST['body']))
{
   // No validation for normal textboxes
   \$fields['body'] = \$_POST['body'];
}
// Validate head
if (isset(\$_POST['head']))
{
   // No validation for normal textboxes
   \$fields['head'] = \$_POST['head'];
}
// Validate parent_id
if (isset(\$_POST['parent_id']))
{
   if (!is_numeric(\$_POST['parent_id']) || strpos(\$_POST['parent_id'], '.') != false)
   {
      alert::addError('Parent id must be an integer.');
   }
   else
   {
      \$fields['parent_id'] = \$_POST['parent_id'];
   }
}
// Validate layout_id
if (isset(\$_POST['layout_id']))
{
   if (\$_POST['layout_id'] == 0)
   {
      \$fields['layout_id'] = NULL;
   }
   else if (!is_numeric(\$_POST['layout_id']) || strpos(\$_POST['layout_id'], '.') != false)
   {
      alert::addError('Layout id must be an integer.');
   }
   else
   {
      \$fields['layout_id'] = \$_POST['layout_id'];
   }
}
// Validate is_dynamic
if (isset(\$_POST['is_dynamic']))
{
   \$fields['is_dynamic'] = 1;
}
else
{
   \$fields['is_dynamic'] = 0;
}
// Validate created_time
if (isset(\$_POST['created_time']))
{
   // No validation for normal strings
   \$fields['created_time'] = \$_POST['created_time'];
}
// Validate last_modified_time
if (isset(\$_POST['last_modified_time']))
{
   // No validation for normal strings
   \$fields['last_modified_time'] = \$_POST['last_modified_time'];
}

// Update database with new data
if (\$query == 'new')
{
   \$fields['created_time'] = (string)(time());
   \$fields['last_modified_time'] = \$fields['created_time'];

   \$result = db::add('page', \$fields);
   if (\$result == false)
   {
      alert::addError('Error adding new page.');
   }
   else
   {
      alert::addAlert('page saved.');
      \$query = db::lastInsertId();
   }
}
else
{
   \$fields['last_modified_time'] = (string)(time());

   \$result = db::update('page', \$fields, array('id' => \$query));
   if (\$result === false)
   {
      alert::addError('Error saving page.');
   }
   else
   {
      alert::addAlert('page saved.');
   }
}

// Redirect back to edit page or list page
if (isset(\$_POST['continue']) || alert::hasErrors())
{
   header('Location: ' . ROOT_URL . '/admin-backup/edit/page/' . \$query);
}
else
{
   header('Location: ' . ROOT_URL . '/admin-backup/list/page');
}

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 52,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991819',
   'last_modified_time' => '1292991819',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove',
   'slug' => 'remove',
   'body' => NULL,
   'head' => NULL,
   'parent_id' => 40,
   'layout_id' => 5,
   'is_dynamic' => false,
   'created_time' => '1292991823',
   'last_modified_time' => '1292991823',
   'expanded' => false,
));

addPage(array(
   'title' => 'Remove user',
   'slug' => 'user',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No user specified.';
   return;
}

db::remove('user', array('username' => \$query));

header('Location: ' . ROOT_URL . '/admin-backup/list/user');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 56,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991827',
   'last_modified_time' => '1292991827',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove layout',
   'slug' => 'layout',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No layout specified.';
   return;
}

db::remove('layout', array('id' => \$query));

header('Location: ' . ROOT_URL . '/admin-backup/list/layout');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 56,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991831',
   'last_modified_time' => '1292991831',
   'expanded' => true,
));

addPage(array(
   'title' => 'Remove page',
   'slug' => 'page',
   'body' => <<<EODEOD
<?php

if (empty(\$query))
{
   echo 'No page specified.';
   return;
}

db::remove('page', array('id' => \$query));

header('Location: ' . ROOT_URL . '/admin-backup/list/page');

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 56,
   'layout_id' => 1,
   'is_dynamic' => true,
   'created_time' => '1292991835',
   'last_modified_time' => '1292991835',
   'expanded' => true,
));

addPage(array(
   'title' => 'Expand Page',
   'slug' => 'expand-page',
   'body' => <<<EODEOD
<?php

if (!isset(\$query))
{
   echo 'No page specified.';
   return;
}

db::update('page', array('expanded' => true), array('id' => \$query));

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => true,
   'created_time' => NULL,
   'last_modified_time' => NULL,
   'expanded' => true,
));

addPage(array(
   'title' => 'Unexpand Page',
   'slug' => 'unexpand-page',
   'body' => <<<EODEOD
<?php

if (!isset(\$query))
{
   echo 'No page specified.';
   return;
}

db::update('page', array('expanded' => false), array('id' => \$query));

?>
EODEOD
   ,
   'head' => NULL,
   'parent_id' => 2,
   'layout_id' => 2,
   'is_dynamic' => true,
   'created_time' => '1293055443',
   'last_modified_time' => '1293055443',
   'expanded' => true,
));

?>
