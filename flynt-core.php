<?php
/**
 * Plugin Name:     Flynt Core
 * Plugin URI:      https://github.com/bleech/wp-starter-plugin
 * Description:     Adds the Core Functionality for the Flynt Framework.
 * Author:          bleech GmbH
 * Author URI:      http://bleech.de
 * Text Domain:     flynt-core
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Flynt_Core_Plugin
 */

require_once __DIR__ . '/lib/Flynt/Helpers.php';
require_once __DIR__ . '/lib/Flynt/ComponentManager.php';
require_once __DIR__ . '/lib/Flynt/BuildConstructionPlan.php';
require_once __DIR__ . '/lib/Flynt/Render.php';
require_once __DIR__ . '/lib/Flynt/Defaults.php';
require_once __DIR__ . '/lib/Flynt.php';

add_filter('Flynt/renderComponent', function ($html, $componentName, $componentData, $areaHtml) {
  ob_start();
  echo "<pre>";
  echo $componentName . "\n";
  var_dump($componentData);
  echo "areaHtml\n";
  var_dump($areaHtml);
  echo "</pre>";

  $html = ob_get_clean();

  return $html;
}, 10, 4);

// echo Flynt\Render::fromConstructionPlan('');
// echo Flynt\Render::fromConstructionPlan(5);
// echo Flynt\Render::fromConstructionPlan([]);
// echo Flynt\Render::fromConstructionPlan([
//   'name' => 'test',
//   'data' => [
//     'test' => 'test'
//   ],
//   'areas' => [
//     'someArea' => [
//       [
//         'name' => 'test'
//       ]
//     ]
//   ]
// ]);
//
// die();
