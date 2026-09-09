<?php
/**
 * Custom PSR-4 style autoloader for the Schema_Org_Visual_Injector namespace.
 *
 * Maps class names to file paths within the includes/ directory.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Autoloader
 *
 * Registers an SPL autoload callback to resolve classes
 * in the Schema_Org_Visual_Injector namespace.
 */
class Autoloader {

	/**
	 * Namespace prefix used for class resolution.
	 *
	 * @var string
	 */
	private static $prefix = 'Schema_Org_Visual_Injector\\';

	/**
	 * Register the autoloader with SPL.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload a class by converting its namespace to a file path.
	 *
	 * @param string $class Fully-qualified class name.
	 *
	 * @return void
	 */
	public static function autoload( $class ) {
		$len = strlen( self::$prefix );

		if ( strncmp( self::$prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = SOVI_PLUGIN_DIR . 'includes/Class-' . str_replace( '_', '-', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
