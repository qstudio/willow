<?php

namespace q\willow;

use q\core;
use q\core\helper as h;
use q\willow;
use q\willow\render;
// use q\view;
// use q\willow;

\q\willow\buffer::run();

class buffer extends \q_willow {

	/**
	 * Check for view template and start OB, if correct
	*/
	public static function run(){

		// not on admin ##
		if ( \is_admin() ) return false;

		// \add_action( 'get_header',  [ get_class(), 'ob_start' ], 0 ); // try -- template_redirect.. was init
		\add_action( 'get_header',  function(){ 
			
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

			// Header ##
			h::log( 't:>@TODO... header inclusion needs to be more graceful, and render needs to have "blocks", which can be passed / set');
			context::ui__header();
		
			// Apply any filters to the final output
			// echo \apply_filters( 'ob_output', $string );
			echo self::prepare( $string );

			// Footer, basically just wp_footer() + closing body / html tags  ##
			context::ui__footer();

		}, 0);

	}


	/**
	 * Prepare output for Buffer
	 * 
	 * @since 4.1.0
	*/
    public static function prepare( String $string = null ) {

		// h::log( $args );

		// sanity ##
		if ( 
			is_null( $string )
		){

			// log ##
			h::log( 'e:>$buffer is empty, so nothing to render.. stopping here.');

			// kick out ##
			return false;

		}

		// we are passed an html string, captured from output buffering, which we need to parse for tags and process ##
		// h::log( $string );

		// build required args ##
		$args = [
			'config'		=> [
				'return' 	=> 'return',
				'debug'		=> false
			],
			'markup'		=> [
				'template'	=> $string
			],
			'context'		=> 'buffer',
			'task'			=> 'prepare',
		];

		// force methods to return for collection by output buffer ##
		self::$args_default['config']['return'] = 'return';
		
		// reset args ##
		willow\render\args::reset();

		// extract markup from passed args ##
		render\markup::pre_validate( $args );

		// validate passed args ##
		if ( ! render\args::validate( $args ) ) {

			render\log::set( $args );
			
			h::log( 'e:>Args validation failed' );

			// reset all args ##
			render\args::reset();

			return false;

		}

		// prepare .willow template markup ##
		// functions
		parse::prepare( $args );

		// h::log( self::$markup );
		// h::log( self::$buffer );

		// prepare field data ##
		// render\fields::prepare();
		// h::log( render::$buffer );

		// buffer contains all the {{ variables }} + data from the template
		if ( self::$buffer ) render\fields::define( self::$buffer );

		// h::log( self::$buffer );

		// Prepare template markup ##
		render\markup::prepare();

		// // Prepare template markup ##
		// render\parse::cleanup();

		// optional logging to show removals and stats ##
		render\log::set( $args );

		// assign output to markup->template ##
		self::$markup['template'] = render\output::prepare();

		// clean up left over tags ##
		willow\parse::cleanup();

		// check what we have ##
		// h::log( self::$buffer );
		// h::log( self::$markup['template'] );
		
		// clear object cache ##
		self::$buffer = [];

		// return to OB to render in template ##
		return self::$markup['template'];

    }

}
