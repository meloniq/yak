<?php
    abstract class Matcher {
        public abstract function match($argument);
        
        public function eq($argument) {
            return new EqualsMatcher($argument);
        }
        
        public function notNull() {
            return new NotNullMatcher();
        }
        
        public function anyArgs() {
            return new AnyArgsMatcher();
        }
    }
    
    class AnyArgsMatcher extends Matcher {
        public function match($argument) {
            return true;
        }
    }
    
    class NotNullMatcher extends Matcher {
        public function match($argument) {
            return ($argument != NULL);
        }
    }
    
    class EqualsMatcher extends Matcher {
        public function match($argument) {
            if ($this->argument == $argument) {
                return TRUE;
            }
            return FALSE;
        }
    }
?>