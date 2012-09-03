<?php
    require_once("Expectation.php");
    require_once("WillAction.php");
    require_once("Matcher.php");
    
    class PHPMockFunction {
        private $functionName;
        private $expectations = array();
        private static $mocks = array();
        private $current_expectation;
        
        public static function mock($functionName) {
            $mocker = new self($functionName);
            self::$mocks[$functionName] = $mocker;
            return $mocker;
        }
        
        public function __construct($functionName) {
            $this->functionName = $functionName;
            if (function_exists($functionName)) {
                if (!function_exists('runkit_function_redefine')) {
                    throw new Exception("PECL runkit extension is required to override functions. see http://www.php.net/manual/en/runkit.installation.php for more info.");
                }
                $rf = new ReflectionFunction($functionName);
                $rp = $rf->getParameters();
                $aParams = array();
                foreach ($rp as $param) {
                    $aParams[] ='$' . $param;
                }
                $fnc_code = '$mock = PHPMockFunction::getMock(\'' . $functionName . '\');' . "\n" . 'return $mock->invoke(func_get_args());';

                runkit_function_redefine(strtolower($functionName), 
                                         '',
                                         $fnc_code);
            } else {
                eval("function $functionName() {
                    return PHPMockFunction::getMock('$functionName')->invoke(func_get_args());
                }");
            }
        }
        
        public static function getMock($functionName) {
            return self::$mocks[$functionName];
        }
        
        public function invoke($arguments) {
            $matchedExpectation = null;
            foreach ($this->expectations as $expectation) {
                if ($expectation->match($arguments)) {
                    $matchedExpectation = $expectation;
                    break;
                }
            }
            if ($matchedExpectation != null) {
                $willAction = $matchedExpectation->getWillAction();
                if ($willAction != null) {
                    switch($willAction->getType()) {
                        case PHPMOCKFUNCTION_WILL_ACTION_RETURNVALUE:
                           return $willAction->getValue();
                            break;
                        case PHPMOCKFUNCTION_WILL_ACTION_THROWEXCEPTION:
                            throw $willAction->getValue();
                            break;
                    }
                }
            } else {
                $msg = "No expectation was found for invocation of '";
                $msg .= $this->functionName;
                $msg .= "' with arguments {";
                $bFirst = TRUE;
                foreach ($arguments as $argument) {
                    if ($bFirst) {
                        $bFirst = FALSE;
                    } else {
                        $msg .= ", ";
                    }
                    $msg .= $argument;
                }
                $msg .= '}' . "\n";
                $msg .= "Allowed invocations:\n";
                foreach ($this->expectations as $expectation) {
                    $msg.= "   " . $expectation->toString() . "\n";
                }
                throw new Exception($msg);
            }
        }
        
        public function expects($invocationRestriction) {
            $current_expectation = new Expectation($this);
            $current_expectation->setInvocationRestriction($invocationRestriction);
            $this->expectations[] = $current_expectation;
            
            return $current_expectation;
        }   
    }
    
?>