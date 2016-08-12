<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backwards compat
 */
$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/email-editor.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/email-editor.php', '/woocommerce-email-editor.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );