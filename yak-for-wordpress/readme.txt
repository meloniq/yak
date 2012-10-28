=== Plugin Name ===
Contributors: jrbriggs
Tags: shop, e-commerce, shopping, cart, commerce, selling, paypal, shopping cart, authorize.net, google checkout, stripe, mastercard, MiGS, accounts-receivable
Requires at least: 3.4
Tested up to: 3.4.2
Stable tag: v3.4.8
Donate link: http://afillyateit.com/forums/topic/693
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

YAK is a shopping cart plugin for WordPress which associates products with weblog entries.

== Description ==

YAK is an extensible, open source, shopping cart plugin for WordPress. It associates products with weblog entries (in other words, posts), so the post ID also becomes the product code. It supports both pages and posts as products, handles different types of product through categorisation, and provides customisable purchase options -- cheque or deposit, basic credit card form (for [manual credit card processing](http://wordpress.org/extend/plugins/yak-ext-manualcc/)), [accounts receivable](http://wordpress.org/extend/plugins/yak-ext-accrecv), basic [Google Checkout](http://wordpress.org/extend/plugins/yak-ext-google-checkout/) integration, standard PayPal integration, [PayPal Payments Pro](http://wordpress.org/extend/plugins/yak-ext-paypal-pro/), [Authorize.net](http://wordpress.org/extend/plugins/yak-ext-authorizenet/), [Stripe](http://wordpress.org/extend/plugins/yak-ext-stripe/) and [MiGS](http://afillyateit.com/yak-for-wordpress/add-on-modules/migs/) (Mastercard Internet Gateway Service, add-on only).


**FEATURES**

* Create products from either posts or pages
* Downloadable products
* Multiple product types -- price per type (i.e. small, medium, large) and quantity per type
* Order administration -- filtering by date, status and order number
* Customer order tracker
* Products page with paging (simple alternative to viewing by category)
* Configurable shipping (either flat rate or by weight unit)
* Configurable shipping address
* Configurable countries list
* Promotions -- percentage or fixed discounts, on shipping or order value
* Sales Reports (basic flash charts showing sales, best sellers, etc)
* Support for https (SSL)
* Tags for configurable emails
* Basic XML feed
* Support for multiple shops (function to retrieve product details from another shop)
* Checkout/order widget
* Customer interface has been translated into a number of languages: Japanese, Chinese, Taiwan Chinese, Thai, Slovakian, Czech, German, French, Italian, Spanish, Norwegian, Swedish, Indonesian
* Basic Sales Tax support (does not work with Google Checkout)


**PAYMENT GATEWAYS**

* Plain page -- custom payment such as providing information for your customers to pay by cheque, or direct deposit/debit
* Manual credit card -- requiring SSL, and a secure hosting environment
* PayPal Standard -- customers are redirected to PayPal for payments
* Google Checkout -- customers are redirected to Google for payments (note: Checkout is not fully integrated)
* Authorize.net -- Authorize's payments gateway is used to charge credit cards
* PayPal Payments Pro -- PayPal's payments gateway is used to charge credit cards
* Accounts Receivable 
* Mastercard Internet Gateway Service -- via an [add-on](http://afillyateit.com/yak-for-wordpress/add-on-modules/migs/)
* [Stripe](https://www.stripe.com)


**EXTENSIONS**

* [YAK Gallery](http://afillyateit.com/yak-for-wordpress/add-on-modules/gallery/) - a gallery plugin for your products
* [YAK AddInfo](http://afillyateit.com/yak-for-wordpress/add-on-modules/addinfo/) - configure additional fields on the final order confirmation
* [YAK MiGS](http://afillyateit.com/yak-for-wordpress/add-on-modules/migs/) - a payment module supporting the Mastercard Internet Gateway Service (payment gateway)
* [YAK Membership](http://afillyateit.com/yak-for-wordpress/add-on-modules/membership) - a members module for YAK. Sell membership through YAK, and then restrict access to parts of your site.
* [YAK Fulfillment](http://afillyateit.com/yak-for-wordpress/add-on-modules/fulfillment) - module providing ability to automatically transmit paid orders (via FTP) to a fulfillment centre.


**FREE EXTENSIONS**

* [Accounts Receivable](http://wordpress.org/extend/plugins/yak-ext-accrecv) - a plugin providing accounts receivable payments
* [Authorize.net](http://wordpress.org/extend/plugins/yak-ext-authorizenet/) - a plugin providing Authorize.net credit card payments
* [Google Checkout](http://wordpress.org/extend/plugins/yak-ext-google-checkout) - a plugin providing Google Checkout payments processing
* [ManualCC](http://wordpress.org/extend/plugins/yak-ext-manualcc/) - a plugin providing manual credit cards payments processing 
* [PayPal Payments Pro](http://wordpress.org/extend/plugins/yak-ext-paypal-pro/) - a plugin providing PayPal Pro credit card payments processing
* [Sales Tax](http://wordpress.org/extend/plugins/yak-ext-salestax) - a plugin providing sales tax calculations
* [Stripe](http://wordpress.org/extend/plugins/yak-ext-stripe/) - a plugin providing Stripe credit card payments


== Installation ==

= Basic Setup =

1. Download the zip distribution and unzip into your wp-content/plugins directory, or alternatively, in the WP dashboard, under plugins, select "Add New", and enter YAK as the term to search for.

2. Activate the plugin (click the Plugins link, then click YAK for WordPress).  You'll now find a new menu group "YAK" containing: "Orders", "Products", "Sales Reports", "General Options", "Shipping Options", and "Misc Options". A new checkout page will have been automatically created (if not already present).  You will also find two new categories have been created:  one called "products", and another subcategory, labelled "default".  This is like a marker for YAK products (mainly used for products of only one type).  If you have products with different types (for example, you're selling T-shirts in sizes large, medium and small), you'll also need to create them as child categories of "products".  At the end of this process, you might have the sub-categories: "default" (automatically created for you), "large", "medium", "small".

3. A landing page is where you want a customer to end up, at the end of the purchasing process. Smaller retailers may want customers to deposit the money directly to their accounts (this is quite common in South-East Asian countries), others want their customers to pay by check or credit card. YAK is reasonably flexible about payment options, so you can create any number of landing pages for the different payment options you want to accept. For the moment, we'll create a new landing page to accept payments by deposit. Click "Add New", in WordPress's "Pages" section.  Give the landing page a title, such as "Deposit".  For the content of the page, you should provide instructions on how a customer is to pay by deposit.  For example:

    >   Thanks for your order. Your order number is [yak\_order\_id]
    >
    >   You should receive a confirmation message shortly.
    >
    >   Please deposit the amount of <strong>[yak\_order\_value]</strong>
    >   into the following bank account:
    >
    >   Bank: SomeBank
    >
    >   Branch: Some Branch
    >
    >   Address:  blah blah blah blah
    >
    >   Account Number: 00-0000-000-000
    >
    >   SWIFT CODE: 00000

4. Another example of a landing page might be paying by cheque.  Create another page in the same way as you created the Deposit page using the text:

    >   Please a cheque in the amount of &#x005b;yak\_order\_value&#x005d; to:
    >
    >   12 Vulcan Lane
    >
    >   Auckland City
    >
    >   Auckland
    >
    >   Please note reference number [yak\_order\_id] on the back of the cheque.

    Save this new page as before. Note that you'll probably want to exclude these pages from the main menu -- which you can do using the plugin [Exclude-Pages](http://wordpress.org/extend/plugins/exclude-pages/).

5. Configure how you want YAK to function, by clicking on the "General Options" link in the YAK menu. On the "Basic" tab:
    
    a. Enter a confirmation email address, if you want to send order confirmations (if so, you'll also need to enter a confirmation message).
    
    b. If you want to display the checkout immediately when a customer clicks the Buy button, select your Checkout page from the drop down list for the option "Redirect on buy to".
    
    c. Most importantly: select the "products" category from the dropdown "Product category name", if it is not already selected.
    
    d. Click the Update Options button to save the changes.

6. On the Products price/quantity tab:
        
    a. Set an automatic discount for your online products (if you don't want to discount your products, set the fraction to 1).
    
    b. Select a currency from the drop down (USD $, GBP £, JPY ¥, etc). This will automatically setup the monetary format.

    c. If you want all new posts to be setup as products, you can enter an automatic quantity to use.
    
7. On the Payments tab:

    a. Next to "Redirect URLs for Payment", in the box labeled "Type Name", enter the text "Deposit".
    
    b. For the "Redirect To" select the Deposit landing page you created earlier.
    
    c. Click the plus (+) button and do the same for the Cheque landing page you also created.
    
8. Configure the default shipping options, by click on the "Shipping Options" link in the YAK menu. On the "Basic" tab:

    a. Select a default country.

    b. Tick the checkboxes for what to include in the shipping address.
    
9. YAK associates products with either posts or pages, but posts are the most common method to create products. You can use special tags in the content of either a post or a page; to add pricing information ([yak\_price]), the quantity of a product ([yak\_quantity]), and the buy button ( [yak\_buy]). Create a new product by performing the following steps:
        
    a. Write a new post (Click on "Add New" in the Posts section).
    
    b. Give your product a meaningful title.
    
    c. Enter details about the product. For example, if you were selling T-Shirts you might create the post as follows:
    
    > Short-sleeve, v-neck t-shirt. Available in any colour, so long as it's black.
    >
    > Price: [yak_price]
    >
    > [yak_buy]

10. Below the post content (near the bottom of the page) is the YAK Product Details panel. This is where you enter the price for your product, an alternative title (useful if you want to give your post one title, and the display in the checkout another); and the types of your product. You can enter an override discount for this particular product (this is useful if you want to discount old stock), and specify multi-select options.

11. Also in that section, click the plus button to add a product type.In the dropdown, select the "default" category you created earlier. Set the quantity of this product that you have available.

12. Publish the post.

13. If you now click the Visit Site link, your new post should be at the top of the home page, with the price displayed, and an _Add to cart_ button visible. Click the button to add the product to your cart. Then click on the link for the checkout and follow through the stages to confirm that your Deposit page works correctly.

= Paypal Setup =

1. To use PayPal to process customer payments, you'll first need to setup a Sandbox account with PayPal. You can do this at [http://developer.paypal.com](http://developer.paypal.com). Click Sign Up Now and fill in the form with the details you want to use for your test account. Activation instructions will be sent to the email address you specified when registering – although this may take some time to arrive.

2. While waiting for the confirmation email,create a landing page for PayPal orders. This page should contain information for a customer after they've completed their order. PayPal's recommendation is something like:

    > Thank you for your payment. Your transaction has been completed, and a receipt for your purchase has been emailed to you.
    > You may log into your account at www.paypal.com/uk to view details of this transaction.
    
    Add a bit more information to make this page more useful:
    
    > Thanks for your order. Your order number is [yak_order_id].
    >
    > A receipt for your purchase has been emailed to you. You may log into your account at www.paypal.com/uk to view details
    > of this transaction.
    >
    > Please note, items can take between 5-10 working days to be delivered.
    
    Give the page a suitable title, and once you've published it, take a note of the URL. For example: http://www.yourdomain.com/?page_id=10 (or http://www.yourdomain.com/paypal-orders if you're using permalinks).
    
3. Once your account is activated, login to the sandbox and choose Test Accounts. Create a pre-configured seller account for the relevant country. Login to the account by selecting it, and clicking on the button Enter Sandbox Test Site (you'll need to enter the email address and password).

4. There are a number of facilities available with the test account, but we're interested in configuring the account profile to work with YAK – primarily, we want PayPal to notify our shop when a purchase is made. To do this, select the Profile link. Beneath the heading Selling Preferences, select the link Instant Payment Notification Preferences. Click the Edit button, and then on the next screen activate IPN by ticking the checkbox. The Notification URL is used by PayPal to send a message to your server when a purchase is completed – this page is located within the YAK directory, so relative to your WordPress root directory the page can be found at:
_/wp-content/plugins/yak-for-wordpress/yak-paypal-ipn.php_. So in the Notification URL box you'll enter something like:
_http://www.yourdomain.com/wp-content/plugins/yak-for-wordpress/yak-paypal-ipn.php_.

5. The next step is to set an auto-return URL, which will be directed to the landing page we created earlier. Select Website Payment Preferences from the PayPal Profile page, and change the Auto Return option (a radio button) to on. Enter the URL to your landing page as the Return URL, then scroll down the page, and change the Payment Data Transfer to on (note: we aren't actually going to use PDT, or Payment Data Transfer, but this is a requirement to get the auto-return functioning correctly).

6. Back in your WordPress administration screen, once again choose the General Options link. Add a new payment type, by selecting the Payments tab, entering "PayPal" as the Type Name, and selecting _EXTERNAL: PayPal (Sandbox)_ from the drop down.

7. Scroll down to _PayPal Settings_ and enter your test account name in the Account box (this might be something like user_1223013353_biz@yourdomain.com). In the section _Return URL_, select the landing page we also selected in the PayPal profile earlier, and select "Instant Payment Notification" from the drop down labeled _Use PDT or IPN_.

8. Click _Update options_ to save your changes.

Note #1: When you test payments using PayPal, you will need to log in to the Sandbox first, for it to work correctly.

Note #2: When you sign up for a live account, remember that there are a number of different [account types](https://www.paypal.com/cgi-bin/webscr?cmd=xpt/Marketing/general/PayPalAccountTypes-outside), and you won't be able to use a Personal account if you want to fully integrate YAK with PayPal.

Note #3: The currency you're using with PayPal needs to match the currency you've selected in General Options->Products price/quantity.

= Downloadable/digital Products =

As well as physical products, you can sell virtual or downloadable products with YAK. A downloadable product is a file, located on your file-system, which isn't accessible to the web server. This means the customer can't just point a browser and download the file directly. For example, if your web server root directory is...

    > c:\Program Files\Apache\htdocs\
    
...you wouldn’t put the download files in a subdirectory of htdocs. Instead you might put them in a directory:

    > c:\Program Files\Apache\downloads
    
Note that while the files shouldn't be accessible from outside your server, they still need to be accessible by the PHP process that is used to run WordPress. To create a downloadable product:

1. Create a directory you want to store the files (not externally accessible, as mentioned). For example, my web server root is _/var/www_, so I'm going to put my files in a directory _/var/www-files_. If you're going to be selling a PDF, copy your PDF (for example, test.pdf) to that directory.

2. Create a new product (click Post, then Add New, etc). In YAK Product Details, use the default category and enter a suitably large quantity. Some people might want to limit the number of downloads, so this would be the place to do it.

3. In the Download file input box, enter the full path of your download file. For example:

    > /var/www-files/test.pdf

4. Publish your post to save the changes.

5. Switch to Yak's _General Options_ and choose the _Download_ tab. There are three options on this screen. The first is an alternative URI if you want to use something like [mod_rewrite](http://httpd.apache.org/docs/2.2/mod/mod_rewrite.html) to change the download link. You could set this to:

    > http://yourdomain.com/downloads
    
    In this case, you will need to create a rewrite rule to change the actual download url (which is located, relative to your WordPress root, at _/wp-content/plugins/yak-for-wordpress/yak-dl.php_). An example mod rewrite rule for this redirection might be something like:
    
    > RewriteEngine on  
    > RewriteRule ^downloads/?$ wp-content/plugins/yak-for-wordpress/yak-dl.php?$1 [L]

    More important is the Download Email. This is the message that is sent to your customers with the download link attached - the essential part of this message is the code [downloads] (which is replaced with the actual link). This could be:
    
    > Thanks again for your order. Your download(s) can be retrieved by clicking the following:
    >
    > [downloads]
    
    The last input box is the email address that the download messages should come from.

6. Visit your site and try purchasing the product - when the funds have been received for your order, a download email will be sent to the email address you provide during the ordering process. Note that the link is tied to the IP address of the first download. Thus a customer can re-download the file if something goes wrong with the first attempt, but they can't send the link to friends.

= Running a Promotion =

You can run a promotion in YAK by selecting General Options, and then clicking on the Promotions tab. There are 8 types of promotion:

* Percentage discount for shipping (Shipping %) – a discount calculated using a percentage of the total shipping cost
* Value discount for shipping (Shipping Value) – a fixed discount subtracted from the shipping
* Percentage discount for the price (Pricing %) – a discount calculated using a percentage of the total price of the order
* Value discount for the price (Pricing Value) – a fixed discount subtracted from the total price
* Threshold-based percentage discount for shipping (Shipping % (Threshold)) – a discount calculated using a percentage of the total
shipping cost, triggered by the order value
* Threshold-based value discount for shipping (Shipping Value (Threshold)) – fixed discount triggered by order value
* Threshold-based percentage discount for the price (Pricing % (Threshold)) – price discount triggered by the order value
* Threshold-based value discount for the price (Price Value (Threshold)) – fixed price discount triggered by the order value

Promotions have a description, which is used in the final checkout confirmation screen and an expiry date (which can be blank or set to a specific date in the future).

To create a standard promotion:

1. Give your promotion a code (this is something you can advertise, give to select customers, etc)
2. Enter a description (displayed to your customer)
3. Select the type of promotion, and enter the value of the discount. If you select a percentage (%) promotion (shipping or price), the value will be a percentage (for example, 10 is a 10% discount); if you select a value promotion (shipping or price) the value will be the fixed value discount.
4. Enter an expiry date (if required),
5. Click the "Update options" button to save your changes.

To add another promotion, click the "+" button – you can create as many promotions as you like. Once you have created promotions, an input box will appear on the checkout. When a customer enters a promotion code, the discount will be calculated on the final screen of the checkout.

To create a threshold-based promotion, instead of a code, enter the order value that will trigger the discount. For example, if you want to give customers a 10% discount when they spend more than $100: 

1. Enter 100 (without any symbol) for the code
2. Select Pricing % (Threshold) from the dropdown menu. 
3. Enter the promotion value - which, in this case, would be 10.

Note #1: promotions can be activated for a specific list of named customers (who would have to have a wordpress login account with your site).
Note #2: promotions can be activated for a specific set of products (which can be flagged as products to include in the promotion, or products to exclude)


== Frequently Asked Questions ==

= Where can I get more help? =
If you need help with this or any other YAK module, you can purchase a support ticket [here](http://afillyateit.com/support-request/).

= Why has the cart suddenly stopped working? =
If YAK has been recently updated, try deactivating and reactivating YAK first. If that doesn't help, have you installed a new plugin or theme lately? Or have you updated any other plugins? Try disabling any recently updated or installed plugins, to see if there is an incompatibility issue. Try using the default theme to see if your theme has caused the incompatibility issue.

= What's the history of YAK? =
The first version of YAK was created in 2005. The sourceforge project was registered in March, 2006 but moved to the present WordPress.org Plugins site in '09. The source code has bounced around: first on Sourceforge, for a while in a privately hosted Mercurial repo, and then in WordPress's subversion repository. The latest stable version is still in subversion, but the working repository is now on [Github](https://github.com/kwoli/yak).

= I've found a bug. Where can I report it? =
Either in the WordPress [support forums](http://wordpress.org/support/plugin/yak-for-wordpress), or raise an issue on Github [here](https://github.com/kwoli/yak/issues).

= Why doesn't the cart translate to my language properly? =
Try checking the following:

1. Is there a translation file for your language (you can look [here](http://plugins.svn.wordpress.org/yak-for-wordpress/trunk/lang))? If there isn't one, why not try creating it yourself (get in contact if you want to know how)!

2. If there is a lang file, check what you've set the WPLANG variable to in wp-config.php (in your WP root directory). If the language variable is set to de_DE, then you'll to rename the file "yak-de.mo" to "yak-de_DE.mo". Why isn't the file named that already? Because some people use the full language qualifier "language_COUNTRY", and others just the language, so there's no standard (YAK's language files therefore remain with the same language code as the original translator provided).

3. If you find incorrect or missing messages, you might like to correct the mistake yourself. In which case, get in [contact](http://wordpress.org/support/plugin/yak-for-wordpress) with the details in the following format:

    > msgid "message text in english"  
    > msgstr "translated text"  
    
    For example:
    
    > msgid "a recipient name is required"  
    > msgstr "un nombre de recipiente es requerido"  
    
    The language file will be updated as soon as possible and released with the next version.

= Why doesn't my post show up properly as a product? =
Have you setup your categories correctly? At the very least you should have a "Products" category, with a "Default" child category. The child category should be selected on your post and have a quantity - the product should have a price.

= Why don't my confirmation emails arrive? =
Usually mailing problems can be traced to hosting issues. Try installing something like [WP-Mail-SMTP](http://wordpress.org/extend/plugins/wp-mail-smtp/), so that WP mail is sent via SMTP. After activating the plugin, navigate to the settings, and ensure you will be sending e-mail via [SMTP](http://en.wikipedia.org/wiki/Smtp ). Enter the domain name of your SMTP host (this is the server your host has provided to send email from), and enter port 25 (usually the default). If your host requires SMTP authentication, ensure that setting is enabled, and enter the username and password. Finally, enter the sender e-mail, and save your changes.

= What tags can I use in my posts or pages? =
The following tags are available:

* [yak\_cancelorder] – cancel an order using the order_id in the session (use on landing pages for PayPal cancellations)
* [yak\_checkout] – display the yak checkout in the page.
* [yak\_clean] – cleanup tag for clearing order details from the session (use on landing pages for PayPal, for example)
* [yak\_order\_id] – display the customers order id/number
* [yak\_order\_value] – display the total value of the current order
* [yak\_price] – display the price of the product (including discount if set)
* [yak\_price discount="false"] – display the price of the product without discount
* [yak\_quantity type="t"] - display the available quantity of a product (where 't' is the product category/type)
* [yak\_buy] – display the buy button
* [yak\_buy\_begin] – include the 'html form' part of the buy button (use if you want to split up the button code)
* [yak\_buy\_content] – include the 'html input' part of the buy button
* [yak\_buy\_end] – include the end of the html form of the buy button
* [yak\_cleanup] – cleanup order details (i.e. remove order details)
* [yak\_paypal\_pdt\_success] – a handler for PayPal's PDT (Payment Data Transfer) response which specifies a message to display after a successful purchase. Use in the form: [yak\_paypal\_pdt\_success]success message goes here[/yak\_paypal\_pdt\_success]
* [yak\_paypal\_pdt\_failure] – handler for PayPal's PDT (Payment Data Transfer) response which specifies a message to display after a failed purchase. Use in the form: [yak\_paypal\_pdt\_failure]failure message goes here[/yak\_paypal\_pdt\_failure]
* [yak\_product\_page] – display a full list of products (with paging if necessary)
* [yak\_get\_remote id="n"] – retrieve the contents of a product (where 'n' is the post id) from a remote YAK server
* [yak\_sku type="t"] – retrieve the SKU of a particular type of product (where 't' is the product category/type)
* [yak\_ordertracker] – display an order tracker on a page, which a logged in customer can use to view the status of their orders

= What tags can I use in my confirmation emails? =
* [order\_detail] – list of the items the customer has ordered
* [html\_order\_detail] – list of the items the customer has ordered in html format
* [order\_id] – the order number
* [order\_cost] – total cost of the order
* [payment\_type] – the payment type the customer selected
* [shipping\_type] – the shipping option the customer selected
* [shipping\_address] – the customer's shipping address
* [billing\_address] – the customer's billing address
* [html\_shipping\_address] – the customer's shipping address in html format
* [html\_billing\_address] – the customer's billing address in html format
* [name] – the customer's name
* [phone] – the customer's phone number
* [special\_instructions] – any special instructions set by the customer when ordering

= How do I change the style of the buy button? =

By default, YAK embeds the buy button using the html code <button>. This button uses a CSS style _yak\_button_, which you can add to a stylesheet to change the default look of the buttons. The simplest place to add this style is to the file ui.css (which is located in the yak-for-wordpress directory). For example, if you wanted a flat, red button instead of the default (Figure F-3), you might add the following style:

    .yak_button {
    background-color: red; padding: 5px;
    border: 1px solid black; 
    }
    
There is also a style for larger buttons (such as the buttons in the cart itself), which works in a similar way: _yak\_medium\_button_.

Note that if you change the style in this way, you'll need to re-apply the change whenever you upgrade.

= How do I use an image for my buttons? =

If you want to use a special image for your buttons you might add a style such as:

    .yak_button {
    border: 0px;
    width: 117px;
    height: 40px;
    font-weight: bold;
    color: blue;
    background-image: url('/wp-content/plugins/yak-for- wordpress/images/button-back.gif');
    }

In this case, the image has been copied into the yak-for-wordpress/images directory

= How do I hide the checkout and landing pages so they don't appear in a menu? =

As a part of installing YAK, you've no doubt created a number of pages that should only be visible when someone purchases an item (i.e. a "thank you page" when a customer returns from PayPal, a "deposit page", etc). However, these pages appear on your main menu – which means the pages are accessible outside of the order processing workflow. The obvious answer would be to tick the checkbox: Keep this page private when editing – however, this results in the page being inaccessible to anyone not logged in. Instead, download the plugin [exclude-pages](http://wordpress.org/extend/plugins/exclude-pages). Activate this plugin and a new option will appear when you edit a page: Include this page in user menus. Deselecting this option means the page won't appear on the menu, but is still accessible during the customer purchasing process.

= How do I hide my products from the main menu? =

Sometimes you want your products to stay off the front page. For example, you use WordPress as both a blog and a shop. The answer, in this case, is to use a plugin to hide categories. One such plugin is the WP Category Visibility Plugin ([http://www.ipeat.com/?page_id=91](http://www.ipeat.com/?page_id=91)). Activate this plugin and a new option will appear on the Posts menu: Category Visibility. This page displays a list of categories and the areas where you want those categories to display, such as Front (page), List, Search, Feed, Archives, etc. You can also activate a category for a specific user level. Note: it looks like [Category Visibility – iPeat Rev](http://wordpress.org/extend/plugins/category-visibility-ipeat/) is a replacement plugin that works with WP 3+.

= How do I categorise my products? =

Don't forget that just because YAK uses a special category (or set of categories) for identifying products, there's no reason why you can't also setup your own set of categories for your products as well. For example, supposing you sell t-shirts in sizes: small, medium, and large. You will have a base category "products", with the three sub-categories. You might also create other categories, such as "Music", "Movies", "Brands" to describe the different styles of t-shirts, and perhaps colours: "black", "blue", "white", "yellow" - so for each post (product) describing a t-shirt, you'll tick the categories for the sizes you have available (small, medium and/or large), along with the style and color. Note that these other categories do NOT have to be a sub-category of "products".

If you haven't already, you'll also want to turn on permalinks for your site (see Settings->Permalinks).

So to list all products that are available in "small" sizes, in the browser you'd navigate to:

http://yoursite.com/category/products/small/

To list all blue t-shirts, you'd navigate to:

http://yoursite.com/category/blue/

To list all Movie t-shirts, you'd navigate to:

http://yoursite.com/category/Movies/

= My customers get an error page when they hit the back button in the checkout. How do I fix this? =

You might have problems with IE (Internet Explorer) when using the back button. This will usually only be noticeable in the checkout, when a customer hits back (from the address entry for example, or from the final confirmation page). You'll end up with a page looking something like this:

    Warning: Page has Expired
    
    The page you requested was created using information you submitted in a form. This page is no longer available.
    As a security precaution, Internet Explorer does not automatically resubmit your information for you.
    To resubmit your information and view this Web page, click the Refresh button.
    
If you experience this issue, perform the following steps:

1. In YAK Settings, click the Advanced tab

2. In the multi-select labeled No caching, click on your checkout page (or pages)

3. Click the button "Update options"


= How do I setup more than one shipping option? =

Go to YAK's Shipping Options screen, and select the Basic tab. Enter the types of shipping you support in the "Shipping options" box at the bottom of the screen.  Enter each option separated by a newline:

    > Standard mail
    > Fedex
    > UPS
    
On the options tab, you will now see a section for each option you've entered where you can enter a fixed total cost for an order, a fixed cost per item (for the first item and then subsequent items), or a cost by weight.


= How do I use HTML in my confirmation email? =

As long as your mail message starts with &lt;html&gt; and ends with &lt;/html&gt; then it should automatically generate the correctly encoded mail message.


== Screenshots ==

1. Creating a YAK product
2. Final page of the checkout


== Changelog ==

= Version 3.4.8 =

* Add support for comma-separated confirmation email addresses (deliver the order notification mail to multiple addresses) - the first address is the primary confirmation mail.


= Version 3.4.7 =

* Fix minor installation issue, which shows up in WP3.5 beta
* Fix problem with displaying SKU
* Add check digit calculation for automatically generated SKUs


= Version 3.4.6 =

* Fix for possible truncation issue with order meta storage


= Version 3.4.5 =

* Minor fix for installation script which auto-generates checkout page
* Add inclusion/exclusion facility to promotions (specify include to include a set of products, exclude to exclude the set)


= Version 3.4.4 =

* Bug fix for T&C checkbox blocking the checkout submit


= Version 3.4.3 =

* Minor modification to force reports to use PAYMENT PROCESSED as well as STOCK SENT.
* Minor change to checkbox output 
* Refactor ordermeta insert into separate function (code tidy up)
* Add facility for customer to enter the quantity before clicking the buy button
* Add facility to hide the update button on the first page of the cart


= Version 3.4.2 =

* Replace openflashchart with jqplot on the sales reports screen


= Version 3.4.1 =

* Fix problem with collapsible tables in the last release


= Version 3.4 =

* Revert back to jQuery in main interface
* Change resource loading, so that css/js files are only loaded on YAK admin pages
* Use JQuery Tools for admin elements (date input, tabs, tool tips) to tidy up the UI


= Version 3.3.5 =

* Add domain wildcard to blacklist (e.g. *@somescammer.com)
* Second attempt at fixing shipping option bug


= Version 3.3.4 =

* Remove demo credit card extension
* Move sales tax extension into separate module [yak-ext-salestax](http://wordpress.org/extend/plugins/yak-ext-salestax)
* Move accounts receivable extension into separate module [yak-ext-salestax](http://wordpress.org/extend/plugins/yak-ext-accrecv)
* Move PayPal Standard extension back into YAK code (it's now active by default)
* Move coupons processing back into YAK code
* Fix bug with shipping options containing non-standard characters ($ and . for example)
* Add blacklist facility -- useful when selling downloadable products if you have doubts about a scammer potentially re-using a dodgy or hacked email address


= Version 3.3.3 =

* Fix bug with spaces in blog title causing problems in session
* Add facility for price entry - such as a donation button where the donation amount can be entered when adding to the cart
* Move public/private key generation into manualcc module
* Turn off discount display, if calculation comes to zero


= Version 3.3.2 =

* Second attempt at removing old versions of manual credit card, google checkout and paypal pro modules


= Version 3.3.1 =

* Move manual credit card, Google Checkout, and PayPal Pro modules out of main YAK codebase.
* Bug fix for amount updates in the Order screen
* Change AJAX button so that it displays "Added" once the product is added to the cart


= Version 3.3.0 =

* Move authorize.net and stripe add-on modules out of main YAK codebase.
* Move order tracker add-on module into YAK codebase


= Version 3.2.8 =

* Add support for [Stripe](https://www.stripe.com) payments
* Fix bug in final order check process where the amount isn't validated correctly against the paid funds


= Version 3.2.7 =

* Another attempt at fixing a bug with renaming the base product category


= Version 3.2.6 =

* Fix for Mexico currency formatting issue


= Version 3.2.5 =

* Another fix for paypal IPN validation


= Version 3.2.4 =

* Tidy up html select generation code
* Remove old configuration options for currency code, money format, etc, and consolidated into a single currency selector. NOTE: when upgrading you will need to check the selected currency on the "Products Price/Qty" tab in YAK's General Options screen.
* Fix minor problem with PayPal IPN requests not validating correctly
* Fix minor issue with tab selection on admin screens


= Version 3.2.3 =

* Fix a problem with USE_SSL setting and css/js resources not loading correctly.


= Version 3.2.2 =

* Fix for broken yak_quantity tag when default category is renamed
* Rename yak-module-* to yak-ext-* to try to fix naming issue on WordPress Extend project page. NOTE: please check which of the YAK add-on modules you have enabled. There will be errors after upgrading caused by this rename, so you'll have to reactivate the modules which have been deactivated.


= Version 3.2.1 =

* Add remove text to cart heading
* Bug fix for multi-select items clearing the cart
* Move text-domain to initialisation event (so it works correctly with plugins such as qtranslate)
* Change permalink handling to support qtranslate style plugins
* Add order detail to "yak-mail" filter (API change)
* Tidy up email send functionality


= Version 3.2.0 =

* Change view orders search, to allow searching for all types of orders (rather than a specific order type)
* Minor change to support functionality in Order Tracker module


= Version 3.1.9 =

* Add functionality for promos tied to specific products.
* Fix minor problem with deleting promotions


= Version 3.1.8 =

* Minor change to install to fix possible upgrade issue on multi-site
* Bug fix for auth.net CVV failure issue
* Minor enhancement to re-use shipping hidden input values


= Version 3.1.7 =

* Update to credit card validation
* Minor tidy up to installation code
* Fix [yak\_description] tag to fix ID handling
* Fix order widget link when SSL enabled


= Version 3.1.6 =

* Change order screen filters (single input box instead of many)
* Add facility to search by product 'tag'
* Make zipcode/postcode configurable (switch on and off from the Basic shipping options screen) 
* Add new tag [shipping\_cost]
* Fix bug with email address (i.e. this format now works again: "Joe Bloggs <joe@bloggs.com>")


= Version 3.1.5 =

* Add [order_num] tag to subject line of confirmation email
* Add Indonesian translation file
* Change display of product-specific meta data


= Version 3.1.4 =

* Fix for slashes problem in DL email
* Add support for x-sendfile (for products with large download files)
* Fix problem with backslashes in title and description fields
* Add new API call: yak-mail


= Version 3.1.3 =

* Fixing minor email issue in Resend-DL
* Output list of emails in Resend-DL
* Fix DL email subject


= Version 3.1.2 =

* Re-release : multi module versions appear to affect main version


= Version 3.1.1 =

* Add subject line for resend-dl email


= Version 3.1.0 =

* Split out code into separate modules to reduce memory footprint
* Add AJAX buy button
* New version of order download to fix issues on some installations
* Change Google Checkout to use HTML API rather than XML (weird errors with XML version)
* Add resend-download facility, to resend download links when a product has been updated


= Version 3.0.3 =

* Another attempt at address bug fix.


= Version 3.0.2=

* Fix for address bug - wrong customer address appearing in checkout


= Version 3.0.1 =

* Add filter to order screen for customer name or email address
* Add filter to order screen for payment type
* Fix Settings link on Plugins page
* Finish off menu reorg


= Version 3.0.0 =

* Another attempted fix at address entry bug
* Tidy up utils include
* Add user-id to yak-order
* Functionality to use stored address for logged in customers
* Reorganise menu structure


= Version 2.5.8 =

* Facility to hide quantity input on cart
* Possible fix for bug in address entry
* Fix spurious error when no payment options are setup


= Version 2.5.7 =

* Change buy validation event handling to be more consistent (hopefully fixes an incompatibility issue when the add-info module is enabled)


= Version 2.5.6 =

* Remove jquery from ui.js (causing issues in IE)
* Fix minor problem with default price not appearing in dropdowns


= Version 2.5.5 =

* Fix problem with cancelling orders
* Fix minor error when payment types aren't setup correctly
* Add billing address tags to confirmation mail


= Version 2.5.4 =

* Fix error on payment-shipping-pairs options screen


= Version 2.5.3 =

* add cancel short tag (yak_cancelorder)


= Version 2.5.2 =

* Fix for billing address submitted to PayPal, rather than shipping address


= Version 2.5.1 =

* Fix for problem with cart items being dropped
* New API call for promo usage
* Remove non-existent CURL opt
* Remove YAK_DEBUG setting (left on unintentionally)
* Update context help on settings screen for conf email


= Version 2.5.0 =

* Code tidy up to use consistent yak_get_blogurl function
* Change payments to use WP action api
* Change modules to use WP action/filter api


= Version 2.4.2 =

* Remove error_log calls, causing problems on some installs


= Version 2.4.1 =

* Minor change to promotions, making price thresholds greater-than-equal-to threshold rather than just greater than
* Add menu links for translation files
* Fix a problem with PayPal PDT tags
* Fix quantity problem reported by Enrico
* Split out admin functions into separate include


= Version 2.4.0 =

* Fix (I hope) intermittent bug with shipping calculation
* Update to Thai translations (change default charset for Thai trans to TIS-620)


= Version 2.3.9 =

* Minor bug with zero override price
* Change 'Title' to 'Item' on confirmation mail (missed that with the change in 2.3.3)
* Add missing translation text to base yak-XX files.
* Update to Thai translations


= Version 2.3.8 =

* Add maintenance mode to disable buy buttons
* Minor order log change for PayPal Pro


= Version 2.3.7 =

* Add search-by-product to the orders screen (either product/post id or by the title, will return a list of the orders containing that product)
* Minor change to display of item meta on shopping cart (only relevant to add-info module currently)
* Add javascript events for add-ons
* Add refunded status to orders (for returns/refunds)
* Add updated French translation, provided by Mark Tiepe.


= Version 2.3.6 =

* Facility to set the number of columns in the multi-select table
* Add API call (yak-buy-validate) for any addons which need to validate the buy button 'click'
* Add API call (yak-buy-item) for any addons which need to intercept the buy button click
* Slight reorganisation of PayPal code to move some functions out of the main file

= Version 2.3.5 =

* Fix problem with require-login
* Split short-tags out into separate script
* Integrate Enrico's shipping->payment matching functionality.
* Provide facility for groups of multi-select options (with titles between)
* Add security roles for YAK pages


= Version 2.3.4 =

* Fix shipping discount (shouldn't be greater than actual shipping)
* Add coupon code based promotions
* Fix security loophole with PayPal payments - check currency code against currency provided by PayPal (thanks to Rich Pedley for the notification)
* Fix problem with hitting <enter> in promo code field on first checkout page


= Version 2.3.3 =

* Minor change to support more flexibility in buy-buttons (only needed if you're using the Gallery, currently)
* Change 'Title' to 'Item' on the checkout


= Version 2.3.2 =

* Update Czech translation, provided by Radek
* Fix for discounted price in product option dropdown


= Version 2.3.1 =

* Fix problem with multi-select options (email confirmation and order detail)


= Version 2.3.0 =

* Fix missing order num on the orders page.
* Add configurable manual credit card types.
* Add facility to display order widget even when empty.
* Turn off page buttons when the products-page has a single page.
* Get rid of ellipsis on credit cards in orders screen.


= Version 2.2.9 =

* Fix clear button, not working in sales tax settings
* Problem with sales tax calculation when no billing address


= Version 2.2.8 =

* Fix email address issue in confirmation
* Add missing i18n message
* Fix minor Google Checkout issue
* Add Slovenian translation, provided by Miha


= Version 2.2.7 =

* Minor change to button display to remove table layout - to make the button easier to style
* Add versioning to CSS/JS
* Minor update to product querying function
* Add db check for existing column
* Add module info on the Settings/About screen


= Version 2.2.6 =

* Fix null error in export
* Fix problem with downloadable product emails not correctly sent
* Fix problem with order num not appearing in email confirmations
* Add price="on|off" attribute to yak_buy_content tag.


= Version 2.2.5 =

* Add short description to product edit
* Fix problem with orders export


= Version 2.2.4 =

* Fix for address details not appearing properly in confirmation email
* Add _[special\_instructions]_ tag for emails


= Version 2.2.3 =

* Fix problem with displaying old address details (pre-2.2.2 addresses)


= Version 2.2.2 =

* Add email address validation (hook into WP's email validation function)
* Add shipping address to PayPal Pro (plus make it configurable)
* Refactor address handling into separate table
* Add company name to address details
* Changed cart page so that it 'remembers' the last selected payment type (for up to the configured cookie lifetime)
* Change promotions to use jquery date select, tidy up promotions entry


= Version 2.2.1 =

* Add uninstall facility (finally)


= Version 2.2.0 =

* NOTE: Testing against WordPress 3.x as of this release
* Updates to use JQuery
* Make suburb an option field in the address entry


= Version 2.1.7 =

* Fix problem with incorrectly calculated shipping after validation error (http://plugins.trac.wordpress.org/ticket/1123)
* Increase timeout on Authorize.net connections (occasionally seems to cause a problem)
* Fix problem with unlimited quantity
* Add shipping type to email flags (http://plugins.trac.wordpress.org/ticket/1114)
* Add check for the address_entry function existing (seems to cause an incompatibility with another plugin)


= Version 2.1.6 =

* Add back missing code to handle unlimited quantities


= Version 2.1.5 =

* Fix bug with confirmation email (missing sales tax, totals wrong)


= Version 2.1.4 =

* Fix processing problem with failed Authorize.net payments
* Rounding issue with sales tax values sent to PayPal


= Version 2.1.3 =

* Fix sales tax in PayPal Std checkouts


= Version 2.1.2 =

* Bug fix for sales tax via manual CC entry, along with removal of spurious <td> (suggested by Brandon Parker) (http://plugins.trac.wordpress.org/ticket/1111)
* Bug fix for sales tax via acc-recv entry (http://plugins.trac.wordpress.org/ticket/1112)


= Version 2.1.1 =

* Second attempt at fixing the Settings link on the Plugins page
* Add public key encryption for storage of CC details


= Version 2.1.0-beta =

* Add facility to increment item quantity (rather than displaying an error message) when the customer clicks on the buy button for an items which is already in the cart.
* Add more values to third party integration event -- and change to map-based array.
* Fix problem with calculating price for promos (in some environments)
* Add fix for draggable product-edit form provided by Brett
* Add facility to require login in order to purchase a product (new checkbox on the product edit tab)
* Fix Settings link on the Plugins page (thanks Omar) 
* Change SQL executions to use $wpdb->prepare for better security
* Initial version of sales tax calculation


= Version 2.0.8 =

* Fix issue with PayPal confirmation.


= Version 2.0.7 =

* Fix bug with shipping promotions (paypal)
* Add pointer to CSS for buttons
* Fix potential div-by-zero issue when calculating promo value
* Adding shipping country to 3rd party integration


= Version 2.0.6 =

* Fix bug in Accounts Receivable payments module


= Version 2.0.5 =

* Add notify_url param (for IPN) back to PayPal call (so you can use more than one shop with the single PayPal account)


= Version 2.0.4 =

* Attempt to fix a possible rounding issue in PayPal Pro.


= Version 2.0.3 =

* Fix bug with SSL during shopping card processing
* Fix bug with PayPal Pro (live) url


= Version 2.0.2 =

* Update Italian translation, provided by Rishi Giovanni Gatti


= Version 2.0.1 =

* Fix minor javascript error in buy button


= Version 2.0 =

* Change code to use WordPress's short tags.  This means the old parameter style (e.g. [yak\_price type id]) is replaced by new style [yak\_price type="" id=""].  For example, [yak\_price small 23] becomes [yak\_price type="small" id="23"].
* Rename [error\_message] tag to [yak\_error\_message]
* Add [yak\_sku] tag.  This takes the id and type parameters (same as yak\_size).  For example, [yak\_sku type="small"]
* Add facility for loading custom modules
* Add "Proceed to checkout" link to yak order widget
* Add support for multiple shipping options
* Add test facility for the confirmation message
* Add basic low stock notification
* Change "Buy" button on initial cart page to "Checkout"
* Add option to include price on Buy Button drop-down (i.e. multi options)
* Change ID on the buy button so it's unique
* Add "PAYMENT\_PROCESSED" option to the orders screen -- for orders which have been (manually) paid (either manual credit card or deposit/cheque, for example) but not yet shipped.
* Add "DELETE" option to the orders screen -- only allows orders which are CANCELLED or in ERROR to be deleted.


= Version 1.8.7 =

* Add unique url (stage=[xxxxx]) for each step in the checkout -- useful for various analytics packages.
* Fix a problem with setting quantity and other data when first creating a *page* product.
* Add facility for presenting "Terms & Conditions" text to a customer, which they have to tick before finally confirming the order.


= Version 1.8.6 =

* Update rounding to fix euro currency problem (patch provided by Enrico Battocchi)
* Fix issue with discounted values sent to PayPal - note there is currently a rounding issue with these discounted values


= Version 1.8.5 =

* Add facility for manual credit card payments to immediately send email confirmation/notification, or wait until the CC has actually been processed.


= Version 1.8.4 =

* Fix problem with manual credit card processing not sending confirmation email


= Version 1.8.3 =

* Updates to Thai translation
* Add multi-type selections to confirmation email


= Version 1.8.2 =

* Possible fix for a problem with PayPal Std and 0-value orders (which shouldn't get submitted)
* Fix minor bug with promo function usage in order confirmation


= Version 1.8.1 =

* Update language files
* Add TIS-620 version of Thai language file
* Fix a problem with HTML email received by Gmail.


= Version 1.8.0 =

* Add discount override, to allow for a discount on a per-product basis (useful to get rid of old stock, for example)
* Add option-selection to products.  This differs from the categorisation-method of specifying different types of a product, and allows you to setup a selectable range of options (multi-select).  Thus you might have a mix-and-match product, where a customer can select 3 out of 10 options, and so on.
* Split installation code into separate file
* Fix problem with exclude-pages
* Add more logging to product update
* Add threshold based promotions -- promotions which are triggered by the value of the order, rather than by a promotion code/voucher.


= Version 1.7.7 =

* Hopefully fix a problem with glob (on some PHP installs)
* Move order widget into separate file
* Change "Espana" into "Spain"
* Fix problem with country missing from shipping address in confirmation email and in order screen
* Add a new tag [phone] to confirmation email
* Fix a problem with address in order export
* Add message to Credit Card entry notifying the customer that they have a final chance to confirm/cancel the order
* Updated German translation, provided by Joern


= Version 1.7.6 =

* Fix problem with data export in Safari
* Add address to order export
* Update to Japanese lang files, provided by Soichi
* Fix for PayPal PDT
* Add missing internationalised text in Order Widget
* Update Swedish trans, provided by Marco


= Version 1.7.5 =

* Fix incompatibility problem with Contact Form 7.
* Fix a problem with Google Analytics tag ([yak_google_analytics]).


= Version 1.7.4 =

* Fix a problem with Wordpress MU not saving options correctly.  Split options out into multiple values, rather than a single array of options.
* Add test for downloadable products
* Add a test to initialise paypal sandbox (for quicker testing)
* Fix problem with widget not clearing order details after successful purchase


= Version 1.7.3 =

* Fix problem with paypal ipn


= Version 1.7.2 =

* Fix problem with Accounts Receivable payment
* Fix problem with Credit Card payment
* Reduce memory usage
* Add instant update of pricing promotions in the first page of the cart (after hitting update)

= Version 1.7.1 =

* Problem with Authorize.net url
* Separate PayPal Pro return url
* Finish moving code into separated payment classes
* Minor change to ipaddress sent to PayPal (only affects internal testing)

= Version 1.7.0 =

* Refactor payment types into separate classes to allow for easier extensibility
* Add 'demo' payment gateway
* Fix promo date saving problem
* Fix promo price calculation (causing problems in PayPal)
* Automatically create the Checkout page on activation
* Automatically create the "products" and "default" categories on activation
* Fix (hopefully) odd error\_log problem in certain environments


= Version 1.6.1 =

* Change third party integration script from yak\_third\_party.php to yak-third-party.php.
* Add total cost (without shipping) to interface for third party func.  The function signature is now: yak\_check\_order\_3p($order\_id, $email, $recipient, $total\_cost).  Also added automated test.
* Plugin links were being added to every plugin row -- fixed so they're only added to the Yak row.


= Version 1.6.0 =

* Remove auto\_set\_quantity -- doesn't make sense since you can now perform all product setup from the edit post page. 
* Remove link back to project page.
* Add custom "Out of Stock" message
* Changes required so that YAK will work with WordPress MU
* Move language files into "lang" subdirectory


= Version 1.5.2 =

* Remove help from YAK settings page.  Moved to the WordPress Extend page: http://wordpress.org/extend/plugins/yak-for-wordpress/installation/
* Add links to the plugin details on the Plugins page
* Fix bug in orders screen (hitting update wasn't requerying)
* Set the priority of YAK's post processing (can be changed by modifying the DEFINE in yak-static.php)


= Version 1.5.1 =

* Fix bug in sales report screen


= Version 1.5.0 =

* Fix shipping address in confirmation email, so that email and phone aren't included
* Add translation hooks for admin interface.  There are now two base translation files: yak-XX.po and yak-admin-XX.po. Most translators will probably only want to translate the customer interface (yak).  Those who want to translate the entire interface can also translate the admin interface as well (yak-admin).
* Fix confirmation email for credit card orders and accounts receivable
* Change accounts receivable button to "Next" rather than "Confirm", since it isn't the last page in the flow.
* Fix translation bug in address screen
* Add placeholder for third party integration (see changelog, or the handbook, for more info)
* Change the order screen so that it doesn't display orders without clicking the query button


= Version 1.4.6 =

* Add override for the shipping weight calculator value -- you can specify the value that is used for the first X grams in shipping calculation, and then the subsequent X grams.
   

= Version 1.4.5.1 =

* Missed a Git collision marker in yak-settings.php


= Version 1.4.5 =

* Localisation fix for the product page (patch provided by DjZoNe)
* Fix a minor problem with the tests
* Add facility to set session.cache_limiter to private for specific pages (such as checkout). This stops the "webpage has expired" messages in Internet Explorer. 
* Add promotion code access (specify a comma-separated list of user[name]s who are allowed to access a promotion)


= Version 1.4.4 =

* [html\_shipping\_address] no longer includes email address
* billing address is now passed to PayPal Standard (shipping address was being sent through before)
* add Portuguese translation provided by Álvaro


= Version 1.4.3 =

* wrong order for product type columns


== Upgrade Notice ==

= 3.4.8 =
Add support for comma-separated confirmation addresses.

= 3.4.7 =
Minor bug fixes.

= 3.4.6 =
Bug fix for truncation issue in order meta.

= 3.4.5 =
Adds include/exclude functionality for product-specific promotions.