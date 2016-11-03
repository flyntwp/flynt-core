<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wp_Starter_Plugin
 */


// First we need to load the composer autoloader so we can use WP Mock
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

require_once __DIR__. '/TestHelper.php';

// prevent using mocks from previous tests
WP_Mock::activateStrictMode();
// Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();

/**
* Now we include any plugin files that we need to be able to run the tests. This
* should be files that define the functions and classes you're going to test.
*/
require dirname( __DIR__ ) . '/wp-starter-plugin.php';
