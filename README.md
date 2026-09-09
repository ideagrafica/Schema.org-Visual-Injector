# Schema.org Visual Injector for WordPress

A powerful WordPress plugin that lets you visually inject Schema.org structured data (JSON-LD) into any post or page without coding.

## Features

- **Visual Interface**: Easy-to-use metabox in the post editor
- **Complete Schema.org Vocabulary**: All 1400+ official Schema.org types in a searchable dropdown
- **11 Curated Types**: Article, Book, Event, FAQPage, LocalBusiness, Organization, Person, Product, Recipe, Service, WebPage — with type-specific fields
- **Dynamic Tokens**: Use placeholders like `{post_title}`, `{post_permalink}`, `{featured_image_url}` that auto-fill with post data
- **Nested Objects**: Support for complex Schema.org structures like PostalAddress, Place, Offer
- **FAQ Support**: Built-in FAQ repeater for FAQPage schema with Question/Answer pairs
- **Custom Post Types**: Works with any public post type
- **ACF/Meta Box Integration**: Pull data from custom fields using `{acf:field_name}` or `{meta:key}`
- **WordPress Options**: Access any option using `{option:key}`
- **Searchable Type Selector**: Quickly find any Schema.org type with the built-in search filter
- **Developer Friendly**: Filter `sovi_json_ld_payload` to modify output

## Installation

1. Upload the plugin files to `/wp-content/plugins/schema-org-visual-injector/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Edit any post/page and fill in the Schema.org fields in the metabox

## Available Tokens

| Token | Description |
|-------|-------------|
| `{post_title}` | Current post title |
| `{post_permalink}` | Current post permalink |
| `{post_excerpt}` | Current post excerpt |
| `{featured_image_url}` | Featured image URL |
| `{meta:key}` | Custom field value |
| `{acf:field_name}` | ACF field value |
| `{option:key}` | WordPress option value |
| `{site:name}` | Site name |
| `{site:url}` | Site URL |
| `{site:description}` | Site description |
| `{site:language}` | Site language |

## Schema Types

### Curated Types (with detailed fields)

| Type | Extra Fields |
|------|-------------|
| Article | headline, author, datePublished, dateModified |
| Book | isbn, author, bookFormat |
| Event | startDate, endDate, location (Place), organizer (Organization) |
| FAQPage | FAQ repeater with Question/Answer pairs |
| LocalBusiness | telephone, priceRange, address (PostalAddress) |
| Organization | logo, sameAs, contactPoint |
| Person | jobTitle, sameAs, worksFor |
| Product | sku, priceCurrency, availability, offers (Offer) |
| Recipe | cookTime, recipeIngredient, recipeInstructions |
| Service | areaServed, serviceType, provider (Organization) |
| WebPage | breadcrumb, datePublished, dateModified |

### All Schema.org Types

All 1400+ official Schema.org types are available with common fields (name, description, image, url). Use the searchable dropdown to find any type from the full schema.org hierarchy.

## Developer Filter

```php
add_filter( 'sovi_json_ld_payload', function( $payload, $post_id ) {
    // Modify $payload array
    return $payload;
}, 10, 2 );
```

## Requirements

- WordPress 5.0+
- PHP 7.4+

## License

GPLv2 or later - see [LICENSE](LICENSE) for details.

## Credits

Developed by [Ideagrafica](https://ideagrafica.com)
