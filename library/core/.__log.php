<?php

namespace q\willow\core;

use Q\willow\core;
// use Q\willow\helper as h;

class log {

	// track who called what ##
	protected
		$plugin				= null, // plugin object ##
		$file				= \WP_CONTENT_DIR."/debug.log",
		$empty 				= false, // track emptied ##
		$backtrace 			= false,
		$backtrace_key 		= false,
		$delimiters 		= [
			'array' 		=> '~>',
			'value' 		=> ':>'
		],
		$special_keys 		= [
			'd' 			=> 'debug', // shown by default
			'e' 			=> 'error',
			'n' 			=> 'notice',
			// 'l' 			=> 'log',
			't'				=> 'todo'
		],
		$key_array 			= [],
		$on_run 			= true,
		$on_shutdown 		= true,
		$shutdown_key 		= [ 'error' ], // control log keys ##
		$shutdown_key_debug = [ 'debug', 'todo' ] // control debug keys ##
	;

	/**
	 * CLass Constructer 
	*/
	function __construct( $plugin = null ){

		// Log::write( $plugin );

        // grab passed plugin object ## 
        $this->plugin = $plugin;

	}

	public function hooks(){

		// filter pre-defined actions ##
		$on_run 			= \apply_filters( 'willow/core/log/on_run', $this->on_run );
		$on_shutdown 		= \apply_filters( 'willow/core/log/on_shutdown', $this->on_shutdown );

		// on_run set to true ##
		if ( $on_run ) {

			// earliest possible action.. empty log ##
			if( ! class_exists( 'Q' ) ) $this->empty();  

			// also, pre-ajax ##
			if( 
				defined('DOING_AJAX') 
				&& DOING_AJAX
			) {

				// h::hard_log( 'DOING AJAX...' );
				if( ! class_exists( 'Q' ) ) $this->empty();

			}

		}

		if ( $on_shutdown ) {

			// latest possible action, write to error_log ##
			register_shutdown_function( [ $this, 'shutdown' ] );

		}

	}



	private function get_backtrace( $args = null ){

		// called directly, else called from h::log() ##
		// $level_function = apply_filters( 'willow/core/log/backtrace/function', 3 );
		// $level_file = apply_filters( 'willow/core/log/backtrace/file', 2 );

		$backtrace_1 = core\method::backtrace([ 
			'level' 	=> \apply_filters( 'willow/core/log/backtrace/function', 3 ), 
			'return' 	=> 'class_function' 
		]);

		// format for key usage ##
		$backtrace_1 = strtolower( str_replace( [ '()' ], [ '' ], $backtrace_1 ) );
			
		$backtrace_2 = core\method::backtrace([ 
			'level' 	=> \apply_filters( 'willow/core/log/backtrace/file', 2 ), 
			'return' 	=> 'file_line' 
		]);

		// $this->backtrace = ' -> '.$backtrace_1.' - '.$backtrace_2;
		$this->backtrace = ' -> '.$backtrace_2;
		$this->backtrace_key = $backtrace_1;
		// h::hard_log( $backtrace );
		// $log = $log.' - '.$backtrace;

	}



	/**
     * Store logs, to render at end of process
     * 
     */
    public function set( $args = null ){

		// test ##
		// h::hard_log( $args );

		// add info about calling function ##
		$this->get_backtrace( $args );
		
		// sanity ##
		if (
			! isset( $args )
			|| is_null( $args )
			// || ! isset( $args['log'] )
		){

			// h::hard_log( 'd:>Problem with passed args' );
			// h::hard_log( $args );

			return false;

		}

		// translate passed log
		// check we have what we need to set a new log point ##
		if ( 
			! $log = $this->translate( $args )
		){

			// h::hard_log( 'Error in passed log data..' );
			// h::hard_log( $args );

			return false;

		}

		// kick back ##
		return true;

	}



	/**
	 * Hardcore way to directly set a log key and value.. no safety here..
	*/
	public function set_to( $key = null, $value = null ){

		// sanity @todo ##
		$this->log[$key] = $value;

	}
	
	

