<?php
/**
 * Core orchestrator for Schema.org Visual Injector.
 *
 * Boots admin and frontend modules based on context.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Core
 *
 * Singleton entry point that wires up all plugin modules.
 */
class Core {

	/**
	 * Singleton instance.
	 *
	 * @var Core|null
	 */
	private static $instance = null;

	/**
	 * Get or create the singleton instance.
	 *
	 * @return Core
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — init hooks.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Register WordPress hooks and instantiate modules.
	 *
	 * @return void
	 */
	private function init_hooks() {
		if ( is_admin() ) {
			new Admin_Metabox();
			new Admin_Settings();
			new Promotional();
		} else {
			new Frontend_Injector();
		}
	}
}
