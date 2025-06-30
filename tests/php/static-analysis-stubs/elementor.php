<?php

// Elementor base classes for PHPStan
namespace Elementor {
    /**
     * Elementor Base Data Control
     * 
     * @package feedzy-rss-feeds
     */
    class Base_Data_Control {
        /**
         * Print control UID
         */
        protected function print_control_uid( $input_type = null ) {}
    }

    /**
     * Elementor Widget Base
     * 
     * @package feedzy-rss-feeds
     */
    class Widget_Base {
        /**
         * Get widget name
         */
        public function get_name() {}
        
        /**
         * Get widget title
         */
        public function get_title() {}
        
        /**
         * Get widget icon
         */
        public function get_icon() {}
        
        /**
         * Get widget categories
         */
        public function get_categories() {}
        
        /**
         * Register controls
         */
        protected function register_controls() {}
        
        /**
         * Render widget
         */
        protected function render() {}
    }
}