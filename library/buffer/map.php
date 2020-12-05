<?php

namespace Q\willow\buffer;

use Q\willow;

class map {

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
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public function prepare() {

		// sanity ##
		if ( 
			is_null( $this->plugin->get( '_buffer_map') )
			|| ! is_array( $this->plugin->get( '_buffer_map') )
			// || is_null( $this->plugin->get( '_buffer_map' )['0'] )
		){

			// log ##
			$this->plugin->log( 'e:>$buffer_map is empty, so nothing to prepare.. stopping here.');

			// kick out ##
			return false;

		}

		// get orignal markup string ##
		$string = $this->plugin->get( '_markup_template');

		// $this->plugin->log( $this->plugin->get( '_buffer_map') );

		// pre format child willows, moving output into parent rows ##
		foreach( $this->plugin->get( '_buffer_map') as $key => $value ){

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
				! $row = $this->get_key_from_value( 'tag', $value['parent'] )
			){

				continue;

			}

			// $this->plugin->log( 'Row: '.$value['hash'].' is a child to: '.$this->plugin->get( '_buffer_map' )[ $row ]['hash'] );

			// str_replace the value of "tag" in this key, in the "output" of the found key with "output" from this key... ##
			$this->plugin->get( '_buffer_map' )[ $row ]['output'] = str_replace( 
				$value['tag'], 
				$value['output'], 
				$this->plugin->get( '_buffer_map' )[ $row ]['output'] 
			);

		}

		// $this->plugin->log( $this->plugin->get( '_buffer_map' ) );
		// $this->plugin->log( $this->plugin->get( '_buffer_log' ) );
		// $this->plugin->log( $string );
		// $return = '';

		// now, search and replace tags in parent with tags from buffer_map ##
		foreach( $this->plugin->get( '_buffer_map' ) as $key => $value ){

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
				strpos( $string, $value['tag'] ) === false
			){

				$this->plugin->log( 'e:>'.$value['hash'].' -> Unable to locate: '.$value['tag'].' in buffer' );

				continue;

			}

			// replacement ##
			$string = str_replace( $value['tag'], $value['output'], $string );

		}

		// $this->plugin->log( $string );

		// kick back ##
		return $string;

	}


	protected function get_key_from_value( $key = null, $value = null ){

		// sanity ##
		if( 
			is_null( $key )
			|| is_null( $value )
		){

			$this->plugin->log( 'e:>Error in passed arguments' );

			return false;

		}

		// $this->plugin->log( 'searching for: '. $value.' in row: '.$key );

		foreach( $this->plugin->get( '_buffer_map' ) as $key_map => $value_map ){

			if ( isset( $value_map[$key] ) && $value_map[$key] == $value ) {

				// $this->plugin->log( 'key '.$key.' found in row: '.$key_map );

				return $key_map;

			}

		}

		// negative, if not found by now ##
		return false;

		/*
		$result = array_search( $value, array_column( $this->plugin->get( '_buffer_map' ), $key ) );
		$keys = array_keys(array_column( $this->plugin->get( '_buffer_map' ), $key ), $value );
		$this->plugin->log( $keys );
		*/
		/*
		if( 
			! isset( $this->plugin->get( '_buffer_map' )[$result] )
		){

			$this->plugin->log( 'e:>Error finding key: '.$result );

			return false;

		}

		// $this->plugin->log( 'key found in row: '.$result );

		return $result;
		*/

	}

}
