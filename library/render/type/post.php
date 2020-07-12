<?php

namespace q\willow\render\type;

use q\willow\core;
use q\core\helper as h;
// use q\ui;
use q\get;
use q\willow\render;

class post extends render\type {

	/**
     * WP Post handler
     *  
     * 
     **/ 
    public static function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null ): string {

		// start with null ##
		$string = null;

		// special fields first ?? ##
		switch( $type_field ) {

			case 'post_ID' :

				$string = $wp_post->ID;

			break ;

			case 'post_title' :

				$string = $wp_post->post_title;

			break ;

			// human readable date ##
			case 'post_date_human' :

				$string = \human_time_diff( 
					\get_the_date( 
						'U', // standard ##
						$wp_post->ID 
					), \current_time('timestamp') );

			break ;

			// formatted date ##
			case 'post_date' :

				$string = 
					\get_the_date( 
						isset( self::$args['date_format'] ) ? 
						self::$args['date_format'] : // take from value passed by caller ##
							core\config::get([ 'context' => 'global', 'task' => 'config', 'property' => 'date_format' ]) ?: // global config ##
							\apply_filters( 'q/format/date', 'F j, Y' ), // standard ##
						// $wp_post->post_date, 
						$wp_post->ID
					);

				// h::log( 'post_date: '.$string );
				
			break ;

			case 'post_permalink' :

				$string = \get_permalink( $wp_post->ID );

			break ;

			case 'post_is_sticky' :

				$string = \is_sticky( $wp_post->ID ) ? 'sticky' : 'not_sticky' ;

			break ;

			case 'post_excerpt' :

				$string = $wp_post->post_excerpt;

				// if is_search - highlight ##
				if ( \is_search() ) {

					$string = 
						render\method::search_the_content([
							'string' 	=> \apply_filters( 'q/get/wp/post_content', $wp_post->post_content ),
							'limit'		=> isset( self::$args['length'] ) ? self::$args['length'] : 100
						]) ? 
						render\method::search_the_content([
							'string' 	=> \strip_shortcodes(\apply_filters( 'q/get/wp/post_content', $wp_post->post_content )),
							'limit'		=> isset( self::$args['length'] ) ? self::$args['length'] : 100
						]) : 
						$wp_post->post_excerpt ;

				}

			break ;

		}

		// __magic__ fields ##
		if ( 
			$wp_post->$type_field
			&& ( 
				empty( $string ) 
				|| is_null( $string ) 
			) 
		) {

			// h::log( 'Field: "'.$field.'" value magically set to: '.render\method::chop( $wp_post->$type_field, 50 ) );

			$string = $wp_post->$type_field;

		}

        // kick back ##
        return $string;

    }



}
