<?php
/**
 * Frontend JSON-LD schema injector.
 *
 * Parses stored tokens, resolves dynamic values, builds nested objects,
 * and outputs the final JSON-LD script tag in wp_head.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Frontend_Injector
 *
 * Hooks into wp_head on singular pages to inject Schema.org JSON-LD.
 */
class Frontend_Injector {

	/**
	 * Constructor — register the injection hook.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'inject_schema' ), 99 );
	}

	/**
	 * Output the Schema.org JSON-LD script block if enabled for the current post.
	 *
	 * @return void
	 */
	public function inject_schema() {
		if ( ! is_singular() ) {
			return;
		}

		$post_id = get_queried_object_id();

		if ( ! $post_id ) {
			return;
		}

		$enabled = get_post_meta( $post_id, '_sovi_schema_enabled', true );

		if ( '1' !== $enabled ) {
			return;
		}

		$schema_type = get_post_meta( $post_id, '_sovi_schema_type', true );
		$schema_data = get_post_meta( $post_id, '_sovi_schema_payload', true );

		if ( empty( $schema_type ) || empty( $schema_data ) || ! is_array( $schema_data ) ) {
			return;
		}

		$resolved_payload = $this->resolve_tokens( $schema_data, $post_id );

		// Build nested Schema.org objects from _nested data.
		$resolved_payload = $this->build_nested_objects( $resolved_payload );

		// Build FAQ mainEntity array from _faq data.
		$resolved_payload = $this->build_faq_entities( $resolved_payload );

		$final_json = array_merge(
			array(
				'@context' => 'https://schema.org',
				'@type'    => sanitize_text_field( $schema_type ),
			),
			$resolved_payload
		);

		/**
		 * Filter the final JSON-LD payload before output.
		 *
		 * @param array<string, mixed> $final_json The assembled schema payload.
		 * @param int                  $post_id    The current post ID.
		 */
		$final_json = apply_filters( 'sovi_json_ld_payload', $final_json, $post_id );

		// Handle global Organization schema injection.
		$this->maybe_inject_global_schema( $post_id );

		echo "\n<!-- Schema.org Visual Injector - https://www.incod.it/schema-visual-injector/ -->\n";
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $final_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n<!-- / Schema.org Visual Injector -->\n\n";
	}

	/**
	 * Resolve all dynamic tokens in the schema data array.
	 *
	 * @param array<string, mixed> $data    The raw schema payload.
	 * @param int                  $post_id The current post ID.
	 *
	 * @return array<string, mixed> The payload with resolved tokens.
	 */
	private function resolve_tokens( array $data, int $post_id ): array {
		foreach ( $data as $key => $value ) {
			if ( is_string( $value ) && strpos( $value, '{' ) !== false ) {
				$data[ $key ] = $this->parse_single_token( $value, $post_id );
			}
		}

		// Resolve tokens inside nested property groups.
		if ( isset( $data['_nested'] ) && is_array( $data['_nested'] ) ) {
			foreach ( $data['_nested'] as $group_key => $fields ) {
				if ( is_array( $fields ) ) {
					foreach ( $fields as $field_key => $field_val ) {
						if ( is_string( $field_val ) && strpos( $field_val, '{' ) !== false ) {
							$data['_nested'][ $group_key ][ $field_key ] = $this->parse_single_token( $field_val, $post_id );
						}
					}
				}
			}
		}

		// Resolve tokens inside FAQ entities.
		if ( isset( $data['_faq'] ) && is_array( $data['_faq'] ) ) {
			foreach ( $data['_faq'] as $index => $entity ) {
				if ( isset( $entity['question'] ) && is_string( $entity['question'] ) && strpos( $entity['question'], '{' ) !== false ) {
					$data['_faq'][ $index ]['question'] = $this->parse_single_token( $entity['question'], $post_id );
				}
				if ( isset( $entity['answer'] ) && is_string( $entity['answer'] ) && strpos( $entity['answer'], '{' ) !== false ) {
					$data['_faq'][ $index ]['answer'] = $this->parse_single_token( $entity['answer'], $post_id );
				}
			}
		}

		// Resolve tokens inside custom properties.
		if ( isset( $data['_custom'] ) && is_array( $data['_custom'] ) ) {
			$resolved_custom = array();
			foreach ( $data['_custom'] as $cp ) {
				$cp_key = isset( $cp['key'] ) ? $cp['key'] : '';
				$cp_val = isset( $cp['val'] ) ? $cp['val'] : '';
				if ( '' !== $cp_key ) {
					if ( is_string( $cp_val ) && strpos( $cp_val, '{' ) !== false ) {
						$cp_val = $this->parse_single_token( $cp_val, $post_id );
					}
					$resolved_custom[ $cp_key ] = $cp_val;
				}
			}
			unset( $data['_custom'] );
			$data = array_merge( $data, $resolved_custom );
		}

		return array_filter( $data );
	}

