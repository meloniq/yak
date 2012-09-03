=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, authorize.net, authorize
Requires at least: 3.2
Tested up to: 3.4
Stable tag: v3.3.0
Donate link: http://afillyateit.com/forums/topic/693

An add-on module for YAK for WordPress, providing support for Authorize.net credit card payments.

== Description ==

The yak-ext-authorizenet plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing support for [Authorize.net](http://www.authorize.net/) credit card payments. When activated and configured, a new screen will appear during the checkout process where the customer can enter their credit card details for payment. This provides a seamless checkout process without redirecting the customer to another site to complete their purchase.


== Installation ==

To integrate with Authorize.net, you will need a merchant account, and both the API Account Login and Transaction Key for that account.

1. Click on WordPress's Plugins menu, then select Add New

2. Enter yak-ext-authorizenet in the search box, and click Search Plugins. You're looking for "YAK Add-on Module – Authorize.net payments" in the list presented. 

3. Install the plugin and then activate it

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

7. In _YAK General Options_, click on the _Payments_ tab, and then scroll down to the section _Authorize.net settings_. Enter your API Login ID and Transaction Key in the boxes so labeled, then select the landing page and error page you just created in the dropdowns.

8. The final step in this process is to add the new payment option at the top of the page. In the section _Redirects for Payment_, enter a type name (such as "Credit Card"), and select _SPECIAL: Authorize.net (Test)_ from the dropdown menu. Click the _Update options_ button, at the bottom of the page to confirm your changes.  Note: change this to _SPECIAL: Authorize.net_ when you have finished testing and want to change to production mode.


== Frequently Asked Questions ==

= Where can I get support? =

If you need help with this or any other YAK module, you can purchase a support ticket [here](http://afillyateit.com/support-request/).

= What authentication method does yak-ext-authorizenet use? =

YAK uses Authorize.net's Advanced Integration Method to process card payments. You can find more information about AIM on their website (The section on test transactions will be of particular use): (http://developer.authorize.net/guides/AIM/)[http://developer.authorize.net/guides/AIM/]

= Are eChecks supported? =

No, only credit card payments through AIM are supported.


== Screenshots ==

1. The settings section for entering Authorize.net API details and selecting the landing pages.
2. The credit card input form during the checkout process (displayed when Authorize.net is activated)


== Changelog ==

= Version 3.3.0 =
* Update installation instructions to provide more detailed instructions
* Add screenshots


= Version 3.2.9 =
* Update UI elements to the latest version of YAK


= Version 3.2.8 =
* Initial version, code moved from the main YAK codebase


== Upgrade Notice ==

= 3.3.0 =
Upgrading to this version is not mandatory. There are no code changes in this release (instruction/information changes only).
