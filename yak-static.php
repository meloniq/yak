<?php
/*
See yak-for-wordpress.php for information and license terms
*/
global $wpdb;

global $order_table, $order_detail_table, $order_meta_table, $order_log_table, $order_dl_table, $product_table,
    $product_detail_table, $order_detail_index, $order_num_index, $coupon_table, $promo_table, $promo_index, $promo_users_table,
    $address_table;

$order_table = $wpdb->prefix . "yak_order";
$order_detail_table = $wpdb->prefix . "yak_order_detail";
$order_meta_table = $wpdb->prefix . "yak_order_meta";
$order_log_table = $wpdb->prefix . "yak_order_log";
$order_dl_table = $wpdb->prefix . "yak_order_dl";
$product_table = $wpdb->prefix . "yak_product";
$product_detail_table = $wpdb->prefix . "yak_product_detail";
$order_detail_index = $wpdb->prefix . "yak_order_detail_idx";
$order_meta_index = $wpdb->prefix . "yak_order_meta_idx";
$order_num_index = $wpdb->prefix . "yak_order_num_idx";
$coupon_table = $wpdb->prefix . "yak_coupon";
$promo_table = $wpdb->prefix . "yak_promotions";
$promo_index = $wpdb->prefix . "yak_promotions_idx";
$promo_users_table = $wpdb->prefix . "yak_promotion_users";
$address_table = $wpdb->prefix . "yak_address";

/**
 * CONSTANTS
 */
define("YAK_PRIORITY", 20);
define("YAK_ROOT_DIR", ABSPATH . 'wp-content/plugins/yak-for-wordpress/');
 
define("MAINTENANCE_MODE", "yak_maintenance_mode");
define("YAK_VERSION", "yak_version");
define("QUANTITY_INPUT_SIZE", "yak_quantity_input_size");
define("INCLUDE_SHIPPING_COSTS", "yak_include_shipping_costs");
define("HIDE_QUANTITY", "yak_hide_quantity");
define("SHOW_OUT_OF_STOCK_MSG", "yak_show_out_of_stock");
define("CUSTOM_OUT_OF_STOCK_MSG", "yak_custom_out_of_stock");
define("CONFIRMATION_EMAIL_ADDRESS", "yak_confirmation_email");
define("CONFIRMATION_SUBJECT", "yak_confirmation_subject");
define("CONFIRMATION_MESSAGE", "yak_confirmation_message");
define("TEST_CONFIRMATION_EMAIL", "yak_test_confirmation_message");
define("COOKIE_LIFETIME", "yak_cookie_lifetime");
define("REDIRECT_ON_BUY_TO", "yak_redirect_on_buy");
define("AUTO_DISCOUNT", "yak_auto_discount");
define("PRICE_ROUNDING", "yak_price_rounding");
define("ORDER_NUMBER_TYPE", "yak_order_number_type");
define("PAYMENT_TYPES", "yak_payment_types");
define("PAYMENT_TYPES_CASE_INSENSITIVE", "yak_payment_types_ci");
define("PAYMENT_TYPES_SET", "yak_payment_types_set");
define("PAYMENT_SHIPPING_PAIRS","yak_payment_shipping_pairs");
define("PAYMENT_SHIPPING_PAIRS_CASE_INSENSITIVE","yak_payment_shipping_pairs_ci");
define("PROMO_SET", "yak_promo_set");
define("DEFAULT_COUNTRY", "yak_default_country");
define("DEFAULT_SHIPPING", "yak_default_shipping");
define("DEFAULT_SHIPPING_FIXED_ITEM_FIRST", "yak_default_shipping_fixed_item_first");
define("DEFAULT_SHIPPING_FIXED_ITEM", "yak_default_shipping_fixed_item");
define("DEFAULT_SHIPPING_WEIGHT_FIRST", "yak_default_shipping_weight_first");
define("DEFAULT_SHIPPING_WEIGHT", "yak_default_shipping_weight");
define("SHIPPING_NOTES", "yak_shipping_notes");
define("SELECTED_SHIPPING_OPTION", "yak_selected_shipping_option");
define("SHIPPING_OPTIONS", "yak_shipping_options");
define("SHIPPING_OPTION_NAMES", "yak_shipping_option_names");
define("DISPLAY_PRODUCT_OPTIONS", "yak_display_product_options");
define("PRODUCT_CATEGORY_NAME", "yak_product_category_name");
define("PRODUCT_PAGE_SIZE", "yak_product_page_size");
define("USE_SSL", "yak_use_ssl");
define("PAYMENT_PAGES", "yak_payment_pages");
define("DEFAULT_SPECIAL_INSTRUCTIONS", "yak_default_special_instructions");
define("TERMS_AND_CONDITIONS", "yak_terms_and_conditions");
define("EMPTY_BASKET_MESSAGE", "yak_empty_basket_message");
define("OPTIONS_DROPDOWN_INCLUDE_PRICE", "yak_options_dropdown_price");
define("REQUIRE_LOGIN", "yak_require_login");
define("DUPLICATE_HANDLING", "yak_duplicate_handling");
define("AJAX_BUY_BUTTON", "yak_ajax_buy_button");
define("CUSTOM_PRICE", "yak_custom_price");
define("HIDE_UPDATE_BUTTON", "yak_hide_update_button");
define("QUANTITY_INPUT_BUY_BUTTON", "yak_quantity_input_buy_button");
define("OVERRIDE_QUANTITY", "yak_override_quantity");

