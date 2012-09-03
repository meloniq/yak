=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, Google Checkout, payments
Requires at least: 3.2
Tested up to: 3.4
Stable tag: v3.3.3

An add-on module for YAK for WordPress, providing support for Google Checkout payments processing.

== Description ==

The yak-ext-google-checkout plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing support for [Google Checkout](http://checkout.google.com/sell/) payments processing.

Detailed installation and configuration instructions for YAK and its add-ons can be found by purchasing the YAK [Handbook](http://afillyateit.com/yak-for-wordpress/handbook), but basic installation instructions can be found [here](http://wordpress.org/extend/plugins/yak-ext-google-checkout/installation/).  If you want to do something more advanced, post a message in the [forums](http://afillyateit.com/forums), or consider buying the Handbook.

== Installation ==

For full installation details, covering all the flexible options YAK has to offer consider purchasing the [YAK Installation Handbook](http://afillyateit.com/yak-for-wordpress/handbook).

1. Click on WordPress's Plugins menu, then select Add New

2. Enter yak-ext-google-checkout in the search box, and click Search Plugins

3. Install the plugin and activate

4. You'll find a new payment option available in YAK->General Options->Payments. Add the payment, giving it a name and selecting "SPECIAL: Google (Sandbox)" from the drop-down menu.

5. Scrolling down the page, you'll find a new section available titled "Google settings" where you can enter your Merchant ID, Merchant Key, and select the Return page to use once payment has been completed.

6. Click the Update Options button to save your changes (you'll now have a new payment type available in your checkout)

Note that when you're ready to start accepting live payments, you'll need to change the payment type to "Special: Google (Live)"

== Frequently Asked Questions ==

See [here](http://afillyateit.com/yak-for-wordpress/faq) for more information about YAK and its add-ons.


== Changelog ==

**Version 3.3.3**

* Update UI elements to the latest version of YAK


**Version 3.3.2**

* Move google library code out of main YAK codebase


**Version 3.3.1**

* Initial version, code moved from the main YAK codebase
