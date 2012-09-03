=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, accounts receivable
Requires at least: 3.2
Tested up to: 3.4
Stable tag: v3.3.6
Donate link: http://afillyateit.com/forums/topic/693
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An add-on module for YAK for WordPress, providing support for Accounts Receivable orders.

== Description ==

The yak-ext-accrecv plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing support for Accounts Receivable orders (for example, something like [IMS Barter](https://www.imsbarter.com/)). When activated, and account's receivable payment is selected, a new input form is presented to the customer where they can enter their merchant account name and number.


== Installation ==

1. Click on WordPress's Plugins menu, then select Add New

2. Enter yak-ext-accrecv in the search box, and click Search Plugins

3. Install the plugin and activate

4. Create a landing page for your customers to see when make an order. For example, assuming you're using something like [IMS Barter](http://www.imsbarter.com) you might title the page "IMS" for example, with content:

    > Thanks for your order. Your order number is [yak\_order\_id].
    > 
    > Please allow up to 3 days for payments to be processed, and 5 working days for delivery.

4. You'll find a new payment option available in YAK->General Options->Payments. Add the payment, giving it a name and selecting "Special: Accounts Receivable" from the drop-down menu.

5. Scrolling down the page, you'll find a new section available titled "Accounts Receivable settings" where you can select the landing page you've just created. 

6. You can also enter the label to use on the accounts receivable form (this will be displayed during the checkout process). You might enter a label such as "Merchant A/C", or "IMS", or some other identifying value. Two input boxes will be presented to the customer during the checkout process: name and number. So assuming you enter "Merchant A/C", the labels in the checkout will be "Merchant A/C name" and "Merchant A/C number".

6. Click the Update Options button to save your changes (you'll now have a new payment type available in your checkout)

Note: there is no validation of the information entered when a customer pays via this mechanism – this is up to you as part of the manual fulfillment process.


== Frequently Asked Questions ==

= Where can I get support? =

If you need help with this plugin, you can purchase a support ticket [here](http://afillyateit.com/support-request/).


==Screenshots==

1. The settings section for selecting the landing page and entering a label to be used on the checkout
2. The account input form during the checkout process (when accounts receivable payment is selected)


== Changelog ==

= Version 3.3.6 =

* Update readme with more info, add screenshots


= Version 3.3.5 =

* Update UI elements to the latest version of YAK


= Version 3.3.4 =

* Initial version, code moved from the main YAK codebase


== Upgrade Notice ==

= 3.3.6 =

Upgrading to this version is not mandatory. There are no code changes in this release (instruction/information changes only).
