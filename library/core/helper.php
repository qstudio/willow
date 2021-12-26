<?php

// namespace ##
namespace willow\core;

// core ##
use willow\plugin as willow;
// use willow;

/**
 * helper Class
 * @package   willow\core
 */
class helper {

	/**
     * Plugin Instance
     *
     * @var     Object      $plugin
     */
	protected 
		$plugin
	;

	/**
	 * CLass Constructer 
	*/
	function __construct(){

		// Log::write( $plugin );

        // grab plugin instance ## 
		$this->plugin = \willow\plugin::get_instance();
		
	}

	/**
     * callback method for class instantiation
     *
     * @since   0.0.2
     * @return  void
     */
	public function hooks() {

		// sanity ##
        if( 
            is_null( $this->plugin )
            || ! ( $this->plugin instanceof plugin ) 
        ) {

            error_log( 'Error in object instance passed to '.__CLASS__ );

            return false;
        
		}
		
		// error_log( 'Helper hooks run..' );

    }

	/**
     * Write to WP Error Log directly, not via log->set
     *
     * @since       4.1.0
     * @return      void
     */
    public static function log( $args = null ){
		
		// error_log( $args );

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
        if ( true === \WP_DEBUG ) {

			// get caller ##
			$backtrace = \willow\core\backtrace::get();

            if ( is_array( $log ) || is_object( $log ) ) {

				error_log( print_r( $log, true ).' -> '.$backtrace );
				
            } else {

				error_log( $log.' -> '.$backtrace );
				
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
    public static function get( $include = null, $return = 'echo', $type = 'url', $path = "library/", $class = null )    {

        // nothing passed ##
        if ( is_null( $include ) ) { 

            return false;            

        }

        // nada ##
        $template = false; 
        
		// w__log( 'd:>h::get class/include: '.$class.'/'.$include );

        // perhaps this is a child theme ##
        if ( 
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

            // w__log( 'parent theme: '.$template );

        // load from extended Plugin ##
        } elseif ( 
			! is_null( $class )
			&& method_exists( $class, 'get_plugin_path' )
            && file_exists( call_user_func( array( $class, 'get_plugin_path' ), $path.$include ) )
            // file_exists( self::get_plugin_path( $path.$include ) )
        ) {

            // w__log( 'd:>h::get class: '.$class );

            // $template = self::get_plugin_url( $path.$include ); // plugin URL ##
            $template = call_user_func( array( $class, 'get_plugin_url' ), $path.$include );

            if ( 'path' === $type ) {
                
                // $template = self::get_plugin_path( $path.$include ); // plugin path ##
                $template = call_user_func( array( $class, 'get_plugin_path' ), $path.$include );
                
            } 

            // w__log( 'extended plugin: '.$template );

        }

        // load from Plugin ##
        elseif ( 
            file_exists( willow::get_plugin_path( $path.$include ) )
        ) {

            $template = willow::get_plugin_url( $path.$include ); // plugin URL ##

            if ( 'path' === $type ) {
                
                $template = willow::get_plugin_path( $path.$include ); // plugin path ##
                
            } 

            // w__log( 'plugin: '.$template );

        }

        if ( $template ) { // continue ##

            // apply filters ##
            $template = \apply_filters( __NAMESPACE__.'/helper/get', $template );

            // echo or return string ##
            if ( 'return' === $return ) {

                // w__log( 'returned' );

                return $template;

            } elseif ( 'require' === $return ) {

                // w__log( 'required' );

                return require_once( $template );

            } else {

                // w__log( 'echoed..' );

                echo $template;

            }

        }

        // nothing cooking ##
        return false;

    }


}
