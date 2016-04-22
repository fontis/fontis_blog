Fontis Blog Extension
=====================

Overview
--------

This extension provides a native blog for Magento. It has full multi-store support,
and integrates seamlessly with the Fontis Algolia and reCAPTCHA extensions.

Further documentation is available from the [Fontis Blog Extension](https://www.fontis.com.au/blog-magento-extension)
page on our website.

Features
--------

* Multiple blogs per store
* Post creation
  * WYSIWYG editor support
  * Summary content on the website and RSS feed
  * Main and thumbnail image support
  * Configurable page title format
* User comments
  * Moderation
  * Notification of new comments by email
  * [Gravatar](https://gravatar.com) support
  * reCAPTCHA support through [Fontis reCAPTCHA](https://github.com/fontis/fontis_recaptcha) extension
* Tags
* Authors
* Archives (daily, monthly or yearly)
* Header images for blogs and categories
* Configurable routes for categories, tags and authors
* Improved canonical URL support
* RSS feeds (global and per-category)
* Search support through [Fontis Algolia](https://www.fontis.com.au/algolia-magento-extension)
* AddThis integration
* Built-in microdata support for posts
* Full multi-store support
* Full admin panel ACL support
* Adds blog posts, categories and tags to the Magento sitemap.xml generator
* Support for Magento CE 1.9+/EE 1.14+ Topmenu block (adding blog links to primary navigation)
* Integration with the Magento EE Full Page Cache (or other caches that implement a similar interface)
* Integration with the [Enhanced Admin Grids extension](https://github.com/mage-eag/mage-enhanced-admin-grids)
* Basic support for the Magento EE Admin Actions Log (blogs and posts only)

Install instructions
--------------------

The extension is available from the [Fontis Composer Repository](https://www.fontis.com.au/composer-faq).
Once you're set up to use it, installing with composer is simple:

```
composer require fontis/blog 2.0.*
```

Or edit your composer.json file directly and add this line to the
"require" section:

```
"fontis/blog": "2.0.*"
```

Contributions
-------------

This project is open source. You are encouraged to fork and submit pull requests.

Acknowledgements
----------------

The Fontis Blog extension is a fork of the *Lazzymonk Blog* extension (v0.5.8)
developed by Robert Chambers and released in March 2009.
