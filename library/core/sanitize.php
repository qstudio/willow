<?php

namespace willow\core;

use willow\core;

class sanitize {
    
    /**
     * Sanitize user input data using WordPress functions
     * 
     * @since       0.1
     * @param       string      $value      Value to sanitize
     * @param       string      $type       Type of value ( email, user, int, key, text[default] )
     * @link        http://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
     * @link        http://wp.tutsplus.com/tutorials/creative-coding/data-sanitization-and-validation-with-wordpress/
     * @return      string      HTML output
     */
    public static function value( $value = null, $type = 'text' ){
        
        // check submitted data ##
        if ( is_null( $value ) ) {
            
            return false;
            
        }
        
        switch ( $type ) {
            
            case( 'email' ):
            
                return \sanitize_email( $value );
                break;
            
            case( 'user' ):
            
                return \sanitize_user( $value );
                break;
            
            case( 'integer' ):
            
                return intval( $value );
                break;
            
            case( 'filename' ):
            
                return \sanitize_file_name( $value );
                break;
            
            case( 'key' ):
            
                return self::sanitize_key( $value ); // altered version of wp sanitize_key
                break;
			
			case( 'php_class' ):

				return self::php_class( $value );
				break;

			case( 'php_namespace' ):

				return self::php_namespace( $value );
				break;

			case( 'php_function' ):

				return self::php_function( $value );
				break;

            case( 'sql' ):
                
                return \esc_sql( $value );
                break;
            
            case( 'stripslashes' ):
                
                return preg_replace("~\\\\+([\"\'\\x00\\\\])~", "$1", $value);
                #stripslashes( $value );
                break;
            
            case( 'none' ):
                
                return $value;
                break;
            
            case( 'text' ):
            default;
                    
                // text validation
                return \sanitize_text_field( $value );
                break;
                
        }
        
	}

	/**
    * Sanitizes a php namespace
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function php_namespace( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*(\\\\[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)*$', '', $key );
        
        // return the key ##
        return $key;
        
	}
	
	/**
    * Sanitizes a php function name
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function php_function( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '/[^A-Za-z0-9-_]+/', '', $key );
        
        // return the key ##
        return $key;
        
	}

    /**
    * Sanitizes a php class name
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function php_class( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '/[^A-Za-z0-9-\\\\_]+/', '', $key );
        
        // return the key ##
        return $key;
        
	}
    
    /**
    * Sanitizes a string key.
    *
    * @since 1.3.0
    * @param string $key String key
    * @return string Sanitized key
    */
    public static function sanitize_key( $key = null ){
        
        // sanity check ##
        if ( ! $key ) { return false; }
        
        // scan the key for allowed characters ##
        $key = preg_replace( '/[^a-zA-Z0-9_\-~!$^+]/', '', $key );
        
        // return the key ##
        return $key;
		
	}

}
