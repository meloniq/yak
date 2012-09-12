<?php
/*
See yak-for-wordpress.php for information and license terms
*/

if (!function_exists('yak_bccomp')) {
    function yak_bccomp($f1, $f2, $scale) {
        if (function_exists('bccomp')) {
            return bccomp($f1, $f2, $scale);
        }
        else if ($f1 == $f2) {
            return 0;
        }
        else if ($f1 > $f2) {
            return 1;
        }
        else {
            return -1;
        }
    }	
}

if (!function_exists('yak_convert_to_querystring')) {
    function yak_convert_to_querystring($names, array $map) {
        $query = '';
        if ($names != null) {
            foreach ($names as $name) {
                if (!empty($map[$name])) {
                    $query .= '&' . $name . '=' . $map[$name];
                }
            }
        }
        return $query;
    }
}

/**
 * Return a default if the specified value is not set
 *
 * @param $val the value to return a default if empty
 * @param $def the default value to use if $val is not set
 */
function yak_default($val, $def = '') {
    if (empty($val)) {
        return $def;   
    }
    else {
        return $val;   
    }
}


/**
 * Fix escaping of single-quotes
 */
if (!function_exists('yak_fix_escaping')) {
    function yak_fix_escaping($s) {
        $s = str_replace("\\'", "'", $s);
        $s = str_replace("\\\\", "\\", $s);
        return $s;
    }
}


/**
 * Return the name of a country by the country code
 */
if (!function_exists('yak_get_country')) {
    function yak_get_country($country_code) {
        global $countries;
        return $countries[$country_code];
    }
}


/**
 * Return a country code by the country name (if not found, return null)
 */
if (!function_exists('yak_get_country_by_name')) {
    function yak_get_country_by_name($ctyname) {
        global $countries;
        
        foreach ($countries as $code=>$name) {
            if ($name == $ctyname) {
                return $code;   
            }
        }
        
        return null;
    }
}


/**
 * Append 2 strings, removing any overlap between the two
 */
if (!function_exists('yak_overlap')) {
    function yak_overlap($s1, $s2) {
        for ($x = strlen($s1)-1; $x >= 0; $x--) {
            $end = substr($s1, $x);
            $start = substr($s2, 0, strlen($end));
            if ($start == $end) {
                return substr($s1, 0, $x) . $s2;
            }
        }
        return $s1 . $s2;
    }
}

/**
 * Convert a memory value as a number of bytes.
 */
function yak_return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

