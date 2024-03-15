<?php
/*
Plugin Name: Schema global implement
Description: Makes simple settings page with text field to be printed allover
Version: 0.0.1
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Author: Firmcatalyst, Vadim Volkov, Melanie Nickl
Author URI: https://firmcatalyst.com/about/
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

namespace FCP\Schema;
defined( 'ABSPATH' ) || exit;

define( 'FCPSCHEMA', 'schema-global-settings' );

require plugin_dir_path( __FILE__ ) . 'inc/form-fields.php';
require plugin_dir_path( __FILE__ ) . 'inc/settings-page.php';
require plugin_dir_path( __FILE__ ) . 'inc/print.php';



function settings_get() {
    return (object) [
        'varname' => FCPSCHEMA,
        'group' => FCPSCHEMA.'-group',
        'page' => FCPSCHEMA.'-page',
        'section' => '',
        'values' => get_option( FCPSCHEMA ),
    ];
}

function get_default_values() { // apply on install && only planned to be usef in fields, which must not be empty // ++ turn to the constant ++!! set up them all by the structure
    return [];
}