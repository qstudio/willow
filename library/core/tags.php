<?php

namespace willow\core;

// use classes ##
// use willow\core\helper as h;
use willow;

class tags {

	private 
		$plugin = false,
		$filtered_tags = null
	;

	/**
	 * Scan for partials in markup and convert to variables and $fields
	 * 
	 * @since 4.1.0
	*/
	public function __construct( \willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	private function map( $tag = null ){

		// sanity ##
		if ( 
			is_null( $tag )
		){

			w__log( 'e:>No tag passed to map');

			return false;

		}

		// load tags ##
		$this->cache();

		// check for class property ##
		if (
			null === $this->filtered_tags
		){

			w__log( 'e:>filtered_tags are not loaded..');

			return false;

		}

		// build map ##
		$tag_map = [

			'wil_o' => $this->filtered_tags['willow']['open'],
			'wil_c' => $this->filtered_tags['willow']['close'],

			'var_o' => $this->filtered_tags['variable']['open'],
			'var_c' => $this->filtered_tags['variable']['close'],
			
			'loo_o' => $this->filtered_tags['loop']['open'],
			'loo_c' => $this->filtered_tags['loop']['close'],

			'i18n_o' => $this->filtered_tags['i18n']['open'],
			'i18n_c' => $this->filtered_tags['i18n']['close'],
			
			'php_fun_o' => $this->filtered_tags['php_function']['open'],
			'php_fun_c' => $this->filtered_tags['php_function']['close'],

			'php_var_o' => $this->filtered_tags['php_variable']['open'],
			'php_var_c' => $this->filtered_tags['php_variable']['close'],
			
			'arg_o' => $this->filtered_tags['argument']['open'],
			'arg_c' => $this->filtered_tags['argument']['close'],

			'sco_o' => $this->filtered_tags['scope']['open'],
			'sco_c' => $this->filtered_tags['scope']['close'],
			
			'par_o' => $this->filtered_tags['partial']['open'],
			'par_c' => $this->filtered_tags['partial']['close'],
			
			'com_o' => $this->filtered_tags['comment']['open'],
			'com_c' => $this->filtered_tags['comment']['close'],
			
			'fla_o' => $this->filtered_tags['flag']['open'],
			'fla_c' => $this->filtered_tags['flag']['close'],

		];

		// @todo -- full back, in case not requested via shortcode ##
		// if ( ! isset( $tag_map[$tag] ) ){

			// return isset @todo...

		// }

		// search for and return matching key, if found ##
		return $tag_map[$tag] ?: false ;

	}



	/**
	 * Cache tags, and run filter once per load 
	*/
	protected function cache(){

		// check if we have already filtered load ##
		if ( $this->filtered_tags ){

			return $this->filtered_tags;

		}
		
		// filter tags once per load ##
		return $this->filtered_tags = \apply_filters( 'willow/render/tags', $this->plugin->get( '_tags' ) );

	}



	/**
	 * Wrap string in defined tags
	*/
	public function wrap( $args = null ){

		// sanity ##
		if (
			! isset( $args )
			|| ! is_array( $args )
			|| ! isset( $args['open'] )
			|| ! isset( $args['value'] )
			|| ! isset( $args['close'] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// check ##
		if (
			! $this->map( $args['open'] )
			|| ! $this->map( $args['close'] )
		){

			w__log( 'e:>Error collecting open or close tags' );

			return false;

		}

		// gather data ##
		$string = $this->map( $args['open'] ).$args['value'].$this->map( $args['close'] );
		
		// replace method, white space aware ##
		if ( 
			isset( $args['replace'] )
		){

			$array = [];
			$array[] = $this->map( $args['open'] ).$args['value'].$this->map( $args['close'] );
			$array[] = trim($this->map( $args['open'] )).$args['value'].trim($this->map( $args['close'] )); // trim all spaces in tags
			$array[] = rtrim($this->map( $args['open'] )).$args['value'].$this->map( $args['close'] ); // trim right on open ##
			$array[] = $this->map( $args['open'] ).$args['value'].ltrim($this->map( $args['close'] )); // trim left on close ##

			// w__log( $array );
			// w__log( 'value: "'.$args['value'].'"' );

			return $array;

		}

		// test ##
		// w__log( 'd:>'.$string );

		// return ##
		return $string;

	}

	
	/**
     * shortcut to get
	 * 
	 * @since 4.1.0
     */
    public function g( $args = null ) {

		// we can pass shortcut ( mapped ) values -- i.e "var_o" ##
		return $this->map( $args ) ?: false ;
 
	}

	
	/**
     * Get a single tag
	 * 
	 * @since 4.1.0
     */
    public function get( $args = null ) {

		// sanity ##
		if (
			is_null( $args )
			|| ! isset( $args['tag'] )
			|| ! isset( $args['method'] )
		){

			w__log('e:> No args passed to method');

			return false;

		}

		// sanity ##
		if (
			! $this->cache()
		){

			w__log('e:>Error in stored $tags');

			return false;

		}

		if (
			! isset( $this->filtered_tags[ $args['tag'] ][ $args['method'] ] )
		){

			w__log('e:>Cannot find tag: '.$args['tag'].'->'.$args['method'] );

			return false;

		}

		// w__log( $this->cache() );

		// // get tags, with filter ##
		// $tags = $this->cache();

		// looking for long form ##
		return $this->filtered_tags[ $args['tag'] ][ $args['method'] ] ;

	}



	/**
     * Get all tag definitions
	 * 
	 * @since 4.1.0
     */
    public function get_all( $args = null ) {

		// sanity ##
		if (
			is_null( $args )
		){

			w__log( 'e:> No args passed to method' );

			return false;

		}

		// sanity ## ?? what is tags ??
		if (
			null === $this->plugin->get( '_tags' )
			|| ! is_array( $this->plugin->get( '_tags' ) )
		){

			w__log( 'e:>Error in stored $tags' );

			return false;

		}

		// get tags, with filter ##
		$tags = $this->cache();

		// looking for long form ##
		$return = 
			isset( $tags ) ?
			$tags :
			false ;

		return $return;

	}


    /**
     * Define tags on a global or per process basis
	 * 
	 * @since 4.1.0
     */
    public static function set( $args = null ) {

       // @todo ##

    }


}
