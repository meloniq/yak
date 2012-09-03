<?php

require_once('PHPMockFunction.php');
require_once('../yak-utils.php');

/**
 * @backupGlobals disabled
 */
class YakUtilsTest extends PHPUnit_Framework_TestCase {
    
    protected function setUp() {
        
    }
 
    protected function tearDown() {
    }
    
    public function test_yak_default_when_not_empty() {
        $rtn1 = yak_default(null, 'default');
        $rtn2 = yak_default('', 'default');
        
        $this->assertEquals('default', $rtn1);
        $this->assertEquals('default', $rtn2);
    }

    public function test_yak_default_when_empty() {
        $rtn1 = yak_default('test', 'default');
        $rtn2 = yak_default(1, 500);
        
        $this->assertEquals('test', $rtn1);
        $this->assertEquals(1, $rtn2);
    }
    
    public function test_convert_to_query_string_when_empty() {
        $rtn = yak_convert_to_querystring(null, array("a"=>"b"));
        
        $this->assertEquals('', $rtn);
    }
    
    public function test_convert_to_query_string_when_not_empty() {
        $_POST = array("a"=>"b", "1"=>"2");
        $rtn = yak_convert_to_querystring(array("a", "1"), &$_POST);
        
        $this->assertEquals('&a=b&1=2', $rtn);
    }

    public function test_html_checkbox_returns_checked() {
        $rtn1 = yak_html_checkbox('on', false);
        $rtn2 = yak_html_checkbox(1, false);
        $rtn3 = yak_html_checkbox(true, false);
        
        $this->assertEquals('checked="checked"', $rtn1);
        $this->assertEquals('checked="checked"', $rtn2);
        $this->assertEquals('checked="checked"', $rtn3);
    }
    
    public function test_html_checkbox_does_not_return_checked() {
        $rtn1 = yak_html_checkbox('off', false);
        $rtn2 = yak_html_checkbox(0, false);
        $rtn3 = yak_html_checkbox(false, false);
        
        $this->assertEquals('', $rtn1);
        $this->assertEquals('', $rtn2);
        $this->assertEquals('', $rtn3);
    }
}