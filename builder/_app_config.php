<?php
/**
 * @package Phreeze Builder
 *
 * APPLICATION-WIDE CONFIGURATION SETTINGS
 *
 * This file contains application-wide configuration settings.  The settings
 * here will be the same regardless of the machine on which the app is running.
 *
 * This configuration should be added to version control.
 *
 * No settings should be added to this file that would need to be changed
 * on a per-machine basic (ie local, staging or production).  Any
 * machine-specific settings should be added to _machine_config.php
 */

/**
 * APPLICATION ROOT DIRECTORY
 * If the application doesn't detect this correctly then it can be set explicitly
 */
if (!GlobalConfig::$APP_ROOT) GlobalConfig::$APP_ROOT = realpath("./");

/**
 * APPLICATION's CONTEXT
 * It's the part of the URL after the domain that points to the application's root.
 * Will vary depending on how the app has been deployed.
 * E.g: http://localhost/myApp -> myApp being the root context; http://myapp.com ->
 * in this case, the root context is ''
 */
if (!GlobalConfig::$APP_CONTEXT) GlobalConfig::$APP_CONTEXT = getAppContext();

/**
 * INCLUDE PATH
 * Adjust the include path as necessary so PHP can locate required libraries
 */
set_include_path(
		GlobalConfig::$APP_ROOT . '/libs/' . PATH_SEPARATOR .
		GlobalConfig::$APP_ROOT . '/../libs/' . PATH_SEPARATOR .
		get_include_path()
);

/**
 * RENDER ENGINE
 */
require_once 'verysimple/Phreeze/SavantRenderEngine.php';
GlobalConfig::$TEMPLATE_ENGINE = 'SavantRenderEngine';
GlobalConfig::$TEMPLATE_PATH = GlobalConfig::$APP_ROOT . '/templates/';
GlobalConfig::$TEMPLATE_CACHE_PATH = '';

/**
 * ROUTE MAP
 * The route map connects URLs to Controller+Method and additionally maps the
 * wildcards to a named parameter so that they are accessible inside the
 * Controller without having to parse the URL for parameters such as IDs
 */
GlobalConfig::$ROUTE_MAP = array(

	// default controller when no route specified
	'GET:' => array('route' => 'Default.Home'),
	'POST:generate' => array('route' => 'Generator.Generate'),
	'POST:analyze' => array('route' => 'Analyzer.Analyze'),
	'POST:load' => array('route' => 'Analyzer.LoadConfig'),
	'POST:saveConfig' => array('route' => 'Generator.Export')
);

function getAppContext() {
        //http://blog.lavoie.sl/2013/02/php-document-root-path-and-url-detection.html
        //http://www.php.net//manual/en/reserved.variables.server.php
        $base_dir = __DIR__; // Absolute path to your installation, ex: /var/www/mywebsite
        $doc_root = preg_replace("!{$_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']); # ex: /var/www
        // using substr instead of preg_replace because in Windows the usage of slashes/backslashes is not consistent
        $base_url = substr($base_dir, strlen($doc_root)); # ex: /var/www
        // replacing any remaining backslashes (Windows)
        return $base_url = str_replace("\\", '/', $base_url); # ex: '' or '/mywebsite'
        //$base_url = preg_replace('/^//', '', $base_url);
}

?>