<?php

/*
 * Implementation of singleton pattern
 *
 * Usage: extend targeted class with Singleton class
 * 
 * Example: 
 *          class Example extends Singleton {};
 *          $example = Example::instance(); 
 */

class Singleton {
    protected static $instance_ = null;
    protected function __construct() {
        // Disable constructor
    }
    protected function __clone() {
        // Disable cloning
    }

    public static function instance() {
        if (!isset(static::$instance_)) {
            static::$instance_ = new static;
        }
        return static::$instance_;
    }
}
?>