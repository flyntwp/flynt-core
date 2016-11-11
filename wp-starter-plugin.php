<?php
/**
 * Plugin Name:     Wp Starter Plugin
 * Plugin URI:      https://github.com/bleech/wp-starter-plugin
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          bleech GmbH
 * Author URI:      http://bleech.de
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
require_once __DIR__ . '/lib/WPStarter/Defaults.php';
require_once __DIR__ . '/lib/WPStarter.php';

use WPStarter\Defaults;

Defaults::init();
