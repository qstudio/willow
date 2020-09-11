<?php

// namespace ##
namespace q\willow\core;

// core ##
use q\willow\core;

/**
 * helper Class
 * @package   willow\core
 */
class helper extends \willow {


    /**
     * Write to WP Error Log
     *
     * @since       1.5.0
     * @return      void
     */
    public static function log( $args = null )
    {
		
		// shift callback level, as we added another level.. ##
		\add_filter( 
			'q/willow/core/log/backtrace/function', function () {
			return 4;
		});
		\add_filter( 
			'q/willow/core/log/backtrace/file', function () {
			return 3;
		});
		
		// pass to core\log::set();
		return core\log::set( $args );

	}
	


	/**
     * Write to WP Error Log directly, not via core\log
     *
     * @since       4.1.0
     * @return      void
     */
    public static function hard_log( $args = null )
    {
		
		error_log( $args );

		// sanity ##
		if ( is_null( $args ) ) { 
			
			// error_log( 'Nothing passed to log(), so bailing..' );

			return false; 
		
		}

		// $args can be a string or an array - so fund out ##
		if (  
			is_string( $args )
		) {

			// default ##
			$log = $args;

		} elseif ( 
			is_array( $args ) 
			&& isset( $args['log_string'] )	
		) {

			// error_log( 'log_string => from $args..' );
			$log = $args['string'];

		} else {
			
			$log = $args;

		} 

		// debugging is on in WP, so write to error_log ##
        if ( true === WP_DEBUG ) {

			// get caller ##
			$backtrace = core\method::backtrace();

            if ( is_array( $log ) || is_object( $log ) ) {

				core\log::error_log( print_r( $log, true ).' -> '.$backtrace, \WP_CONTENT_DIR."/debug.log" );
				
            } else {

				core\log::error_log( $log.' -> '.$backtrace, \WP_CONTENT_DIR."/debug.log" );
				
            }

		}
		
		// done ##
		return true;

    }




	/**
    * check if a file exists with environmental fallback
    * first check the active theme, then the plugin
    *
    * @param    $include        string      Include file with path ( from library/  ) to include. i.e. - templates/loop-nothing.php
    * @param    $return         string      return method ( echo, return, require )
    * @param    $type           string      type of return string ( url, path )
    * @param    $path           string      path prefix
    * @param    $class          string      parent class to reference for location of assets
    *
    * @since 0.1
    */
    public static function get( $include = null, $return = 'echo', $type = 'url', $path = "library/", $class = null )
    {

        // nothing passed ##
        if ( is_null( $include ) ) { 

            return false;            

        }

        // nada ##
        $template = false; 
        
        #if ( ! defined( 'TEMPLATEPATH' ) ) {

        #    h::log( 'MISSING for: '.$include.' - AJAX = '.( \wp_doing_ajax() ? 'true' : 'false' ) );

		#}
		
		// h::log( 'd:>h::get class/include: '.$class.'/'.$include );

        // perhaps this is a child theme ##
        if ( 
            // defined( 'Q_CHILD_THEME' )
            // && Q_CHILD_THEME
			\get_template_directory() !== \get_stylesheet_directory()
            && file_exists( \get_stylesheet_directory().'/'.$path.$include )
        ) {

            $template = \get_stylesheet_directory_uri().'/'.$path.$include; // template URL ##
            
            if ( 'path' === $type ) { 

                $template = \get_stylesheet_directory().'/'.$path.$include;  // template path ##

            }

        }

        // load active theme over plugin ##
        elseif ( 
            file_exists( \get_template_directory().'/'.$path.$include ) 
        ) { 

            $template = \get_template_directory_uri().'/'.$path.$include; // template URL ##
            
            if ( 'path' === $type ) { 

                $template = \get_template_directory().'/'.$path.$include;  // template path ##

            }

            #if ( self::$debug ) self::log( 'parent theme: '.$template );

        // load from extended Plugin ##
        } elseif ( 
			! is_null( $class )
			&& method_exists( $class, 'get_plugin_path' )
            && file_exists( call_user_func( array( $class, 'get_plugin_path' ), $path.$include ) )
            // file_exists( self::get_plugin_path( $path.$include ) )
        ) {

            // h::log( 'd:>h::get class: '.$class );

            // $template = self::get_plugin_url( $path.$include ); // plugin URL ##
            $template = call_user_func( array( $class, 'get_plugin_url' ), $path.$include );

            if ( 'path' === $type ) {
                
                // $template = self::get_plugin_path( $path.$include ); // plugin path ##
                $template = call_user_func( array( $class, 'get_plugin_path' ), $path.$include );
                
            } 

            // h::log( 'extended plugin: '.$template );

        }

        // load from Plugin ##
        elseif ( 
            file_exists( self::get_plugin_path( $path.$include ) )
        ) {

            $template = self::get_plugin_url( $path.$include ); // plugin URL ##

            if ( 'path' === $type ) {
                
                $template = self::get_plugin_path( $path.$include ); // plugin path ##
                
            } 

            #if ( self::$debug ) self::log( 'plugin: '.$template );

        }

        if ( $template ) { // continue ##

            // apply filters ##
            $template = \apply_filters( __NAMESPACE__.'_helper_get', $template );

            // echo or return string ##
            if ( 'return' === $return ) {

                #if ( self::$debug ) h::log( 'returned' );

                return $template;

            } elseif ( 'require' === $return ) {

                #if ( self::$debug ) h::log( 'required' );

                return require_once( $template );

            } else {

                #if ( self::$debug ) h::log( 'echoed..' );

                echo $template;

            }

        }

        // nothing cooking ##
        return false;

    }


}
