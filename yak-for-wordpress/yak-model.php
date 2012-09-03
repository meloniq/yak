<?php
/*
See yak-for-wordpress.php for information and license terms
*/

/**
 * Data class describing items in an order.
 */
class YakItem {

    var $id_type;
    var $id;
    var $cat_id;
    var $quantity;
    var $name;
    var $item_weight;
    var $special_option;
    var $type;
    
    var $selected_options;
    
    var $price;
    var $discount;
    
    var $meta = array();

    /**
     * Constructor
     *
     * @param $id_type post or page type
     * @param $id the product id (i.e. post id or page id)
     * @param $cat_id category for the product type
     * @param $quantity amount to purchase
     */
    function YakItem($id_type, $id, $cat_id, $quantity, $type = null) {
        $this->id_type = $id_type;
        $this->id = $id;
        $this->cat_id = $cat_id;
        $this->quantity = $quantity;
        $this->type = $type;
    }
    
    function get_total_weight() {
        return $this->quantity * $this->item_weight;
    }
    
    function get_discount_total() {
        return $this->quantity * $this->get_discount_price();
    }
    
    function get_discount_price() {
        return $this->price - $this->discount;
    }
    
    function get_total() {
        return $this->quantity * $this->price;
    }
}


/**
 * Data class describing an order.
 */
class YakOrder {
    var $id;
    var $order_num;
    var $time;
    var $address;
    var $country_code;
    var $billing_address;
    var $billing_country_code;
    var $payment_type;
    var $selected_shipping_type;
    var $funds_received;
    var $shipping_cost;
    var $total;
    var $status;
    
    var $items;
    var $log;
    var $meta;
    
    var $shipping_addr = null;
    var $billing_addr = null;
    
    var $user_id;
    
    /**
     * Constructor
     *
     * @param $id the id of the order
     * @param $num the external order number (displayed to a customer)
     * @param $time the time of the order
     * @param $address the shipping address
     * @param $billing_address address to send the bill to
     * @param $payment_type method of paying (Credit Card, Deposit, Cheque, etc)
     * @param $funds_received payment received so far
     * @param $shipping_cost cost of shipping the order
     * @param $total total cost of the order
     * @param $status current status of the order
     */
    function YakOrder($id, $num, $time, $payment_type, $funds_received, $shipping_cost, $total, $status, $selected_shipping_type) {
        $this->id = $id;
        $this->order_num = $num;
        $this->time = $time;
        $this->payment_type = $payment_type;
        $this->selected_shipping_type = $selected_shipping_type;
        $this->funds_received = $funds_received;
        $this->shipping_cost = $shipping_cost;
        $this->total = $total;
        $this->status = $status;
    }
    
    function get_shipping_address() {
        if ($this->shipping_addr != null) {
            return $this->shipping_addr;
        }
        else {
            return $this->billing_addr;
        }
    }
    
    function get_billing_address() {
        if ($this->billing_addr != null) {
            return $this->billing_addr;
        }
        else {
            return $this->shipping_addr;
        }
    }
    
    function get_shipping_address_string() {
        global $countries;
        $rtn = '';
        if ($this->shipping_addr != null) {
            $rtn = $this->shipping_addr->as_string('country');
            
            if ($this->shipping_addr->country != null) {
                $rtn .= "\n" . $countries[$this->shipping_addr->country];
            }
        }
        else {
            $rtn = $this->address . "\n" . $countries[$this->country_code];
        }
        
        return $rtn;
    }
    
    function get_billing_address_string() {
        global $countries;
        $rtn = '';
        if ($this->billing_addr != null) {
            $rtn = $this->billing_addr->as_string('country');
            
            if ($this->billing_addr->country != null) {
                $rtn .= "\n" . $countries[$this->billing_addr->country];
            }
        }
        else {
            $rtn = $this->billing_address . "\n" . $countries[$this->billing_country_code];
        }
        
        return $rtn;
    }
}


/**
 * Class describing a product in YAK
 */
class YakProduct {
    
    var $post_title;
    var $id;
    var $ID;
    var $status;
    var $title;
    var $price;
    var $content;
    var $description;
    var $custom_price;
    
    var $discount_override;
    var $multi_select_options;
    var $multi_select_min;
    var $multi_select_max;
    var $multi_select_cols;
    
    var $types;
    var $num_types;
    
    /**
     * Constructor
     *
     * @param $id the id of the post for the product
     * @param $post_title the title of the post (hence the name of the product)
     * @param $status the status of the post/page
     * @param $title alternative title for the product
     * @param $price price of the product
     */
    function YakProduct($id, $post_title, $status, $title, $price) {
        $this->id = $id;
        $this->ID = $id;
        $this->post_title = $post_title;
        $this->status = $status;
        $this->title = $title;
        $this->price = $price;
        
        $this->types = array();
    }
    
    function get_multi_select_options() {
        return explode("\r\n", $this->multi_select_options);
    }
}


/**
 * Class describing a product type (category)
 */
class YakProductType {
    var $post_id;
    var $cat_id;
    var $name;
    var $sku;
    var $qty;
    var $override_price;
    var $discount_override;
    var $weight;
    var $dl_file;
    
    /** 
     * Constructor
     *
     * @param $post_id reference to the product id
     * @param $cat_id the id of the category for this type of product
     * @param $name the name of the category/product type
     * @param $qty the quantity available of this type
     * @param $weight the weight of this type
     * @param $dl_file the download file if this is a downloadable product
     */
    function YakProductType($post_id, $cat_id, $name, $sku, $qty, $override_price, $weight, $dl_file) {
        $this->post_id = $post_id;
        $this->cat_id = $cat_id;
        $this->name = $name;
        $this->sku = $sku;
        $this->qty = $qty;
        $this->override_price = $override_price;
        $this->weight = $weight;
        $this->dl_file = $dl_file;
    }
}


