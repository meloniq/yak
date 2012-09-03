<?php
    define(PHPMOCKFUNCTION_WILL_ACTION_RETURNVALUE, 0x01);
    define(PHPMOCKFUNCTION_WILL_ACTION_THROWEXCEPTION, 0x02);
    
    abstract class WillAction {
        public abstract function getType();
        public abstract function getValue();
        
        public function returnValue($value) {
            return new ReturnValueAction($value);
        }
        
        public function throwException($value) {
            return new ThrowExceptionAction($value);
        }
    }
    
    class ReturnValueAction extends WillAction {
        private $value;
        
        public function __construct($value) {
            $this->value = $value;
        }
        
        public function getType() {
            return PHPMOCKFUNCTION_WILL_ACTION_RETURNVALUE;
        }
        
        public function getValue() {
            return $this->value;
        }
    }
    
    class ThrowExceptionAction extends WillAction {
        private $value;
        
        public function __construct($value) {
            $this->value = $value;
        }
        
        public function getType() {
            return PHPMOCKFUNCTION_WILL_ACTION_THROWEXCEPTION;
        }
        
        public function getValue() {
            return $this->value;
        }
    }
?>