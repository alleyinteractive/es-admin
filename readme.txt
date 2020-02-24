=== ES Admin ===
Contributors: mboynes, alleyinteractive
Tags: search, admin, wp-admin, faceted search, elasticsearch
Requires at least: 4.5
Tested up to: 4.6
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Insanely powerful admin search, powered by Elasticsearch.

== Description ==

Adds a powerful faceted search page to the admin area to search all posts across all post types.

Optionally, this plugin can also replace core's post search forms throughout the entire admin area to leverage Elasticsearch for faster, more relevant search results.

== Prerequisites ==

* Elasticsearch server
* Elasticsearch plugin/adapter (e.g. [SearchPress](https://github.com/alleyinteractive/searchpress)
* [ES_WP_Query](https://github.com/alleyinteractive/es-wp-query) if you want to use the optional core search replacement.

== Installation ==

ES Admin is designed to be able to work with any Elasticsearch index, regardless of how the data is mapped. In order to accomplish this, ES Admin requires you to register an "Adapter", which maps appropriate post fields (e.g. `post_content`) to the Elasticsearch index path (e.g. `post_content.analyzed`). ES Admin currently comes with four adapters: [SearchPress](https://github.com/alleyinteractive/searchpress), [WordPress.com](https://vip-svn.wordpress.com/plugins/wpcom-elasticsearch/wpcom-elasticsearch.php) (used by WordPress.com VIP sites hosted on the main WordPress.com platform), [Jetpack Search](https://jetpack.com/support/search/) (available on VIP Go or as part of Jetpack's Professional Plan), and a generic adapter primarily used for unit testing. You aren't limited to using one of the included adapters, you can very easily build your own to work with your Elasticsearch plugin of choice by extending the `ES_Admin\Adapters\Adapter` abstract class. See the [Generic Adapter](https://github.com/alleyinteractive/es-admin/blob/master/lib/adapters/class-generic.php) as an example to create your own.

Once you have your adapter, you register it with ES Admin using the `es_admin_adapter` filter. This filter expects a class name in return, which must be an `ES_Admin\Adapters\Adapter` extension. Here's an example using this filter with the SearchPress adapter:

```php
add_filter( 'es_admin_adapter', function() {
	return '\ES_Admin\Adapters\SearchPress';
} );
```

In order to use the optional "core search replacement" feature, which will replace any post search form in the admin with an Elasticsearch integration, you must also install and activate [ES_WP_Query](https://github.com/alleyinteractive/es-wp-query) (see the [README](https://github.com/alleyinteractive/es-wp-query/blob/master/README.md) on setting up ES_WP_Query). ES_WP_Query must be setup with its own adapter prior to the `after_setup_theme` action fires at priority `10`.

Here's an example setting up ES_WP_Query to use its SearchPress adapter, and doing so at `after_setup_theme` priority `5` to ensure it fires before ES Admin needs it:

```php
add_action( 'after_setup_theme', function() {
	if ( function_exists( 'es_wp_query_load_adapter' ) ) {
		es_wp_query_load_adapter( 'searchpress' );
	}
}, 5 );
```

When setup correctly, ES Admin will add a settings page to enable/disable the search integration. Once enabled, all post search forms in the admin will use Elasticsearch to deliver search results.
