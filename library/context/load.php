<?php

namespace willow;

use willow;

class context  {

	private 
		// $plugin = false,
		$lookup_error = false
	;

	/**
	 * Construct
     */
    public function __construct(){

		// silence ##

	}

	/** 
	 * extract and validate args from template to gather data and return via render methods
	 * 
	 * @since 0.0.1
	 */
	public function __call( string $function = null, array $args = null ):void
	{	

		// w__log( '$function: '.$function );
		// w__log( $args );

		// reset lookup error tracker ##
		$this->lookup_error = false;

		// check class__method is formatted correctly ##
		if ( 
			false === strpos( $function, '__' )
		){

			w__log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return;

		}	

		// we expect all render methods to have standard format CLASS__METHOD ##	
		list( $class, $method ) = explode( '__', $function, 2 );

		// sanity ##
		if ( 
			! $class
			|| ! $method
		){
		
			w__log( 'e:>Error in passed render method: "'.$function.'" - should have format CLASS__METHOD' );

			return;

		}

		// w__log( 'd:>search if -- class: '.$class.'::'.$method.' available' );

		// look for "namespace/context/CLASS" ##
		$namespace = __NAMESPACE__."\\context\\".$class;
		// w__log( 'd:>namespace: '.$namespace );

		// called class ( namespace ) exists ##
		if ( class_exists( $namespace ) ) {

			// reset args ##
			\willow()->render->args->reset();

			// w__log( 'd:>class: '.$namespace.' available' );

			// w__log( $args );

			// take first array item, unwrap array --> __call wraps the array inside an array ##
			if ( is_array( $args ) && isset( $args[0] ) ) { 
				
				// w__log('Taking the first array item..');
				$args = $args[0];
			
			}

			// w__log( $args );

			// extract markup from passed args ##
			\willow()->render->markup->pre_validate( $args );

			// make args an array, if it's not ##
			if ( ! is_array( $args ) ){
			
				// w__log( 'Caste $args to empty array' );

				$args = [];
			
			}

			// define context for all in class -- i.e "post" ##
			$args['context'] = $class;

			// set task tracker -- i.e "title" ##
			$args['task'] = $method;

			// w__log( $args );

			// create hash ##
			$hash = false;
			$hash = $args['config']['hash'] ?? $args['context'].'__'.$args['task'].'.'.rand() ; // HASH can be passed from calling Willow ## 

			// w__log( 'e:>Context Loaded: '.$hash );

			// log hash ##
			// \willow::$hash 	= [
			\willow()->set( '_hash', [
				'hash'			=> $hash,
				'context'		=> $args['context'],
				'task'			=> $args['task'],
				'tag'			=> isset( $args['config']['tag'] ) ? $args['config']['tag'] : false , // matching tag from template ##
				'parent'		=> isset( $args['config']['parent'] ) ? $args['config']['parent'] : false,
			]);

			if (
				! \method_exists( $namespace, 'get' ) // base context method is get() -- and missing ##
				&& ! \method_exists( $namespace, $args['task'] ) // ... also, context + specific task missing ##
				&& ! \willow()->get( 'extend' )->get( $args['context'], $args['task'] ) // ... and no extended context method match ##
			) {

				// log stop point ##
				\willow()->render->log->set( $args );
	
				w__log( 'e:>Cannot locate method: '.$namespace.'::'.$args['task'] );
	
				// reset all args ##
				\willow()->render->args->reset();

				// kick out ##
				return;
	
			}

			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_args' ) );
	
			// validate passed args ##
			if ( ! \willow()->render->args->validate( $args ) ) {
	
				\willow()->render->log->set( $args );
				
				w__log( 'e:>Args validation failed' );

				// reset all args ##
				\willow()->render->args->reset();
	
				return;
	
			}

			// w__log( \willow()->get( '_args' ) );

			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_fields' ) );
			// w__log( $args );

			// prepare markup, fields and handlers based on passed configuration ##
			\willow()->parser->hooks( $args );

			// w__log( $args );

			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_scope_map' ) );
			// w__log( \willow()->get( '_fields' ) );
			// w__log( \willow()->get( '_args' ) );

			// internal->buffering ##
			if(
				isset( $args['config']['buffer'] )
			){

				ob_start();
				
			}

			if (
				$extend = \willow()->get( 'extend' )->get( $args['context'], $args['task'] )
			){

				// w__log( 'd:>Willow->extend: '.$extend['class'].'->'.$extend['method'].'()' );
				// w__log( \willow()->get( '_args' ) );

				// gather field data from extend ##
				// $return_array = $extend['class']::{ $extend['method'] }( \willow()->get( '_args') ) ;

				$class = $extend['class'];
				$method = $extend['method'];
				
				// new object ##
				$object = new $class( \willow() );

				// return post method to 
				$return_array = $object->{ $method }( \willow()->get( '_args' ) );

			} else if ( 
				\method_exists( $namespace, $args['task'] ) 
			){

				// w__log( 'e:>Willow->task: '.$namespace.'->'.$args['task'].'()' );

				$method = $args['task'];

				// new object ##
				$object = new $namespace( \willow() );

				// return post method to 
				$return_array = $object->{ $method }( \willow()->get( '_args' ) );

				// gather field data from $method ##
				// $return_array = $namespace::{ $args['task'] }( \willow()->get( '_args') ) ;

			} else if ( 
				\method_exists( $namespace, 'get' ) 
			){

				// w__log( 'e:>Willow->get: '.$namespace.'->get()' );

				// gather field data from get() ##
				// $return_array = $namespace::get( \willow()->get( '_args') ) ;

				// w__log( \willow()->get( '_args' ) ); exit;

				// new object ##
				$object = new $namespace( \willow() );

				// return post method to 
				$return_array = $object->get( \willow()->get( '_args' ) );

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

				\willow()->render->log->set( $args );
				
				w__log( 'e:>No matching method found for "'.$args['context'].'~'.$args['task'].'"' );

				// reset all args ##
				\willow()->render->args->reset();
	
				return;

			}

			if(
				! $return_array
				|| ! is_array( $return_array )
			){

				w__log( 'e:>Willow "'.$args['context'].'->'.$args['task'].'" function found, but returned nothing - stopping here.' );
				// w__log( $return_array );

				// BREAKING CHANGE  ## 
				// this did not return before - but what use is there to continue with no data to markup ?
				// any filters and hooks could already have run from the called function - which was found, but returned false ##
				return;

			}

			// w__log( $return_array );

			// assign fields from returned data array ##
			\willow()->render->fields->define( $return_array );
			// w__log( \willow()->get( '_fields' ) );
			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_args' ) );

			// w__log( $return_array );

			// prepare field data ##
			\willow()->render->fields->prepare();
			// w__log( \willow()->get( '_fields' ) );
			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_scope_map' ) );

			// check if feature is enabled ##
			if ( ! \willow()->render->args->is_enabled() ) {

				// build log ##
				\willow()->render->log->set( $args );

				// reset all args ##
				\willow()->render->args->reset();
				
				w__log( 'd:>Not enabled...' );

				// done ##
				return;
	
		   	}    
		
			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_fields' ) );

			// Prepare template markup ##
			\willow()->render->markup->prepare();

			// w__log( \willow()->get( '_markup' ) );
			// w__log( \willow()->get( '_fields' ) );

			// clean up left over tags ## --- @TODO --> REMOVED as handled by cleanup ??? ##
			// willow\parse::cleanup();

			// optional logging to show removals and stats ##
			\willow()->render->log->set( $args );

			// return or echo ##
			$output = \willow()->render->output->prepare();

			// w__log( $output );

		} else {

			// nothing matched, so report and return false ##
			w__log( 'e:>No matching context for: '.$namespace );

			// optional clean up.. how do we know what to clean ?? ##
			// @todo -- add shutdown cleanup, so remove all lost pieces ##

			// kick back nada - as this renders on the UI ##
			return;

		}

	}
	

}
