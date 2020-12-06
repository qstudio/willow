<?php

namespace Q\willow\type;

use Q\willow\core\helper as h;
use Q\willow;

class post {

	private 
		$plugin = false,
		$type_method = false
	;

	/**
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

		// get types ##
		$this->type_method = new willow\type\method( $this->plugin );

	}

	/**
     * WP Post handler
     *  
     * 
     **/ 
    public function format( \WP_Post $wp_post = null, String $type_field = null, String $field = null, $context = null ): string {

		// check if type allowed ##
		if ( ! array_key_exists( __CLASS__, $this->type_method->get_allowed() ) ) {

			// w__log( 'e:>Value Type not allowed: '.__CLASS__ );

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:Value Type not allowed: "'.__CLASS__.'"');

			// return $args[0]->$args[1]; // WHY ??#
			return false;

		}

		// $value needs to be a WP_Post object ##
		if ( ! $wp_post instanceof \WP_Post ) {

			// log ##
			w__log( $this->plugin->get( '_args' )['task'].'~>e:Error in pased $args - not a WP_Post object');

			return false;

		}

		// start with empty string ##
		$string = '';

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
						isset( $this->plugin->get( '_args' )['date_format'] ) ? 
						$this->plugin->get( '_args' )['date_format'] : // take from value passed by caller ##
							core\config::get([ 'context' => 'global', 'task' => 'config', 'property' => 'date_format' ]) ?: // global config ##
							\apply_filters( 'q/format/date', 'F j, Y' ), // standard ##
						// $wp_post->post_date, 
						$wp_post->ID
					);

				// w__log( 'post_date: '.$string );
				
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
							'limit'		=> isset( $this->plugin->get( '_args' )['length'] ) ? $this->plugin->get( '_args' )['length'] : 100
						]) ? 
						render\method::search_the_content([
							'string' 	=> \strip_shortcodes(\apply_filters( 'q/get/wp/post_content', $wp_post->post_content )),
							'limit'		=> isset( $this->plugin->get( '_args' )['length'] ) ? $this->plugin->get( '_args' )['length'] : 100
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

			// w__log( 'Field: "'.$field.'" value magically set to: '.render\method::chop( $wp_post->$type_field, 50 ) );

			$string = $wp_post->$type_field;

		}

        // kick back ##
        return $string;

    }



}
