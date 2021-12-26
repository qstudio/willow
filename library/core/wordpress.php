<?php

namespace willow\core;

use willow\core;

class wordpress {

    /**
     * Save a value to the options table, either updating or creating a new key
     * 
     * @since       1.5.0
     * @return      Void
     */
    public static function add_update_option( $option_name, $new_value, $deprecated = '', $autoload = 'no' ){
    
        if ( \get_option( $option_name ) != $new_value ) {

            \update_option( $option_name, $new_value );

        } else {

            \add_option( $option_name, $new_value, $deprecated, $autoload );

        }
    
    }

}
