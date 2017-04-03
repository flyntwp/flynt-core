<?php
/**
 * Plugin Name:     Flynt Core
 * Plugin URI:      https://github.com/flyntwp/flynt-core
 * Description:     Adds the Core Functionality for the Flynt Framework.
 * Author:          bleech GmbH
 * Author URI:      https://bleech.de
 * Text Domain:     flynt-core
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Flynt_Core_Plugin
 */

require_once __DIR__ . '/lib/Flynt/Helpers.php';
require_once __DIR__ . '/lib/Flynt/ComponentManager.php';
require_once __DIR__ . '/lib/Flynt/BuildConstructionPlan.php';
require_once __DIR__ . '/lib/Flynt/Render.php';
require_once __DIR__ . '/lib/Flynt/Defaults.php';
require_once __DIR__ . '/lib/Flynt.php';
