<?php

namespace willow;

// use q\core;
use willow\core\helper as h;
use willow\render;
use willow\context;
use willow;

// load it up ##
\willow\context::__run();

class context extends \willow {

	public static function __run(){

		// load libraries ##
		self::load();

	}

    /**
    * Load Libraries
    *
    * @since        4.1.0
    */
    public static function load()
    {

		// context extensions ##
		require_once self::get_plugin_path( 'library/context/extend.php' );

		// acf field groups ##
		require_once self::get_plugin_path( 'library/context/group.php' );

		// post objects content, title, excerpt etc ##
		require_once self::get_plugin_path( 'library/context/post.php' );

		// author, custom fields etc. ##
		require_once self::get_plugin_path( 'library/context/meta.php' );

		// navigation items ##
		require_once self::get_plugin_path( 'library/context/navigation.php' );

		// media items ##
		require_once self::get_plugin_path( 'library/context/media.php' );

		// taxonomies ##
		require_once self::get_plugin_path( 'library/context/taxonomy.php' );

		// extension ##
		// require_once self::get_plugin_path( 'library/context/extension.php' );

		// modules ##
		require_once self::get_plugin_path( 'library/context/module.php' );

		// plugins ##
		require_once self::get_plugin_path( 'library/context/plugin.php' );

		// widgets ##
		require_once self::get_plugin_path( 'library/context/widget.php' );

		// ui render methods - open, close.. etc ##
		require_once self::get_plugin_path( 'library/context/ui.php' );

		// elements, html snippets, which can be processed to expand via {> markdown <} ##
		require_once self::get_plugin_path( 'library/context/partial.php' );

		// user context ##
		require_once self::get_plugin_path( 'library/context/user.php' );

		// action hook context ##
		require_once self::get_plugin_path( 'library/context/action.php' );

		// filter hook context ##
		// require_once self::get_plugin_path( 'library/context/filter.php' );

		// wordpress context ##
		require_once self::get_plugin_path( 'library/context/wordpress.php' );

	}