define("SHIPPING_IS_BILLING", "shipping_is_billing");
define("SHIPPING_COUNTRY", "shipping_country");
define("SHIPPING_STATE", "shipping_state");
define("BILLING_COUNTRY", "billing_country");
define("BILLING_STATE", "billing_state");

define("PAYMENT_TYPE", "payment_type");

define("ADDRESS_COOKIE_SUFFIX", "_address4");

define("LOW_STOCK_THRESHOLD", "yak_low_stock_threshold");
define("LOW_STOCK_EMAIL", "yak_low_stock_email");

define("ITEMS_NAME", rawurlencode(get_bloginfo('blogname')) . '-items');

define("DOWNLOAD_EMAIL", "yak_download_email");
define("DOWNLOAD_EMAIL_ADDRESS", "yak_download_email_address");
define("DOWNLOAD_URI", "yak_download_uri");
define("DOWNLOAD_FILE", "yak_download_file");
define("DOWNLOAD_USE_XSENDFILE", "yak_download_use_xsendfile");

define("CREDIT_CARD", "Credit Card");

define("SPECIAL_OPTIONS_TEXT", "yak_special_options_text");
define("NO_CACHE_PAGES", "yak_no_caching");

define("HTTP_PROXY_URL", "yak_http_proxy_url");

define("ADDRESS_NAME", "yak_address_name");
define("ADDRESS_COMPANY_NAME", "yak_address_cpy_name");
define("ADDRESS_PHONE", "yak_address_phone");
define("ADDRESS_SUBURB", "yak_address_suburb");
define("ADDRESS_POSTCODE", "yak_address_postcode");
define("ADDRESS", "yak_address_info");
define("ADDRESS_SEPARATE_BILLING", "yak_address_billing");
define("SHIPPING_WEIGHT_CALC", "yak_shipping_weight_calc");
define("DEFAULT_SHIPPING_WEIGHT_CALC", 100);
define("ENABLED_COUNTRIES", "yak_enabled_countries");
define("CATEGORIES", "yak_categories");
define("PAGES", "yak_pages");
define("PROMOTIONS", "yak_promotions");
define("REMOTE_GRAB_SERVER", "yak_remote_grab_server");
define("REMOTE_GRAB_PATH", "yak_remote_grab_path");
define("BLACKLIST", "yak_blacklist");
define("BLACKLISTED_COOKIE", "yak_bl");

define("GENERATED", "Generated");
define("SEQUENCE", "Sequence");

define("SALES_TAX_PRODUCT_TYPE", "sales-tax");

/**
 * order statuses
 */
define("STOCK_SENT", "STOCK SENT");
define("CANCELLED", "CANCELLED");
define("ERROR", "ERROR");
define("PAYMENT_PROCESSED", "PAYMENT PROCESSED");
define("REFUNDED", "REFUNDED");

global $order_statuses;
$order_statuses = array('', STOCK_SENT, CANCELLED, ERROR, PAYMENT_PROCESSED, REFUNDED);