	/**
	 * Parse a single token string and return the resolved value.
	 *
	 * Supported tokens:
	 *  - {post_title}, {post_permalink}, {post_excerpt}, {featured_image_url}
	 *  - {meta:key} — custom field from wp_postmeta
	 *  - {acf:field_name} — ACF field (requires Advanced Custom Fields)
	 *  - {option:key} — wp_options value
	 *  - {site:name}, {site:description}, {site:url}, {site:language}
	 *
	 * @param string $token   The token string containing placeholders.
	 * @param int    $post_id The current post ID.
	 *
	 * @return string The resolved value.
	 */
	private function parse_single_token( string $token, int $post_id ): string {
		switch ( $token ) {
			case '{post_title}':
				return get_the_title( $post_id );
			case '{post_permalink}':
				return (string) get_permalink( $post_id );
			case '{post_excerpt}':
				return (string) get_the_excerpt( $post_id );
			case '{featured_image_url}':
				return (string) get_the_post_thumbnail_url( $post_id, 'full' );
			default:
				// Handle {meta:key} tokens.
				if ( preg_match( '/\{meta:(.*?)\}/', $token, $matches ) ) {
					$value = get_post_meta( $post_id, sanitize_key( $matches[1] ), true );
					return (string) $value;
				}

				// Handle {acf:field_name} tokens.
				if ( function_exists( 'get_field' ) && preg_match( '/\{acf:(.*?)\}/', $token, $matches ) ) {
					$value = get_field( sanitize_key( $matches[1] ), $post_id );
					return is_string( $value ) ? $value : wp_json_encode( $value );
				}

				// Handle {option:key} tokens.
				if ( preg_match( '/\{option:(.*?)\}/', $token, $matches ) ) {
					$value = get_option( sanitize_key( $matches[1] ), '' );
					return (string) $value;
				}

				// Handle {site:property} tokens.
				switch ( $token ) {
					case '{site:name}':
						return (string) get_bloginfo( 'name' );
					case '{site:description}':
						return (string) get_bloginfo( 'description' );
					case '{site:url}':
						return (string) home_url( '/' );
					case '{site:language}':
						return (string) get_bloginfo( 'language' );
				}

				return $token;
		}
	}

	/**
	 * Build nested Schema.org objects from the _nested payload section.
	 *
	 * Converts flat nested arrays (e.g., address.streetAddress) into
	 * proper Schema.org typed objects (e.g., { "@type": "PostalAddress", ... }).
	 *
	 * @param array<string, mixed> $data The resolved payload.
	 *
	 * @return array<string, mixed> Payload with nested objects built.
	 */
	private function build_nested_objects( array $data ): array {
		if ( ! isset( $data['_nested'] ) || ! is_array( $data['_nested'] ) ) {
			return $data;
		}

		$nested_groups = $data['_nested'];
		unset( $data['_nested'] );

		$all_types = Schema_Dictionary::get_supported_types();
		$schema_type = isset( $data['@type'] ) ? $data['@type'] : '';

		foreach ( $nested_groups as $group_key => $fields ) {
			if ( ! is_array( $fields ) ) {
				continue;
			}

			// Filter out empty values.
			$clean_fields = array_filter( $fields );

			if ( empty( $clean_fields ) ) {
				continue;
			}

			// Look up the @type for this nested group from the dictionary.
			$nested_type = $this->get_nested_group_type( $schema_type, $group_key );

			$nested_object = array( '@type' => $nested_type );
			$nested_object = array_merge( $nested_object, $clean_fields );

			$data[ $group_key ] = $nested_object;
		}

		return $data;
	}

	/**
	 * Look up the Schema.org @type for a nested group.
	 *
	 * @param string $schema_type The parent Schema.org type.
	 * @param string $group_key   The nested group key (e.g., 'address', 'location').
	 *
	 * @return string The nested @type (e.g., 'PostalAddress').
	 */
	private function get_nested_group_type( string $schema_type, string $group_key ): string {
		$groups = Schema_Dictionary::get_nested_groups_for_type( $schema_type );

		if ( isset( $groups[ $group_key ]['@type'] ) ) {
			return $groups[ $group_key ]['@type'];
		}

		// Fallback: capitalize the group key as a reasonable @type guess.
		return ucfirst( $group_key );
	}

	/**
	 * Build FAQ mainEntity array from _faq payload section.
	 *
	 * Converts the flat FAQ entity array into Schema.org Question/Answer objects.
	 *
	 * @param array<string, mixed> $data The resolved payload.
	 *
	 * @return array<string, mixed> Payload with FAQ mainEntity built.
	 */
	private function build_faq_entities( array $data ): array {
		if ( ! isset( $data['_faq'] ) || ! is_array( $data['_faq'] ) ) {
			return $data;
		}

		$faq_data = $data['_faq'];
		unset( $data['_faq'] );

		$entities = array();

		foreach ( $faq_data as $entity ) {
			$question = isset( $entity['question'] ) ? trim( $entity['question'] ) : '';
			$answer   = isset( $entity['answer'] ) ? trim( $entity['answer'] ) : '';

			if ( '' === $question && '' === $answer ) {
				continue;
			}

			$entities[] = array(
				'@type' => 'Question',
				'name'  => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $answer,
				),
			);
		}

		if ( ! empty( $entities ) ) {
			$data['mainEntity'] = $entities;
		}

		return $data;
	}

	/**
	 * Optionally inject a global Organization schema if enabled in settings.
	 *
	 * @param int $post_id The current post ID.
	 *
	 * @return void
	 */
	private function maybe_inject_global_schema( int $post_id ) {
		$options = Admin_Settings::get_options();

		if ( empty( $options['global_enabled'] ) || '1' !== $options['global_enabled'] ) {
			return;
		}

		$org_data = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Organization',
		);

		if ( ! empty( $options['global_org_name'] ) ) {
			$org_data['name'] = $options['global_org_name'];
		}

		if ( ! empty( $options['global_org_url'] ) ) {
			$org_data['url'] = $options['global_org_url'];
		}

		if ( ! empty( $options['global_org_logo'] ) ) {
			$org_data['logo'] = $options['global_org_logo'];
		}

		if ( count( $org_data ) > 2 ) {
			echo "\n<!-- Schema.org Visual Injector (Global Organization) -->\n";
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $org_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n</script>\n<!-- / Schema.org Visual Injector (Global Organization) -->\n\n";
		}
	}
}
