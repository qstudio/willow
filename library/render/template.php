<?php

namespace willow\render;


// Q ##
use willow\get;
use willow\core\helper as h;
use willow;
use willow\core;
use willow\render;

class template extends willow\render {

	public static function partial( $args = null ){

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

			h::log( 'e:>A valid array of data is required to markup the template from "'.$args['context'].'~'.$args['task'].'"' );

			return false;

		}

		// get template ##
		$config = core\config::get([ 'context' => $args['context'], 'task' => $args['task'] ]);

		// filter ##
		$config = core\filter::apply([ 
			'parameters'    => [ 'config' => $config ], // pass ( $template ) as single array ##
			'filter'        => 'willow/render/template/config/'.$args['context'].'/'.$args['task'], // filter handle ##
			'return'        => $config
	   	]); 

		// h::log( $config );

		// args->template - if not set, define to task ##
		$markup = isset( $args['markup'] ) ? $args['markup'] : $args['task'] ;

		// filter ##
		$markup = core\filter::apply([ 
			'parameters'    => [ 'markup' => $markup ], // pass ( $template ) as single array ##
			'filter'        => 'willow/render/template/template/'.$args['context'].'/'.$args['task'], // filter handle ##
			'return'        => $markup
	   	]); 

		// check we have what we need ## -- TODO, move to Q Willow render::view() method
		if( 
			! $config
			|| ! is_array( $config )
			|| ! isset( $config['markup'] )
			|| ! is_array( $config['markup'] )
			|| ! isset( $config['markup'][ $markup ] )
		){

			h::log( 'e:>Missing or corrupt config settings' );

			return false;

		}

		// h::log( $config['markup'][ $template ] );

		// markup string ##
		$string = render\method::markup( $config['markup'][ $markup ], [ 0 => $args['data'] ] );

		// filter ##
		$string = core\filter::apply([ 
			'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
			'filter'        => 'willow/render/template/string/'.$args['context'].'/'.$args['task'], // filter handle ##
			'return'        => $string
	   	]); 

		// h::log( $string );

		// validate we have a string ##
		if(
			! $string
			|| ! is_string( $string )
		){

			h::log( 'e:>Error in string returned from markup' );

			return false;

		}

		// echo or return ##
		if(
			isset( $args['return'] )
			&& 'return' == $args['return']
		){

			return $string;

		} elseif ( 
			isset( $config['config']['return'] ) 
			&& 'echo' == $config['config']['return']
		) {

			echo $string;

		} else {

			return $string; // ~~ default to return ??

		}

		// done ##
		return true;

	}

}