global $credit_card_payments;
$credit_card_payments = array(
    CREDIT_CARD, AUTHORIZE_NET, AUTHORIZE_NET_TEST, PAYPAL_PRO_SANDBOX, PAYPAL_PRO_LIVE, DEMO_PAYMENT
);

/**
 * order number type
 */
global $order_number_types;
$order_number_types = array();
$order_number_types[GENERATED] = GENERATED;
$order_number_types[SEQUENCE] = SEQUENCE;

 
/**
 * required address fields
 */
global $required_address_fields;
$required_address_fields = array();
$required_address_fields['email'] = __('an email address is required', 'yak');
if (yak_get_option(ADDRESS_NAME, '') == 'on') {
    $required_address_fields['recipient'] = __('a recipient name is required', 'yak');
}
if (yak_get_option(ADDRESS_PHONE, '') == 'on') {
    $required_address_fields['phone'] = __('phone is required', 'yak');    
}
if (yak_get_option(ADDRESS_POSTCODE, '') == 'on') {
    $required_address_fields['postcode'] = __('postcode is required', 'yak');
}
if (yak_get_option(ADDRESS, '') == 'on') {
    $required_address_fields['addr1'] = __('address line 1 is required', 'yak');
    $required_address_fields['city'] = __('city is required', 'yak');
}

/**
 * countries list
 */
