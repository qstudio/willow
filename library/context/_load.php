<?php

namespace q\willow;

// use q\core;
use q\core\helper as h;
use q\willow\core\helper as wh;
use q\willow\render;
use q\willow\context;
use q\willow;

// load it up ##
\q\willow\context::run();

class context extends \q_willow {

	public static function run(){

		// load libraries ##
		\q\core\load::libraries( self::load() );

	}

    /**
    * Load Libraries
    *
    * @since        4.1.0
    */
    public static function load()
    {

		return $array = [

			// class extensions ##
			'extend' => wh::get( 'context/extend.php', 'return', 'path' ),

			// acf field groups ##
			'group' => wh::get( 'context/group.php', 'return', 'path' ),

			// post objects content, title, excerpt etc ##
			'post' => wh::get( 'context/post.php', 'return', 'path' ),

			// author, custom fields etc. ##
			'meta' => wh::get( 'context/meta.php', 'return', 'path' ),

			// navigation items ##
			'navigation' => wh::get( 'context/navigation.php', 'return', 'path' ),

			// media items ##
			'media' => wh::get( 'context/media.php', 'return', 'path' ),

			// taxonomies ##
			'taxonomy' => wh::get( 'context/taxonomy.php', 'return', 'path' ),

			// extension ##
			'extension' => wh::get( 'context/extension.php', 'return', 'path' ),

			// widgets ##
			'widget' => wh::get( 'context/widget.php', 'return', 'path' ),

			// ui render methods - open, close.. etc ##
			'ui' => wh::get( 'context/ui.php', 'return', 'path' ),

			// elements, html snippets, which can be processed to expand via {{> markdown }} ##
			'partial' => wh::get( 'context/partial.php', 'return', 'path' ),

			// perhaps type css ##
			// perhaps type js ##
			// perhaps type font ##

		];

	}



	/** 
	 * bounce to function getter ##
	 * function name can be any of the following patterns:
	 * 
	 * group__  acf field group
	 * field__  single post meta field ( can be any type, such as repeater )
	 * partial__  snippets, code, blocks, collections like post_meta
	 * post__  content, title, excerpt etc..
	 * media__
	 * navigation__ 
	 * taxonomy__
	 * ui__
	 * extension__
	 * widget__
	 */
	public static function __callStatic( $function, $args ){	

		// h::log( '$function: '.$function );
		// h::log( $args );

		// check class__method is formatted correctly ##
		if ( 
			false === strpos( $function, '__' )
		){

			h::log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return false;

		}	

		// we expect all render methods to have standard format CLASS__METHOD ##	
		list( $class, $method ) = explode( '__', $function );

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
				
				h::log( 'e:>Args validation failed' );

				// reset all args ##
				render\args::reset();
	
				return false;
	
			}

			// prepare markup, fields and handlers based on passed configuration ##
			willow\parse::prepare( $args );

			// call class::method to gather data ##
			// $namespace::run( $args );

			if (
				$extend = context\extend::get( $args['context'], $args['task'] )
			){

				// 	h::log( 'load extended method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from extend ##
				$extend['class']::{ $extend['method'] }( render::$args );

			} else if ( 
				\method_exists( $namespace, $args['task'] ) 
			){

				// 	h::log( 'load base method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from $method ##
				$namespace::{ $args['task'] }( render::$args );

			} else if ( 
				\method_exists( $namespace, 'get' ) 
			){

				// 	h::log( 'load default get() method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from get() ##
				$namespace::get( render::$args );

			} else {

				// no matching class::method found, so stop ##

				render\log::set( $args );
				
				h::log( 'e:>No matching class::method found' );

				// reset all args ##
				render\args::reset();
	
				return false;

			}

			// prepare field data ##
			render\fields::prepare();

			// check if feature is enabled ##
			if ( ! render\args::is_enabled() ) {

				render\log::set( $args );

				h::log( 'd:>Not enabled...' );

				// reset all args ##
				render\args::reset();
	
				return false;
	
		   	}    
		
			// h::log( render::$fields );

			// Prepare template markup ##
			render\markup::prepare();

			// h::log( 'running-> '.$extend['class'].'::'.$extend['method'] );
			if( 'hello' == $args['task'] ) {
				// h::log( $args['context'].'__'.$args['task'] );
				// h::log( render::$fields );
				// h::log( render::$markup );
			}

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
