<?php
/**
 * Admin metabox for Schema.org property mapping.
 *
 * Registers a metabox on all public post types and handles
 * saving/loading of schema metadata including nested objects and FAQ entities.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Metabox
 *
 * Renders the Schema.org configuration metabox and persists data.
 */
class Admin_Metabox {

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the Schema metabox on every public post type.
	 *
	 * @return void
	 */
	public function register_metabox() {
		$post_types = get_post_types( array( 'public' => true ), 'names' );

		foreach ( $post_types as $pt ) {
			add_meta_box(
				'sovi_schema_metabox',
				__( 'Schema.org Visual Injector', 'schema-org-visual-injector' ),
				array( $this, 'render_metabox' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render the metabox contents.
	 *
	 * @param \WP_Post $post The current post object.
	 *
	 * @return void
	 */
	public function render_metabox( \WP_Post $post ) {
		wp_nonce_field( 'sovi_save_schema_data', 'sovi_metabox_nonce' );

		$enabled     = get_post_meta( $post->ID, '_sovi_schema_enabled', true );
		$schema_type = get_post_meta( $post->ID, '_sovi_schema_type', true ) ?: 'Article';
		$payload     = get_post_meta( $post->ID, '_sovi_schema_payload', true );
		$curated     = Schema_Dictionary::get_curated_types();
		$all_types   = Schema_Dictionary::get_supported_types();

		if ( ! is_array( $payload ) ) {
			$payload = array();
		}

		$props = Schema_Dictionary::get_default_properties_for_type( $schema_type );
		?>
		<div class="sovi-metabox-wrapper">
			<p>
				<label>
					<input type="checkbox" name="sovi_schema_enabled" value="1" <?php checked( $enabled, '1' ); ?> />
					<strong><?php esc_html_e( 'Enable Schema JSON-LD injection for this content', 'schema-org-visual-injector' ); ?></strong>
				</label>
			</p>

			<p>
				<label for="sovi_schema_type"><strong><?php esc_html_e( 'Select Schema.org Type:', 'schema-org-visual-injector' ); ?></strong></label><br/>
				<input type="text" id="sovi-type-search" class="widefat" placeholder="<?php esc_attr_e( 'Search schema types...', 'schema-org-visual-injector' ); ?>" autocomplete="off" />
				<select name="sovi_schema_type" id="sovi_schema_type" class="widefat" size="10">
					<optgroup label="<?php esc_attr_e( 'Curated Types (with detailed fields)', 'schema-org-visual-injector' ); ?>">
						<?php foreach ( $curated as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $schema_type, $key ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</optgroup>
					<optgroup label="<?php esc_attr_e( 'All Schema.org Types', 'schema-org-visual-injector' ); ?>">
						<?php foreach ( $all_types as $key => $label ) : ?>
							<?php if ( ! isset( $curated[ $key ] ) ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $schema_type, $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endif; ?>
						<?php endforeach; ?>
					</optgroup>
				</select>
			</p>

			<hr/>

			<!-- Standard properties (rebuilt via JS on type change) -->
			<div id="sovi-properties-container">
				<h4><?php esc_html_e( 'Property Mapping (Supports dynamic tokens)', 'schema-org-visual-injector' ); ?></h4>

				<?php
				foreach ( $props as $prop ) :
					$val = isset( $payload[ $prop ] ) ? $payload[ $prop ] : '';
					?>
					<p>
						<label for="sovi_prop_<?php echo esc_attr( $prop ); ?>"><?php echo esc_html( $prop ); ?>:</label>
						<input type="text" id="sovi_prop_<?php echo esc_attr( $prop ); ?>" name="sovi_payload[<?php echo esc_attr( $prop ); ?>]" value="<?php echo esc_attr( $val ); ?>" class="widefat sovi-prop-input" />
					</p>
				<?php endforeach; ?>
			</div>

			<!-- Nested property groups — rendered for curated types only -->
			<div id="sovi-nested-container">
				<?php
				foreach ( $curated as $type_key => $type_label ) :
					$groups = Schema_Dictionary::get_nested_groups_for_type( $type_key );
					if ( empty( $groups ) ) {
						continue;
					}
					foreach ( $groups as $group_key => $group ) :
						?>
						<div class="sovi-nested-group" data-type="<?php echo esc_attr( $type_key ); ?>" data-group="<?php echo esc_attr( $group_key ); ?>"<?php echo ( $schema_type === $type_key ) ? '' : ' style="display:none;"'; ?>>
							<h4><?php echo esc_html( $group['label'] ); ?> <span class="sovi-nested-type-hint">(<?php echo esc_html( $group['@type'] ); ?>)</span></h4>
							<?php foreach ( $group['fields'] as $field ) :
								$nested_val = '';
								if ( isset( $payload[ '_nested' ][ $group_key ][ $field ] ) ) {
									$nested_val = $payload[ '_nested' ][ $group_key ][ $field ];
								}
								?>
								<p>
									<label for="sovi_nested_<?php echo esc_attr( $group_key ); ?>_<?php echo esc_attr( $field ); ?>"><?php echo esc_html( $field ); ?>:</label>
									<input type="text"
									       id="sovi_nested_<?php echo esc_attr( $group_key ); ?>_<?php echo esc_attr( $field ); ?>"
									       name="sovi_nested[<?php echo esc_attr( $group_key ); ?>][<?php echo esc_attr( $field ); ?>]"
									       value="<?php echo esc_attr( $nested_val ); ?>"
									       class="widefat sovi-nested-input"
									       placeholder="<?php echo esc_attr( $field ); ?>" />
								</p>
							<?php endforeach; ?>
						</div>
					<?php endforeach;
				endforeach; ?>
			</div>

			<!-- FAQ entities section (for FAQPage type) -->
			<div id="sovi-faq-container"<?php echo ( 'FAQPage' === $schema_type ) ? '' : ' style="display:none;"'; ?>>
				<h4><?php esc_html_e( 'FAQ Entities (Question & Answer Pairs)', 'schema-org-visual-injector' ); ?></h4>
				<p class="description"><?php esc_html_e( 'Add question/answer pairs. Each supports dynamic tokens like {meta:field_name}.', 'schema-org-visual-injector' ); ?></p>
				<div id="sovi-faq-list">
					<?php
					$faq_entities = isset( $payload[ '_faq' ] ) && is_array( $payload[ '_faq' ] ) ? $payload[ '_faq' ] : array();

					if ( empty( $faq_entities ) ) {
						$faq_entities = array( array( 'question' => '', 'answer' => '' ) );
					}

					foreach ( $faq_entities as $index => $entity ) :
						?>
						<div class="sovi-faq-row" data-index="<?php echo esc_attr( $index ); ?>">
							<p>
								<label><strong><?php esc_html_e( 'Question:', 'schema-org-visual-injector' ); ?></strong></label>
								<input type="text" name="sovi_faq_question[]" value="<?php echo esc_attr( isset( $entity['question'] ) ? $entity['question'] : '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., What is Schema.org?', 'schema-org-visual-injector' ); ?>" />
							</p>
							<p>
								<label><strong><?php esc_html_e( 'Answer:', 'schema-org-visual-injector' ); ?></strong></label>
								<textarea name="sovi_faq_answer[]" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'e.g., Schema.org is a collaborative vocabulary...', 'schema-org-visual-injector' ); ?>"><?php echo esc_textarea( isset( $entity['answer'] ) ? $entity['answer'] : '' ); ?></textarea>
							</p>
							<p><button type="button" class="button sovi-remove-faq-row"><?php esc_html_e( 'Remove', 'schema-org-visual-injector' ); ?></button></p>
							<hr/>
						</div>
					<?php endforeach; ?>
				</div>
				<p>
					<button type="button" id="sovi-add-faq-row" class="button button-primary"><?php esc_html_e( '+ Add Question/Answer Pair', 'schema-org-visual-injector' ); ?></button>
				</p>
			</div>

			<!-- Custom properties -->
			<div id="sovi-custom-properties">
				<h4><?php esc_html_e( 'Custom Properties', 'schema-org-visual-injector' ); ?></h4>
				<p class="description"><?php esc_html_e( 'Add additional Schema.org properties not listed above.', 'schema-org-visual-injector' ); ?></p>
				<div id="sovi-custom-props-list">
					<?php
					$custom_props = isset( $payload['_custom'] ) ? $payload['_custom'] : array();
					if ( ! empty( $custom_props ) && is_array( $custom_props ) ) :
						foreach ( $custom_props as $cp ) :
							?>
							<p class="sovi-custom-prop-row">
								<input type="text" name="sovi_custom_key[]" value="<?php echo esc_attr( isset( $cp['key'] ) ? $cp['key'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Property name', 'schema-org-visual-injector' ); ?>" class="regular-text" />
								<input type="text" name="sovi_custom_val[]" value="<?php echo esc_attr( isset( $cp['val'] ) ? $cp['val'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Value (supports tokens)', 'schema-org-visual-injector' ); ?>" class="regular-text" />
								<button type="button" class="button sovi-remove-custom-prop">&times;</button>
							</p>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<p>
					<button type="button" id="sovi-add-custom-prop" class="button"><?php esc_html_e( '+ Add Custom Property', 'schema-org-visual-injector' ); ?></button>
				</p>
			</div>

			<?php Promotional::render_metabox_footer(); ?>
		</div>
		<?php
	}

	/**
	 * Save metabox data on post save.
	 *
	 * @param int $post_id The post ID being saved.
	 *
	 * @return void
	 */
	public function save_metabox_data( int $post_id ) {
		if ( ! isset( $_POST['sovi_metabox_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['sovi_metabox_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'sovi_save_schema_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$enabled = isset( $_POST['sovi_schema_enabled'] ) ? '1' : '0';
		update_post_meta( $post_id, '_sovi_schema_enabled', $enabled );

		if ( isset( $_POST['sovi_schema_type'] ) ) {
			update_post_meta( $post_id, '_sovi_schema_type', sanitize_text_field( wp_unslash( $_POST['sovi_schema_type'] ) ) );
		}

		$clean_payload = array();

		// Standard properties.
		$payload_post = isset( $_POST['sovi_payload'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sovi_payload'] ) ) : array();
		if ( is_array( $payload_post ) ) {
			foreach ( $payload_post as $key => $val ) {
				$clean_payload[ sanitize_key( $key ) ] = $val;
			}
		}

		// Nested property groups (address, location, offers, etc.).
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Input is sanitized per-field below.
		$nested_post = isset( $_POST['sovi_nested'] ) ? wp_unslash( $_POST['sovi_nested'] ) : array();
		if ( is_array( $nested_post ) ) {
			$nested_clean = array();
			foreach ( $nested_post as $group_key => $fields ) {
				$group_key = sanitize_key( $group_key );
				if ( is_array( $fields ) ) {
					$nested_clean[ $group_key ] = array_map( 'sanitize_text_field', $fields );
					// Remove empty nested groups.
					$nested_clean[ $group_key ] = array_filter( $nested_clean[ $group_key ] );
					if ( empty( $nested_clean[ $group_key ] ) ) {
						unset( $nested_clean[ $group_key ] );
					}
				}
			}
			if ( ! empty( $nested_clean ) ) {
				$clean_payload['_nested'] = $nested_clean;
			}
		}

		// FAQ entities (for FAQPage type).
		$faq_question_post = isset( $_POST['sovi_faq_question'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sovi_faq_question'] ) ) : array();
		$faq_answer_post   = isset( $_POST['sovi_faq_answer'] ) ? array_map( 'sanitize_textarea_field', wp_unslash( $_POST['sovi_faq_answer'] ) ) : array();

		if ( is_array( $faq_question_post ) ) {
			$questions = $faq_question_post;
			$answers   = is_array( $faq_answer_post ) ? $faq_answer_post : array();

			$faq_clean = array();
			$count     = count( $questions );
			for ( $i = 0; $i < $count; $i++ ) {
				$q = isset( $questions[ $i ] ) ? trim( $questions[ $i ] ) : '';
				$a = isset( $answers[ $i ] ) ? trim( $answers[ $i ] ) : '';
				if ( '' !== $q || '' !== $a ) {
					$faq_clean[] = array(
						'question' => $q,
						'answer'   => $a,
					);
				}
			}
			if ( ! empty( $faq_clean ) ) {
				$clean_payload['_faq'] = $faq_clean;
			}
		}

		// Custom properties.
		$custom_key_post = isset( $_POST['sovi_custom_key'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sovi_custom_key'] ) ) : array();
		$custom_val_post = isset( $_POST['sovi_custom_val'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sovi_custom_val'] ) ) : array();

		$custom_keys = is_array( $custom_key_post ) ? $custom_key_post : array();
		$custom_vals = is_array( $custom_val_post ) ? $custom_val_post : array();

		$custom_props = array();
		$custom_count = count( $custom_keys );
		for ( $i = 0; $i < $custom_count; $i++ ) {
			$key = isset( $custom_keys[ $i ] ) ? trim( $custom_keys[ $i ] ) : '';
			$val = isset( $custom_vals[ $i ] ) ? $custom_vals[ $i ] : '';
			if ( '' !== $key ) {
				$custom_props[] = array(
					'key' => $key,
					'val' => $val,
				);
			}
		}

		if ( ! empty( $custom_props ) ) {
			$clean_payload['_custom'] = $custom_props;
		}

		update_post_meta( $post_id, '_sovi_schema_payload', $clean_payload );
	}

	/**
	 * Enqueue admin CSS and JS on all post editing screens (including CPTs).
	 *
	 * Uses get_current_screen() for reliable detection across all post types.
	 *
	 * @param string $hook The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || 'post' !== $screen->base ) {
			return;
		}

		wp_enqueue_style(
			'sovi-admin-css',
			SOVI_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			SOVI_VERSION
		);

		wp_enqueue_script(
			'sovi-admin-js',
			SOVI_PLUGIN_URL . 'assets/js/admin-metabox.js',
			array( 'jquery' ),
			SOVI_VERSION,
			true
		);
	}
}