global $countries;
$countries = array(); 
$countries["AF"] = "Afghanistan";
$countries["AL"] = "Albania";
$countries["DZ"] = "Algeria";
$countries["AS"] = "American Samoa";
$countries["AD"] = "Andorra";
$countries["AI"] = "Anguilla";
$countries["AQ"] = "Antarctica";
$countries["AG"] = "Antigua And Barbuda";
$countries["AR"] = "Argentina";
$countries["AM"] = "Armenia";
$countries["AW"] = "Aruba";
$countries["AU"] = "Australia";
$countries["AT"] = "Austria";
$countries["AZ"] = "Azerbaijan";
$countries["BS"] = "Bahamas";
$countries["BH"] = "Bahrain";
$countries["BD"] = "Bangladesh";
$countries["BB"] = "Barbados";
$countries["BY"] = "Belarus";
$countries["BE"] = "Belgium";
$countries["BZ"] = "Belize";
$countries["BJ"] = "Benin";
$countries["BM"] = "Bermuda";
$countries["BT"] = "Bhutan";
$countries["BO"] = "Bolivia";
$countries["BA"] = "Bosnia and Herzegovina";
$countries["BW"] = "Botswana";
$countries["BV"] = "Bouvet Island";
$countries["BR"] = "Brazil";
$countries["IO"] = "British Indian Ocean Territory";
$countries["BN"] = "Brunei Darussalam";
$countries["BG"] = "Bulgaria";
$countries["BF"] = "Burkina Faso";
$countries["BI"] = "Burundi";
$countries["KH"] = "Cambodia";
$countries["CM"] = "Cameroon";
$countries["CA"] = "Canada";
$countries["CV"] = "Cape Verde";
$countries["KY"] = "Cayman Islands";
$countries["CF"] = "Central African Republic";
$countries["TD"] = "Chad";
$countries["CL"] = "Chile";
$countries["CN"] = "China";
$countries["CX"] = "Christmas Island";
$countries["CC"] = "Cocos (Keeling) Islands";
$countries["CO"] = "Colombia";
$countries["KM"] = "Comoros";
$countries["CG"] = "Congo, the Democratic Republic of the";
$countries["CK"] = "Cook Islands";
$countries["CR"] = "Costa Rica";
$countries["HR"] = "Croatia";
$countries["CY"] = "Cyprus";
$countries["CZ"] = "Czech Republic";
$countries["DK"] = "Denmark";
$countries["DJ"] = "Djibouti";
$countries["DM"] = "Dominica";
$countries["DO"] = "Dominican Republic";
$countries["EC"] = "Ecuador";
$countries["EG"] = "Egypt";
$countries["SV"] = "El Salvador";
$countries["GQ"] = "Equatorial Guinea";
$countries["ER"] = "Eritrea";
$countries["EE"] = "Estonia";
$countries["ET"] = "Ethiopia";
$countries["FK"] = "Falkland Islands";
$countries["FO"] = "Faroe Islands";
$countries["FJ"] = "Fiji";
$countries["FI"] = "Finland";
$countries["FR"] = "France";
$countries["GF"] = "French Guiana";
$countries["PF"] = "French Polynesia";
$countries["TF"] = "French Southern Territories";
$countries["GA"] = "Gabon";
$countries["GM"] = "Gambia";
$countries["GE"] = "Georgia";
$countries["DE"] = "Germany";
$countries["GH"] = "Ghana";
$countries["GI"] = "Gibraltar";
$countries["GR"] = "Greece";
$countries["GL"] = "Greenland";
$countries["GD"] = "Grenada";
$countries["GP"] = "Guadeloupe";
$countries["GU"] = "Guam";
$countries["GT"] = "Guatemala";
$countries["GN"] = "Guinea";
$countries["GW"] = "Guinea-Bissau";
$countries["GY"] = "Guyana";
$countries["HT"] = "Haiti";
$countries["HM"] = "Heard and Mc Donald Islands";
$countries["HN"] = "Honduras";
$countries["HK"] = "Hong Kong";
$countries["HU"] = "Hungary";
$countries["IS"] = "Iceland";
$countries["IN"] = "India";
$countries["ID"] = "Indonesia";
$countries["IE"] = "Ireland";
$countries["IL"] = "Israel";
$countries["IT"] = "Italy";
$countries["JM"] = "Jamaica";
$countries["JP"] = "Japan";
$countries["JO"] = "Jordan";
$countries["KZ"] = "Kazakhstan";
$countries["KE"] = "Kenya";
$countries["KI"] = "Kiribati";
$countries["KP"] = "Korea, Democratic People's Republic of";
$countries["KR"] = "Korea, Republic of";
$countries["KW"] = "Kuwait";
$countries["KG"] = "Kyrgyzstan";
$countries["LA"] = "Lao People's Democratic Republic";
$countries["LV"] = "Latvia";
$countries["LB"] = "Lebanon";
$countries["LS"] = "Lesotho";
$countries["LR"] = "Liberia";
$countries["LY"] = "Libya";
$countries["LI"] = "Liechtenstein";
$countries["LT"] = "Lithuania";
$countries["LU"] = "Luxembourg";
$countries["MO"] = "Macao";
$countries["MK"] = "Macedonia";
$countries["MG"] = "Madagascar";
$countries["MW"] = "Malawi";
$countries["MY"] = "Malaysia";
$countries["MV"] = "Maldives";
$countries["ML"] = "Mali";
$countries["MT"] = "Malta";
$countries["MH"] = "Marshall Islands";
$countries["MQ"] = "Martinique";
$countries["MR"] = "Mauritania";
$countries["MU"] = "Mauritius";
$countries["YT"] = "Mayotte";
$countries["MX"] = "Mexico";
$countries["FM"] = "Micronesia, Federated States of";
$countries["MD"] = "Moldova, Republic of";
$countries["MC"] = "Monaco";
$countries["MN"] = "Mongolia";
$countries["MS"] = "Montserrat";
$countries["MA"] = "Morocco";
$countries["MZ"] = "Mozambique";
$countries["MM"] = "Myanmar";
$countries["NA"] = "Namibia";
$countries["NR"] = "Nauru";
$countries["NP"] = "Nepal";
$countries["NL"] = "Netherlands";
$countries["AN"] = "Netherlands Antilles";
$countries["NC"] = "New Caledonia";
$countries["NZ"] = "New Zealand";
$countries["NI"] = "Nicaragua";
$countries["NE"] = "Niger";
$countries["NG"] = "Nigeria";
$countries["NU"] = "Niue";
$countries["NF"] = "Norfolk Island";
$countries["MP"] = "Northern Mariana Islands";
$countries["NO"] = "Norway";
$countries["OM"] = "Oman";
$countries["PK"] = "Pakistan";
$countries["PW"] = "Palau";
$countries["PA"] = "Panama";
$countries["PG"] = "Papua New Guinea";
$countries["PY"] = "Paraguay";
$countries["PE"] = "Peru";
$countries["PH"] = "Philippines";
$countries["PN"] = "Pitcairn";
$countries["PL"] = "Poland";
$countries["PT"] = "Portugal";
$countries["PR"] = "Puerto Rico";
$countries["QA"] = "Qatar";
$countries["RE"] = "Reunion";
$countries["RO"] = "Romania";
$countries["RU"] = "Russian Federation";
$countries["RW"] = "Rwanda";
$countries["KN"] = "Saint Kitts and Nevis";
$countries["LC"] = "Saint Lucia";
$countries["VC"] = "Saint Vincent and the Grenadines";
$countries["WS"] = "Samoa (Independent)";
$countries["SM"] = "San Marino";
$countries["ST"] = "Sao Tome and Principe";
$countries["SA"] = "Saudi Arabia";
$countries["SN"] = "Senegal";
$countries["CS"] = "Serbia and Montenegro";
$countries["SC"] = "Seychelles";
$countries["SL"] = "Sierra Leone";
$countries["SG"] = "Singapore";
$countries["SK"] = "Slovakia";
$countries["SI"] = "Slovenia";
$countries["SB"] = "Solomon Islands";
$countries["SO"] = "Somalia";
$countries["ZA"] = "South Africa";
$countries["GS"] = "South Georgia and the South Sandwich Islands";
$countries["ES"] = "Spain";
$countries["LK"] = "Sri Lanka";
$countries["SH"] = "St. Helena";
$countries["PM"] = "St. Pierre and Miquelon";
$countries["SR"] = "Suriname";
$countries["SJ"] = "Svalbard and Jan Mayen Islands";
$countries["SZ"] = "Swaziland";
$countries["SE"] = "Sweden";
$countries["CH"] = "Switzerland";
$countries["TW"] = "Taiwan";
$countries["TJ"] = "Tajikistan";
$countries["TZ"] = "Tanzania";
$countries["TH"] = "Thailand";
$countries["TG"] = "Togo";
$countries["TK"] = "Tokelau";
$countries["TO"] = "Tonga";
$countries["TT"] = "Trinidad and Tobago";
$countries["TN"] = "Tunisia";
$countries["TR"] = "Turkey";
$countries["TM"] = "Turkmenistan";
$countries["TC"] = "Turks and Caicos Islands";
$countries["TV"] = "Tuvalu";
$countries["UG"] = "Uganda";
$countries["UA"] = "Ukraine";
$countries["AE"] = "United Arab Emirates";
$countries["GB"] = "United Kingdom";
$countries["US"] = "United States";
$countries["UM"] = "United States Minor Outlying Islands";
$countries["UY"] = "Uruguay";
$countries["UZ"] = "Uzbekistan";
$countries["VU"] = "Vanuatu";
$countries["VA"] = "Vatican City State (Holy See)";
$countries["VE"] = "Venezuela";
$countries["VN"] = "Viet Nam";
$countries["VG"] = "Virgin Islands (British)";
$countries["VI"] = "Virgin Islands (U.S.)";
$countries["WF"] = "Wallis and Futuna Islands";
$countries["EH"] = "Western Sahara";
$countries["YE"] = "Yemen";
$countries["ZM"] = "Zambia";
$countries["ZW"] = "Zimbabwe";

