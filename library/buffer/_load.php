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

	// public static

        // // passed args ##
        // $args 	= [
		// 	'fields'	=> []
		// ],

		// $output 	= null, // return string ##
        // $fields 	= null, // array of field names and values ##
		// $markup 	= null, // array to store passed markup and extra keys added by formatting ##
		// $log 		= null, // tracking array for feedback ##
		// $buffer 	= null // for buffering... ##
		// $buffering 	= false // for buffer switch... ##

	// ;

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

				// set buffer ##
				// self::$buffering = true;
				// render::$buffering = true;

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

			// @TODO... this needs to be more graceful, and render needs to have "blocks", which can be passed / set
			// echo theme\view\ui\header::render();
			context::ui__header();
		
			// Apply any filters to the final output
			// echo \apply_filters( 'ob_output', $string );
			echo self::prepare( $string );

			// @TODO... this needs to be more graceful, and render needs to have "blocks", which can be passed / set
			// echo theme\view\ui\footer::render();
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

		// prepare markup, fields and handlers based on passed configuration ##
		// so.. let's parser prepare an array in $buffer of hash + value.. then pass this to fields::define ??
		parse::prepare( $args );

		// h::log( render::$markup );
		// h::log( render::$buffer );

		// prepare field data ##
		// render\fields::prepare();
		// h::log( render::$buffer );
		render\fields::define( render::$buffer );

		// h::log( render::$fields );

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
		// h::log( render::$markup['template'] );
		
		// clear object cache ##
		self::$buffer = [];

		// return to OB ##
		return self::$markup['template'];

    }

}
