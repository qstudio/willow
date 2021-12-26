<?php

namespace willow\buffer;

use willow;
use willow\core\helper as h;

class map {

	private 
		$plugin = false
	;

	/**
     * @todo
     * 
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public function prepare() {

		// get orignal markup string ##
		$_markup_template = \willow()->get( '_markup_template' );

		// get buffer map ##
		$_buffer_map = \willow()->get( '_buffer_map' );
		// w__log( $_buffer_map );

		// sanity ##
		if ( 
			is_null( $_buffer_map )
			|| ! is_array( $_buffer_map )
			// || is_null( \willow()->get( '_buffer_map' )['0'] )
		){

			// log ##
			w__log( 'e:>$_buffer_map is empty, so nothing to prepare.. stopping here.');

			// kick out ##
			return false;

		}

		// pre format child willows, moving output into parent rows ##
		foreach( $_buffer_map as $key => $value ){

			if( 
				// '0' == $key // skip first key, this contains the buffer markup ##
				// ||
				! $value['parent'] // skip rows without a parent value ( primary parsed elements ) ##
			){

				continue;

			}

			// // if $value['parent'] set, then take
			// if( $value['parent'] ){

			if ( 
				! $row = $this->get_key_from_value( $_buffer_map, 'tag', $value['parent'] )
			){

				continue;

			}

			// w__log( 'Row: '.$value['hash'].' is a child to: '.\willow()->get( '_buffer_map' )[ $row ]['hash'] );

			// str_replace the value of "tag" in this key, in the "output" of the found key with "output" from this key... ##
			// self::$buffer_map[ $row ]['output'] = str_replace( $value['tag'], $value['output'], self::$buffer_map[ $row ]['output'] );
			// $_buffer_map = \willow()->get( '_buffer_map' );
			$_buffer_map[ $row ]['output'] = str_replace( 
				$value['tag'], 
				$value['output'], 
				$_buffer_map[ $row ]['output'] // \willow()->get( '_buffer_map' )[ $row ]['output'] 
			);

		}

		// w__log( \willow()->get( '_buffer_map' ) );
		// w__log( \willow()->get( '_buffer_log' ) );
		// w__log( $_markup_template );
		// $return = '';

		// now, search and replace tags in parent with tags from buffer_map ##
		foreach( $_buffer_map as $key => $value ){

			// w__log( $value );

			// skip first row or rows which do not have a parent ##
			if( 
				// '0' == $key 
				// || 
				$value['parent'] // skip rows with a parent value ##
				|| ! isset( $value['hash'] ) // skip rows without a hash ###
			){

				continue;

			}

			// check if we have string, so we can warm if not ##
			if( 
				strpos( $_markup_template, $value['tag'] ) === false
			){

				w__log( 'e:>'.$value['hash'].' -> Unable to locate: '.$value['tag'].' in buffer' );

				continue;

			}

			// replacement ##
			$_markup_template = str_replace( $value['tag'], $value['output'], $_markup_template );

		}

		// check ##
		// w__log( $_markup_template );
		// // w__log( $string );

		// save buffer map ##
		\willow()->set( '_buffer_map', $_buffer_map );

		// kick back ##
		return $_markup_template;

	}


	protected function get_key_from_value( $_buffer_map = null, $key = null, $value = null ){

		// sanity ##
		if( 
			is_null( $key )
			|| is_null( $value )
		){

			w__log( 'e:>Error in passed arguments' );

			return false;

		}

		// w__log( 'searching for: '. $value.' in row: '.$key );

		foreach( $_buffer_map as $key_map => $value_map ){

			if ( isset( $value_map[$key] ) && $value_map[$key] == $value ) {

				// w__log( 'key '.$key.' found in row: '.$key_map );

				return $key_map;

			}

		}

		// negative, if not found by now ##
		return false;

	}

}