if (!function_exists('yak_array_contains')) {
    function yak_array_contains($haystacks, $needle) {
        foreach ($haystacks as $haystack) {
            if (yak_str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Return true if one string can be found in another
 *
 * @param $haystack the string to search *in*
 * @param $needle the string to search *for*
 */
if (!function_exists('yak_str_contains')) {
    function yak_str_contains($haystack, $needle) {
        $pos = strpos($haystack, $needle);
        
        if ($pos === false) {
            return false;
        }
        else {
            return true;
        }
    }   
}

if (!function_exists('yak_curl_redir_exec')) {
    function yak_curl_redir_exec($ch, $debug = "") {
        static $curl_loops = 0;
        static $curl_max_loops = 20;

        if ($curl_loops++ >= $curl_max_loops) { 
            $curl_loops = 0;
            return FALSE;
        } 
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $debbbb = $data;
        list($header, $data) = explode("\n\n", $data, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url) {
                //couldn't process the url to redirect to
                $curl_loops = 0;
                return $data;
            }
            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
            curl_setopt($ch, CURLOPT_URL, $new_url);

            return curl_redir_exec($ch);
        }
        else {
            $curl_loops = 0;
            return $debbbb;
        }
    }
}


/**
 * Execute an HTTP method.  If curl is available, use curl, otherwise pfsockopen.
 *
 * @param $host the server name/ip
 * @param $uri the uri to invoke
 * @param $params the parameters to use in the execution
 * @param $headers the HTTP headers
 * @param $method the HTTP method
 */
if (!function_exists('yak_do_http')) {
    if (function_exists('curl_init')) {
        function yak_do_http($host, $uri, $params, $headers = null, $method = 'POST', $timeout = null) {
            $ch = curl_init();
              
            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_URL, $host . $uri);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
            else {
                curl_setopt($ch, CURLOPT_URL, $host . $uri . '?' . $params);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $proxy_url = yak_get_option(HTTP_PROXY_URL, '');
            if ($proxy_url != '') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($ch, CURLOPT_PROXY, $proxy_url);    
            }
            
            if ($timeout != null) {
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            }
            
            if ($headers != null) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
            }
            
            $response = yak_curl_redir_exec($ch);
            
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            else {
                $pos = strrpos($response, "\r\n\r\n");
                return substr($response, $pos + 4);
            }
        }
    }
    else {
        function yak_do_http($host, $uri, $params, $headers = null, $method = 'POST') {
            $parsed = parse_url($host);
            $hostname = $parsed['host'];
            $port = 80;
            if ($parsed['scheme'] == 'https') {
                $host = 'ssl://' . $hostname;
                $port = 443;
            }
            
            $header = "Host: $hostname\r\n";
            $header .= "Accept-Encoding: identity\r\n";
            
            # compose HTTP request header
            if ($method == 'POST') {
                $header .= "User-Agent: YAK for WordPress\r\n";
                if (!yak_array_contains($headers, 'Content-Type')) {
                    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
                }
                $header .= "Content-Length: " . strlen($params) . "\r\n";
            }
            else {
                $uri .= '?' . $params;
                $params = '';
            }
            
            if ($headers != null) {
                foreach ($headers as $key=>$val) {
                    $header .= $key . ": " . $val . "\r\n";       
                }
            }
            
            $header .= "Connection: close\r\n\r\n";

            if ($timeout == null) {
                $timeout = ini_get("default_socket_timeout");
            }

            $fp = pfsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp) {
                yak_log("comms error $errstr ($errno)");
                return "ERROR: $errstr ($errno)";
            }
            else { 
                fputs($fp, "$method $uri HTTP/1.1\r\n");
                fputs($fp, $header . $params);
                fwrite($fp, $out);
                
                $response = '';
                while (!feof($fp)) {
                    $response .= fgets($fp, 128);
                }
                fclose($fp);
                
                $response = split("\r\n\r\n", $response);
                $header = $response[0];
                $responsecontent = $response[1];
                if (!(strpos($header, "Transfer-Encoding: chunked") === false)) {
                    $aux = split("\r\n", $responsecontent);
                    $size = count($aux);
                    for ($i = 0; $i < $size; $i++) {
                        if ($i == 0 || ($i % 2 == 0)) {
                            $aux[$i] = "";
                        }
                    }
                    $responsecontent = implode("", $aux);
                }
                
                $rtn = chop($responsecontent);
                
                yak_log($rtn);
                
                return $rtn;
            }   
        }
    }
}


if (!function_exists('yak_get_tag_value')) {
    function yak_get_tag_value($content, $tag_start, $tag_end = null, $start_from = 0) {
        if (!isset($tag_end) || $tag_end == '') {
            $endtag = "\n";
        }
        $tag_start_len = strlen($tag_start);
        $pos = strpos($content, $tag_start, $start_from);
        if ($pos >= 0) {
            $pos2 = strpos($content, $tag_end, $pos + $tag_start_len);
            return substr($content, $pos + $tag_start_len, $pos2 - ($pos + $tag_start_len));
        }
        else {
            return null;
        }
    }
}


/**
 * Process a url to make sure we handle it intelligently.
 *     1. if there is no protocol, then assume this is a url for the local site.
 *     2. if we're going to append to the url, add either ? or & to the end depending upon
 *        what's already in the url
 *
 * @param $url the initial url to use
 * @param $safe_append are we going to append parameters to the url?
 */
if (!function_exists('yak_get_url')) {
    function yak_get_url($url, $safe_append = false) {
        if (strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
            $rtn = get_bloginfo('url') . $url;
        }
        else {
            $rtn = $url;
        }
        
        if ($safe_append == true) {
            if (strpos($rtn, '?') === false) {
                $rtn = $rtn . '?';
            }
            else {
                $rtn = $rtn . '&';
            }
        }
        
        return $rtn;
    }
}


/**
 * Encode a param array as a url-encoded string
 */
if (!function_exists('yak_encode_params')) {
    function yak_encode_params($param_array) {
        $params = "";
        foreach( $param_array as $key => $value ) {
            $params .= "$key=" . urlencode( $value ) . "&";
        }
        return $params;
    }
}

/**
 * Decode a param string into an array -- note: ignores duplicates (http params can have
 * dups, so this isn't correct behaviour, but works for YAK's purposes... for the moment)
 */
if (!function_exists('yak_decode_params')) {
    function yak_decode_params($params) {
        $rtn = array();
        foreach (explode('&', $params) as $keyval) {
            $kv = explode('=', $keyval);
            $rtn[$kv[0]] = urldecode($kv[1]);
        }
        return $rtn;
    }
}


