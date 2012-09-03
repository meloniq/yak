=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, sales tax, tax
Requires at least: 3.2
Tested up to: 3.4.1
Stable tag: v3.3.7
Donate link: http://afillyateit.com/forums/topic/693
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An add-on module for YAK for WordPress, providing support for basic sales tax calculations.

== Description ==

The yak-ext-salestax plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing support for basic sales tax calculation.


== Installation ==

1. Click on WordPress's Plugins menu, then select Add New

2. Enter yak-ext-salestax in the search box, and click Search Plugins

3. Install the plugin and activate

4. To activate sales tax, click on the Sales Tax link in the YAK submenu and select the Basic tab. There are two options on this screen: Enable sales tax (switch on the basic processing) and Display zero tax (whether to display tax when the calculated result is 0). Select the first checkbox (and the second if you want to display tax, no matter what), and hit the "Update options" button. 

5. Select the Country tax tab. On this screen you can specify the tax percentage on a per-country basis. For each country where you want to calculate sales tax, enter the fractional multiplier in the input box. For example, GST (Goods and Sales Tax) in Australia is 10%, so the multiplier will be 0.1. You can see an example of this here:

![Country tax](/extend/plugins/yak-ext-salestax/screenshot-2.png)

6. Click the "Update Options" button to save your changes

7. Currently, for the United States and Canada, you can also enter tax per State. Select the US State Tax or Canada State Tax tabs and enter sales tax multipliers accordingly. Hit the Update options button, once again, to apply any changes.


== Frequently Asked Questions ==

= Where can I get support? =

If you need help with this or any other YAK module, you can purchase a support ticket [here](http://afillyateit.com/support-request/).


== Screenshots ==

1. The basic options screen
2. The country tax options screen



== Changelog ==

= Version 3.3.7 =

* Update installation instructions to provide more detailed instructions
* Add screenshots


= Version 3.3.6 =

* Fix issue where YAK is deleted and the sales-tax plugin causes WP to crash


= Version 3.3.5 =

* Update UI elements to the latest version of YAK


= Version 3.3.4 =

* Initial version, code moved from the main YAK codebase


== Upgrade Notice ==

= 3.3.7 =

Upgrading to this version is not mandatory. There are no code changes in this release (instruction/information changes only).
