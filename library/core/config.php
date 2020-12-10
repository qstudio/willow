<?php

namespace willow\core;

// import ## 
use willow;

class config {

	private
		// loaded config ##
		$has_config = false,
		$delete_config = true,
		$cache_files = [], // track array of files loaded, with ful path, so we can remove duplicates ##
		$config = [],
		$cache = [],
		$config_args = [], // passed args ##
		$get_child_path = false, // check for child theme path method ##
		$get_parent_path = false, // check for parent theme path method ##
		$file_extensions = [], // .php / .willow allow file types ##
		$willow_path = '', // default load location ##
		$template = '', // currently viewed template ##
		$properties_loaded = false, // track and load just once ##
		$global_loaded = false // track and load just once ##
	;

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
	function __construct( $plugin = null ){

		// Log::write( $plugin );

        // grab passed plugin object ## 
		$this->plugin = $plugin;
		
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
            || ! ( $this->plugin instanceof \willow\plugin ) 
        ) {

            error_log( 'Error in object instance passed to '.__CLASS__ );

            return false;
        
		}
		
		// error_log( 'Helper hooks run..' );

		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'parent'; // context source ##
				return $this->filter( $args, $source );
			}
		, 1, 1 );

		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'extend'; // context source ##
				return $this->filter( $args, $source );
			}
		, 100, 1 );
		
		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'child'; // context source ##
				return $this->filter( $args, $source );
			}
		, 1000, 1 );

		// load saved config ##
		\add_action( 'wp', [ $this, 'get_cache' ], 10 );

		// save stored config to file ##
		\add_action( 'shutdown', [ $this, 'set_cache' ], 100000 );

		// hook into Fastest Cache clear routine -- @todo, this should be in a plugin filter ##
		\add_action( 'wpfc_delete_cache', [ $this, 'delete_from_fastest_cache' ], 1 );

	}

	public function properties(){

		// cache ##
		if ( $this->properties_loaded ) return false;

		// check for child theme path method ##
		$this->get_child_path = method_exists( 'q\theme\plugin', 'get_child_path' );

		// check for parent theme path method ##
		$this->get_parent_path = method_exists( 'q\theme\plugin', 'get_parent_path' );

		// config file extension ##
		$this->file_extensions = \apply_filters( 'willow/config/load/ext', [ 
			'.willow',
			'.php', 
		] );

		// config file path ( h::get will do fallback checks form child theme, parent theme, plugin + Q.. )
		$this->willow_path = \apply_filters( 'willow/config/load/path', 'willow/' );

		// template ##
		$this->template = willow\core\method::template() ? willow\core\method::template() : '404';

		// update tracker ##
		$this->properties_loaded = true;

		// done ##
		return;

	}

	/**
	 * Save config file
	 * 
	 * @aince 4.1.0
	*/
	public function set_cache(){

		// do not save file from admin, as it will be incomplete ##
		if( 
			\is_admin() 
			|| \wp_doing_ajax()
		){ 
		
			// w__log( 'd:>Attempt to save config from admin or AJAX blocked' );

			return false; 
		
		}

		// if theme debugging, then load from single config files ##
		if ( $this->plugin->_debug ) { 

			// w__log('d:>Deubbing, so we do not need to resave __q.php.' );
			// w__log( 't:>How to dump file / cache and reload from config files, other than to delete __q.php??' );

			return false; 

		}

		if ( $this->has_config ){ 
		
			w__log('d:>We do not need to resave the file, as it already exists' );
			// w__log( 't:>How to dump file / cache and reload from config files, other than to delete __q.php??' );

			return false; 
		
		}

		// cache in DB ##
		\set_site_transient( 'willow_config', $this->config, 24 * HOUR_IN_SECONDS );

		// w__log('d:>saved config to DB...' );

		// return true;

		if ( method_exists( 'q\theme\plugin', 'get_child_path' ) ){ 

			// w__log( 'e:>Q Theme class not available, perhaps this function was hooked too early?' );

			willow\core\method::file_put_array( \q\theme\plugin::get_child_path( '/__q.php' ), $this->config );

		}

		return true;

	}

	/**
	 * Load config file
	 * 
	 * @aince 4.1.0
	*/
	public function get_cache(){

		// if theme debugging, then load from indiviual config files ##
		if ( $this->plugin->get('_debug') ) { 

			// w__log( 'd:>Theme is debugging, so load from individual context files...' );

			// load ##
			if ( $this->delete_config ) {

				$this->delete_cache();

			}

			return false;

		}

		// w__log( 'd:>Child theme method found, so trying to load data from __q.php' );
		// w__log( \get_site_transient( 'willow_config' ) );
		if ( 
			$array = \get_site_transient( 'willow_config' )
		) {

			// log ##
			// w__log( 'd:>DB Transient Found...' );

			if( is_array( $array ) ) {

				// store array in object cache ##
				$this->config = $array;

				// update flag ##
				$this->has_config = true;

				// log ##
				// w__log( 'd:>Theme NOT debugging ( production mode ) -- so loaded config data DB' );

				// good ##
				return true;

			}

		}

		w__log( 'e:>Cache check to DB is empty, so new cache being generated from here..' );

		return false;

	}

	/**
	 * Hook into Fastest Cache delete routine - as an example how to clear Willow cache from third party plugin
	 * 
	 * @todo	Should be moved to a plugin->filter
	 * @since 	1.4.7
	 * @return 	void
	*/
	public function delete_from_fastest_cache(){

		// w__log( 'e:>Delete Willow cache from Fastest Cache hook...' );
		
		$this->delete_cache();

	}

	public function delete_cache(){

		\delete_site_transient( 'willow_config' );

		// w__log( 'e:>Deleted config cache from DB...' );

		if ( method_exists( 'q\theme\plugin', 'get_child_path' ) ){ 

			$file = \q\theme\plugin::get_child_path('/__q.php');

			if ( $file && file_exists( $file ) ) {

				unlink( $file );

				// w__log( 'd:>...also deleting __q.php, so cache is clear' );

			}

		}

		// update tracker ##
		$this->delete_config = false;

	}

	public function global(){

		// cache ##
		if ( $this->global_loaded ){ return false; }

		// file ##
		$file = $this->plugin->get_plugin_path( 'library/willow/global.php' );

		// cache ##
		$cache_key = 'willow_global_php';

		// send file to config loader ##
		$this->load( $file, $cache_key );

		// save file to cache ##
		$this->cache_files[] = $file;

		// update tracker ##
		$this->global_loaded = true;

	}
	
	/**
	 * Get configuration files
	 *
	 * @used by filter willow/config/get/all
	 *
	 * @return		Array $array -- must return, but can be empty ##
	 */
	public function filter( $args = null, $source = null ) {

		// w__log( 'e:>SOURCE: '.$source );

		// w__log( $this->extend );

		// fill properties ##
		$this->properties();

		// load global config - once ##
		$this->global();

		if ( 
			$this->has_config 
			&& isset( $this->config[ $args['context'] ] ) 
			&& isset( $this->config[ $args['context'] ][ $args['task'] ] ) 
		){ 
			
			// w__log( 'd:>Config loading from cache file: '.$args['context'].'->'.$args['task'] ); 
			// w__log( $this->config );
			
			return $args; 
		
		}

		// we got this far, so we need to re save the config file ##
		$this->has_config = false;

		// sanity ##
		if ( 
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] ) 
			|| ! isset( $args['task'] )
			// || is_null( $source )
		){

			// config is loaded by context or process, so we need one of those to continue ##
			w__log( 'e:>Error in passed args, $context and $task are required.' );

			// kick back args for future filters ##
			return $args;

		}

		// array of config files to load -- key ( for cache ) + value ##
		$array = [

			// template~context~task ##
			$this->template.'__'.$args['context'].'__'.$args['task'] => $this->template.'~'.$args['context'].'~'.$args['task'],

			// template/context~task in sub directory ##
			// view\is::get().'__'.$args['context'].'__'.$args['task'].'_dir' => view\is::get().'/'.$args['context'].'~'.$args['task'],

			// context~task ##
			$args['context'].'__'.$args['task'] => $args['context'].'~'.$args['task'],

			// context/context~task -- in sub directory ##
			$args['context'].'__'.$args['task'].'_dir' => $args['context'].'/'.$args['context'].'~'.$args['task'],

			// context specific -> run, debug, return etc  ##
			$args['context'] => $args['context'],

			// global controllers -> run, debug, return etc.. ##
			'global' => 'global'

		];

		// filter options ##
		$array = \apply_filters( 'willow/config/load/array', $array );

		// w__log( 'd:>looking for source: '.$source );
		if( 'extend' == $source ) {

			// w__log( 'd:>looking for source: '.$source );
			// get _extend var ##
			$_extend = $this->plugin->get( '_extend' );

			if(
				! empty( $_extend )
			){

				// new empty array ##
				$extended_lookups = [];

				foreach( $_extend as $k => $v ){

					// w__log( $v );
					if( $v['lookup'] ){

						// add location path ##
						$extended_lookups[] = $_extend[$k];

					}

				}

			}
		}

		// w__log( $extended_lookups );

		// check for child theme path method ##
		// $get_child_path = method_exists( 'q_theme', 'get_child_path' );

		// check for parent theme path method ##
		// $get_parent_path = method_exists( 'q_theme', 'get_parent_path' );

		// loop over allowed extensions ##
		// @TODO - this could be a whole load more effecient... ##
		foreach( $this->file_extensions as $ext ) {

			// loop over array values ##
			foreach( $array as $k => $v ){

				switch( $source ){

					// child context lookup ##
					case "child" :

						// check for theme method ##
						if ( ! $this->get_child_path ){ break; }

						// look for file ##
						$file = \q\theme\plugin::get_child_path( '/library/'.$this->willow_path.$v.$ext );

						// build cache key ##
						$cache_key = 'child_'.$v.'_'.str_replace( '.', '', $ext );

						// w__log( 'd:>child->looking up file: '.$file );

					break  ;

					// parent context lookup ## 
					case "parent" :

						// check for theme method ##
						if ( ! $this->get_parent_path ){ break; }

						// look for file ##
						$file = \q\theme\plugin::get_parent_path( '/library/'.$this->willow_path.$v.$ext );

						// build cache key ##
						$cache_key = 'parent_'.$v.'_'.str_replace( '.', '', $ext );
						// w__log( 'd:>parent->looking up file: '.$file );

					break  ;

					case "extend" :

						if( ! empty( $extended_lookups ) ){

							foreach( $extended_lookups as $kl => $vl ){

								// w__log( $v );

								if ( $vl['context'] == $args['context'] ){

									if ( false !== $key = array_search( $args['task'], $vl['methods'] ) ) {

										$file = $vl['lookup'].$args['context'].'/'.$args['context'].'~'.$args['task'].$ext;

										// w__log( 'e:>Extend File: '.$file );
										$cache_key = $source.'_'.$args['context'].'_'.$args['task'].'_'.str_replace( '.', '', $ext );

									}

								}

							}

						}

					break ;

					// global lookup ~~> willow/FILE.ext
					// REMOVED, as offers little additional config and is merged elsewhere ##
					/*
					case "global" :
					default :

						$file = h::get( $this->willow_path.$v.$ext, 'return', 'path' );
						$file = $this->get_plugin_path('/library');

						$cache_key = 'global_'.$v.'_'.str_replace( '.', '', $ext );
						// w__log( 'd:>global->looking up file: '.$file );

					break ;
					*/

				}

				if ( 
					isset( $file )
					&& file_exists( $file )
					&& is_file( $file )
				){

					// skip file, if loaded already -- 
					// but NO, as child needs to override parent.. ##
					if ( in_array( $file, $this->cache_files ) ) {

						// w__log( 'd:>File: '.$file.' already loaded' );

						continue;

					}

					// build cache key ##
					if ( ! $cache_key ) {

						// w__log( 'e:>Cache key missing for file: '.$file );
					
						$cache_key = 
							! is_null( $source ) ? 
							$k.'_'.$source.'_'.willow\core\method::file_extension( $file ) : 
							$k.'_'.willow\core\method::file_extension( $file ) ;

					}

					// w__log( 'd:>Loading config file: '.$file.' cache key: '.$cache_key );

					// send file to config loader ##
					$this->load( $file, $cache_key );

					// save file to cache ##
					$this->cache_files[] = $file;

				}

			}

		}

		// kick back args for future filters ##
		return $args;

	}

	/**
	 * Get stored config setting, merging in any new of changed settings from extensions
	 */
	public function get( $args = null ) {

		// without $context or $task, we can't get anything specific, so just run main filter ##
		// \apply_filters( 'willow/config/get/all', $this->config, isset( $args['field'] ) ?: $args['field'] );

		// shortcut.. allow for string passing, risky.. ##
		if(
			is_string( $args )
		){

			// pre format ##
			if ( true === strpos( $args, '__' ) ){

				$explode = explode( '__', $args );

				// make an array ##
				$args = [];
				if ( isset( $explode[0] ) ) $args['context'] = $explode[0];
				if ( isset( $explode[1] ) ) $args['task'] = $explode[1];
				if ( isset( $explode[2] ) ) $args['property'] = $explode[2];

			}

		}

		// capture passed args ##
		$this->config_args = $args; // capture args ##

		// sanity ##
		if ( 
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] ) 
			|| ! isset( $args['task'] )
		){

			// get caller ##
			$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			// config is loaded by context or process, so we need one of those to continue ##
			w__log( 'e:>Q -> '.$backtrace.': config is loaded by context and process, so we need both of those to continue' );

			return false;

		}

		// start blank ##
		$return = false;

		// run filter passing lookup args to allow themes and plugins to control config ##
		$this->run_filter();

		// define property for logging ##
		$property = $args['context'].'::'.$args['task'] ;

		// w__log('d:>Looking for $config property: '.$property );
		// w__log( ->config );

		if ( 
			! isset( $this->config[ $args['context'] ] ) 
			|| ! isset( $this->config[ $args['context'] ][ $args['task'] ] ) 
		){
	
			// w__log( 'd:>config not available : "'.$property.'"' );

			// continue;

		} else {

			// return single property ##
			if ( 
				isset( $args['property'] ) 
				&& isset( $this->config[ $args['context'] ][ $args['task'] ][ $args['property'] ] )
			){

				// get single property values
				$return = $this->config[ $args['context'] ][ $args['task'] ][ $args['property'] ];

			} else {

				// get task values ##
				$return = $this->config[ $args['context'] ][ $args['task'] ];

			}

			// filter single property values -- too slow ??
			// $return = \apply_filters( 'willow/config/get/'.$args['context'].'/'.$args['task'], $return );

			// single loading -- good idea? ##
			// $found = true;

			// ok ##
			// w__log( 'd:>config set to : "'.$property.'"' );
			// w__log( $return );

		}

		// filter return with specific context/task/ ##
		$return = \apply_filters( 'willow/config/get/'.$this->config_args['context'].'/'.$this->config_args['task'], $return );

		// kick back ##
		return $return;

	}

	public function run_filter() {

		// sanity ##
		if (
			is_null( $this->config_args )
			|| ! is_array( $this->config_args )
			|| ! isset( $this->config_args['context'] )
			|| ! isset( $this->config_args['task'] )
		){

			w__log('e:>Error in passed args');

			return false;

		}

		// w__log( $args );

		\apply_filters( 'willow/config/load', $this->config_args );

	}

	/**
	 * Include configuration file, with local cache via handle
	 * 
	 * @since 4.1.0
	*/
	public function load( $file = null, $handle = null ){

		// return args for other filters ### ?? ###
		$return = $this->config_args;

		// sanity ##
		if (
			is_null( $file )
			|| is_null( $handle )
			// || is_null( $args )
		){

			w__log( 'Error in passed params' );

			return $return;

		}

		// w__log( 'd:>Looking for handle: "'.$handle.'" in file: "'.$file.'"' );

		$backtrace = willow\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

		// use cached version ##
		if( isset( $this->cache[$handle] ) ){

			// w__log( 'd:>Returning cached version of config for handle: '.$handle.' from: '.$backtrace );
			// w__log( $this->cache[$handle] );
			return $return;

		}

		// check if file exists ##
		if (
			! file_exists( $file )
			|| ! is_file( $file )
		){
			
			w__log( 'e:>Error, file does not exist: '.$file.' from: '.$backtrace );

			return $return; #$this->config;

		}

		// w__log( 'dealing with file: '.$file. ' - ext: '.core\method::file_extension( $file ) );

		// get file extension ##
		switch( willow\core\method::file_extension( $file ) ){

			case "willow" :

				// key ##
				$file_key = basename( $file, ".willow" );

				// check format ##
				if( false === strpos( $file_key, '~' ) ){

					w__log( 'e:>Error, file name not correclty formatted: '.$file );

					return $return; #$this->config;

				}

				// we need to break file_key into parts ##
				$explode = explode( '~', $file_key) ;
				$context = $explode[0];
				$task = $explode[1];

				$contents = file_get_contents( $file );

				// w__log( '$context: '.$context.' task: '.$task );
				// w__log( $contents );
				// w__log( 't:>Todo, allow for logic in willow files, to assign other markup keys...' );

				// willow files are always treated as markup - assigned to "CONTEXT->TASK->markup" key ##
				$array[ $context ][ $task ]['markup'] = $contents;

				// return $return;

			break ;

			default ;
			case "php" :

				$array = require_once( $file );
				// w__log( $array );

			break;

		}

		// load config from JSON ##
		if (
			$array
			// $array = include( $file )
		){

			// w__log( 'd:>Loading handle: "'.$handle.'" from file: "'.$file.'"' );

			// not an array, so take value and add to new array as $markup key ##
			if (  
				! is_array( $array )
			){

				$value = $array;
				// w__log( $value );
				$array = [];
				$array['markup'] = $value;

			}

			// @todo - some cleaning and sanity ## strip functions etc ##

			// check if we have a 'config' key.. and take that ##
			if ( is_array( $array ) ) {

				// w__log( 'd:>config handle: "'.$handle.'" loading: '.$file.' from: '.$backtrace );
				// w__log( $array );

				// filter single property values -- too slow ??
				// perhaps this way is too open, and we should just run this at single property usage time... ##
				/*
				if ( isset( $array[ $this->config_args['context'].'__'.$this->config_args['task'] ] ) ) {

					$key = $this->config_args['context'].'__'.$this->config_args['task'];
					$property = $array[ $key ];

					$filter = 
						\apply_filters( 
							'willow/config/load/'.$this->config_args['context'].'/'.$this->config_args['task'], 
							$property
					);

					if ( $filter ) {

						// how to validate ??
						// is this an array.. ?? ##
						$array[ $key ] = $filter;

					}

				}
				*/

				// set cache check ##
				$this->cache[$handle] = $array;

				// merge results into array ##
				$this->config = willow\core\method::parse_args( $array, $this->config );

				// save file again ??

				// return ##
				return $return;

			} else {

				w__log( 'd:>config not an array -- handle: "'.$handle.' from: '.$backtrace );

			}

		}

		// return args for other filters ### ?? ###
		return $return;

	}

}