if (!function_exists('yak_date_control')) {
    function yak_date_control($name, $year, $month, $day, $include_day = true) {
        global $months;

    	echo yak_html_select(array('id'=>'month_' . $name, 'name'=>'month_' . $name, 'selected'=>$month, 'values'=>$months));
    	if ($include_day) {
    	    echo '<input type="text" id="day_' , $name , '" name="day_' , $name , '" value="' , $day , '" maxlength="2" size="4" />, ';
	    }
    	echo '<input type="text" id="year_' , $name , '" name="year_' , $name , '" value="' , $year , '" maxlength="4" size="6" />';
    }
}


if (!function_exists('yak_address_hidden_input')) {
    function yak_address_hidden_input($address, $type) {
        if ($address != null) {
            foreach ($address->get_members() as $key=>$value) {
                echo '<input type="hidden" name="' , $type , '_' , $key , '" value="' , $value , '" />';
            }
        }
    }
}


/**
 * utility function to set an admin option -- looks for the option in the POST, if found checks for an array
 * otherwise sets a default value if present.
 */
if (!function_exists('yak_admin_options_set')) {
    function yak_admin_options_set($name, $default = null, $array = false, $strip_spaces = false, $autoload = true) {
        if (isset($_POST[$name])) {
            $val = $_POST[$name];
            if ($strip_spaces) {
                $val = str_replace(' ', '', $val);
            }
            
            if ($array) {
                yak_admin_option_set($name, $val, $autoload);
            }
            else {
                yak_admin_option_set($name, stripslashes($val), $autoload);
            }
        }
        else if (!empty($default)) {
            yak_admin_option_set($name, $default, $autoload);
        }
    }
}


if (!function_exists('yak_admin_option_set')) {
    function yak_admin_option_set($name, $val, $autoload = 'yes') {
        $opt = get_option($name);
        if ($opt === $val) {
            return;
        }
        
        if (false === $opt) {
            add_option($name, $val, '', $autoload);
        }
        else {
            update_option($name, $val);
        }
    }
}


if (!function_exists('yak_calc_version_number')) {
    function yak_calc_version_number($version) {
        if (!isset($version) || empty($version)) {
            return null;
        }
        // calculate a version 'number'
        $ver = ereg_replace("[^0-9.]", "", $version);
        $arr = split('\.', $ver);
        $size = count($arr);
        for ($x = 0; $x < $size; $x++) {
            $arr[$x] = str_pad($arr[$x], 3, '0', STR_PAD_LEFT);
        }
        return intval(join("", $arr));
    }
}


/**
 * return the number of digits in a string
 */
if (!function_exists('yak_count_digits')) {
    function yak_count_digits($str) {
        $digits = split("[0-9]", $str);
        return count($digits);
    }
}


/**
 * Return the remote IP address
 */
if (!function_exists('yak_get_ip')) {
    function yak_get_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip == '::1') {
            return '127.0.0.1';
        }
        else {
            return $ip;
        }
    }
}

/**
 * Return the blog url using ssl if necessary.
 */
if (!function_exists('yak_get_blogurl')) {
    function yak_get_blogurl() {
        $url = get_bloginfo('wpurl');
        if (yak_get_option(USE_SSL, 'off') == 'on') {
            return str_replace('http:', 'https:', $url);
        }
        else {
            return $url;
        }
    }
}

if (!function_exists('yak_get_product_post')) {
    function yak_get_product_post($override_post) {
        global $post;
        
        // yak_post can be used to override 'the_loop' post
        if ($GLOBALS['yak_post'] != null) {
            return $GLOBALS['yak_post'];
        }
        else if (isset($override_post)) {
            return $override_post;
        }
        else {
            return $post; 
        }
    }
}


/**
 * Return a YAK option by its name.
 *
 * @param $name the name of the parameter to return
 * @param $default the default value to return if no parameter is found
 */
function yak_get_option($name, $default='') {
    $value = get_option($name);
    
    return yak_default($value, $default);
}


/**
 * Return the current page permalink, using ssl if necessary.
 */
if (!function_exists('yak_get_permalink')) {
    function yak_get_permalink($query_string = null) {
        $url = get_permalink();
        if (yak_get_option(USE_SSL, 'off') == 'on') {
            $url = str_replace('http:', 'https:', $url);
        }
        
        if (!empty($query_string)) {
            if (!yak_str_contains($url, '?')) {
                $url .= '?';
            }
            else {
                $url .= '&';
            }
            $url .= $query_string;
        }
        
        return $url;
    }
}


