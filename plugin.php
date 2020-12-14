<?php

namespace willow;

// import classes ##
use willow;
use willow\plugin as plugin;
use willow\core\helper as h;

// If this file is called directly, Bulk!
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/*
* Main Plugin Class
*/
final class plugin {

    /**
     * Instance
     *
     * @var     Object      $instance
     */
	private static $instance;

	public static 
	
		// current tag ##
		$_version = '2.0.3'
	
	;

	/**
	 * Props
	 * 
	 * @var		Array		$props
	*/
	public 

		// debugging control ##
		$_debug = \WP_DEBUG, // boolean --> control debugging / minification etc ##

		// log object ##
		// $log = null,

		// helper object ##
		$helper = null,

		// tags ##
		$tags = null,

		// extend object ##
		$extend = null,

		// log var ##
		// $_log = null,

		// config object ##
		$config = null,

		// filter object ##
		$filter = null,

		// parser objects ##
		$parse = false,

		// render objects ##
		$render = false,

		// filter methods ##
		$filter_method = false,

		// type handler objects ##
		$type = false

	;

	protected

		// get object ##
		$get = null,

		// allow apps to extend context methods ##
		$_extend = [],

		// passed args ##
		$_args 	= [
			'fields'	=> []
		],

		$_output 	= null, // return string ##
		$_fields 	= [], // array of field names and values ##
		$_markup 	= null, // array to store passed markup and extra keys added by formatting ##
		$_hash 		= null, // willow hash log, with data about calling method ##

		// share willow match across parsers ##
		// $_willow_match = false,

		// used to map original {: scope :} value to unqiue {: scope_hash :} ##
		// $scope_count = 0,
		$_scope_map = [], 

		// default args to merge with passed array ##
		$_args_default = [
			'config'            => [
				'run'           => true, // don't run this item ##
				'debug'         => false, // don't debug this item ##
				'return'        => 'echo', // default to echo return string ##
			],
		],

		// BUFFER ##
		$_buffer_args 			= null,
		$_buffer_markup 		= null,
		$_buffer_map			= [], // buffer markup map ##
		$_markup_template		= '', // original markup from viewed template

		// frontend pre-processor callbacks to update field values ##
		$_callbacks = [
			'get_posts'         => [ // standard WP get_posts()
				'namespace'     => 'global', // global scope to allow for namespacing ##
				'method'        => '\get_posts()',
				'args'          => [] // default - can be edited via global and specific filters ##
			],
		],

