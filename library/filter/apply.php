<?php

namespace willow\filter;

use willow;

class apply {

	private 
		$plugin = false,
		$hooked = false // run hooks once ##
	;

	/**
	 * 
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	public function hooks(){

		if ( false === $this->hooked ) {

			// w__log( 'Adding filters..' );

			// filter tag ##
			\add_filter( 'willow/render/markup/tag', [ $this, 'tag' ], 10, 2 );

			// run once ##
			$this->hooked = true;

		}

	}

	/**
	 * Apply filters to entire {~ Willow ~} tag
	 * 
	 * @since 	1.2.0
	 * @return	String
	*/
	public function tag( $value, $key ) {

		$_filter = \willow()->get( '_filter' );
		$_args = \willow()->get( '_args' );

		// check for tag filter ##
		if( 
			isset( $_filter[ $_args['config']['hash'] ] )
			&& is_array( $_filter[ $_args['config']['hash'] ] )
		){

			// w__log( 'e:>Filters set for Willow tag: "{~ '.$_args['context'].'~'.$_args['task'].' ~}"' );
			// w__log( $_filter[ $_args['config']['hash'] ] );
			// w__log( $value );

			// get filters ##
			// $filters = filter\method::prepare([ 'filters' => $_filter[ $_args['config']['hash'] ] ]);

			// w__log( $filters );

			// store pre-filter value ##
			$pre_value = $value; 

			// bounce to filter::apply()
			$filter_value = \willow()->filter->process([ 
				'filters' 	=> $_filter[ $_args['config']['hash'] ], 
				'string' 	=> $value, 
				'use' 		=> 'tag', // for filters ##
				// 'key'		=> $key
			]);

			// compare pre and post filter values ##
			if( $filter_value != $pre_value ){

				// w__log( 'd:>Filtered value is different: '.$filter_value );

				// run unique str_replace on whole variable ##
				// $string = str_replace( $var_value, $filter_value, $string );

				return $filter_value;

			}

			return $value;

		}

		// nothing cooking -- return raw value ##
		return $value;

	}

}