/**
 * Some installs of PHP don't include the glob function, so this should hopefully fix this problem.
 */
if (!function_exists('yak_glob')) { 
    function yak_glob($path) {
        // use the standard glob if it exists
        if (function_exists('glob')) {
            return yak_default(glob($path), array());
        }
        else {
            // otherwise, roll our own
            $dir = dirname($path);
            $pattern = basename($path);
            $files = array();
            if (is_dir($dir)) {
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if (fnmatch($pattern, $file)) {
                            $files[] = $dir . '/' . $file;
                        }
                    }
                }
            }
            return $files;
        }
    }
}


if (!function_exists('yak_get_shipping_varname')) {
    function yak_get_shipping_varname($code, $zone, $var, $md5 = true) {
        $code = preg_replace('/[^a-zA-Z0-9_-]/s', '', $code);
        $suffix = $code . '_' . $zone . '_' . $var;
        if ($md5) {
            $suffix = md5($suffix);
        }
        return 'yak_ship_' . $suffix;
    }
}


if (!function_exists('yak_html_checkbox')) {
    /**
     * Echo the correct checked option for an HTML checkbox
     *
     * @param $value if 'on' output CHECKED, otherwise nothing
     */
    function yak_html_checkbox($value, $echo = true) {
        if ((is_string($value) && $value == 'on') || (is_bool($value) && $value == true) || $value == 1) {
            if ($echo) {
                echo 'checked="checked"';
            }
            else {
                return 'checked="checked"';
            }
        }
        return '';
    }
}


if (!function_exists('yak_html_select')) {
    /**
     * Output an HTML select (drop down) element.
     *
     * @param $name the name to use in the select
     * @param $selected the selected option
     * @param $values a key->value array of the options to use in the select
     * @param $reverse_key_value the value in the array contains the key of the option (defaults to false)
     * @param $match_values an array of values to check when building the select.  If a value isn't in this array
     *                      then it won't be included in the select (defaults to null, and thus ignored)
     * @param $nokey if true, then the array is idx->value not key->value, so use the value for the option key
     */
    function yak_html_select($args) {
        if (isset($args['debug']) && $args['debug']) {
            yak_log(print_r($args, true));
        }
        
        $defaults = array(
            "id" => "",
            "name" => "",
            "selected" => null,
            "values" => array(),
            "reverse_key_value" => false,
            "match_values" => null,
            "nokey" => false,
            "class" => null,
            "onchange" => null,
            "multiple" => 1,
            "disabled" => false,
            "array_index" => 0,
            "style" => null,
            "debug" => false,
            "title" => null
        );
        $args = array_merge($defaults, $args);
        extract($args);
        
        $rtn = "<select name=\"$name\"";
        
        if (!empty($id)) {
            $rtn .= " id=\"$id\"";
        }
        
        if (!empty($onchange)) {
            $rtn .= " onchange=\"$onchange\"";
        }
        
        if (!empty($class)) {
            $rtn .= " class=\"$class\"";
        }
        
        if (!empty($style)) {
            $rtn .= " style=\"$style\"";
        }
        
        if ($multiple > 1) {
            $rtn .= " multiple=\"multiple\"";
            $rtn .= " size=\"$multiple\"";
        }
        
        if ($disabled) {
            $rtn .= ' disabled="disabled"';
        }
        
        if (!empty($title)) {
            $rtn .= " title=\"$title\"";
        }
        
        $rtn .= ">\n";
        
        if (isset($values)) {
            $set_sel = false;
            foreach ($values as $key=>$value) {
                if (is_array($value)) {
                    $value = $value[$array_index];
                }
                if ($match_values != null && !in_array($value, $match_values)) {
                    continue;
                }
                
                if ($reverse_key_value) {
                    $k = $value;
                    if ($nokey == true) {
                        $v = $value;
                    }
                    else {
                        $v = $key;   
                    }
                }
                else {
                    if ($nokey == true) {
                        $k = $value;   
                    }
                    else {
                        $k = $key;
                    }
                    $v = $value;
                }
                
                // set the option value (I'm also referring to this as the key)
                $rtn .= "  <option value=\"$k\"";
                
                if (!$set_sel && ((is_array($selected) && in_array($k, $selected)) || $selected == $k)) {
                    $rtn .= " SELECTED";
                    if ($multiple == 1) {
                        $set_sel = true;
                    }
                }
                $rtn .= ">" . __($v, 'yak') . "</option>\n";
            }
        }
        
        $rtn .= "</select>";
        
        return $rtn;
    }
}