	/**
     * Translate Log Message
	 * 
	 * Possible formats
	 * - array, object, int - direct dump
	 * - string - "hello"
	 * - $log[] = value - "l:>hello"
     * - $notice[] = value - "n:>hello"
	 * - $error[] = value - "e:>hello"
	 * - $group[] = value - "group:>hello"
	 * - $key[$key][$key] = value - "n~>group~>-problem:>this is a string"
     */
    private function translate( $args = null ){

		// arrays and objects are dumped directly ##
		if ( 
			is_int( $args )
			|| is_numeric( $args )
		){

			// h::hard_log( 'is_int OR is_numeric' );
			$return = $this->push( 'debug', print_r( $args, true ).$this->backtrace, $this->backtrace_key );
			
			// var_dump( $return );

			return $return;

		}

		// arrays and objects are dumped directly ##
		if ( 
			is_array( $args )
			|| is_object( $args )
		){

			// h::hard_log( 'is_array OR is_object' );
			$this->push( 'debug', ( is_object( $args ) ? 'Object' : 'Array' ).' below from'.$this->backtrace, $this->backtrace_key );
			$return = $this->push( 'debug', print_r( $args, true ), $this->backtrace_key );
			
			// var_dump( $return );

			return $return;

		}

		// bool ##
		if (
			is_bool( $args )
		){

			// h::hard_log( 'is_bool' );
			$return =  $this->push( 'debug', ( true === $args ? 'boolean:true' : 'boolean:false' ).$this->backtrace, $this->backtrace_key );

			// var_dump( $return );

			return $return;

		}

		// filter delimters ##
		$this->delimiters = \apply_filters( 'willow/core/log/delimiters', $this->delimiters );

		// string ##
		if ( 
			is_string( $args ) 
		) {

			// h::hard_log( 'is_string' );

			// string might be a normal string, or contain markdown to represent an array of data ##

			// no fixed pattern ##
			if ( ! core\method::strposa( $args, $this->delimiters ) ) {

				// h::hard_log( 'string has no known delimiters, so treat as log:>value' );
				$return = $this->push( 'debug', $args.$this->backtrace, $this->backtrace_key );

				// var_dump( $return );

				return $return;

			}

			// string ##
			if ( 
				false === strpos( $args, $this->delimiters['array'] ) 
				&& false !== strpos( $args, $this->delimiters['value'] ) 
			) {
			
				// h::hard_log( 'only key:value delimiters found..' );

				// get some ##
				$key_value = explode( $this->delimiters['value'], $args );
				// h::hard_log( $key_value );

				$key = $key_value[0];
				$value = $key_value[1];

				// h::hard_log( "d:>key: $key + value: $value" );

				// return with special key replacement check ##
				$return = $this->push( $this->key_replace( $key ), $value.$this->backtrace, $this->backtrace_key );

				// var_dump( $return );

				return $return;

			}

			// array ##
			if ( 
				false !== strpos( $args, $this->delimiters['array'] ) 
				&& false !== strpos( $args, $this->delimiters['value'] ) 
			) {
			
				// h::hard_log( 'both array and value delimiters found..' );

				// get some ##
				$array_key_value = explode( $this->delimiters['value'], $args );
				// h::hard_log( $array_key_value );

				$value_keys = $array_key_value[0];
				$value = $array_key_value[1];

				$keys = explode( $this->delimiters['array'], $value_keys );
				
				// h::hard_log( $keys );
				// h::hard_log( "l:>$value" );

				$return = $this->push( $keys, $value.$this->backtrace, $this->backtrace_key );

				// var_dump( $return );

				return $return;

			}

		}

        return false;

	}
	


