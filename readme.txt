=== Schema.org Visual Injector ===
Contributors: inCod, ideagrafica
Tags: schema.org, structured data, json-ld, seo, geo
Requires at least: 6.0
Tested up to: 7.1
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Visual JSON-LD Schema.org injector for Posts, Pages, WooCommerce Products, and Custom Post Types with dynamic field support.

== Description ==

Schema.org Visual Injector provides a clean, bloat-free way to assign, map, and output official Schema.org structured data on any WordPress site.

Whether you are optimizing Blog Posts, Pages, WooCommerce Products, or Custom Post Types (CPTs), this plugin allows you to visually build JSON-LD payload graphs without writing custom PHP templates.

= Key Features =

* **Complete Schema.org Vocabulary:** All 1400+ official Schema.org types available in a searchable dropdown — from Article and Product to every type in the full schema.org hierarchy.
* **Curated Types with Detailed Fields:** 11 curated types (Article, Book, Event, FAQPage, LocalBusiness, Organization, Person, Product, Recipe, Service, WebPage) with type-specific property fields.
* **Nested Schema Objects:** Automatic sub-property mapping for complex types — PostalAddress for LocalBusiness, Place/Organization for Event, Offer for Product, and more.
* **FAQPage Ready:** Built-in Question & Answer repeater for FAQ structured data with dynamic token support.
* **Universal Post Type Support:** Works seamlessly with standard Posts, Pages, WooCommerce Products, and custom post types (CPTs).
* **Dynamic Tokens:** Map Schema properties using dynamic placeholders like `{post_title}`, `{featured_image_url}`, `{meta:your_key}`, `{acf:your_field}`, `{option:key}`, or `{site:url}`.
* **Searchable Type Selector:** Quickly find any of the 1400+ Schema.org types with the built-in search filter.
* **Lightweight & High Performance:** Zero database clutter. Zero external API calls on frontend load. Native JSON-LD output in `<head>`.
* **Developer Friendly:** Fully extensible via WP actions and filters, including the `sovi_json_ld_payload` filter.

= Need Full Automation & AI Engine Optimization? =

Looking to generate `/llms.txt` automatically, auto-map entire catalogs, and optimize for AI Search Engines (SearchGPT, Perplexity, Claude)? Check out [GEO Optimizer Pro](https://www.incod.it/geo-optimizer-pro/?utm_source=wp_org&utm_medium=plugin&utm_campaign=sovi_free_readme).

== Installation ==

1. Upload the `schema-org-visual-injector` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Edit any Post, Page, Product, or Custom Post Type.
4. Locate the "Schema.org Visual Injector" box below the editor, select your Schema type, map your fields, and save.

== Frequently Asked Questions ==

= Does this plugin support WooCommerce Products? =

Yes! You can select `Product` as the Schema type and map prices, SKUs, and stock meta keys directly.

= Does it support Advanced Custom Fields (ACF)? =

Yes. You can use the `{acf:field_name}` dynamic token inside any Schema property input field.

= Will this slow down my website? =

No. Schema payload is parsed and rendered locally on `wp_head` execution with cached meta lookups.

= Which dynamic tokens are supported? =

* `{post_title}` — The title of the current post.
* `{post_permalink}` — The canonical URL of the post.
* `{post_excerpt}` — The excerpt or summary of the post.
* `{featured_image_url}` — Full-size URL of the featured image.
* `{meta:custom_meta_key}` — Value from `wp_postmeta` for any custom key.
* `{acf:field_name}` — Native ACF integration (requires Advanced Custom Fields plugin).

= Can I use this on Custom Post Types? =

Yes. The plugin automatically detects all registered public post types and adds the Schema metabox to each.

== Screenshots ==

1. Schema.org Visual Injector metabox inside WordPress Block/Classic Editor.
2. Property mapping interface with dynamic token hints.

== Changelog ==

= 1.0.0 =
* Initial release on WordPress.org Directory.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
