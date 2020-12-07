<?php

namespace Q\willow\render;

use Q\willow;
use Q\willow\core\helper as h;

class template {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	public function partial( $args = null ){

		// sanity ##
		if( 
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Missing or corrupt arguments' );

			return false;

		}

		// required fields ##
		if(
			! isset( $args['context'] )
			|| ! isset( $args['task'] )
		){

			w__log( 'e:>Both context and task as required to render a template.' );

			return false;

		}

		// we need data ##
		if(
			! isset( $args['data'] )
			|| ! is_array( $args['data'] )
		){

			w__log( 'e:>A valid array of data is required to markup the template from "'.$args['context'].'~'.$args['task'].'"' );

			return false;

		}

		// w__log( $args );

		// get template ##
		$config = $this->plugin->config->get([ 'context' => $args['context'], 'task' => $args['task'] ]);

		// w__log( $config );

		// filter ##
		$config = $this->plugin->filter->apply([ 
			'parameters'    => [ 'config' => $config ], // pass ( $template ) as single array ##
			'filter'        => 'willow/render/template/config/'.$args['context'].'/'.$args['task'], // filter handle ##
			'return'        => $config
	   	]); 

		// args->template - if not set, define to task ##
		$markup = isset( $args['markup'] ) ? $args['markup'] : $args['task'] ;

		// w__log( 'markup: '.$markup );

		// filter ##
		$markup = $this->plugin->filter->apply([ 
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

			w__log( 'e:>Missing or corrupt config settings' );

			return false;

		}

		// w__log( $config['markup'][ $markup ] );

		// markup string ##
		$string = willow\render\method::markup( $config['markup'][ $markup ], [ 0 => $args['data'] ] );

		// filter ##
		$string = $this->plugin->filter->apply([ 
			'parameters'    => [ 'string' => $string ], // pass ( $string ) as single array ##
			'filter'        => 'willow/render/template/string/'.$args['context'].'/'.$args['task'], // filter handle ##
			'return'        => $string
	   	]); 

		// w__log( $string );

		// validate we have a string ##
		if(
			! $string
			|| ! is_string( $string )
		){

			w__log( 'e:>Error in string returned from markup' );

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
