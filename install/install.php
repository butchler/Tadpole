<?php

// Step 1: Generate config file ===================================================================

// Enable error reporting so we can see what goes wrong
ini_set('display_errors', '1');
error_reporting(E_ALL);

$dbDriver = $_POST['db_driver'];
$dbHost = $_POST['db_host'];
$dbUsername = $_POST['db_username'];
$dbPassword = $_POST['db_password'];
$dbName = $_POST['db_name'];

// Try to open the config.php file
$configFile = fopen('../config.php', 'w');
if (!$configFile)
{
   die('Could not open config file for writing! Are you sure your config.php file is writeable by the server?');
}

// Generate config file from template
ob_start();
eval('?>' . file_get_contents('./config-template.php'));
$configContents = ob_get_clean();

// Write to config.php file
if (!fwrite($configFile, $configContents))
{
   die('There was an error while trying to write to config.php!');
}

// Step 2: Connect to database ====================================================================
// Now we can connect to database and do stuff!
require_once('../config.php');
require_once(INCLUDE_PATH . '/db.php');

if (!db::connect(DB_DRIVER, DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME))
{
   die('Could not connect to database!');
}

// Step 3: Fill up database! ====================================================================
$success = true;   // Hopefully it will stay this way

echo '<div style="font-size: small;">';

$ok = db::createTable('table-field', array(
   'table_name' => 'string',
   'name' => 'string',
   'type' => 'string'
), NULL, false);
if ($ok)
{
   echo "Created table 'table-field'.<br />\n";
}
else
{
   echo "<strong>Error creating table 'table-field' (" . db::getErrorMessage() . ").</strong><br />\n";
}

$ok = db::createTable('table-primary-key', array(
   'table_name' => 'string',
   'name' => 'string',
), 'table_name', false);
if ($ok)
{
   echo "Created table 'table-primary-key'.<br />\n";
}
else
{
   echo "<strong>Error creating table 'table-primary-key' (" . db::getErrorMessage() . ").</strong><br />\n";
}

// Create tables and put content into them
include('layouts.php');
include('pages.php');
include('data-types.php');

// Create user table
db::createTable('user', array(
   'username' => 'string',
   'password_hash' => 'string',
   'roles' => 'text',
), 'username');

// Generate random default password for admin user
$consonants = str_split('bcdfghjklmnpqrstvwxz');
$vowels = array('a', 'e', 'i', 'o', 'u', 'y', 'ee');
$numbers = str_split('0123456789');

// Generate random password as cvcvcv#, where c = consonant, v = vowel, and # = number
$adminPassword = $consonants[array_rand($consonants)] . $vowels[array_rand($vowels)] . $consonants[array_rand($consonants)] . $vowels[array_rand($vowels)] . $consonants[array_rand($consonants)] . $vowels[array_rand($vowels)] . $numbers[array_rand($numbers)];

// Create admin user
$result = db::add('user', array(
   'username' => 'admin',
   'password_hash' => sha1($adminPassword),
   'roles' => 'administrator'
));

if ($result == false)
{
   echo '<strong>Could not create admin user!</strong>';
   $success = false;
}
else
{
   echo 'Created admin user.';
}

echo '</div>';

// Step 4: If we succeeded, display next steps to user ====================================================================
if ($success)
{
   echo '
<h2>Tadpole is installed!</h2>
Things to do now:
<ol>
   <li>Delete the entire install/ folder from your tadpole installation.</li>
   <li>Make your config.php read only so that it can\'t be messed with (it\'s important).</li>
   <li>Go to your administration interface at <a href="' . ROOT_URL . '/admin">' . ROOT_URL . '/admin</a> and log in with this username and password:<br />
   Username: <code>admin</code><br />
   Password: <code>' . $adminPassword . '</code>
   </li>
</ol>

If you want to enable URL rewriting (to take the ugly ?q= out of the URLs):
<ol>
   <li>Enable the Apache mod_rewrite module if it is not already enabled.</li>
   <li>Make sure .htaccess overrides are enabled for the directory in which you installed Tadpole.</li>
   <li>Edit the _.htaccess file and change the RewriteBase to location of your Tadpole installation relative to the web server.</li>
   <li>Rename the _.htaccess file to .htaccess</li>
   <li>Modify your config.php file and change URL_REWRITING_ENABLED to true.</li>
</ol>
';
}

?>
