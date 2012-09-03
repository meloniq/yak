=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, stripe
Requires at least: 3.2
Tested up to: 3.4.1
Stable tag: v3.3.1
Donate link: http://afillyateit.com/forums/topic/693

An add-on module for YAK for WordPress, providing support for Stripe credit card payments.

== Description ==

The yak-ext-stripe plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing 
support for [Stripe](https://stripe.com) credit card payments. Stripe (https://stripe.com) is a payments platform that allows you to securely accept credit card payments without redirecting to another site. There are less costs involved in starting up, when you compare with something like Authorize.net, and transaction fees (at time of writing) are similar to PayPal Pro, but without the monthly fees.


== Installation ==

To start accepting payments through Stripe, after signing up for an account (go to https://stripe.com for more info), you’ll need to install, activate and configure the module:

1. In the WordPress dashboard, click Plugins, then Add New, and enter "stripe".  

2. Click the Search button. You're looking for "YAK Add-on Module – Stripe" in the list of plugins presented. 

3. Install the plugin, and then activate

4. Next, create a landing page for successful orders. Give the page an appropriate title (such as "Thanks for your order" or "Successful payment"). The text of the page might be something like:

    >   
    >   Thanks for your order.  
    >   
    >   Your reference is <strong>[yak\_order\_id]</strong>.
    >   
    >   Your order will be processed within 5 working days.
    >   
     
5. You will also need a landing page for handling errors during the payments process. This page will be titled "Processing Error", and the content will be simply:

    >   
    >   [error\_message]
    >   

There are a couple of other tags you might also find useful in this screen: [yak\_back\_to\_address] displays a button to redirect the customer back to the address entry form, and [yak\_back\_to\_cc] displays a button to redirect them back to the credit card entry form.

6. In _YAK General Options_, click on the _Basic_ tab and then tick the option _Use SSL checkout_ (if it's not already ticked). Note that you will need to setup your web server to support secure connections at this point (by purchasing and configuring an SSL certificate).

7. Add a new payment option, by selecting _YAK General Options_, and clicking on the _Payments_ tab. In the section _Redirects for Payment_, enter a type name (such as "Credit Card"), and select _SPECIAL: Stripe_ from the dropdown menu. 

8. Scroll down the page to the section labeled _Stripe settings_, and select your success and error pages from the drop down lists.

9. Enter your Secret Key and Publishable keys in the requisite boxes. You can find these by going to your Stripe account \([https://manage.stripe.com/account](https://manage.stripe.com/account)\) and clicking on the _API Keys_ tab. For test purposes, you can use the Test Secret and Publishable keys – when you're ready to take your site live, you'll need to change to the Live keys.
     
10. Click the _Update options_ button to save your changes. You'll now have a new payment option on the checkout (if you only have one payment option, no dropdown is displayed).


== Frequently Asked Questions ==

= Where can I get support? =

If you need help with this plugin, you can purchase a support ticket [here](http://afillyateit.com/support-request/).

= Do I need to use SSL with this payment type? =

Short answer, yes.  For a longer answer, see Stripe's own help page on SSL [here](https://stripe.com/help/ssl).


== Screenshots ==

1. The settings section for entering Stripe API details and selecting the landing pages.
2. An error message on the credit card input form, when an invalid credit card was entered.


== Changelog ==

= Version 3.3.1 =
* Update installation instructions to provide more detailed instructions
* Add screenshots

= Version 3.3.0 =
* Fix for weird includes issue with some installs.

= Version 3.2.9 =
* Update UI elements to the latest version of YAK

= Version 3.2.8 =
* Initial version, code moved from the main YAK codebase


== Upgrade Notice ==

= 3.3.1 =

Upgrading to this version is not required. There are no code changes in this release (instruction/information changes only).