/**
 * US States List
 */
global $states;
$states = array();
$states["AL"] = "Alabama";
$states["AK"] = "Alaska";
$states["AS"] = "American Samoa";
$states["AZ"] = "Arizona";
$states["AR"] = "Arkansas";
$states["CA"] = "California";
$states["CO"] = "Colorado";
$states["CT"] = "Connecticut";
$states["DE"] = "Delaware";
$states["DC"] = "District of Columbia";
$states["FM"] = "Federated States of Micronesia";
$states["FL"] = "Florida";
$states["GA"] = "Georgia";
$states["GU"] = "Guam";
$states["HI"] = "Hawaii";
$states["ID"] = "Idaho";
$states["IL"] = "Illinois";
$states["IN"] = "Indiana";
$states["IA"] = "Iowa";
$states["KS"] = "Kansas";
$states["KY"] = "Kentucky";
$states["LA"] = "Louisiana";
$states["ME"] = "Maine";
$states["MH"] = "Marshall Islands";
$states["MD"] = "Maryland";
$states["MA"] = "Massachusetts";
$states["MI"] = "Michigan";
$states["MN"] = "Minnesota";
$states["MS"] = "Mississippi";
$states["MO"] = "Missouri";
$states["MT"] = "Montana";
$states["NE"] = "Nebraska";
$states["NV"] = "Nevada";
$states["NH"] = "New Hampshire";
$states["NJ"] = "New Jersey";
$states["NM"] = "New Mexico";
$states["NY"] = "New York";
$states["NC"] = "North Carolina";
$states["ND"] = "North Dakota";
$states["MP"] = "Northern Mariana Islands";
$states["OH"] = "Ohio";
$states["OK"] = "Oklahoma";
$states["OR"] = "Oregon";
$states["PW"] = "Palau";
$states["PA"] = "Pennsylvania";
$states["PR"] = "Puerto Rico";
$states["RI"] = "Rhode Island";
$states["SC"] = "South Carolina";
$states["SD"] = "South Dakota";
$states["TN"] = "Tennessee";
$states["TX"] = "Texas";
$states["UT"] = "Utah";
$states["VT"] = "Vermont";
$states["VI"] = "Virgin Islands";
$states["VA"] = "Virginia";
$states["WA"] = "Washington";
$states["WV"] = "West Virginia";
$states["WI"] = "Wisconsin";
$states["WY"] = "Wyoming";
$states["AA"] = "Armed Forces Americas";
$states["AE"] = "Armed Forces";
$states["AP"] = "Armed Forces Pacific";