/**
 * Class representing a summary total (for the reports page)
 */
class YakTotal {
    var $desc;
    var $secondary_desc;
    var $total;
    var $secondary_total;
    
    function YakTotal($desc, $total, $secondary_total = 0, $secondary_desc = '') {
        $this->desc = $desc;
        $this->total = $total;
        $this->secondary_total = $secondary_total;
        $this->secondary_desc = $secondary_desc;
    }
}


/**
 * Class representing a promotion (temporary reduction is price or shipping)
 */
class YakPromotion {
    var $promo_id;
    var $code;
    var $promo_type;
    var $value;
    var $description;
    var $discount;
    var $users;
    var $products;
    var $products_inclusion;
    var $expiry_date;
    var $threshold;
    
    function YakPromotion($promo_id, $code, $promo_type, $threshold, $value, $description, $expiry_date, $users = null, $products = null) {
        $this->promo_id = $promo_id;
        $this->code = $code;
        $this->promo_type = $promo_type;
        $this->value = $value;
        $this->description = $description;
        $this->expiry_date = $expiry_date;
        $this->threshold = $threshold;
        $this->users = $users;
        $this->products = $products;
    }
    
    function get_users_string() {
        if (!empty($this->users)) {
            return implode(',', $this->users);
        }
        else {
            return '';
        }
    }
    
    function get_products_string() {
        if (!empty($this->products)) {
            return implode(',', $this->products);
        }
        else {
            return '';
        }
    }
}


/**
 * Class representing a shipping or billing address
 */
class YakAddress {
    
    var $id;
    var $email;
    var $recipient;
    var $company_name;
    var $phone;
    var $addr1;
    var $addr2;
    var $suburb;
    var $city;
    var $region;
    var $state;
    var $postcode;
    var $country;
    
    var $type;
    
    function YakAddress($email, $recipient, $company_name, $phone, $addr1, $addr2, $suburb, $city, $region, $state, $postcode, $country, $type) {
        $this->email = $email;
        $this->recipient = stripslashes($recipient);
        $this->company_name = stripslashes($company_name);
        $this->phone = $phone;
        $this->addr1 = stripslashes($addr1);
        $this->addr2 = stripslashes($addr2);
        $this->suburb = stripslashes($suburb);
        $this->city = stripslashes($city);
        $this->region = stripslashes($region);
        $this->state = $state;
        $this->postcode = $postcode;
        $this->country = $country;
        $this->type = $type;
    }
    
    function get_members() {
        $arr = array();
        $arr['email'] = $this->email;
        $arr['recipient'] = $this->recipient;
        $arr['company_name'] = $this->company_name;
        $arr['phone'] = $this->phone;
        $arr['addr1'] = $this->addr1;
        $arr['addr2'] = $this->addr2;
        $arr['suburb'] = $this->suburb;
        $arr['city'] = $this->city;
        $arr['region'] = $this->region;
        $arr['state'] = $this->state;
        $arr['postcode'] = $this->postcode;
        $arr['country'] = $this->country;
        return $arr;
    }
    
    function as_string() {
        $args = func_get_args();
        return $this->convert_to_string("\n", $args);
    }
    
    function as_csv() {
        $args = func_get_args();
        return $this->convert_to_string(',', $args);
    }
    
    function convert_to_string($delim, $args = array()) {
        $rtn = '';
        foreach ($this->get_members() as $key => $val) {
            if (!empty($val) && !in_array($key, $args)) {
                if ($rtn != '') {
                    $rtn .= $delim;
                }
                $rtn .= $val;
            }
        }
        return $rtn;
    }
    
    function get_state_or_region() {
        if (!empty($this->state)) {
            return $this->state;
        }
        else {
            return $this->region;
        }
    }
    
    function get_first_name() {
        $arr = split("[\n\r\t ]+", $this->recipient);
        return $arr[0];
    }
    
    function get_last_name() {
        $arr = split("[\n\r\t ]+", $this->recipient);
        return $arr[1];
    }
}
   

/**
 * Class used to hold a parameter array and position elements
 */   
class YakParams {
    var $params;
    var $pos1;
    var $pos2;
    
    function YakParams($pos1, $pos2) {
        $this->pos1 = $pos1;
        $this->pos2 = $pos2;
    }
}

class YakShippingOption {
    var $code;
    var $name;
    var $zones;
    
    function YakShippingOption($name) {
        $this->name = trim($name);
        $this->code = preg_replace('/[^a-zA-Z0-9_-]/s', '', $this->name);
        $this->zones = array();
    }
    
    function add_zone($zone) {
        $this->zones[] = $zone;
    }
}

class YakShippingZone {
    var $zone;
    var $countries;
    var $fixed;
    var $fixeditemfirst; 
    var $fixeditem;
    var $weightfirst;
    var $weight;
    
    function YakShippingZone($zone, $countries, $fixed, $fixeditemfirst, $fixeditem, $weightfirst, $weight) {
        $this->zone = $zone;
        $this->countries = $countries;
        $this->fixed = $fixed;
        $this->fixeditemfirst = $fixeditemfirst;
        $this->fixeditem = $fixeditem;
        $this->weightfirst = $weightfirst;
        $this->weight = $weight;
    }
    
    function get_countries_list() {
        if (strlen($this->countries) > 70) {
            return substr($this->countries, 0, 70) . '...';
        }
        else {
            return $this->countries;
        }
    }
}
?>