if (!function_exists('yak_in_list')) {
    function yak_in_list($name, $typeList) {
        foreach ($typeList as $type) {
            if ($name == $type->name) {
                return true;
            }
        }
        return false;
    }
}


if (!function_exists('yak_is_page')) {
    function yak_is_page() {
        $pid = $_GET['post'];
        return get_post_type($pid) == 'page' || yak_str_contains($_SERVER['PHP_SELF'], 'page') || $_GET['post_type'] == 'page';
    }
}


function yak_log($msg) {
    @error_log(preg_replace("/\n+\s*/", " ", $msg));
}


if (!function_exists("yak_sendmail")) {
    function yak_sendmail($from, $to, $subject, $message) {
        $mailer = apply_filters('yak-mailer', null);
        
        if (!empty($mailer)) {
            call_user_func($mailer, $from, $to, $subject, $message);
        }
        else {
            yak_mail($from, $to, $subject, $message);
        }
    }
}


if (!function_exists('yak_split_email')) {
    function yak_split_email($email) {
        if (strpos($email, '<') !== false) {
			$from_name = substr($email, 0, strpos($email, '<') - 1);
			$from_name = str_replace('"', '', $from_name);
			$from_name = trim($from_name);

			$from_email = substr($email, strpos($email, '<') + 1);
			$from_email = str_replace('>', '', $from_email);
			$from_email = trim($from_email);
		}
		else {
			$from_email = trim($email);
			$from_name = '';
		}
		
		return array($from_email, $from_name);
    }
}

if (!function_exists('yak_mail')) {
    /**
     * Savagely hacked out wp_mail from the Wordpress codebase and modified for my purposes...
     */
    function yak_mail($from, $to, $subject, $message) {
    	global $phpmailer;
    	
    	yak_log("Sending mail from $from to $to with subject $subject");
    	
    	// (Re)create it, if it's gone missing
    	if (!is_object($phpmailer) || !is_a($phpmailer, 'PHPMailer')) {
    		require_once ABSPATH . WPINC . '/class-phpmailer.php';
    		require_once ABSPATH . WPINC . '/class-smtp.php';
    		$phpmailer = new PHPMailer();
    	}

    	// Empty out the values that may be set
    	$phpmailer->ClearAddresses();
    	$phpmailer->ClearAllRecipients();
    	$phpmailer->ClearAttachments();
    	$phpmailer->ClearBCCs();
    	$phpmailer->ClearCCs();
    	$phpmailer->ClearCustomHeaders();
    	$phpmailer->ClearReplyTos();
    	
    	list($from_email, $from_name) = yak_split_email($from);
    	list($to_email, $to_name) = yak_split_email($to);
		
    	$phpmailer->From = $from_email;
    	if (!empty($from_name)) {
        	$phpmailer->FromName = $from_name;
    	}
    	else {
    	    $phpmailer->FromName = '';
    	}

    	// Set destination address
    	$phpmailer->AddAddress($to_email);

    	// Set mail's subject and body
    	$phpmailer->Subject = $subject;
    	
    	if (yak_str_contains($message, '<html')) {
    	    $phpmailer->IsHTML(true);
    	    $phpmailer->Body = $message;
            $phpmailer->AltBody= strip_tags($message);
    	}
    	else {
    	    $phpmailer->Body = $message;
	    }

    	// Set to use PHP's mail()
    	$phpmailer->IsMail();

    	// Set the content-type and charset
    	$phpmailer->CharSet = get_bloginfo('charset');

        do_action_ref_array('phpmailer_init', array(&$phpmailer));

    	return @$phpmailer->Send();
    }
}

if (!function_exists('yak_format_month')) {
    function yak_format_month($month) {
        return date("F", mktime(0, 0, 0, ($month + 1)));
    }
}

if (!function_exists("_ye")) {
    function _ye($msg, $pot) {
        $msg = __($msg, $pot);

        $msg_array = array(__($msg, $pot));
        $arg_array = array_slice(func_get_args(), 2);
                
        echo call_user_func_array('sprintf', array_merge($msg_array, $arg_array));
    }
}

if (!function_exists("__y")) {
    function __y($msg, $pot) {
        $msg = __($msg, $pot);

        $msg_array = array(__($msg, $pot));
        $arg_array = array_slice(func_get_args(), 2);
                
        return call_user_func_array('sprintf', array_merge($msg_array, $arg_array));
    }
}

?>
