=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, credit cards, payments
Requires at least: 3.2
Tested up to: 3.4.1
Stable tag: v3.3.5
Donate link: http://afillyateit.com/forums/topic/693
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


An add-on module for YAK for WordPress, providing support for manual credit card payments processing.

== Description ==

The yak-ext-manualcc plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing support for manual credit card payments - in other words, payments which the merchant processes manually through the systems provided by their bank. Basic validation is performed on the credit card number, but it is up to your external provider to handle fraud detection, settlement and so on. Note that if you use this method of accepting payments, you *MUST* use SSL to secure your cart – not securing your customers interactions represents a significant security risk (especially so, for credit card transactions). In addition, it is *NOT* recommended to run your blog on a shared hosting environment. While the CC details are stored in the database, they are only as secure as your host's operating environment. A [dedicated host](http://en.wikipedia.org/wiki/Dedicated_hosting), or at the very least a [VPS](http://en.wikipedia.org/wiki/Virtual_private_server) (Virtual Private Server) such as Linode ([http://www.linode.com](http://www.linode.com)) is essential.

== Installation ==

1. Click on WordPress's Plugins menu, then select Add New

2. Enter yak-ext-manualcc in the search box, and click Search Plugins

3. Install the plugin and activate

4. Create a landing page for credit card payments, by clicking on Pages, then selecting Add New. Give the page a meaningful title, such as "Credit Card Payment". The text of the page will be something like:

    > Thanks for your order. Your reference is <strong>[yak\_order\_id]</strong>.
    >
    > Credit card orders are processed weekdays from 9am till 6pm only.
    > You will receive confirmation once your credit card payment has been processed.

5. You'll find a new payment option available in YAK->General Options->Payments. Add the payment, giving it a name and selecting "SPECIAL: Credit Card" from the drop-down menu.

6. Scrolling down the page, you'll find a new section available titled "Manual Credit Card settings" where you can select your landing page for credit card payments

7. Tick whether to send a notification email immediately upon receiving an order, or only after processing the payment

8. Enter the public key to use for encrypting payment details (see the FAQ for more info on securing credit card orders)

9. Select the types of card you want to accept in the multi-select box labelled "Allowed credit card types".

10. Click the Update Options button to save your changes (you'll now have a new payment type available in your checkout)


== Frequently Asked Questions ==

= Where can I get support? =

If you need help with this plugin, you can purchase a support ticket [here](http://afillyateit.com/support-request/).

= I'm on a shared host, how can I secure my (manual) credit card orders? =
Processing manual credit card payments on a shared host isn't recommended (at all). The credit card numbers are stored as clear text in your database, which means if there is a security vulnerability on your server, another user could potentially get access to that data. There is a "window of risk" — between the time the order is made, and the time you process it (at which point the credit card number is blanked out) — but, however short the window, this is still an area of concern. Even on a virtual server, where your data is more isolated than a shared account, there is still risk to storing credit card numbers as plain text in your database.

To reduce the risk, consider encrypting the credit card data, by following the steps below:

1. Click on "General Options" in the YAK panel, then select the "Payments" tab.
2. In the section "Manual Credit Card Settings", click the "Generate" button in order to generate a new public/private key pair.
3. Copy the data for the private key (including the -----BEGIN RSA PRIVATE KEY----- header and footer) and save to a text file. Store this on a machine *other* than your web server. Preferably, store in multiple locations. On a USB key in a safe (for example), and perhaps on an encrypted drive on your main computer.
4. Copy the data for the public key (everything in the second text box)
5. Click on "General Options" in the Settings panel, then select the "Payments" tab.
6. Scroll down to the "Manual Credit Card settings" section, and paste the public key into the "Public Key" text box. Make sure you copy as is, with no modifications.

When a credit card order is submitted, the number will be encrypted using this public key. When you view orders, ("Orders" link in the YAK settings), you will now see a new input box, where you will paste the private key (previously copied) which will be used to decrypt the credit numbers.

*WARNING*: do not lose the private key. Although you can generate any number of these keys, once data has been encrypted with a public key it can only be decrypted with the associated private key. Lose the private key, and this data is lost forever.

== Screenshots ==

1. The manual credit card settings section, after clicking the Generate button to generate a public/private key pair.
2. The credit card input form during the checkout process


== Changelog ==

= Version 3.3.5 =

* Update readme with more info, add screenshots


= Version 3.3.4 =

* Fix issue where YAK is deleted and the manual-cc plugin causes WP to crash


= Version 3.3.3 =

* Update UI elements to the latest version of YAK


= Version 3.3.2 =

* Move public/private key generation code out of the main YAK codebase into this module


= Version 3.3.1 =

* Initial version, code moved from the main YAK codebase


== Upgrade Notice ==

= 3.3.5 =

Upgrading to this version is not mandatory. There are no code changes in this release (instruction/information changes only).
