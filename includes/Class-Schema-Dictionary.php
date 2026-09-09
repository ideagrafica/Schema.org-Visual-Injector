<?php
/**
 * Static dictionary of Schema.org types and their properties.
 *
 * Provides a local, non-blocking reference for supported schema types
 * and their default property sets.
 *
 * @package Schema_Org_Visual_Injector
 */

namespace Schema_Org_Visual_Injector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Schema_Dictionary
 *
 * Contains the full vocabulary of supported Schema.org types
 * and maps each type to its recommended properties.
 */
class Schema_Dictionary {

	/**
	 * Get the list of supported Schema.org types.
	 *
	 * @return array<string, string> Associative array of type key => human label.
	 */
	public static function get_supported_types(): array {
		return array(
			'Article'          => __( 'Article (Blog Post, News)', 'schema-org-visual-injector' ),
			'Book'             => __( 'Book', 'schema-org-visual-injector' ),
			'Event'            => __( 'Event', 'schema-org-visual-injector' ),
			'FAQPage'          => __( 'FAQ Page', 'schema-org-visual-injector' ),
			'LocalBusiness'    => __( 'Local Business', 'schema-org-visual-injector' ),
			'Organization'     => __( 'Organization', 'schema-org-visual-injector' ),
			'Person'           => __( 'Person', 'schema-org-visual-injector' ),
			'Product'          => __( 'Product (WooCommerce compatible)', 'schema-org-visual-injector' ),
			'Recipe'           => __( 'Recipe', 'schema-org-visual-injector' ),
			'Service'          => __( 'Service', 'schema-org-visual-injector' ),
			'WebPage'          => __( 'WebPage', 'schema-org-visual-injector' ),
		);
	}

	/**
	 * Get the default properties for a given Schema.org type.
	 *
	 * @param string $type The Schema.org type key.
	 *
	 * @return array<int, string> List of property names.
	 */
	public static function get_default_properties_for_type( string $type ): array {
		$common = array( 'name', 'description', 'image', 'url' );

		switch ( $type ) {
			case 'Product':
				return array_merge( $common, array( 'sku', 'price', 'priceCurrency', 'availability' ) );
			case 'Article':
				return array_merge( $common, array( 'author', 'datePublished', 'dateModified', 'headline' ) );
			case 'Event':
				return array_merge( $common, array( 'startDate', 'endDate', 'location', 'organizer' ) );
			case 'LocalBusiness':
				return array_merge( $common, array( 'address', 'telephone', 'priceRange' ) );
			case 'Organization':
				return array_merge( $common, array( 'logo', 'sameAs', 'contactPoint' ) );
			case 'Person':
				return array_merge( $common, array( 'jobTitle', 'sameAs', 'worksFor' ) );
			case 'Recipe':
				return array_merge( $common, array( 'cookTime', 'recipeIngredient', 'recipeInstructions' ) );
			case 'Book':
				return array_merge( $common, array( 'isbn', 'author', 'bookFormat' ) );
			case 'Service':
				return array_merge( $common, array( 'provider', 'areaServed', 'serviceType' ) );
			case 'FAQPage':
				return $common;
			case 'WebPage':
				return array_merge( $common, array( 'breadcrumb', 'datePublished', 'dateModified' ) );
			default:
				return $common;
		}
	}

	/**
	 * Get nested property groups for a given Schema.org type.
	 *
	 * Returns sub-property definitions for complex Schema types that
	 * require nested objects (e.g., PostalAddress for LocalBusiness).
	 *
	 * @param string $type The Schema.org type key.
	 *
	 * @return array<string, array{
	 *   @type: string,
	 *   label: string,
	 *   fields: array<int, string>
	 * }> Associative array of nested group definitions.
	 */
	public static function get_nested_groups_for_type( string $type ): array {
		switch ( $type ) {
			case 'LocalBusiness':
				return array(
					'address' => array(
						'@type'  => 'PostalAddress',
						'label'  => __( 'Address', 'schema-org-visual-injector' ),
						'fields' => array( 'streetAddress', 'addressLocality', 'addressRegion', 'postalCode', 'addressCountry' ),
					),
				);
			case 'Event':
				return array(
					'location'  => array(
						'@type'  => 'Place',
						'label'  => __( 'Location', 'schema-org-visual-injector' ),
						'fields' => array( 'name', 'streetAddress', 'addressLocality', 'postalCode', 'addressCountry' ),
					),
					'organizer' => array(
						'@type'  => 'Organization',
						'label'  => __( 'Organizer', 'schema-org-visual-injector' ),
						'fields' => array( 'name', 'url' ),
					),
				);
			case 'Product':
				return array(
					'offers' => array(
						'@type'  => 'Offer',
						'label'  => __( 'Offers', 'schema-org-visual-injector' ),
						'fields' => array( 'price', 'priceCurrency', 'availability', 'url' ),
					),
				);
			case 'Service':
				return array(
					'provider' => array(
						'@type'  => 'Organization',
						'label'  => __( 'Provider', 'schema-org-visual-injector' ),
						'fields' => array( 'name', 'url' ),
					),
				);
			default:
				return array();
		}
	}
}
