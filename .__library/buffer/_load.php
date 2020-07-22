<?php

namespace q\willow;

use q\core;
use q\core\helper as h;
use q\willow;
use q\willow\render;

\q\willow\buffer::run();

class buffer extends \q_willow {

	// public static $filter;

	/**
	 * Check for view template and start OB, if correct
	*/
	public static function run(){

		// start here ##
		self::$filter = [];

		// not on admin ##
		if ( \is_admin() ) return false;

		// \add_action( 'get_header',  [ get_class(), 'ob_start' ], 0 ); // try -- template_redirect.. was init
		\add_action( 'init',  function(){ 
			
			if ( 'willow' == \q\view\is::format() ){

				// h::log( 'd:>starting OB, as on a willow template: "'.\q\view\is::format().'"' );

				return ob_start();

			}

			// h::log( 'd:>not a willow template, so no ob: "'.\q\view\is::format().'"' );

			return false; 
		}
		, 0 ); 

		\add_action( 'shutdown', function() {

			if ( 'willow' != \q\view\is::format() ){

				// h::log( 'e:>No buffer.. so no go' );
				
				return false; 
			
			}

			// h::log( 'e:>Doing shutdown buffer' );

			$string = '';
		
			// We'll need to get the number of ob levels we're in, so that we can iterate over each, collecting
			// that buffer's output into the final output.
			$levels = ob_get_level();
		
			for ($i = 0; $i < $levels; $i++) {
				$string .= ob_get_clean();
			}

			// ob_flush();
			if( ob_get_level() > 0 ) ob_flush();

			// Header ##
			// h::log( 't:>@TODO... header inclusion needs to be more graceful, and render needs to have "blocks", which can be passed / set');
			// context::ui__head([ 'config' => [ 'embed' => true ]]);
			// context::ui__header([ 'config' => [ 'embed' => true ]]);
		
			// Apply any filters to the final output
			// echo \apply_filters( 'ob_output', $string );
			echo self::prepare( $string );

			// Footer, basically just wp_footer() + closing body / html tags  ##
			// context::ui__footer([ 'config' => [ 'embed' => true ]]);

			// reset all args ##
			render\args::reset();

		}, 0);

	}


	/**
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( String $string = null ) {

		// sanity ##
		if ( 
			is_null( $string )
		){

			// log ##
			h::log( 'e:>$buffer is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

		// h::log( 'e:>Running Buffer::Prepare: '.rand() );

		// we are passed an html string, captured from output buffering, which we need to parse for tags and process ##
		// h::log( $string );

		// build required args ##
		self::$buffer_args 		= [
			'config'			=> [
				'return' 		=> 'return',
				'debug'			=> false,
			],
			'context'			=> 'buffer',
			'task'				=> 'prepare',
		];

		// take buffer output string as markup->template ##
		self::$buffer_markup = $string;

		// force methods to return for collection by output buffer ##
		self::$args_default['config']['return'] = 'return';

		// prepare .willow template markup ##
		willow\parse::prepare( self::$buffer_args, 'buffer' );

		// h::log( self::$buffer_markup );
		// h::log( self::$buffer_fields );
		// h::log( 'Buffer Field Count: '.count( self::$buffer_fields) );

		// get var tags ##
		$open = trim( willow\tags::g( 'var_o' ) );
		$close = trim( willow\tags::g( 'var_c' ) );

		// loop over each field, replacing variables with values ##
        foreach( self::$buffer_fields as $key => $value ) {

			$regex = \apply_filters( 'q/render/markup/buffer', "~\\$open\s+$key\s+\\$close~" ); 

			// variable replacement -- regex way ##
			self::$buffer_markup = preg_replace( $regex, $value, self::$buffer_markup ); 

		}

		// h::log( self::$buffer_markup );

		// clean up left over tags ##
		willow\parse::cleanup( self::$buffer_args, 'buffer' );

		// h::log( self::$buffer_markup );

		// clear buffer objects ##
		self::$buffer_fields = [];
		self::$buffer_args = [];
		self::$filter = null;

		// return to OB to render in template ##
		return self::$buffer_markup;

    }

}
