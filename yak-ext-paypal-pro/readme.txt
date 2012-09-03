=== Plugin Name ===
Contributors: jasonrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, shopping cart, paypal, paypal pro, payments
Requires at least: 3.2
Tested up to: 3.4.1
Stable tag: v3.3.4
Donate link: http://afillyateit.com/forums/topic/693
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An add-on module for YAK for WordPress, providing support for PayPal Pro.

== Description ==

The yak-ext-paypal-pro plugin is an add-on module for [YAK for WordPress](http://wordpress.org/extend/plugins/yak-for-wordpress) providing support for [PayPal Pro](https://www.paypal.com/webapps/mpp/website-payments-pro), to processes card payments directly (without requiring the redirect to another site).

== Installation ==

= Setup your PayPal account =

To setup Payments Pro, you'll need a PayPal Sandbox account to start with. 

1. After creating an account, in the Sandbox Test Accounts section, you'll see a link labeled: Create a Website Payments Pro account. Click on the link and then hit the Continue button on the next page.

2. Click to create a new business account, and fill in the next two pages with the information required about your test business - this is one of the most painful parts of the whole process (at least if you're filling in dummy information). PayPal requires valid area
codes, telephone codes, etc; which make the procedure more difficult than it needs to be.

3. Once the account has been created, check the email (still on the Sandbox site – the Test Email section) and activate the account.

4. After logging in, click the "Get Verified" link and enter a bank name on the next page (note that in the Sandbox, the other details will already be filled). While using the Sandbox, you won't need to go through the actual verification process, but this step, at least, is required.

5. Once verification is complete, click the "Profile" link and under "Account Information", select "Request API Credentials". 

6. Under Option 1, select "Set up PayPal API credentials" and permissions and then, on the next page, once again select "Request API Credentials". The next page has a couple of radio buttons - we want to select the first option: "Request API signature", then click the Agree and Submit button. You'll need to record the following information: API Username, API Password and Signature.

= Install and configure the plugin =

Once your account has been successfully setup:

1. Click on WordPress's Plugins menu, then select Add New

2. Enter yak-ext-paypal-pro in the search box, and click Search Plugins

3. Install the plugin and activate

4. Create a return page in WordPress, with the following content:

    > Thanks for your order. 
    > Your reference is <strong>[yak_order_id]</strong>.
    >
    > Your order will be processed within 5 working days.
    
5. Create another page, titled "Processing Error", with the following content:

    > [error_message]

6. You'll find a new payment option available in YAK->General Options->Payments. Add the payment, giving it a name and selecting "SPECIAL: PayPal Payments Pro (Sandbox)" from the drop-down menu.

7. Scrolling down the page, you'll find a new section available titled "PayPal Pro settings" where you can enter your PayPal Pro API username, password and signature (recorded earlier)

8. Select the Return page and Error page you created before

9. Click the "Update Options" button to save your changes (you'll now have a new payment type available in your checkout)

Note that when you're ready to start accepting live payments, you'll need to change the payment type to "Special: PayPal Payments Pro (Live)"


== Frequently Asked Questions ==

= Where can I get support? =

If you need help with this plugin, you can purchase a support ticket [here](http://afillyateit.com/support-request/).


== Screenshots ==

1. The PayPal Pro account API details
2. The PayPal Pro settings screen
3. The credit card input form, display when PayPal Pro payment is selected


== Changelog ==

= Version 3.3.4 =

* Update readme with more info, add screenshots


= Version 3.3.3 =

* Fix issue where YAK is deleted and the paypal-pro plugin causes WP to crash


= Version 3.3.2 =

* Update UI elements to the latest version of YAK


= Version 3.3.1 =

* Initial version, code moved from the main YAK codebase


== Upgrade Notice ==

= 3.3.4 =

Upgrading to this version is not mandatory. There are no code changes in this release (instruction/information changes only).