/**
 * Canada states list
 */
global $canada_states;
$canada_states = array();
$canada_states['AB'] = 'Alberta';
$canada_states['BC'] = 'British Columbia';
$canada_states['MB'] = 'Manitoba';
$canada_states['NB'] = 'New Brunswick';
$canada_states['NL'] = 'Newfoundland and Labrador';
$canada_states['NT'] = 'Northwest Territories';
$canada_states['NS'] = 'Nova Scotia';
$canada_states['NU'] = 'Nunavut';
$canada_states['ON'] = 'Ontario';
$canada_states['PE'] = 'Prince Edward Island';
$canada_states['QC'] = 'Quebec';
$canada_states['SK'] = 'Saskatchewan';
$canada_states['YT'] = 'Yukon';

/**
 * promotion types
 */
global $promo_types;
$promo_types = array();
$promo_types["shipping_perc"] = "Shipping %";
$promo_types["shipping_val"] = "Shipping Value";
$promo_types["pricing_perc"] = "Pricing %";
$promo_types["pricing_val"] = "Pricing Value";
$promo_types["shipping_perc_threshold"] = "Shipping % (Threshold)";
$promo_types["shipping_val_threshold"] = "Shipping Value (Threshold)";
$promo_types["pricing_perc_threshold"] = "Pricing % (Threshold)";
$promo_types["pricing_val_threshold"] = "Pricing Value (Threshold)";
$promo_types["shipping_perc_qty_threshold"] = "Shipping % (Qty Threshold)";
$promo_types["shipping_val_qty_threshold"] = "Shipping Value (Qty Threshold)";
$promo_types["pricing_perc_qty_threshold"] = "Pricing % (Qty Threshold)";
$promo_types["pricing_val_qty_threshold"] = "Pricing Value (Qty Threshold)";
$promo_types["coupon_codes_shipping_perc"] = "Coupon Codes Shipping %";
$promo_types["coupon_codes_shipping_val"] = "Coupon Codes Shipping Value";
$promo_types["coupon_codes_pricing_perc"] = "Coupon Codes Pricing %";
$promo_types["coupon_codes_pricing_val"] = "Coupon Codes Pricing Value";

/**
 * months
 */
global $months;
$months = array();
$months['00'] = ''; 
$months['01'] = 'January';
$months['02'] = 'February';
$months['03'] = 'March';
$months['04'] = 'April';
$months['05'] = 'May';
$months['06'] = 'June';
$months['07'] = 'July';
$months['08'] = 'August';
$months['09'] = 'September';
$months['10'] = 'October';
$months['11'] = 'November';
$months['12'] = 'December';

/**
 * Duplicate handling (when a customer tries to add an item which
 * is already in the cart).
 */
global $duplicate_handling;
$duplicate_handling = array();
$duplicate_handling['error'] = __("Display an error message", "yak-admin");
$duplicate_handling['increment'] = __("Increase the quantity", "yak-admin");


?>