<?php

namespace Q\willow;

use Q\willow;

class context  {

	private 
		$plugin = false,
		$render_args = false,
		$render_markup = false,
		$render_log = false,
		$render_output = false,
		$render_fields = false,
		$parse_prepare = false,

		$lookup_error = false
	;

	/**
     * @todo
     * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

		// render args ##
		$this->render_args = new willow\render\args( $this->plugin );

		// render markup ##
		$this->render_markup = new willow\render\markup( $this->plugin );

		// build render log ##
		$this->render_log = new willow\render\log( $this->plugin );

		// render output ##
		$this->render_output = new willow\render\output( $this->plugin );

		// render fields ##
		$this->render_fields = new willow\render\fields( $this->plugin );

		// parse prepare ##
		$this->parse_prepare = new willow\parse\prepare( $this->plugin );

	}

	/** 
	 * extract and validate args from template to gather data and return via render methods
	 * 
	 * @since 0.0.1
	 */
	public function __call( $function, $args ){	

		// w__log( '$function: '.$function );
		// w__log( $args );

		// reset lookup error tracker ##
		$this->lookup_error = false;

		// check class__method is formatted correctly ##
		if ( 
			false === strpos( $function, '__' )
		){

			w__log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return false;

		}	

		// we expect all render methods to have standard format CLASS__METHOD ##	
		list( $class, $method ) = explode( '__', $function, 2 );

		// sanity ##
		if ( 
			! $class
			|| ! $method
		){
		
			w__log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return false;

		}

		// w__log( 'd:>search if -- class: '.$class.'::'.$method.' available' );

		// look for "namespace/context/CLASS" ##
		$namespace = __NAMESPACE__."\\context\\".$class;
		// w__log( 'd:>namespace: '.$namespace );

		// called class ( namespace ) exists ##
		if ( class_exists( $namespace ) ) {

			// reset args ##
			$this->render_args->reset();

			// w__log( 'd:>class: '.$namespace.' available' );

			// w__log( $args );

			// take first array item, unwrap array --> __call wraps the array inside an array ##
			if ( is_array( $args ) && isset( $args[0] ) ) { 
				
				// w__log('Taking the first array item..');
				$args = $args[0];
			
			}

			// w__log( $args );

			// extract markup from passed args ##
			$this->render_markup->pre_validate( $args );

			// make args an array, if it's not ##
			if ( ! is_array( $args ) ){
			
				// w__log( 'Caste $args to array' );

				$args = [];
			
			}

			// define context for all in class -- i.e "post" ##
			$args['context'] = $class;

			// set task tracker -- i.e "title" ##
			$args['task'] = $method;

			// w__log( $args );

			// create hash ##
			$hash = false;
			$hash = $args['config']['hash'] ?: $args['context'].'__'.$args['task'].'.'.rand(); // HASH can be passed from calling Willow ## 

			// w__log( 'e:>Context Loaded: '.$hash );

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
				$this->render_log->set( $args );
	
				w__log( 'e:>Cannot locate method: '.$namespace.'::'.$args['task'] );
	
				// we need to reset the class ##

				// reset all args ##
				$this->render_args->reset();

				// kick out ##
				return false;
	
			}
	
			// validate passed args ##
			if ( ! $this->render_args->validate( $args ) ) {
	
				$this->render_log->set( $args );
				
				w__log( 'e:>Args validation failed' );

				// reset all args ##
				$this->render_args->reset();
	
				return false;
	
			}

			// w__log( $args );

			// prepare markup, fields and handlers based on passed configuration ##
			$this->parse_prepare->hooks( $args );

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

				// w__log( 'run extended method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from extend ##
				$return_array = $extend['class']::{ $extend['method'] }( $this->plugin->get( '_args') ) ;

			} else if ( 
				\method_exists( $namespace, $args['task'] ) 
			){

				// 	w__log( 'load base method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from $method ##
				$return_array = $namespace::{ $args['task'] }( $this->plugin->get( '_args') ) ;

			} else if ( 
				\method_exists( $namespace, 'get' ) 
			){

				// 	w__log( 'load default get() method: '.$extend['class'].'::'.$extend['method'] );

				// gather field data from get() ##
				// $return_array = $namespace::get( $this->plugin->get( '_args') ) ;

				// w__log( $this->plugin->get( '_args' ) ); exit;

				// new object ##
				$object = new $namespace( $this->plugin );

				// return post method to 
				$return_array = $object->get( $this->plugin->get( '_args' ) );

				// w__log( $return_array );

			} else {

				// w__log( 'e:>No matching class::method found' );

				// nothing found ##
				$this->lookup_error = true;

			}

			// internal buffer ##
			if(
				isset( $args['config']['buffer'] )
			){

				// get buffer data ##
				$return_array = [ $args['task'] => ob_get_clean() ];

				// ob_flush();
				if( ob_get_level() > 0 ) ob_flush();

				// w__log( $return_array );

			}

			// test ##
			// w__log( $return_array );

			if(
				true === $this->lookup_error
			){

				$this->render_log->set( $args );
				
				w__log( 'e:>No matching method found for "'.$args['context'].'::'.$args['task'].'"' );

				// reset all args ##
				$this->render_args->reset();
	
				return false;

			}

			if(
				! $return_array
				|| ! is_array( $return_array )
			){

				w__log( 'e:>Error in returned data from "'.$args['context'].'::'.$args['task'].'"' );
				// w__log( $return_array );

				// ...

			}

			// w__log( $return_array );

			// assign fields ##
			$this->render_fields->define( $return_array );

			// w__log( self::$markup );

			// w__log( $return_array );

			// prepare field data ##
			$this->render_fields->prepare();

			// w__log( self::$markup );

			// w__log( self::$fields );

			// w__log( self::$markup['template'] );

			// check if feature is enabled ##
			if ( ! $this->render_args->is_enabled() ) {

				$this->render_log->set( $args );

				w__log( 'd:>Not enabled...' );

				// reset all args ##
				$this->render_args->reset();
	
				return false;
	
		   	}    
		
			// w__log( self::$fields );

			// Prepare template markup ##
			$this->render_markup->prepare();

			// w__log( self::$markup );

			// clean up left over tags ## --- @TODO --> REMOVED as handled by cleanup ??? ##
			// willow\parse::cleanup();

			// optional logging to show removals and stats ##
			$this->render_log->set( $args );

			// return or echo ##
			$output = $this->render_output->prepare();

			// w__log( $output );

		} else {

			// nothing matched, so report and return false ##
			w__log( 'e:>No matching context for: '.$namespace );

			// optional clean up.. how do we know what to clean ?? ##
			// @todo -- add shutdown cleanup, so remove all lost pieces ##

			// kick back nada - as this renders on the UI ##
			return false;

		}

	}
	

}
