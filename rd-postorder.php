<?php
/**
 * Plugin Name: Rundiz PostOrder
 * Plugin URI: https://rundiz.com/?p=319
 * Description: Re-order posts to what you want.
 * Version: 1.0.4
 * Requires at least: 4.7.0
 * Requires PHP: 5.5
 * Author: Vee Winch
 * Author URI: http://rundiz.com
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: rd-postorder
 * Domain Path: /App/languages/
 *
 * @package rundiz-postorder
 */


// Define this plugin main file path.
if (!defined('RDPOSTORDER_FILE')) {
    define('RDPOSTORDER_FILE', __FILE__);
}


// Plugin's autoload.
require __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';


// Run this wp plugin.
$App = new \RdPostOrder\App\App();
$App->run();
unset($App);


// That's it. Everything is load and works inside the main App class.