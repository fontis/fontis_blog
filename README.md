Fontis Blog Extension
=====================

Overview
--------

This extension provides a native blog for Magento. It has full multi-store
support, and integrates seamlessly with the Fontis reCAPTCHA extension.

Features
--------

* Post creation
  * WYSIWYG editor support
  * Summary content on the website and RSS feed
* User comments
  * Moderation
  * [Gravatar][gravatar] support
  * reCAPTCHA support through [Fontis reCAPTCHA][recaptcha] extension
* RSS feeds (global and per-category)
* Sidebar widget
* Archives (daily, monthly or yearly)
* Links to bookmark services
* Full multi-store support
* Adds blog posts and categories to the Magento sitemap.xml generator
* Integration with the Magento EE Full Page Cache (or other caches which implement a similar interface)

Install instructions
--------------------

1. Using Modman

  ```bash
  modman clone git@github.com:fontis/fontis_blog.git
  ```

1. Manually

  Copy files from src/ into your Magento root.

Contributions
-------------

This project is open source. You are encouraged to fork and submit pull requests.

Acknowledgements
----------------

The Fontis Blog extension is a fork of the _Lazzymonk Blog_ extension (v0.5.8)
developed by Robert Chambers and released in March 2009.

[gravatar]: http://gravatar.com
[recaptcha]: https://github.com/fontis/fontis_recaptcha
[modman]: https://github.com/colinmollenhour/modman
