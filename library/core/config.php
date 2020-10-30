<?php

namespace willow\core;

use willow\core;
use willow\core\helper as h;

\willow\core\config::__run();

class config extends \willow {

	private static
		// loaded config ##
		$has_config = false,
		$delete_config = true,
		$cache_files = [], // track array of files loaded, with ful path, so we can remove duplicates ##
		$config = [],
		$cache = [],
		$config_args = [], // passed args ##
		$child_theme_path = false, // check for child theme path method ##
		$parent_theme_path = false, // check for parent theme path method ##
		$file_extensions = [], // .php / .willow allow file types ##
		$willow_path = '', // default load location ##
		$template = '', // currently viewed template ##
		$properties_loaded = false, // track and load just once ##
		$global_loaded = false // track and load just once ##
	;

	public static function __run(){

		// filter Willow Config ##
		// Priority -- Parent Theme = 1, Extension = 100, Child Theme = 1000 ##
		/*
		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = null; // context source ##
				return self::filter( $args, $source );
			}
		, 1, 1 );
		*/
		/*
		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'plugin'; // context source ##
				return self::filter( $args, $source );
			}
		, 1, 1 );
		*/

		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'parent'; // context source ##
				return self::filter( $args, $source );
			}
		, 1, 1 );

		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'extend'; // context source ##
				return self::filter( $args, $source );
			}
		, 100, 1 );
		
		\add_filter( 'willow/config/load', 
			function( $args ){
				$source = 'child'; // context source ##
				return self::filter( $args, $source );
			}
		, 1000, 1 );

		// load saved config ##
		\add_action( 'wp', [ get_class(), 'get_cache' ], 10 );

		// save stored config to file ##
		\add_action( 'shutdown', [ get_class(), 'set_cache' ], 100000 );

		// hook into Fastest Cache clear routine -- @todo, this should be in a plugin filter ##
		\add_action( 'wpfc_delete_cache', [ get_class(), 'delete_from_fastest_cache' ], 1 );

	}


	public static function properties(){

		// cache ##
		if ( self::$properties_loaded ) return false;

		// check for child theme path method ##
		self::$child_theme_path = method_exists( 'q_theme', 'get_child_theme_path' );

		// check for parent theme path method ##
		self::$parent_theme_path = method_exists( 'q_theme', 'get_parent_theme_path' );

		// config file extension ##
		self::$file_extensions = \apply_filters( 'willow/config/load/ext', [ 
			'.willow',
			'.php', 
		] );

		// config file path ( h::get will do fallback checks form child theme, parent theme, plugin + Q.. )
		self::$willow_path = \apply_filters( 'willow/config/load/path', 'willow/' );

		// template ##
		self::$template = core\method::template() ? core\method::template() : '404';

		// hit ##
		self::$properties_loaded = true;

		// done ##
		return;

	}



	/**
	 * Save config file
	 * 
	 * @aince 4.1.0
	*/
	public static function set_cache(){

		// do not save file from admin, as it will be incomplete ##
		if( 
			\is_admin() 
			|| \wp_doing_ajax()
		){ 
		
			// h::log( 'd:>Attempt to save config from admin or AJAX blocked' );

			return false; 
		
		}

		// if theme debugging, then load from single config files ##
		if ( self::$debug ) { 

			// h::log('d:>Deubbing, so we do not need to resave __q.php.' );
			// h::log( 't:>How to dump file / cache and reload from config files, other than to delete __q.php??' );

			return false; 

		}

		if ( self::$has_config ){ 
		
			h::log('d:>We do not need to resave the file, as it already exists' );
			// h::log( 't:>How to dump file / cache and reload from config files, other than to delete __q.php??' );

			return false; 
		
		}

		// cache in DB ##
		\set_site_transient( 'willow_config', self::$config, 24 * HOUR_IN_SECONDS );

		// h::log('d:>saved config to DB...' );

		// return true;

		if ( method_exists( 'q_theme', 'get_child_theme_path' ) ){ 

			// h::log( 'e:>Q Theme class not available, perhaps this function was hooked too early?' );

			core\method::file_put_array( \q_theme::get_child_theme_path( '/__q.php' ), self::$config );

		}

		return true;

	}


	/**
	 * Load config file
	 * 
	 * @aince 4.1.0
	*/
	public static function get_cache(){

		/*
		if ( ! method_exists( 'q_theme', 'get_child_theme_path' ) ){ 

			h::log( 'e:>Q Theme class not available, perhaps this function was hooked too early?' );

			return false;

		}
		*/

		// if ( method_exists( 'q_theme', 'get_child_theme_path' ) ){ 

		// if theme debugging, then load from indiviual config files ##
		if ( self::$debug ) { 

			// h::log( 'd:>Theme is debugging, so load from individual context files...' );

			// load ##
			if ( self::$delete_config ) {

				self::delete_cache();
				/*

				\delete_site_transient( 'willow_config' );

				// h::log( 'e:>Deleted config cache from DB...' );

				if ( method_exists( 'q_theme', 'get_child_theme_path' ) ){ 

					$file = \q_theme::get_child_theme_path('/__q.php');

					if ( $file && file_exists( $file ) ) {

						unlink( $file );

						h::log( 'd:>...also deleting __q.php, so cache is clear' );

					}

				}

				// update tracker ##
				self::$delete_config = false;
				*/

			}

			return false;

		}

		// h::log( 'd:>Child theme method found, so trying to load data from __q.php' );
		// h::log( \get_site_transient( 'willow_config' ) );
		if ( 
			$array = \get_site_transient( 'willow_config' )
		) {

			// log ##
			// h::log( 'd:>DB Transient Found...' );

			if( is_array( $array ) ) {

				// store array in object cache ##
				self::$config = $array;

				// update flag ##
				self::$has_config = true;

				// log ##
				// h::log( 'd:>Theme NOT debugging ( production mode ) -- so loaded config data DB' );

				// good ##
				return true;

			}

		}

		/*
		if( $file = \q_theme::get_child_theme_path('/__q.php') ) {

			if ( is_file( $file ) ) {

				$array = require_once( $file );	

				if (
					$array
					&& is_array( $array )
				){

					// store array in object cache ##
					self::$config = $array;

					// update flag ##
					self::$has_config = true;

					// log ##
					h::log( 'd:>Theme NOT debugging ( production mode ) -- so loaded config data from __q.php' );

					// good ##
					return true;

				}

			}
			
		}
		*/

		h::log( 'e:>Cache check to DB is empty, so new cache being generated from here..' );

		return false;

		// } else {

		// 	h::log( 'e:>Child theme method NOT found, could not load __q.php' );

		// 	return false;

		// }

	}


	/**
	 * Hook into Fastest Cache delete routine - as an example how to clear Willow cache from third party plugin
	 * 
	 * @since 	1.4.7
	 * @return 	void
	*/
	public static function delete_from_fastest_cache(){

		// h::log( 'e:>Delete Willow cache from Fastest Cache hook...' );
		
		self::delete_cache();

	}


	public static function delete_cache(){

		\delete_site_transient( 'willow_config' );

		// h::log( 'e:>Deleted config cache from DB...' );

		if ( method_exists( 'q_theme', 'get_child_theme_path' ) ){ 

			$file = \q_theme::get_child_theme_path('/__q.php');

			if ( $file && file_exists( $file ) ) {

				unlink( $file );

				// h::log( 'd:>...also deleting __q.php, so cache is clear' );

			}

		}

		// update tracker ##
		self::$delete_config = false;

	}


	public static function global(){

		// cache ##
		if ( self::$global_loaded ){ return false; }

		// file ##
		$file = self::get_plugin_path('library/willow/global.php');

		// cache ##
		$cache_key = 'willow_global_php';

		// send file to config loader ##
		self::load( $file, $cache_key );

		// save file to cache ##
		self::$cache_files[] = $file;

		// update tracker ##
		self::$global_loaded = true;

	}

	
	/**
	 * Get configuration files
	 *
	 * @used by filter willow/config/get/all
	 *
	 * @return		Array $array -- must return, but can be empty ##
	 */
	public static function filter( $args = null, $source = null ) {

		// h::log( 'e:>SOURCE: '.$source );

		// h::log( self::$extend );

		// fill properties ##
		self::properties();

		// load global config - once ##
		self::global();

		if ( 
			self::$has_config 
			&& isset( self::$config[ $args['context'] ] ) 
			&& isset( self::$config[ $args['context'] ][ $args['task'] ] ) 
		){ 
			
			// h::log( 'd:>Config loading from cache file: '.$args['context'].'->'.$args['task'] ); 
			// h::log( self::$config );
			
			return $args; 
		
		}

		// we got this far, so we need to re save the config file ##
		self::$has_config = false;

		// sanity ##
		if ( 
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] ) 
			|| ! isset( $args['task'] )
			// || is_null( $source )
		){

			// config is loaded by context or process, so we need one of those to continue ##
			h::log( 'e:>Error in passed args, $context and $task are required.' );

			// kick back args for future filters ##
			return $args;

		}

		// array of config files to load -- key ( for cache ) + value ##
		$array = [

			// template~context~task ##
			self::$template.'__'.$args['context'].'__'.$args['task'] => self::$template.'~'.$args['context'].'~'.$args['task'],

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

		// h::log( 'd:>looking for source: '.$source );
		if( 'extend' == $source ) {

			// h::log( 'd:>looking for source: '.$source );

			if(
				! empty( self::$extend )
			){

				$extended_lookups = [];
				foreach( self::$extend as $k => $v ){

					// h::log( $v );
					if( $v['lookup'] ){

						$extended_lookups[] = self::$extend[$k];

					}

				}

			}
		}

		// h::log( $extended_lookups );

		// check for child theme path method ##
		// $child_theme_path = method_exists( 'q_theme', 'get_child_theme_path' );

		// check for parent theme path method ##
		// $parent_theme_path = method_exists( 'q_theme', 'get_parent_theme_path' );

		// loop over allowed extensions ##
		// TODO - this could be a whole load more effecient... ##
		foreach( self::$file_extensions as $ext ) {

			// loop over array values ##
			foreach( $array as $k => $v ){

				switch( $source ){

					// child context lookup ##
					case "child" :

						// check for theme method ##
						if ( ! self::$child_theme_path ){ break; }

						// look for file ##
						$file = \q_theme::get_child_theme_path( '/library/'.self::$willow_path.$v.$ext );

						// build cache key ##
						$cache_key = 'child_'.$v.'_'.str_replace( '.', '', $ext );

						// h::log( 'd:>child->looking up file: '.$file );

					break  ;

					// parent context lookup ## 
					case "parent" :

						// check for theme method ##
						if ( ! self::$parent_theme_path ){ break; }

						// look for file ##
						$file = \q_theme::get_parent_theme_path( '/library/'.self::$willow_path.$v.$ext );

						// build cache key ##
						$cache_key = 'parent_'.$v.'_'.str_replace( '.', '', $ext );
						// h::log( 'd:>parent->looking up file: '.$file );

					break  ;

					case "extend" :

						if( ! empty( $extended_lookups ) ){

							foreach( $extended_lookups as $kl => $vl ){

								// h::log( $v );

								if ( $vl['context'] == $args['context'] ){

									if ( false !== $key = array_search( $args['task'], $vl['methods'] ) ) {

										$file = $vl['lookup'].$args['context'].'/'.$args['context'].'~'.$args['task'].$ext;

										// h::log( 'e:>Extend File: '.$file );
										$cache_key = $source.'_'.$args['context'].'_'.$args['task'].'_'.str_replace( '.', '', $ext );

									}

								}

							}

						}

					break ;

					// global lookup ~~> willow/FILE.ext
					/*
					case "global" :
					default :

						$file = h::get( self::$willow_path.$v.$ext, 'return', 'path' );
						$file = self::get_plugin_path('/library');

						$cache_key = 'global_'.$v.'_'.str_replace( '.', '', $ext );
						// h::log( 'd:>global->looking up file: '.$file );

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
					if ( in_array( $file, self::$cache_files ) ) {

						// h::log( 'd:>File: '.$file.' already loaded' );

						continue;

					}

					// build cache key ##
					if ( ! $cache_key ) {

						// h::log( 'e:>Cache key missing for file: '.$file );
					
						$cache_key = 
							! is_null( $source ) ? 
							$k.'_'.$source.'_'.core\method::file_extension( $file ) : 
							$k.'_'.core\method::file_extension( $file ) ;

					}

					// h::log( 'd:>Loading config file: '.$file.' cache key: '.$cache_key );

					// send file to config loader ##
					self::load( $file, $cache_key );

					// save file to cache ##
					self::$cache_files[] = $file;

				}

			}

		}

		// kick back args for future filters ##
		return $args;

	}




	/**
	 * Get stored config setting, merging in any new of changed settings from extensions ##
	 */
	public static function get( $args = null ) {

		// without $context or $task, we can't get anything specific, so just run main filter ##
		// \apply_filters( 'willow/config/get/all', self::$config, isset( $args['field'] ) ?: $args['field'] );

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
		self::$config_args = $args; // capture args ##

		// sanity ##
		if ( 
			is_null( $args )
			|| ! is_array( $args )
			|| ! isset( $args['context'] ) 
			|| ! isset( $args['task'] )
		){

			// get caller ##
			$backtrace = core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

			// config is loaded by context or process, so we need one of those to continue ##
			h::log( 'e:>Q -> '.$backtrace.': config is loaded by context and process, so we need both of those to continue' );

			return false;

		}

		// start blank ##
		$return = false;

		// run filter passing lookup args to allow themes and plugins to control config ##
		self::run_filter();

		// define property for logging ##
		$property = $args['context'].'::'.$args['task'] ;

		// h::log('d:>Looking for $config property: '.$property );
		// h::log( self::$config );

		if ( 
			! isset( self::$config[ $args['context'] ] ) 
			|| ! isset( self::$config[ $args['context'] ][ $args['task'] ] ) 
		){
	
			// h::log( 'd:>config not available : "'.$property.'"' );

			// continue;

		} else {

			// return single property ##
			if ( 
				isset( $args['property'] ) 
				&& isset( self::$config[ $args['context'] ][ $args['task'] ][ $args['property'] ] )
			){

				// get single property values
				$return = self::$config[ $args['context'] ][ $args['task'] ][ $args['property'] ];

			} else {

				// get task values ##
				$return = self::$config[ $args['context'] ][ $args['task'] ];

			}

			// filter single property values -- too slow ??
			// $return = \apply_filters( 'willow/config/get/'.$args['context'].'/'.$args['task'], $return );

			// single loading -- good idea? ##
			// $found = true;

			// ok ##
			// h::log( 'd:>config set to : "'.$property.'"' );
			// h::log( $return );

		}

		// filter return with specific context/task/ ##
		$return = \apply_filters( 'willow/config/get/'.self::$config_args['context'].'/'.self::$config_args['task'], $return );

		// kick back ##
		return $return;

	}



	public static function run_filter() {

		// sanity ##
		if (
			is_null( self::$config_args )
			|| ! is_array( self::$config_args )
			|| ! isset( self::$config_args['context'] )
			|| ! isset( self::$config_args['task'] )
		){

			h::log('e:>Error in passed args');

			return false;

		}

		// h::log( $args );

		\apply_filters( 'willow/config/load', self::$config_args );

	}





	/**
	 * Include configuration file, with local cache via handle
	 * 
	 * @since 4.1.0
	*/
	public static function load( $file = null, $handle = null ){

		// return args for other filters ### ?? ###
		$return = self::$config_args;

		// sanity ##
		if (
			is_null( $file )
			|| is_null( $handle )
			// || is_null( $args )
		){

			h::log( 'Error in passed params' );

			return $return;

		}

		// h::log( 'd:>Looking for handle: "'.$handle.'" in file: "'.$file.'"' );

		$backtrace = core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

		// use cached version ##
		if( isset( self::$cache[$handle] ) ){

			h::log( 'd:>Returning cached version of config for handle: '.$handle.' from: '.$backtrace );
			// h::log( self::$cache[$handle] );
			return $return;

		}

		// check if file exists ##
		if (
			! file_exists( $file )
			|| ! is_file( $file )
		){
			
			h::log( 'e:>Error, file does not exist: '.$file.' from: '.$backtrace );

			return $return; #self::$config;

		}

		// h::log( 'dealing with file: '.$file. ' - ext: '.core\method::file_extension( $file ) );

		// get file extension ##
		switch( core\method::file_extension( $file ) ){

			case "willow" :

				// key ##
				$file_key = basename( $file, ".willow" );

				// check format ##
				if( false === strpos( $file_key, '~' ) ){

					h::log( 'e:>Error, file name not correclty formatted: '.$file );

					return $return; #self::$config;

				}

				// we need to break file_key into parts ##
				$explode = explode( '~', $file_key) ;
				$context = $explode[0];
				$task = $explode[1];

				$contents = file_get_contents( $file );

				// h::log( '$context: '.$context.' task: '.$task );
				// h::log( $contents );
				// h::log( 't:>Todo, allow for logic in willow files, to assign other markup keys...' );

				// willow files are always treated as markup - assigned to "CONTEXT->TASK->markup" key ##
				$array[ $context ][ $task ]['markup'] = $contents;

				// return $return;

			break ;

			default ;
			case "php" :

				$array = require_once( $file );
				// h::log( $array );

			break;

		}

		// load config from JSON ##
		if (
			$array
			// $array = include( $file )
		){

			// h::log( 'd:>Loading handle: "'.$handle.'" from file: "'.$file.'"' );

			// not an array, so take value and add to new array as $markup key ##
			if (  
				! is_array( $array )
			){

				$value = $array;
				// h::log( $value );
				$array = [];
				$array['markup'] = $value;

			}

			// @todo - some cleaning and sanity ## strip functions etc ##

			// check if we have a 'config' key.. and take that ##
			if ( is_array( $array ) ) {

				// h::log( 'd:>config handle: "'.$handle.'" loading: '.$file.' from: '.$backtrace );
				// h::log( $array );

				// filter single property values -- too slow ??
				// perhaps this way is too open, and we should just run this at single property usage time... ##
				/*
				if ( isset( $array[ self::$config_args['context'].'__'.self::$config_args['task'] ] ) ) {

					$key = self::$config_args['context'].'__'.self::$config_args['task'];
					$property = $array[ $key ];

					$filter = 
						\apply_filters( 
							'willow/config/load/'.self::$config_args['context'].'/'.self::$config_args['task'], 
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
				self::$cache[$handle] = $array;

				// merge results into array ##
				self::$config = core\method::parse_args( $array, self::$config );

				// save file again ??

				// return ##
				return $return;

			} else {

				h::log( 'd:>config not an array -- handle: "'.$handle.' from: '.$backtrace );

			}

		}

		// return args for other filters ### ?? ###
		return $return;

	}

}
