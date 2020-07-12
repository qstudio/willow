<?php

/*
 * logic-less, procedural semantic markup language 
 *
 * @package         q-willow
 * @author          Q Studio <social@qstudio.us>
 * @license         GPL-2.0+
 * @link            http://qstudio.us/
 * @copyright       2010 Q Studio
 *
 * @wordpress-plugin
 * Plugin Name:     Q Willow
 * Plugin URI:      https://www.qstudio.us
 * Description:     Willow is a Simple, logic-less, procedural semantic markup language 
 * Version:         0.0.1
 * Author:          Q Studio
 * Author URI:      https://www.qstudio.us
 * License:         GPL
 * Copyright:       Q Studio
 * Class:           q_willow
 * Text Domain:     q-willow
 * Domain Path:     /languages
 * GitHub Plugin URI: qstudio/q-willow
*/

// quick check :) ##
defined( 'ABSPATH' ) OR exit;

/* Check for Class */
if ( ! class_exists( 'q_willow' ) ) {

    // instatiate plugin via WP plugins_loaded ##
    add_action( 'plugins_loaded', array ( 'q_willow', 'get_instance' ), 1 );

    // Q Class ##
    class q_willow {

        // Refers to a single instance of this class. ##
        private static $instance = null;

        // Plugin Settings
        const version = '0.0.1';
        const text_domain = 'q-willow'; // for translation ##
		// static $debug = false; // global debugging, normally false, as individual plugins can control local level debugging ##
		
		protected static

			$extend = [] // allow apps to extend context methods ##

		;

		public static

			// passed args ##
			$args 	= [
				'fields'	=> []
			],

			$output 	= null, // return string ##
			$fields 	= null, // array of field names and values ##
			$markup 	= null, // array to store passed markup and extra keys added by formatting ##
			$log 		= null, // tracking array for feedback ##
			$buffer 	= null, // for buffering... ##
			// $buffering 	= false, // for buffer switch... ##

			// default args to merge with passed array ##
			$args_default = [
				'config'            => [
					'run'           => true, // don't run this item ##
					'debug'         => false, // don't debug this item ##
					'return'        => 'echo', // default to echo return string ##
				],
			]

		;

		protected static

			// frontend pre-processor callbacks to update field values ##
			$callbacks = [
				'get_posts'         => [ // standard WP get_posts()
					'namespace'     => 'global', // global scope to allow for namespacing ##
					'method'        => '\get_posts()',
					'args'          => [] // default - can be edited via global and specific filters ##
				],
			],

			// value formatters ##
			$format = [
				// Arrays could be collection of WP Post Objects OR repeater block - so check ##
				'array'             => [
					'type'          => 'is_array',
					'method'        => 'format_array'
				],
				'post_object'       => [
					'type'          => 'is_object',
					'method'        => 'format_object'
				],
				'integer'           => [
					'type'          => 'is_int',
					'method'        => 'format_integer'
				],
				'string'            => [
					'type'          => 'is_string',
					'method'        => 'format_text',
				],
			],
			
			// allowed field types ##
			$type = [
				'repeater'       	=> [],
				'post'       		=> [],
				'category'       	=> [],
				'taxonomy'       	=> [],
				'src'             	=> [], // @todo... this is too specific ##
				'media'       		=> [],
				'author'       		=> [],
			],

			// standard fields to add to wp_post objects
			$type_fields = [

				// standard WP fields ##
				'post_ID',
				'post_title',
				'post_content',
				'post_excerpt',
				'post_permalink',
				'post_is_sticky',
				
				// dates ##
				'post_date', // formatted ##
				'post_date_human', // human readable ##
				
				// category ##
				'category_name', 
				'category_permalink',
				
				// author ##
				'author_permalink',
				'author_name',
				
				// image src ##
				'src', // @todo.. needs to merge into media ##
				// 'media', 

			]
			
		;

		// tags ##
		protected static 

			/* define template delimiters */
			// based on Mustache, but not the same... https://github.com/bobthecow/mustache.php/wiki/Mustache-Tags
			$tags = [

				// variables ##
				'variable'		=> [
					'open' 		=> '{{ ', // open ## 
					'close' 	=> ' }}', // close ##
				],

				// parameters / arguments ##
				'argument'		=> [
					'open' 		=> '( ', // open ## 
					'close' 	=> ' )', // close ##
				],

				// flags ##
				'flag'		=> [
					'open' 		=> '[ ', // open ## 
					'close' 	=> ' ]', // close ##
				],
				
				// section ##
				'section'		=> [
					'open' 		=> '{{# ', // open ##
					'close' 	=> ' }}', // close ##
					'end'		=> '{{/#}}' // end statement ##
				],

				// function -- also, an unescaped variable -- @todo --- ##
				'function'		=> [
					'open' 		=> '{{{ ', // open ## 
					'close' 	=> ' }}}', // close ##
				],

				// partial ##
				'partial'		=> [
					'open' 		=> '{{> ', // open ## 
					'close' 	=> ' }}', // close ##
				],

				// comment ##
				'comment'		=> [
					'open' 		=> '{{! ', // open ## 
					'close' 	=> ' }}', // close ##
				],

			]

		;

        /**
         * Creates or returns an instance of this class.
         *
         * @return  Foo     A single instance of this class.
         */
        public static function get_instance()
        {

            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;

        }


        /**
         * Instatiate Class
         *
         * @since       0.2
         * @return      void
         */
        private function __construct()
        {

            // activation ##
            register_activation_hook( __FILE__, array ( $this, 'register_activation_hook' ) );

            // deactvation ##
            register_deactivation_hook( __FILE__, array ( $this, 'register_deactivation_hook' ) );

            // set text domain ##
            add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );

            // load libraries ##
            self::load_libraries();

        }




        /**
         * plugin activation
         *
         * @since   0.2
         */
        public function register_activation_hook()
        {

            $q_options = array(
                'configured'    => true
                ,'version'      => self::version
            );

            // init running, so update configuration flag ##
            add_option( 'q_willow', $q_options, '', true );

        }


        /**
         * plugin deactivation
         *
         * @since   0.2
         */
        public function register_deactivation_hook()
        {

            // de-configure plugin ##
            delete_option('q_willow');

        }


        /**
         * Load Text Domain for translations
         *
         * @since       1.7.0
         *
         */
        public function load_plugin_textdomain()
        {

            // set text-domain ##
            $domain = self::text_domain;

            // The "plugin_locale" filter is also used in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $domain );

            // try from global WP location first ##
            load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );

            // try from plugin last ##
            load_plugin_textdomain( $domain, FALSE, plugin_dir_path( __FILE__ ).'library/languages/' );

        }



        /**
         * Get Plugin URL
         *
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_url( $path = '' )
        {

            #return plugins_url( ltrim( $path, '/' ), __FILE__ );
            return plugins_url( $path, __FILE__ );

        }


        /**
         * Get Plugin Path
         *
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_path( $path = '' )
        {

            return plugin_dir_path( __FILE__ ).$path;

		}




        /**
         * Check for required breaking dependencies
         *
         * @return      Boolean
         * @since       1.0.0
         */
        public static function has_dependencies()
        {

            // check for what's needed ##
            if (
                ! class_exists( 'Q' )
            ) {

                helper::log( 'e:>Q Willow requires Q to run correctly..' );

                return false;

            }

            // ok ##
            return true;

        }



        /**
        * Load Libraries
        *
        * @since        2.0
        */
		private static function load_libraries()
        {

			// check for dependencies, required for UI components - admin will still run ##
            if ( ! self::has_dependencies() ) {

                return false;

            }

            // methods ##
			require_once self::get_plugin_path( 'library/core/_load.php' );

			// parsers ##
			require_once self::get_plugin_path( 'library/parse/_load.php' );

			// tags ##
			require_once self::get_plugin_path( 'library/tags/_load.php' );

			// render ##
			require_once self::get_plugin_path( 'library/render/_load.php' );

			// context ##
			require_once self::get_plugin_path( 'library/context/_load.php' );

			// output buffer ##
			require_once self::get_plugin_path( 'library/buffer/_load.php' );

        }


    }

}
