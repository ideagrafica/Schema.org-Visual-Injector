<?php
/**
 * Admin settings page for Schema.org Visual Injector.
 *
 * Provides a lightweight control panel under Settings with global options.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Settings
 *
 * Registers and renders the plugin settings page under Settings menu.
 */
class Admin_Settings {

	/**
	 * Option name used in wp_options.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'sovi_settings';

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add the settings page under the Settings menu.
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'Schema.org Visual Injector', 'schema-org-visual-injector' ),
			__( 'Schema Visual Injector', 'schema-org-visual-injector' ),
			'manage_options',
			'sovi-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings and sections using the Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'sovi_settings_group', self::OPTION_NAME, array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
		) );

		add_settings_section(
			'sovi_general_section',
			__( 'General Settings', 'schema-org-visual-injector' ),
			array( $this, 'general_section_callback' ),
			'sovi-settings'
		);

		add_settings_field(
			'sovi_default_type',
			__( 'Default Schema Type', 'schema-org-visual-injector' ),
			array( $this, 'render_default_type_field' ),
			'sovi-settings',
			'sovi_general_section'
		);

		add_settings_field(
			'sovi_global_schema',
			__( 'Global Organization Schema', 'schema-org-visual-injector' ),
			array( $this, 'render_global_schema_field' ),
			'sovi-settings',
			'sovi_general_section'
		);
	}

	/**
	 * Sanitize all settings before saving to the database.
	 *
	 * @param array<string, mixed> $input Raw submitted settings.
	 *
	 * @return array<string, mixed> Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['default_type'] = isset( $input['default_type'] )
			? sanitize_text_field( $input['default_type'] )
			: 'Article';

		$sanitized['global_enabled'] = ! empty( $input['global_enabled'] ) ? '1' : '0';

		$sanitized['global_org_name'] = isset( $input['global_org_name'] )
			? sanitize_text_field( wp_unslash( $input['global_org_name'] ) )
			: '';

		$sanitized['global_org_url'] = isset( $input['global_org_url'] )
			? esc_url_raw( wp_unslash( $input['global_org_url'] ), array( 'http', 'https' ) )
			: '';

		$sanitized['global_org_logo'] = isset( $input['global_org_logo'] )
			? esc_url_raw( wp_unslash( $input['global_org_logo'] ), array( 'http', 'https' ) )
			: '';

		return $sanitized;
	}

	/**
	 * Description text for the general settings section.
	 *
	 * @return void
	 */
	public function general_section_callback() {
		echo '<p>' . esc_html__( 'Configure default behavior for Schema.org Visual Injector.', 'schema-org-visual-injector' ) . '</p>';
	}

	/**
	 * Render the default Schema type dropdown.
	 *
	 * @return void
	 */
	public function render_default_type_field() {
		$options   = self::get_options();
		$types     = Schema_Dictionary::get_supported_types();
		$selected  = isset( $options['default_type'] ) ? $options['default_type'] : 'Article';

		echo '<select name="sovi_settings[default_type]" id="sovi_settings[default_type]">';
		foreach ( $types as $key => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $key ),
				selected( $selected, $key, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Used as pre-selected type when creating new content.', 'schema-org-visual-injector' ) . '</p>';
	}

	/**
	 * Render the global Organization schema fields.
	 *
	 * @return void
	 */
	public function render_global_schema_field() {
		$options = self::get_options();

		printf(
			'<label><input type="checkbox" name="sovi_settings[global_enabled]" value="1" %s /> %s</label>',
			checked( isset( $options['global_enabled'] ) && '1' === $options['global_enabled'], true, false ),
			esc_html__( 'Enable global Organization schema on all pages', 'schema-org-visual-injector' )
		);

		echo '<br/><br/>';

		printf(
			'<input type="text" name="sovi_settings[global_org_name]" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( isset( $options['global_org_name'] ) ? $options['global_org_name'] : '' ),
			esc_attr__( 'Organization Name', 'schema-org-visual-injector' )
		);

		echo '<br/>';

		printf(
			'<input type="url" name="sovi_settings[global_org_url]" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( isset( $options['global_org_url'] ) ? $options['global_org_url'] : '' ),
			esc_attr__( 'https://example.com', 'schema-org-visual-injector' )
		);

		echo '<br/>';

		printf(
			'<input type="url" name="sovi_settings[global_org_logo]" value="%s" class="regular-text" placeholder="%s" />',
			esc_attr( isset( $options['global_org_logo'] ) ? $options['global_org_logo'] : '' ),
			esc_attr__( 'Logo URL (https://)', 'schema-org-visual-injector' )
		);
	}

	/**
	 * Render the full settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'sovi_settings_group' );
				do_settings_sections( 'ovi-settings' );
				submit_button();
				?>
			</form>

			<?php Promotional::render_settings_sidebar(); ?>

			<div class="sovi-info-card" style="margin-top:30px; padding:15px; background:#fff; border:1px solid #c3c4c7;">
				<h3><?php esc_html_e( 'Dynamic Tokens Reference', 'schema-org-visual-injector' ); ?></h3>
				<table class="widefat" style="max-width:600px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Token', 'schema-org-visual-injector' ); ?></th>
							<th><?php esc_html_e( 'Description', 'schema-org-visual-injector' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr><td><code>{post_title}</code></td><td><?php esc_html_e( 'Post title', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{post_permalink}</code></td><td><?php esc_html_e( 'Post canonical URL', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{post_excerpt}</code></td><td><?php esc_html_e( 'Post excerpt/summary', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{featured_image_url}</code></td><td><?php esc_html_e( 'Featured image full-size URL', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{meta:key}</code></td><td><?php esc_html_e( 'Custom field value from wp_postmeta', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{acf:field}</code></td><td><?php esc_html_e( 'ACF field value (requires ACF plugin)', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{option:key}</code></td><td><?php esc_html_e( 'Value from wp_options table', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{site:name}</code></td><td><?php esc_html_e( 'Site name (get_bloginfo)', 'schema-org-visual-injector' ); ?></td></tr>
						<tr><td><code>{site:url}</code></td><td><?php esc_html_e( 'Site home URL', 'schema-org-visual-injector' ); ?></td></tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieve plugin options with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_options(): array {
		$defaults = array(
			'default_type'   => 'Article',
			'global_enabled' => '0',
			'global_org_name' => '',
			'global_org_url'  => '',
			'global_org_logo' => '',
		);

		$options = get_option( self::OPTION_NAME, array() );

		return wp_parse_args( $options, $defaults );
	}
}
