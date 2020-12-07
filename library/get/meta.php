<?php

namespace Q\willow\get;

use Q\willow;
use Q\willow\core\helper as h;

class meta {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct( \Q\willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}

	/**
     * link to parent, works for single WP Post or page objects
     *
     * @since       1.0.1
     * @return      string   HTML
     */
    public function parent( $args = null ) {

		// w__log( 'here..' );
		// w__log( $args );

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

        // set-up new array ##
		$array = [];
		
		// pages might have a parent
		if ( 
			'page' == $args['config']['post']->post_type
			&& $args['config']['post']->post_parent
			&& \get_post( $args['config']['post']->post_parent )
		) {

			$parent = \get_post( $args['config']['post']->post_parent );

            $array['permalink'] = \get_permalink( $parent->ID );
            $array['slug'] = $parent->post_name;
            $array['title'] = $parent->post_title;

		// is singular post ##
		} elseif ( \is_single( $args['config']['post'] ) ) {

			// w__log( 'd:>Get category title..' );

			// $args->ID = $the_post->post_parent;
			if ( 
				! $terms = willow\get\post::object_terms([ 
					'config' 		=> [ 
						'post'		=> $args['config']['post']
					],
					'taxonomy'		=> 'category',
					'args' 			=> [
						'number'	=> 1
					]
				])
					
			){

				w__log( 'e:>Returned terms empty' );

				return false;

			}

			// w__log( $terms );

			// we expect an array with 1 key [0] of WP_Term object - validate ##
			if (
			 	! is_array( $terms )
			 	|| ! isset( $terms[0] )
			 	|| ! $terms[0] instanceof \WP_Term
			){

			 	w__log( 'e:>Error in returned terms data' );

			 	return false;

			}

			$array['permalink'] = \get_category_link( $terms[0] );
			$array['slug'] = $terms[0]->slug;
			$array['title'] = $terms[0]->name;

		}

		// w__log( $array );

        // return ##
		return willow\get\method::prepare_return( $args, $array );

	}



	
	/**
     * Get Post meta field from acf, format if required and markup
     *
     * @since       4.1.0
     */
    public function field( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
			// || ! isset( $args['field'] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// pst ID ##
		$post_id = isset( $args['config']['post'] ) ? $args['config']['post']->ID : null ;

		// get field ##
		if ( $value = \get_field( $args['task'], $post_id ) ) {

			// w__log( $value );

			return $value;

		}

		w__log( 'e:>get_field retuned no data - field: "'.$args['task'].'"');
		
		// return ##
		return false;

	}

	/**
	 * Get post author
	 * 
	 * @since 4.1.0
	*/
	public function author( $args = null ) {

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// get post ##
		$post = $args['config']['post'];
		
		// get author ##
		$author = $post->post_author;
		$authordata = \get_userdata( $author );

		// validate ##
		if (
			! $authordata
		) {

			w__log( 'd:>Error in returned author data' );

			return false;

		}

		// get author name ##
		$author_name = $authordata && isset( $authordata->display_name ) ? $authordata->display_name : 'Author' ;

		// new array ##
		$array = [];

		// assign values ##
		$array['permalink'] = \esc_url( \get_author_posts_url( $author ) );
		$array['slug'] = $authordata->user_login;
		$array['title'] = $author_name;

		// w__log( $array );

		// return array ##
		// return $array;

		// return ##
		return willow\get\method::prepare_return( $args, $array );

	}

	public function comment( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// get post ##
		$post = $args['config']['post'];

		// comments ##
		if ( 
			$this->plugin->config->get([ 'context' => 'global', 'task' => 'config', 'property' => 'allow_comments' ])
			&& 'open' == $post->comment_status // comments are open
		) {
			
			// new array ##
			$array = [];

			// get number of comments ##
			$comments_number = \get_comments_number( $post->ID );

			if ( $comments_number == 0 ) {
				$comments = __( 'Comment', self::text_domain );
			} elseif ( $comments_number > 1 ) {
				$comments = $comments_number.' '.__( 'Comments', self::text_domain );
			} else {
				$comments = '1 '.__( 'Comment', self::text_domain );
			}

			// assign ##
			$array['title'] = $comments;
			$array['count'] = $comments_number;

			if ( \is_single() ) {

				$array['permalink'] = \get_the_permalink( $post->ID ).'#/scroll/comments';

			} else {

				$array['permalink'] = \get_the_permalink( $post->ID ).'#/scroll/comments'; // variable link ##

			}

			// w__log( $array );

			// return ##
			return willow\get\method::prepare_return( $args, $array );

		}

		// comments are closed ##
		return false;

	}

	/**
    * Return the_date with specified format
    *
    * @since       1.0.1
    * @return      string       HTML
    */
    public function date( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'Error in passed args' );

			return false;

		}

        // set-up new array ##
		$array = [];

		// get the post_content with filters applied ##
		$array['date'] = 
			\get_the_date( 
				isset( $args['config']['date_format'] ) ? 
				$args['date_format']['config'] : // take from value passed by caller ##
					$this->plugin->config->get([ 'context' => 'global', 'task' => 'config', 'property' => 'date_format' ]) ?: // take from global config ##
					\apply_filters( 'q/format/date', 'F j, Y' ), // standard ##
				$args['config']['post']->ID
			);

		// w__log( $array );

		// return ##
		return willow\get\method::prepare_return( $args, $array );

	}

	/**
    * Return the_date with specified format
    *
    * @since       1.0.1
    * @return      string       HTML
    */
    public function date_human( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'Error in passed args' );

			return false;

		}

		// get the_post ##
		$post = $args['config']['post'];

        // starts with an empty array ##
		$array = [];

		// post time in @since format ##
		$array['date_human'] = \human_time_diff( \get_the_date( 'U', $post->ID ), \current_time('timestamp') );

		// w__log( $array );

		// return ##
		return willow\get\method::prepare_return( $args, $array );

	}

    /**
    * The Post Data ( meta.. )
    *
    * @since       1.0.2
    */
    public static function data( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'Error in passed args' );

			return false;

		}

        // get the_post ##
		$post = $args['config']['post'];

        // test ID ##
        #w__log( $post->ID );

		// starts with an empty array ##
		$array = [];

		// post time in @since format ##
		$array['date_human'] = \human_time_diff( \get_the_date( 'U', $post->ID ), \current_time('timestamp') );

		// post time ##
		$array['date'] = self::date( $args );

		// post author ##
		$array = willow\render\method::extract( willow\get\meta::author( $args ), 'author_', $array );

		// category will be an array, so create category_title, permalink and slug fields ##
		$array = willow\render\method::extract( willow\get\taxonomy::category( $args ), 'category_', $array );

		// tag will be an array, so create tag_title, permalink and slug fields ##
		$array = willow\render\method::extract( willow\get\taxonomy::tag( $args ), 'tag_', $array );

		// tags will be an array, we'll let the rendered deal with this via a section tag.. ##
		$array['tags'] = willow\get\taxonomy::tags( $args );

		// comment will be an array, so create comment_count, link ##
		$array = willow\render\method::extract( willow\get\meta::comment( $args ), 'comment_', $array );

		// w__log( $array );

		// return
		return willow\get\method::prepare_return( $args, $array );

	}

}
