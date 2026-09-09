<?php
/**
 * Promotional elements for Schema.org Visual Injector.
 *
 * Renders non-intrusive promotional cards for GEO Optimizer Pro
 * in compliance with WordPress.org plugin guidelines.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Promotional
 *
 * Renders promo cards in metabox footer and settings sidebar only.
 */
class Promotional {

	/**
	 * Constructor — currently no hooks needed, methods are called directly.
	 */
	public function __construct() {
		// Reserved for future admin-wide promo hooks if needed.
	}

	/**
	 * Render the promotional sidebar card on the settings page.
	 *
	 * @return void
	 */
	public static function render_settings_sidebar() {
		?>
		<div class="sovi-promo-card">
			<h3><?php esc_html_e( 'Take GEO & AI Optimization Further', 'schema-org-visual-injector' ); ?></h3>
			<p>
				<?php esc_html_e( 'Need automated Schema mapping for thousands of products, automated /llms.txt generation, and deep AI Search Analytics?', 'schema-org-visual-injector' ); ?>
			</p>
			<p>
				<a href="https://www.incod.it/geo-optimizer-pro/?utm_source=wp_org&utm_medium=plugin&utm_campaign=sovi_free_sidebar" target="_blank" rel="noopener noreferrer" class="button button-primary">
					<?php esc_html_e( 'Discover GEO Optimizer Pro &rarr;', 'schema-org-visual-injector' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the promotional footer inside the Schema metabox.
	 *
	 * @return void
	 */
	public static function render_metabox_footer() {
		?>
		<div class="sovi-metabox-footer">
			<span><?php esc_html_e( 'Schema.org Visual Injector Free Edition', 'schema-org-visual-injector' ); ?> | </span>
			<a href="https://www.incod.it/geo-optimizer-pro/?utm_source=wp_org&utm_medium=plugin&utm_campaign=sovi_free_metabox" target="_blank" rel="noopener noreferrer" class="sovi-promo-link">
				<?php esc_html_e( 'Upgrade to GEO Optimizer Pro for Auto-Mapping & LLM Engine Optimization &rarr;', 'schema-org-visual-injector' ); ?>
			</a>
		</div>
		<?php
	}
}
