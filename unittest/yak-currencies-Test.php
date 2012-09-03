<?php

require_once('PHPMockFunction.php');
require_once('../yak-currencies.php');
 
 /**
  * @backupGlobals disabled
  */
class YakCurrenciesTest extends PHPUnit_Framework_TestCase {
 
    var $yak_get_option;
 
    protected function setUp() {
        $this->yak_get_option = PHPMockFunction::mock('yak_get_option');
    }
 
    protected function tearDown() {
    }

    protected function setup_selected_ccy($ccy) {
        $this->yak_get_option->expects(InvocationRestriction::once())
             ->with(SELECTED_CURRENCY)
             ->will(WillAction::returnValue($ccy));
    }
 
    public function test_return_no_sign() {
        $this->setup_selected_ccy('USD');
        
        $rtn = yak_format_money(123.54, false);
        
        $this->assertEquals('123.54', $rtn);
    }
        
    public function test_return_sign() {
        $this->setup_selected_ccy('USD');
        
        $rtn = yak_format_money(123.54, true);
        
        $this->assertEquals('$123.54', $rtn);
    }
        
    public function test_sign_after() {
        $this->setup_selected_ccy('ALL');
            
        $rtn = yak_format_money(123.54, true);
        
        $this->assertEquals('123,54L', $rtn);
    }
        
    public function test_florin_sign() {
        $this->setup_selected_ccy('AWG');
            
        $rtn = yak_format_money(54.12, true);
            
        $this->assertEquals("ƒ54,12", $rtn);
    }
        
    public function test_arabic_conversion_with_no_sign() {
        $this->setup_selected_ccy('BHD');
            
        $rtn = yak_format_money(14454.565, false);            
        $this->assertEquals("14٬454٫57", $rtn);
    }
        
    public function test_arabic_conversion_with_sign() {
        $this->setup_selected_ccy('BHD');
            
        $rtn1 = yak_format_money(14454.565, true);
            
        $this->setup_selected_ccy('YER');
            
        $rtn2 = yak_format_money(560000.501, true);
            
        $this->assertEquals(".د.ب14٬454٫57", $rtn1);
        $this->assertEquals("﷼560٬000٫50", $rtn2);
    }
        
    public function test_space_thousand_delim() {
        $this->setup_selected_ccy('UZS');
        
        $rtn = yak_format_money(123456789.098, true);
        
        $this->assertEquals("123 456 789,10сўм", $rtn);
    }
    
    public function test_mexico_special_case() {
        $this->setup_selected_ccy('MXN');
        
        $rtn = yak_format_money(1234567890123.456, true);
        
        $this->assertEquals("$1'234'567'890,123.46", $rtn);
    }
}
?>