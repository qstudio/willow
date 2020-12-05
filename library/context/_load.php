<?php

namespace Q\willow;

use Q\willow;
use Q\willow\core\helper as h;

class context  {

	private 
		$plugin = false
	;

	/**
     * @todo
     * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/** 
	 * extract and validate args from template to gather data and return via render methods
	 * 
	 * @since 0.0.1
	 */
	public function __call( $function, $args ){	

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

		// render args ##
		$render_args = new willow\render\args( $this->plugin );

		// render markup ##
		$render_markup = new willow\render\markup( $this->plugin );

		// build render log ##
		$render_log = new willow\render\log( $this->plugin );

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
		// h::log( 'd:>namespace: '.$namespace );

		if (
			class_exists( $namespace ) // && exists ##
		) {

			// reset args ##
			$render_args->reset();
			// render\args::reset();

			// h::log( 'd:>class: '.$namespace.' available' );

			// h::log( $args );

			// take first array item, unwrap array - __callStatic wraps the array in an array ##
			if ( is_array( $args ) && isset( $args[0] ) ) { 
				
				// h::log('Taking the first array item..');
				$args = $args[0];
			
			}

			// h::log( $args );

			// extract markup from passed args ##
			$render_markup->pre_validate( $args );

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
			$hash = $args['config']['hash'] ?: $args['context'].'__'.$args['task'].'.'.rand(); // HASH can be passed from calling Willow ## 

			// h::log( 'e:>Context Loaded: '.$hash );

			// log hash ##
			// \willow::$hash 	= [
			$this->plugin->set( '_hash', [
				'hash'			=> $hash,
				'context'		=> $args['context'],
				'task'			=> $args['task'],
				'tag'			=> isset( $args['config']['tag'] ) ? $args['config']['tag'] : false , // matching tag from template ##
				'parent'		=> isset( $args['config']['parent'] ) ? $args['config']['parent'] : false,
			]);

			if (
				! \method_exists( $namespace, 'get' ) // base context method is get() -- and missing ##
				&& ! \method_exists( $namespace, $args['task'] ) // ... also, context + specific task missing ##
				&& ! $this->plugin->get( 'extend' )->get( $args['context'], $args['task'] ) // ... and no extended context method match ##
			) {

				// log stop point ##
				// render\log::set( $args );
				$render_log->set( $args );
	
				// @todo // log-->h::log( 'e:>Cannot locate method: '.$namespace.'::'.$args['task'] );
	
				// we need to reset the class ##

				// reset all args ##
				$render_args->reset();

				// kick out ##
				return false;
	
			}
	
			// validate passed args ##
			if ( ! $render_args->validate( $args ) ) {
	
				$render_log->set( $args );
				
				// h::log( 'e:>Args validation failed' );

				// reset all args ##
				$render_args->reset();
	
				return false;
	
			}

			// h::log( $args );

			// prepare markup, fields and handlers based on passed configuration ##
			$parse_prepare = new willow\parse\prepare( $this->plugin, $args );
			$parse_prepare->hooks();

			// call class::method to gather data ##
			// $namespace::run( $args );

			// internal->buffering ##
			if(
				isset( $args['config']['buffer'] )
			){

				ob_start();
				
			}

			if (
				$extend = $this->plugin->get( 'extend' )->get( $args['context'], $args['task'] )
			){

				// h::log( 'run extended method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from extend ##
				$return_array = $extend['class']::{ $extend['method'] }( $this->plugin->get( '_args') ) ;

			} else if ( 
				\method_exists( $namespace, $args['task'] ) 
			){

				// 	h::log( 'load base method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from $method ##
				$return_array = $namespace::{ $args['task'] }( $this->plugin->get( '_args') ) ;

			} else if ( 
				\method_exists( $namespace, 'get' ) 
			){

				// 	h::log( 'load default get() method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from get() ##
				// $return_array = $namespace::get( $this->plugin->get( '_args') ) ;

				// h::log( $this->plugin->get( '_args' ) ); exit;

				// new object ##
				$object = new $namespace( $this->plugin );

				// return post method to 
				$return_array = $object->get( $this->plugin->get( '_args' ) );

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

				$render_log->set( $args );
				
				h::log( 'e:>No matching method found for "'.$args['context'].'::'.$args['task'].'"' );

				// reset all args ##
				$render_args->reset();
	
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

			// h::log( $return_array );

			// assign fields ##
			$render_fields = new willow\render\fields( $this->plugin );
			$render_fields->define( $return_array );

			// h::log( self::$markup );

			// h::log( $return_array );

			// prepare field data ##
			$render_fields->prepare();

			// h::log( self::$markup );

			// h::log( self::$fields );

			// h::log( self::$markup['template'] );

			// check if feature is enabled ##
			if ( ! $render_args->is_enabled() ) {

				$render_log->set( $args );

				h::log( 'd:>Not enabled...' );

				// reset all args ##
				$render_args->reset();
	
				return false;
	
		   	}    
		
			// h::log( self::$fields );

			// Prepare template markup ##
			$render_markup->prepare();

			// h::log( self::$markup );

			// h::log( 'running-> '.$extend['class'].'::'.$extend['method'] );
			// if( 'hello' == $args['task'] ) {
				// h::log( $args['context'].'__'.$args['task'] );
				// h::log( render::$fields );
				// h::log( render::$markup );
			// }

			// clean up left over tags ## --- REMOVED ##
			// willow\parse::cleanup();

			// optional logging to show removals and stats ##
			$render_log->set( $args );

			// return or echo ##
			$render_output = new willow\render\output( $this->plugin );
			return $render_output->prepare();

		}

		// nothing matched, so report and return false ##
		h::log( 'e:>No matching context for: '.$namespace );

		// optional clean up.. how do we know what to clean ?? ##
		// @todo -- add shutdown cleanup, so remove all lost pieces ##

		// kick back nada - as this renders on the UI ##
		return false;

	}
	

}
