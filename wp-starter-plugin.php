<?php
/**
 * Plugin Name:     Wp Starter Plugin
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     wp-starter-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wp_Starter_Plugin
 */

require_once __DIR__ . '/lib/WPStarter/Helpers.php';
require_once __DIR__ . '/lib/WPStarter/ModuleManager.php';
require_once __DIR__ . '/lib/WPStarter/BuildConstructionPlan.php';
require_once __DIR__ . '/lib/WPStarter/Render.php';
require_once __DIR__ . '/lib/WPStarter.php';
require_once __DIR__ . '/lib/WPStarter/DefaultLoader.php';

use WPStarter\DefaultLoader;

DefaultLoader::init();
