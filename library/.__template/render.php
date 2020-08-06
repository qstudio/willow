<?php

namespace q\willow\template;


// Q ##
use q\get;
use q\core\helper as h;

use q\willow;
use q\willow\core;
// use q\willow\render;

class render extends willow\template {

	public static function markup( $args = null ){

		// sanity ##
		if( 
			is_null( $args )
			|| ! is_array( $args )
		){

			h::log( 'e:>Missing or corrupt arguments' );

			return false;

		}

		// required fields ##
		if(
			! isset( $args['context'] )
			|| ! isset( $args['task'] )
		){

			h::log( 'e:>Both context and task as required to render a template.' );

			return false;

		}

		// we need data ##
		if(
			! isset( $args['data'] )
			|| ! is_array( $args['data'] )
		){

			h::log( 'e:>A valid array of data is required to markup the template.' );

			return false;

		}

		// get template ##
		$config = \q\core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ]);

		// h::log( $config );

		// args->template - if not set, define to task ##
		$template = isset( $args['template'] ) ? $args['template'] : $args['task'] ;

		// check we have what we need ## -- TODO, move to Q Willow render::view() method
		if( 
			! $config
			|| ! is_array( $config )
			|| ! isset( $config['markup'] )
			|| ! is_array( $config['markup'] )
			|| ! isset( $config['markup'][ $template ] ) // @todo - this should be dynamic, passed to the method
		){

			h::log( 'e:>Missing or corrupt config settings' );

			return false;

		}

		// h::log( $config['markup'][ $template ] );

		// markup string ##
		$string = \q\strings\method::markup( $config['markup'][ $template ], [ 0 => $args['data'] ] );

		// h::log( $string );

		// validate we have a string ##
		if(
			! $string
			|| ! is_string( $string )
		){

			h::log( 'e:>Error in string returned from markup' );

			return false;

		}

		// echo -- as this action is hooked ##
		if ( 
			isset( $config['config']['return'] ) 
			&& 'echo' == $config['config']['return']
		) {

			echo $string;

		} else {

			return $string;

		}

		// done ##
		return true;

	}

}