		// value formatters ##
		$_format = [
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
				'method'        => 'format_string',
			],
		],
		
		// allowed field types ##
		$_type = [
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
		$_wp_post_fields = [

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

			// highlight -- NEW ##
			'highlight'

		],

		// standard fields to add to wp_term arrays
		$_wp_term_fields = [

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
		$_tags = [

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

			// translatable string ##
			// https://developer.wordpress.org/themes/functionality/internationalization/
			// https://codex.wordpress.org/I18n_for_WordPress_Developers
			'i18n'			=> [
				'open' 		=> '{_ ', // open ## 
				'close' 	=> ' _}', // close ##
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
		$_filter		= [], // array that holds all collected filters to be processed ##

		// list of allowed filters ##
		// used in tags like [ esc_html, strtoupper ] can be chained ##
		// @todo - move all to filter, defined inside Willow, but which can be easily hooked into to extend
		$_filters 	= [

			// escape ##
			'esc_html',
			'esc_attr',
			'esc_url',
			'urlraw',
			'urlencode',
			'esc_js',
			'esc_textarea',
			'html_entity_decode',

			// format ##
			'strtoupper',
			'strtolower',
			'strip_tags',
			'nl2br',
			'wpautop',
			'intval',
			'absint',
			'w__substr_first',
			'w__substr_last',
			'w__array_to_string', // try to convert an array to a string ##

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
		$_filters_filtered = null,

		// allowed flags - currently not referenced or filtered, but might be in future release ##
		$_flags 	= [
			
			'debug', // short cut to debug a single Willow
			'buffer', // php_function, willow ##
			'return', // php_function ## --> DEFAULT --> tells Willow to update template with return value from function, if any ##
			'null', // php_function ## -- this forces the function return value to not update the template markup ##
			'array', // arguments ##
			'html', // comments ##
			'php', // comments ##

		],

		// load flags once, with callback filter to allow for changes - this property tracks the load status ##
		// $flags_filtered = false

		$_regex = [
			'clean'	=>"/[^A-Za-z0-9_]/" // clean up string to alphanumeric + _
			// @todo.. move other regexes here ##

		],

		// per match flags ##
		$_flags_willow = false,
		$_flags_argument = false,
		$_flags_variable = false,
		$_flags_comment = false,
		$_flags_php_function = false,
		$_flags_php_variable = false,

		// $parse_args = false,
		$_parse_context = false,
		$_parse_task = false

	;

    /**
     * Initiator
     *
     * @since   0.0.2
     * @return  Object    
     */
    public static function get_instance() {

        // object defined once --> singleton ##
        if ( 
            isset( self::$instance ) 
            && NULL !== self::$instance
        ){

            return self::$instance;

        }

        // create an object, if null ##
        self::$instance = new self;

        // store instance in filter, for potential external access ##
        \add_filter( __NAMESPACE__.'/instance', function() {

            return self::$instance;
            
        });

        // return the object ##
        return self::$instance; 

    }

    /**
     * Class constructor to define object props --> empty
     * 
     * @since   0.0.1
     * @return  void
    */
    private function __construct() {

        // retrieve args from filter - allowing for manipulation ##
        // $args = \apply_filters( __NAMESPACE__.'/args', NULL );

        // store passed args and merge with default class props ##
        // $this->props = array_merge( $this->props, $args );

		// Log::write( $this->props );
		
	}
	
	public function factory( $plugin ){

		// kick off extend and store object ##
		$plugin->set( 'extend', new willow\context\extend( $plugin ) );

		// kick off filter and store object ##
		$plugin->set( 'filter', new willow\core\filter( $plugin ) );

		// build helper object ##
		$plugin->set( 'helper', new willow\core\helper( $plugin ) );

		// kick off tags and store object ##
		$plugin->set( 'tags', new willow\core\tags( $plugin ) );

		// prepare filters ##
		$plugin->set( 'filter_method', new willow\filter\method( $plugin ) );

	}

    /**
     * Get stored object property
	 * 
     * @todo	Make this work with single props, not from an array 
     * @param   $key    string
     * @since   0.0.2
     * @return  Mixed
    */
    public function get( $key = null ) {

        // check if key set ##
        if( is_null( $key ) ){

            // return false;
			// return false;
			return self::get_instance();

        }
        
        // return if isset ##
        return $this->{$key} ?? false ;

    }

    /**
     * Set stored object properties 
     * 
	 * @todo	Make this work with single props, not from an array
     * @param   $key    string
     * @param   $value  Mixed
     * @since   0.0.2
     * @return  Mixed
    */
    public function set( $key = null, $value = null ) {

        // sanity ##
        if( 
            is_null( $key ) 
        ){

            return false;

        }

        // w__log( 'prop->set: '.$key.' -> '.$value );

        // set new value ##
		return $this->{$key} = $value;

    }

    /**
     * Load Text Domain for translations
     *
     * @since       0.0.1
     * @return      Void
     */
    public function load_plugin_textdomain(){

        // The "plugin_locale" filter is also used in load_plugin_textdomain()
        $locale = apply_filters( 'plugin_locale', \get_locale(), 'willow' );

        // try from global WP location first ##
        \load_textdomain( 'willow', WP_LANG_DIR.'/plugins/willow-'.$locale.'.mo' );

        // try from plugin last ##
        \load_plugin_textdomain( 'willow', FALSE, \plugin_dir_path( __FILE__ ).'src/languages/' );

    }

    /**
     * Plugin activation
     *
     * @since   0.0.1
     * @return  void
     */
    public static function activation_hook(){

        // Log::write( 'Plugin Activated..' );

        // check user caps ##
        if ( ! \current_user_can( 'activate_plugins' ) ) {
            
            return;

        }

        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        \check_admin_referer( "activate-plugin_{$plugin}" );

        // store data about the current plugin state at activation point ##
        $config = [
            'configured'            => true , 
            'version'               => self::$_version ,
            'wp'                    => \get_bloginfo( 'version' ) ?? null ,
			'timestamp'             => time(),
		];
		
		// Get path to main .htaccess for WordPress ##
		$htaccess = \trailingslashit(ABSPATH).'.htaccess';

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

		\insert_with_markers( $htaccess, "Willow", $lines );

        // Log::write( $config );

        // activation running, so update configuration flag ##
        \update_option( 'plugin_willow', $config, true );

    }

    /**
     * Plugin deactivation
     *
     * @since   0.0.1
     * @return  void
     */
    public static function deactivation_hook(){

        // Log::write( 'Plugin De-activated..' );

        // check user caps ##
        if ( ! \current_user_can( 'activate_plugins' ) ) {
        
            return;
        
        }

        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        \check_admin_referer( "deactivate-plugin_{$plugin}" );

        // de-configure plugin ##
        \delete_option('plugin_willow');

        // clear rewrite rules ##
        \flush_rewrite_rules();

	}
	
	/**
	 * We want the debugging to be controlled in global and local steps
	 * If Q debug is true -- all debugging is true
	 * else follow settings in Q, or this plugin $debug variable
	 */
	public function debug(){

		// define debug ##
		$this->_debug = 
			( 
				class_exists( 'Q' )
				&& true === \Q::$debug
			) ?
			true :
			$this->_debug;

		// test ##
		// w__log( 'Q exists: '.json_encode( class_exists( 'Q' ) ) );
		// w__log( 'Q debug: '.json_encode( \Q::$debug ) );
		// w__log( json_encode( self::$debug ) );

		return $this->_debug;

	}

    /**
     * Get Plugin URL
     *
     * @since       0.1
     * @param       string      $path   Path to plugin directory
     * @return      string      Absoulte URL to plugin directory
     */
    public function get_plugin_url( $path = '' ){

        return \plugins_url( $path, __FILE__ );

    }

    /**
     * Get Plugin Path
     *
     * @since       0.1
     * @param       string      $path   Path to plugin directory
     * @return      string      Absoulte URL to plugin directory
     */
    public function get_plugin_path( $path = '' ){

        return \plugin_dir_path( __FILE__ ).$path;

    }
    
}
