<?php
/*
* Plugin name: PlaceHodor
* Description: Substitute / replace missing images by a default image. The plugin will (try to) display / generate the good size (width and height) of the missing image.

* Author: Xuan NGUYEN
* Author URI: https://xuxu.fr/
* Version: 1.3.2
* Text-domain: placehodor
*/

define( 'LNJPH_PLUGIN_VERSION', '1.3.2' );
define( 'LNJPH_PATH', __DIR__ );
define( 'LNJPH_NAMESPACE', 'LNJ' );
define( 'LNJPH_SLUG', 'placehodor' );
define( 'LNJPH_SLUG_CAMELCASE', 'PlaceHodor' );
define( 'LNJPH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LNJPH_PLUGIN_FILE', __FILE__ );

//
require LNJPH_PATH . '/classes/class-placehodor.php';

// Launch
\LNJ\PlaceHodor::lnjph_run();
