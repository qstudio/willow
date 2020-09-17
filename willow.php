<?php

/*
 * Willow is a logic-less, procedural, semantic template engine
 *
 * @package         willow
 * @author          Q Studio <social@qstudio.us>
 * @license         GPL-2.0+
 * @link            http://qstudio.us/
 * @copyright       2020 Q Studio
 *
 * @wordpress-plugin
 * Plugin Name:     Willow
 * Plugin URI:      https://www.qstudio.us
 * Description:     Willow is a Simple, logic-less, procedural semantic template engine 
 * Version:         1.4.5
 * Author:          Q Studio
 * Author URI:      https://www.qstudio.us
 * License:         GPL
 * Copyright:       Q Studio
 * Class:           willow
 * Text Domain:     willow
 * Domain Path:     /languages
 * GitHub Plugin URI: qstudio/willow
*/

// quick check :) ##
defined( 'ABSPATH' ) OR exit;

/* Check for Class */
if ( ! class_exists( 'willow' ) ) {

	// activation ##
	register_activation_hook( __FILE__, array ( 'willow', 'register_activation_hook' ) );
			
	// deactvation ##
	register_deactivation_hook( __FILE__, array ( 'willow', 'register_deactivation_hook' ) );

    // instatiate plugin via WP plugins_loaded ##
    add_action( 'plugins_loaded', array ( 'willow', 'get_instance' ), 1 );

    // Q Class ##
    class willow {

        // Refers to a single instance of this class. ##
        private static $instance = null;

        // Plugin Settings
        const version = '1.4.5';
        const text_domain = 'willow'; // for translation ##
		
		protected static

			$extend = [] // allow apps to extend context methods ##

		;

		public static

			// debugging control ##
			$debug = false,

			// passed args ##
			$args 	= [
				'fields'	=> []
			],

			$output 	= null, // return string ##
			$fields 	= null, // array of field names and values ##
			$fields_map = [], // field map for variables which are altered by filters ##
			$markup 	= null, // array to store passed markup and extra keys added by formatting ##
			$log		= null, // tracking array for feedback ##
			$hash 		= null, // willow hash log, with data about calling method ##

			// default args to merge with passed array ##
			$args_default = [
				'config'            => [
					'run'           => true, // don't run this item ##
					'debug'         => false, // don't debug this item ##
					'return'        => 'echo', // default to echo return string ##
				],
			],

			// BUFFER, -- @TODO, perhaps can be protected ##
			$buffer_args 	= null,
			$buffer_markup 	= null,
			$buffer_map		= [] // buffer markup map ##

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
				'meta'				=> [],
			],

			// standard fields to add to wp_post arrays
			$wp_post_fields = [

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

				// post meta --NEW ##
				'meta', // allow for field_name.meta.meta_key

			],

			// standard fields to add to wp_term arrays
			$wp_term_fields = [

				// standard WP fields ##
				'term_ID',
				'term_title',
				'term_slug',
				'term_parent',
				'term_permalink',
				'term_taxonomy',
				'term_description',
				'term_parent',
				'term_count'

			],
			
			/* define template delimiters */
			// based on Mustache, but not the same... https://github.com/bobthecow/mustache.php/wiki/Mustache-Tags
			$tags = [

				// willow tag 
				'willow'		=> [
					'open' 		=> '{~ ', // open ## 
					'close' 	=> ' ~}', // close ##
				],

				// variables ##
				'variable'		=> [
					'open' 		=> '{{ ', // open ## 
					'close' 	=> ' }}', // close ##
				],

				// arguments / parameters ##
				'argument'		=> [
					'open' 		=> '{+ ', // open ## 
					'close' 	=> ' +}', // close ##
				],

				// scope for context->task ##
				'scope'		=> [
					'open' 		=> '{: ', // open ## 
					'close' 	=> ' :}', // close ##
				],

				// flags ##
				'flag'			=> [
					'open' 		=> '[ ', // open ## 
					'close' 	=> ' ]', // close ##
				],
				
				// loops ##
				'loop'			=> [
					'open' 		=> '{@ ', // open ##
					'close'		=> ' @}' // close ##
				],

				// php function ##
				'php_function'		=> [
					'open' 		=> '{% ', // open ## 
					'close' 	=> ' %}', // close ##
				],

				// php variable ##
				'php_variable'	=> [
					'open' 		=> '{# ', // open ## 
					'close' 	=> ' #}', // close ##
				],

				// partial ##
				'partial'		=> [
					'open' 		=> '{> ', // open ## 
					'close' 	=> ' <}', // close ##
				],

				// comment ##
				'comment'		=> [
					'open' 		=> '{! ', // open ## 
					'close' 	=> ' !}', // close ##
				],

			],

			// post-processing of tags and variables ##
			$filter		= null, // array that holds all collected filters to be processed ##

			// list of allowed filters ##
			// used in tags like [ esc_html, strtoupper ] can be chained ##
			$filters 	= [
				// escape ##
				'esc_html',
				'esc_attr',
				'esc_url',
				'urlraw',
				'urlencode',
				'esc_js',
				'esc_textarea',

				// format ##
				'strtoupper',
				'strtolower',
				'strip_tags',
				'nl2br',
				'wpautop',
				'intval',
				'absint',

				// sanitize ##
				'sanitize_title_with_dashes',
				'sanitize_title',
				'sanitize_email',
				'sanitize_key',
				'sanitize_file_name',
				'sanitize_html_class',
				'sanitize_text_field',
				'sanitize_text_field',
				'sanitize_user'
			], 

			// load filters once, with callback filter to allow for changes - this property tracks the load status ##
			$filters_filtered = false,
			// $filter_hash

			// allowed flags - currently not referenced or filtered, but might be in future release ##
			$flags 	= [
				
				'buffer', // php_function, willow ##
				'return', // php_function ##
				'array', // arguments ##
				'html', // comments ##
				'php', // comments ##

			]

			// load flags once, with callback filter to allow for changes - this property tracks the load status ##
			// $flags_filtered = false

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

            // set text domain ##
            add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );

            // load libraries ##
			self::load_libraries();
			
			// check debug settings ##
			add_action( 'plugins_loaded', array( get_class(), 'debug' ), 11 );

        }




        /**
         * plugin activation
         *
         * @since   0.2
         */
        public static function register_activation_hook()
        {

			if ( ! current_user_can( 'activate_plugins' ) ) {
				
				return;

			}

	        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    	    check_admin_referer( "activate-plugin_{$plugin}" );

            $q_options = array(
                'configured'    => true
                ,'version'      => self::version
            );

            // init running, so update configuration flag ##
			add_option( 'willow', $q_options, '', true );

			// Get path to main .htaccess for WordPress ##
			$htaccess = trailingslashit(ABSPATH).'.htaccess';

			/*
			# BEGIN Willow
			<Files ~ "\.willow$">
			Order allow,deny
			Deny from all
			</Files>
			# END Willow
			*/

			$lines = [];
			$lines[] = "<Files ~ '\.willow$'>";
			$lines[] = "Order allow,deny";
			$lines[] = "Deny from all";
			$lines[] = "</Files>";

			\insert_with_markers( $htaccess, "Q ~ Willow", $lines );
			
        }


        /**
         * plugin deactivation
         *
         * @since   0.2
         */
        public static function register_deactivation_hook()
        {

			if ( ! current_user_can( 'activate_plugins' ) ) {
			
				return;
			
			}

        	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        	check_admin_referer( "deactivate-plugin_{$plugin}" );

            // de-configure plugin ##
            delete_option('willow');

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
         * We want the debugging to be controlled in global and local steps
         * If Q debug is true -- all debugging is true
         * else follow settings in Q, or this plugin $debug variable
         */
        public static function debug()
        {

            // define debug ##
            self::$debug = 
                ( 
                    class_exists( 'Q' )
                    && true === \Q::$debug
                ) ?
                true :
                self::$debug ;

            // test ##
            // helper::log( 'Q exists: '.json_encode( class_exists( 'Q' ) ) );
            // helper::log( 'Q debug: '.json_encode( \Q::$debug ) );
            // helper::log( json_encode( self::$debug ) );

            return self::$debug;

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
        * Load Libraries
        *
        * @since        2.0
        */
		private static function load_libraries()
        {

            // methods ##
			require_once self::get_plugin_path( 'library/core/_load.php' );

			// getters ##
			require_once self::get_plugin_path( 'library/get/_load.php' );

			// view ##
			require_once self::get_plugin_path( 'library/view/_load.php' );

			// strings ##
			require_once self::get_plugin_path( 'library/strings/_load.php' );

			// plugins ##
			require_once self::get_plugin_path( 'library/plugin/_load.php' );

			// parsers ##
			require_once self::get_plugin_path( 'library/parse/_load.php' );

			// tags ##
			require_once self::get_plugin_path( 'library/tags/_load.php' );

			// render ##
			require_once self::get_plugin_path( 'library/render/_load.php' );

			// context ##
			require_once self::get_plugin_path( 'library/context/_load.php' );

			// filters ##
			require_once self::get_plugin_path( 'library/filter/_load.php' );

			// output ##
			require_once self::get_plugin_path( 'library/buffer/_load.php' );

        }


    }

}
