<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Schema_Org_Visual_Injector
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove all post meta created by the plugin.
delete_post_meta_by_key( '_sovi_schema_enabled' );
delete_post_meta_by_key( '_sovi_schema_type' );
delete_post_meta_by_key( '_sovi_schema_payload' );

// Remove any plugin-level options.
delete_option( 'sovi_settings' );
