<?php

namespace q\willow\get;

use q\willow\core;
use q\willow\core\helper as h;
use q\willow\get;

class media extends \q\willow\get {


    /**
     * Check for a return src and exif-data from attachment ID ##
     *
     */
    public static function src( $args = null )
    {

		// sanity ##
		if (
			is_null( $args )
			|| ! isset( $args['attachment_id'] )
			// || ! isset( $args['handle'] )
		){

			h::log( 'e:>Error in passed args' );

			return false;

		}

		// check and assign ##
		// h::log( render::$args );
		// h::log( $args );
		// handle could be assigned in a few ways -- so, let's check them all in specific to generic order ##
		// from passed args ##
		if ( 
			isset( $args['handle'] ) 
		){

			// nothing to do ##

		// handle filtered into config by markup pre-processor ##
		} else if ( 
			class_exists( 'willow' )
			&& isset( \willow::$args ) // @TODO - should be self::$args - test ##
			&& isset( $args['field'] )
			&& isset( \willow::$args[ $args['field'] ]['config']['handle'] ) 
		){

			$args['handle'] = \willow::$args[ $args['field'] ]['config']['handle'];

		// filterable default ##
		} else {

			$args['handle'] = \apply_filters( 'q/get/media/src/handle', 'medium' );

		}

        // $args['handle'] = 
        //     isset( self::$args['field']['src']['handle'] ) ?
        //     self::$args['field']['src']['handle'] : // get handle defined in calling args ##
        //     \apply_filters( 'q/render/type/src/handle', 'medium' ); // filterable default ##

        // h::log( 'Handle: '.$args['handle'] );

        // test incoming args ##
        // h::log( \willow::$args[ $args['field'] ] );

        // set-up a new array ##
        $array = [];

        // self::log( 'Handle: '.$args['handle'] );
		if ( ! $src = \wp_get_attachment_image_src( $args['attachment_id'], $args['handle'] ) ){

			h::log( \willow::$args['task'].'~>n wp_get_attachment_image_src did not return data' );

			return false;

		}

		// h::log( $src );
		
		// take array items ##
		$array['src'] = $src[0];
		$array['src_width'] = $src[1];
		$array['src_height'] = $src[2];

		$array['src_alt'] = 
			\get_post_meta( $args['attachment_id'], '_wp_attachment_image_alt', true ) ?
			\get_post_meta( $args['attachment_id'], '_wp_attachment_image_alt', true ) :
			\get_the_title( $args['post'] );

		// image found ? ##
		if ( ! $array['src'] ) { 
		
			h::log( 'd:>array->src missing, so cannot continue...' );

			return false; 
		
		}
		
		// h::log( 't:>MEDIA config is not respecting passed values from willow or task specific config - why not??' );
		// conditional -- add img caption ##
		if ( 
			// set locally..
			(
				class_exists( 'willow' )
				&& isset( \willow::$args['config']['meta'] )
				&& true === \willow::$args['config']['meta'] 
			)
			/*
			||
			// OR, set globally ##
			(
				class_exists( 'willow' )
				&& isset( \q\willow\core\config::get([ 'context' => 'media', 'task' => 'config' ])['meta'] )
				&& true == \q\willow\core\config::get([ 'context' => 'media', 'task' => 'config' ])['meta']
			)
			*/
		) {

			// h::log( 'd:>Adding media meta' );

			// add caption values ##
			$array = array_merge( 
				self::meta( $args ), 
				$array
			);
		
		}

		// h::log( 't:>global / local logic is wrong, as global always overrules local... look into that..' );
		// conditional -- add img meta values ( sizes ) and srcset ##
        if ( 
			// set locally..
			(	
				class_exists( 'willow' )
				&& isset( \willow::$args['config']['srcset'] )
            	&& true === \willow::$args['config']['srcset'] 
			)
			/*
			||
			// OR, set globally ##
			(
				class_exists( 'willow' )
				&& isset( \q\willow\core\config::get([ 'context' => 'media', 'task' => 'config' ])['srcset'] )
				&& true == \q\willow\core\config::get([ 'context' => 'media', 'task' => 'config' ])['srcset']
			)
			*/
        ) {

			// h::log( 'd:>Adding srcset' );

			// add srcset values ##
			$array = array_merge( 
				self::srcset( $args ), 
				$array
			);

		}

        // image found ? ##
		// if ( ! $array['src'] ) { return false; }
		
		// h::log( $array );

        // kick back array ##
        return $array;

    }


}
