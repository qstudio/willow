<?php

namespace q\willow\core;

use q\core;
use q\core\helper as h;
use q\view;

\q\willow\core\config::run();

class config extends \q_willow {

	private static
		// loaded config ##
		$has_config = false,
		$delete_config = true,
		$cache_files = [], // track array of files loaded, with ful path, so we can remove duplicates ##
		$config = [],
		$cache = [],
		$config_args = [] // passed args ##
	;

	public static function run(){

		// filter Q Config -- ALL FIELDS [ $array "data" ]##
		// Priority -- Q = 1, Q Plugin = 10, Q Parent = 100, Q Child = 1000
		\add_filter( 'q/config/load', 
			function( $args ){
				$source = null; // context source ##
				return self::filter( $args, $source );
			}
		, 1, 1 );

		\add_filter( 'q/config/load', 
			function( $args ){
				$source = 'plugin'; // context source ##
				return self::filter( $args, $source );
			}
		, 10, 1 );
		
		\add_filter( 'q/config/load', 
			function( $args ){
				$source = 'parent'; // context source ##
				return self::filter( $args, $source );
			}
		, 100, 1 );

		\add_filter( 'q/config/load', 
			function( $args ){
				$source = 'parent'; // context source ##
				return self::filter( $args, $source );
			}
		, 1000, 1 );

		// load saved config ##
		\add_action( 'wp', [ get_class(), 'get_cache' ], 10 );

		// save stored config to file ##
		\add_action( 'shutdown', [ get_class(), 'set_cache' ], 100000 );

		// notes ##
		// h::log( 't:>config is collecting data as it goes.. perhaps this will blow up, but seems ok so far..' );

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
		
			h::log( 'd:>Attempt to save config from admin or AJAX blocked' );

			return false; 
		
		}

		// if theme debugging, then load from single config files ##
		if ( \Q::$debug ) {

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
		\set_site_transient( 'q_willow_config', self::$config, 24 * HOUR_IN_SECONDS );

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
		if ( \Q::$debug ) {

			// h::log( 'd:>Theme is debugging, so load from individual context files...' );

			// load ##
			if ( self::$delete_config ) {

				\delete_site_transient( 'q_willow_config' );

				// h::log( 'd:>Deleted config cache from DB...' );

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

			return false;

		}

		// h::log( 'd:>Child theme method found, so trying to load data from __q.php' );
		// h::log( \get_site_transient( 'q_willow_config' ) );
		if ( 
			$array = \get_site_transient( 'q_willow_config' )
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

		h::log( 'e:>failed to load config data from DB, perhaps we need to re-generate from here..' );

		return false;

		// } else {

		// 	h::log( 'e:>Child theme method NOT found, could not load __q.php' );

		// 	return false;

		// }

	}


	
	/**
	 * Get configuration files
	 *
	 * @used by filter q/config/get/all
	 *
	 * @return		Array $array -- must return, but can be empty ##
	 */
	public static function filter( $args = null, $source = null ) {

		// h::log( $args );

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

		// config file extension ##
		$extensions = \apply_filters( 'q/config/load/ext', [ 
			'.willow',
			'.php', 
		] );

		// config file path ( h::get will do fallback checks form child theme, parent theme, plugin + Q.. )
		$path = \apply_filters( 'q/config/load/path', 'view/context/' );

		// array of config files to load -- key ( for cache ) + value ##
		$array = [
			// template__context__task ##
			view\is::get().'__'.$args['context'].'__'.$args['task'] => view\is::get().'__'.$args['context'].'__'.$args['task'],

			// context__task ##
			$args['context'].'__'.$args['task'] => $args['context'].'__'.$args['task'],

			// context ##
			$args['context'] => $args['context'],

			// global ##
			'global' => 'global'
		];

		// filter options ##
		$array = \apply_filters( 'q/config/load/array', $array );

		// h::log( 'd:>looking for source: '.$source );

		// loop over options ##
		foreach( $extensions as $ext ) {
			foreach( $array as $k => $v ){

				switch( $source ){

					// child context lookup ##
					case "child" :

						// check for theme method ##
						if ( ! method_exists( 'q_theme', 'get_child_theme_path' ) ){ break; }
						$file = \q_theme::get_child_theme_path( '/library/'.$path.$source.'/'.$v.$ext );
						// h::log( 'd:>child->looking up file: '.$file );

					break  ;

					// parent lookup ## 
					case "parent" :

						// check for theme method ##
						if ( ! method_exists( 'q_theme', 'get_parent_theme_path' ) ){ break; }
						$file = \q_theme::get_parent_theme_path( '/library/'.$path.$source.'/'.$v.$ext );
						// h::log( 'd:>parent->looking up file: '.$file );

					break  ;

					// global lookup, so context/XX.php
					default :

						$file = h::get( $path.$v.$ext, 'return', 'path' );
						// h::log( 'd:>global->looking up file: '.$file );

					break ;

				}

				if ( 
					$file
					&& file_exists( $file ) // OR is_file ??
					&& is_file( $file )
				){

					// skip file, if loaded already ##
					if ( in_array( $file, self::$cache_files ) ) {

						// h::log( 'd:>File: '.$file.' already loaded' );

						continue;

					}

					// build cache key ##
					$cache_key = 
						! is_null( $source ) ? 
						$k.'_'.$source.'_'.core\method::file_extension( $file ) : 
						$k.'_'.core\method::file_extension( $file ) ;

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
		// \apply_filters( 'q/config/get/all', self::$config, isset( $args['field'] ) ?: $args['field'] );

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
			$backtrace = \q\core\method::backtrace([ 'level' => 2, 'return' => 'class_function' ]);

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

				$return = self::$config[ $args['context'] ][ $args['task'] ][ $args['property'] ];

			} else {

				// get property value ##
				$return = self::$config[ $args['context'] ][ $args['task'] ];

			}

			// filter single property values -- too slow ??
			// $return = \apply_filters( 'q/config/get/'.$args['context'].'/'.$args['task'], $return );

			// single loading -- good idea? ##
			// $found = true;

			// ok ##
			// h::log( 'd:>config set to : "'.$property.'"' );
			// h::log( $return );

		}

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

		\apply_filters( 'q/config/load', self::$config_args );

	}





	/**
	 * Include configuration file, with local cache via handle
	 * 
	 * @since 4.1.0
	*/
	public static function load( $file = null, $handle = null )
	{

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

			// h::log( 'd:>Returning cached version of config for handle: '.$handle.' from: '.$backtrace );
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
				if( false === strpos( $file_key, '__' ) ){

					h::log( 'e:>Error, file name not correclty formatted: '.$file );

					return $return; #self::$config;

				}

				// we need to break file_key into parts ##
				$explode = explode( '__', $file_key) ;
				$context = $explode[0];
				$task = $explode[1];

				$contents = file_get_contents( $file );

				// h::log( '$context: '.$context.' task: '.$task );
				// h::log( $contents );
				h::log( 't:>Todo, allow for logic in willow files, to assign other markup keys...' );

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
				if ( isset( $array[ self::$config_args['context'].'__'.self::$config_args['task'] ] ) ) {

					$key = self::$config_args['context'].'__'.self::$config_args['task'];
					$property = $array[ $key ];

					$filter = 
						\apply_filters( 
							'q/willow/config/load/'.self::$config_args['context'].'/'.self::$config_args['task'], 
							$property
					);

					if ( $filter ) {

						// how to validate ??
						// is this an array.. ?? ##
						$array[ $key ] = $filter;

					}

				}

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
