<?php

// 'localhost/tadpole/install/install.php' becomes 'http://localhost/tadpole'
$publicUrl = 'http://' . dirname(dirname($_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']));

echo <<<EOD
<?php

// Your database settings. The format of DB_DSN is described under the individual PDO driver sections in the PDO manual: http://www.php.net/manual/en/pdo.drivers.php
define('DB_DRIVER', '$dbDriver');
define('DB_HOST', '$dbHost');
define('DB_USERNAME', '$dbUsername');
define('DB_PASSWORD', '$dbPassword');
define('DB_NAME', '$dbName');

// The public URL of your Tadpole CMS website
define('SITE_URL', '$publicUrl');

// If you have url rewriting set up correctly, set this to true so that you don't have to have the ugly ?q= in your URLs
define('URL_REWRITING_ENABLED', false);

// You usually shouldn't have to modify the settings below
define('ROOT_URL', SITE_URL . (URL_REWRITING_ENABLED ? '' : '/?q='));

define('TADPOLE_PATH', dirname(__FILE__));
define('INCLUDE_PATH', TADPOLE_PATH . '/includes');

?>
EOD;

?>