	/** 
	 * extract and validate args from template to gather data and return via render methods
	 * 
	 * @since 0.0.1
	 */
	public static function __callStatic( $function, $args ){	

		// h::log( '$function: '.$function );
		// h::log( $args );

		// reset class::method tracker ##
		$lookup_error = false;

		// check class__method is formatted correctly ##
		if ( 
			false === strpos( $function, '__' )
		){

			h::log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return false;

		}	

		// we expect all render methods to have standard format CLASS__METHOD ##	
		list( $class, $method ) = explode( '__', $function, 2 );

		// sanity ##
		if ( 
			! $class
			|| ! $method
		){
		
			h::log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return false;

		}

		// h::log( 'd:>search if -- class: '.$class.'::'.$method.' available' );

		// look for "namespace/render/CLASS" ##
		$namespace = __NAMESPACE__."\\context\\".$class;
		// h::log( 'd:>namespace --- '.$namespace );

		if (
			class_exists( $namespace ) // && exists ##
		) {

			// reset args ##
			render\args::reset();

			// h::log( 'd:>class: '.$namespace.' available' );

			// h::log( $args );

			// take first array item, unwrap array - __callStatic wraps the array in an array ##
			if ( is_array( $args ) && isset( $args[0] ) ) { 
				
				// h::log('Taking the first array item..');
				$args = $args[0];
			
			}

			// h::log( $args );

			// extract markup from passed args ##
			render\markup::pre_validate( $args );

			// make args an array, if it's not ##
			if ( ! is_array( $args ) ){
			
				// h::log( 'Caste $args to array' );

				$args = [];
			
			}

			// define context for all in class -- i.e "post" ##
			$args['context'] = $class;

			// set task tracker -- i.e "title" ##
			$args['task'] = $method;

			// h::log( $args );

			// create hash ##
			$hash = false;
			$hash = $args['config']['hash'] ?: $args['context'].'__'.$args['task'].'.'.rand(); // HASH now passed from calling Willow ## 

			// h::log( 'e:>Context Loaded: '.$hash );

			// log hash ##
			\willow::$hash 	= [
				'hash'			=> $hash,
				'context'		=> $args['context'],
				'task'			=> $args['task'],
				'tag'			=> isset( $args['config']['tag'] ) ? $args['config']['tag'] : false , // matching tag from template ##
				'parent'		=> isset( $args['config']['parent'] ) ? $args['config']['parent'] : false,
			];

			if (
				! \method_exists( $namespace, 'get' ) // base method is get() ##
				&& ! \method_exists( $namespace, $args['task'] ) ##
				&& ! context\extend::get( $args['context'], $args['task'] ) // look for extends ##
			) {
	
				render\log::set( $args );
	
				h::log( 'e:>Cannot locate method: '.$namespace.'::'.$args['task'] );
	
				// we need to reset the class ##

				// reset all args ##
				render\args::reset();

				return false;
	
			}
	
			// validate passed args ##
			if ( ! render\args::validate( $args ) ) {
	
				render\log::set( $args );
				
				// h::log( 'e:>Args validation failed' );

				// reset all args ##
				render\args::reset();
	
				return false;
	
			}

			// h::log( $args );

			// prepare markup, fields and handlers based on passed configuration ##
			willow\parse::prepare( $args );

			// call class::method to gather data ##
			// $namespace::run( $args );

			// internal->internal buffering ##
			if(
				isset( $args['config']['buffer'] )
			){

				ob_start();
				
			}

			if (
				$extend = context\extend::get( $args['context'], $args['task'] )
			){

				// h::log( 'run extended method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from extend ##
				$return_array = $extend['class']::{ $extend['method'] }( render::$args ) ;

			} else if ( 
				\method_exists( $namespace, $args['task'] ) 
			){

				// 	h::log( 'load base method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from $method ##
				$return_array = $namespace::{ $args['task'] }( render::$args ) ;

			} else if ( 
				\method_exists( $namespace, 'get' ) 
			){

				// 	h::log( 'load default get() method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from get() ##
				$return_array = $namespace::get( render::$args ) ;

			} else {

				// h::log( 'e:>No matching class::method found' );

				// nothing found ##
				$lookup_error = true;

			}

			// internal buffer ##
			if(
				isset( $args['config']['buffer'] )
			){

				// get buffer data ##
				$return_array = [ $args['task'] => ob_get_clean() ];

				// ob_flush();
				if( ob_get_level() > 0 ) ob_flush();

				// h::log( $return_array );

			}

			// test ##
			// h::log( $return_array );

			if(
				true === $lookup_error
			){

				render\log::set( $args );
				
				h::log( 'e:>No matching method found for "'.$args['context'].'::'.$args['task'].'"' );

				// reset all args ##
				render\args::reset();
	
				return false;

			}

			if(
				! $return_array
				|| ! is_array( $return_array )
			){

				// h::log( 'e:>Error in returned data from "'.$args['context'].'::'.$args['task'].'"' );
				// h::log( $return_array );

				// ...

			}

			// assign fields ##
			render\fields::define( $return_array );

			// h::log( self::$markup['template'] );

			// h::log( $return_array );

			// prepare field data ##
			render\fields::prepare();

			// h::log( self::$markup['template'] );

			// check if feature is enabled ##
			if ( ! render\args::is_enabled() ) {

				render\log::set( $args );

				h::log( 'd:>Not enabled...' );

				// reset all args ##
				render\args::reset();
	
				return false;
	
		   	}    
		
			// h::log( self::$fields );

			// Prepare template markup ##
			render\markup::prepare();

			// h::log( 'running-> '.$extend['class'].'::'.$extend['method'] );
			// if( 'hello' == $args['task'] ) {
				// h::log( $args['context'].'__'.$args['task'] );
				// h::log( render::$fields );
				// h::log( render::$markup );
			// }

			// clean up left over tags ## --- REMOVED ##
			// willow\parse::cleanup();

			// optional logging to show removals and stats ##
			render\log::set( $args );

			// return or echo ##
			return render\output::prepare();

		}

		// nothing matched, so report and return false ##
		h::log( 'e:>No matching context for: '.$namespace );

		// optional clean up.. how do we know what to clean ?? ##
		// @todo -- add shutdown cleanup, so remove all lost pieces ##

		// kick back nada - as this renders on the UI ##
		return false;

	}
	

}
