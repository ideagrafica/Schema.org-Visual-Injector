# Schema.org Visual Injector for WordPress

A powerful WordPress plugin that lets you visually inject Schema.org structured data (JSON-LD) into any post or page without coding.

## Features

- **Visual Interface**: Easy-to-use metabox in the post editor
- **11 Schema.org Types**: Article, Book, Event, FAQPage, LocalBusiness, Organization, Person, Product, Recipe, Service, WebPage
- **Dynamic Tokens**: Use placeholders like `{post_title}`, `{post_permalink}`, `{featured_image_url}` that auto-fill with post data
- **Nested Objects**: Support for complex Schema.org structures like PostalAddress, Place, Offer
- **FAQ Support**: Built-in FAQ repeater for FAQPage schema with Question/Answer pairs
- **Custom Post Types**: Works with any public post type
- **ACF/Meta Box Integration**: Pull data from custom fields using `{acf:field_name}` or `{meta:key}`
- **WordPress Options**: Access any option using `{option:key}`
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

## Schema Types & Fields

### Article
- headline, description, author, datePublished, dateModified, image

### Event
- name, description, startDate, endDate, location (Place), organizer (Organization)

### LocalBusiness
- name, description, telephone, email, address (PostalAddress), url, priceRange

### Product
- name, description, sku, brand, offers (Offer)

### FAQPage
- FAQ repeater with Question/Answer pairs

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
