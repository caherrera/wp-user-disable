<?php
/**
 *
 * Disables user accounts via ID.
 *
 * @since             1.0.0
 * @package           Disable User Login
 *
 * @wordpress-plugin
 * Plugin Name:       Disable User Login
 * Description:       Disables user accounts via email address.
 * Version:           1.0.0
 * Author:            sflores
 * Author URI:        http://www.solu4b.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       disable-wp-user-at-login
 */

define( 'DWUL_PLUGIN_PATH', plugin_dir_url( __FILE__ ) ); 
require_once( dirname(__FILE__) . '/admin-option.php' ); 
require_once( dirname(__FILE__) . '/custom-ajax.php' );
require_once( dirname(__FILE__) . '/create-user-schema.php' );
register_activation_hook(__FILE__,'dwul_install');