	/**
	 * Push item into the array, checking if selected key already exists 
	 */
	private function push( $key = null, $value = null, $new_key = '' ){

		// @todo - sanity ##

		// h::hard_log( '$value: '.$value );
		// h::hard_log( \willow::$log );

		// grab reference of $this->log ##
		$log = $this->plugin->get( '_log' );

		// check if $key already exists ##
		if ( 
			is_string( $key )
			|| (
				is_array( $key )
				&& 1 == count( $key )
			)
		){

			// take first array value, if array, else key.. ##
			$key = is_array( $key ) ? $key[0] : $key ;

			// h::hard_log( $log );

			if ( 
				! isset( $log[$key] )
			){

				$log[$key] = [];
				// h::hard_log( 'd:> create new empty array for "'.$key.'"' );

			}

			// new key is based on the class_method called when the log was set ##
			// this key might already exist, if so, add as a new array key + value ##
			$new_key = isset( $new_key ) ? $new_key : core\method::get_acronym( $value ) ;

			// make new key safe ??
			// $new_key = str_replace( [ '\\','::' ], '_', $new_key );

			// new key ??
			// h::hard_log( 'd:>new_key: "'.$new_key.'"' );

			// key already exists ##
			if ( 
				! isset( $log[$key][$new_key] ) 
			){

				// h::hard_log( 'd:>create new empty array in: "'.$key.'->'.$new_key.'"' );

				// create new key as empty array ##
				$log[$key][$new_key] = [];

			}

			// check if the value has been added already ##
			if ( in_array( $value, $log[$key][$new_key], true ) ) {

				// h::hard_log( 'd:> "'.$key.'->'.$new_key.'" value already exists, so skip' );

				return false;

			}

			// h::hard_log( 'd:> add value to: '.$key.'->'.$new_key.'"' );

			// add value to array ##
			$log[$key][$new_key][] = $value;

			// var_dump( $log ); echo '<br/><br/>';

			// kick back ##
			return $this->log = $log;

		}

		if(
			is_array( $key )
			&& count( $key ) > 1
		){

			// var_dump( $key ); echo '<br/><br/>';

			// manually ## 
			if (
				isset( $key[2] )
			){
				if ( ! isset( $this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ][ $this->key_replace($key[2]) ] ) ) {
					
					// h::hard_log( 'create: '.$this->key_replace($key[0]).'->'.$this->key_replace($key[1]).'->'.$this->key_replace($key[2]) );
					$this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ][ $this->key_replace($key[2]) ] = [];
				
				}

				// value exists ##
				if ( 
					in_array( $value, $this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ][ $this->key_replace($key[2]) ], true ) 
				){ 
					// h::hard_log( 'value exists: '.$this->key_replace($key[0]).'->'.$this->key_replace($key[1]).'->'.$this->key_replace($key[2]).'->'.$value );
					return false;
				};

				// h::hard_log( 'add: '.$this->key_replace($key[0]).'->'.$this->key_replace($key[1]).'->'.$this->key_replace($key[2]).'->'.$value );

				// add value and return ##
				return $this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ][ $this->key_replace($key[2]) ][] = $value;

			}

			if (
				isset( $key[1] )
			){

				if ( ! isset( $this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ] ) ) {

					// h::hard_log( 'create: '.$this->key_replace($key[0]).'->'.$this->key_replace($key[1]) );
					$this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ] = [];

				}

				// value exists ##
				if ( 
					in_array( $value, $this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ], true ) 
				){ 
					// h::hard_log( 'value exists: '.$this->key_replace($key[0]).'->'.$this->key_replace($key[1]).'->'.$value );
					return false;
				};

				// h::hard_log( 'add: '.$this->key_replace($key[0]).'->'.$this->key_replace($key[1]).'->'.$value );

				// add value and return ##
				return $this->log[ $this->key_replace($key[0]) ][ $this->key_replace($key[1]) ][] = $value;

			}

			if (
				isset( $key[0] )
			){
				if ( ! isset( $this->log[ $this->key_replace($key[0])] ) ) {

					// h::hard_log( 'create: '.$this->key_replace($key[0]) );
					$this->log[ $this->key_replace($key[0]) ] = [];

				}

				// value exists ##
				if ( 
					in_array( $value, $this->log[ $this->key_replace($key[0]) ], true ) 
				){ 
					// h::hard_log( 'value exists: '.$this->key_replace($key[0]).'->'.$value );
					return false;
				};

				// h::hard_log( 'add: '.$this->key_replace($key[0]).'->'.$value );

				// add value and return ##
				return $this->log[ $this->key_replace($key[0]) ][] = $value;

			}
			
		}

		// negative #
		return false;

	}




	public function in_multidimensional_array( $needle, $haystack ) {

		foreach( $haystack as $key => $value ) {

		   $current_key = $key;

		   if( 
			   $needle === $value 
			   || ( 
				   is_array( $value ) 
				   && $this->in_multidimensional_array( $needle, $value ) !== false 
				)
			) {

				return $current_key;

			}
		}

		return false;

	}

	

	/**
	 * Create Multidimensional array from keys ##
	 * 
	 * @link 	https://eval.in/828697 
	 */
	public function create_multidimensional_array( $array = [], $keys, $value ){    

		$tmp_array = &$array;

		while( count( $keys ) > 0 ){     

			$k = array_shift( $keys );     

			if( ! is_array( $tmp_array ) ) {

				$tmp_array = [];

			}
			$tmp_array = &$tmp_array[$this->key_replace( $k )];

		}

		$tmp_array = $value;

		return $array;

	}



	/**
	 * Special Key replacement 
	 *
	 * - e = error
	 * - n = notice
	 * - l = log ( default ) 
	 */
	private function key_replace( $key = null ){
		
		// @todo -- sanity ##

		// filter special keys ##
		$this->special_keys = \apply_filters( 'willow/core/log/special_keys', $this->special_keys );

		// lookfor key
		if ( 
			isset( $this->special_keys[$key] )
		){

			// h::hard_log( "key is special: $key" );
			return $this->special_keys[$key];

		}

		// h::hard_log( "key is NOT special: $key" );
		return $key;

	}


		
    /**
     * Logging function
     * 
     */
    protected function write( $key = null ){

		// test ##
		// $this->set( 'write: '.$key );
		// h::hard_log( $this->log );

		// if key set, check if exists, else bale ##
		if ( 
			! is_null( $key )
			&& ! isset( $this->log[ $key ] ) 
		) {

			h::hard_log( '"'.$key.'" Log is empty.' );

			return false;

		}

		// if key set, check if exists, else bale ##
		if ( 
			// array_filter( $this->log[$key] )
			// || 
			empty( $this->log[$key] )
		) {

			// $this->set( '"'.$key.'" Log is empty.' );

			return false;

		}

		// option to debug only specific key ##
		if ( isset( $key ) ) {
			
			$return = $this->log[ $key ];  // key group ##

			// empty log key ##
			unset( $this->log[ $key ] );

        } else {

			$return = $this->log ; // all

			// empty log ##
			// unset( $this->log );
			$this->log = [];

		}
			
		// create named array key, based on passed key, so easier to read log ##
		if ( ! is_null( $key ) ) { $return = [ $key => $return ]; }

		// keys are added sequentially, so we need to reverse to see the actual flow ##
		if ( is_array( $return ) ) { $return = array_reverse( $return ); }

		// clean up ##
		// $return = $this->array_unique_multidimensional( $return );

		// take first key, skip one level ##
		// $first_key = array_key_first( $return );
		// $return = $return[ $first_key ];

		// debugging is on in WP, so write to error_log ##
        if ( true === WP_DEBUG ) {

			if ( 
				is_array( $return ) 
				|| is_object( $return ) 
			) {
				// error_log( print_r( $return, true ) );
				$this->error_log( print_r( $return, true ), $this->file );
            } else {
				// error_log( $return );
				// trigger_error( $return );
				$this->error_log( $return, $this->file );
            }

		}

		// done ##
		return true;

	}


	
	/**
	 * Replacement error_log function, with custom return format 
	 * 
	 * @since 4.1.0
	 */ 
	public function error_log( $log = null, $file = null )
	{

		// sanity ##
		if(  
			is_null( $log )
		){

			return false;
			// $this->error_log( 'EMPTY...' );

		} else {

			// var_dump( $log );

		}
		
		// $displayErrors 	= ini_get( 'display_errors' );
		$log_errors     = ini_get( 'log_errors' );
		$error_log      = ini_get( 'error_log' ); #echo $error_log; 
		$file 			= ! is_null( $file ) ? $file : $error_log ;

		// if( $displayErrors ) echo $errStr.PHP_EOL;

		if( $log_errors ) {

			$message = sprintf( 
				// '[%s] %s (%s, %s)', 
				'%s', 
				// date('d-m H:i'), 
				// date('H:i'), 
				$log, 
				// $errFile, 
				// $errLine 
			);

			// file_put_contents( $error_log, $message.PHP_EOL, FILE_APPEND );
			file_put_contents( $file, $message.PHP_EOL, FILE_APPEND );
			// echo 'wrote to: '.$file;

		}

		// ok ##
		return true;

	}



	/**
     * Clear Temp Log
     * 
     */
    private function clear( $args = null ){

		// test ##
        // h::hard_log( $args );

		// sanity ##
		// ...

		// if key set, check if exists, else bale ##
		if ( 
			isset( $args['key'] )
			&& ! isset( $this->log[ $args['key'] ] ) 
		) {

			h::hard_log( 'Log key empty: "'.$args['key'].'"' );

			return false;

		}

		// h::hard_log( $this->log );

        // option to debug only specific fields ##
        if ( isset( $args['key'] ) ) {

			unset( $this->log[ $args['key'] ] );

			h::hard_log( 'n>Emptied log key: "'.$args['key'].'"' );

			return true;

		}

		unset( $this->log );

		h::hard_log( 'n>Emptied all log keys' );
		
		return true;

	}
	


	/**
     * Empty Log
     * 
     */
    private function empty( $args = null ){

		// do not save file from admin, as it will be incomplete ##
		if( 
			\is_admin() 
			|| \wp_doing_ajax()
		){ 
		
			// h::hard_log( 'd:>Attempt to empty log from admin blocked' );

			return false; 
		
		}

		// empty once -- commented out.. ##
		if( $this->empty ) { return false; }

		// empty dedicated log file ##
		$f = @fopen( $this->file, "r+" );
		if ( $f !== false ) {
			
			ftruncate($f, 0);
			fclose($f);

			// log to log ##
			// h::hard_log( 'Log Emptied: '.date('l jS \of F Y h:i:s A') );

			// track ##
			$this->empty == true;

		}

	}
	

	

	/**
     * Shutdown method
     * 
     */
    public function shutdown(){

		// check what's in the log ##
		// var_dump( $this->log );

		// filter what to write to log - defaults to "error" key ##
		$key = \apply_filters( 'willow/core/log/default', $this->shutdown_key );
		$key_debug = \apply_filters( 'willow/core/log/debug', $this->shutdown_key_debug );

		// var_dump( $key ); echo '<br/><br/>';
		// var_dump( $key_debug ); echo '<br/><br/>';

		// write specific key, as filter might return false / null ##
		if( 
			! $key 
			|| is_null( $key ) 
			|| empty( $key ) 
			// || ! isset( $this->log[ $key ] )
		){

			h::hard_log( 'd:>shutdown -- no key, so write all..' );

			// log all ##
			return $this->write();

		}

		$log = [];
		
		// log key ##
		foreach( ( array )$key as $k => $v ) {

			// h::debug( 'd:>key is: '.$v );
			
			// skip missing keys ##
			if ( ! isset( $this->log[$v] ) ) { continue; }

			// log specific key ##
			// $this->write( $v );
			$log[$v] = $this->log[$v];

		}

		// debugging so log more keys... ##
		if ( 
			$key_debug
		) {

			foreach( ( array )$key_debug as $k => $v ) {

				// skip keys already written above ##
				if ( is_array( $key ) && array_key_exists( $v, $key ) ) { continue; }

				// skip missing keys ##
				if ( ! isset( $this->log[$v] ) ) { continue; }

				// h::debug( 'd:>debug key is: '.$v );

				// log specific key ##
				// $this->write( $v );
				$log[$v] = $this->log[$v];

			}

		}

		// assign to new key ##
		$this->log['willow'] = $log;

		// write new key to log ##
		$this->write( 'willow' );

		// done ##
		return true;

	}

}
