<?php

namespace willow\get;

use willow;
use willow\core\helper as h;

class query {

	private
		$plugin = null // this
	;

	/**
	 * 
     */
    public function __construct(){

		// grab passed plugin object ## 
		$this->plugin = willow\plugin::get_instance();

	}

	/**
     * Get Main Posts Loop
     *
     * @since       1.0.2
     */
    public function posts( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'Error in passed args' );

			return false;

		}

		// w__log( $args['wp_query_args'] );

		// add hardcoded query args ##
		$wp_query_args['paged'] = \get_query_var( 'paged' ) ? \get_query_var( 'paged' ) : 1 ;
		
		// merge passed args ##
		if ( 
			isset( $args['wp_query_args'] )
			&& is_array( $args['wp_query_args'] )
		){

            // merge passed args ##
			$wp_query_args = array_merge( $args['wp_query_args'], $wp_query_args );
			
		}
		
        // merge in global $wp_query variables ( required for archive pages ) ##
        if ( 
			isset( $args['wp_query_args']['query_vars'] ) 
			// && true === $args['query_vars']	
		) {

            // grab all global wp_query args ##
            global $wp_query;

            // merge all args together ##
            $wp_query_args = array_merge( $wp_query->query_vars, $wp_query_args );

			// w__log('e:>added query vars');

		}
		
		// filter posts_args ##
		$wp_query_args = \apply_filters( 'willow/get/query/posts/wp_query_args', $wp_query_args );

		// w__log( $wp_query_args );
		
		// set-up new array to hold returned post objects ##
		$array = [];

		// w__log( $wp_query_args );

        // run query ##
		$q_query = new \WP_Query( $wp_query_args );

		// fix sticky post weirdness.. ##
		/*
		$sticky_posts = \get_option( 'sticky_posts' ) ? count( \get_option( 'sticky_posts' ) ) : 0 ;
		if( 
			$sticky_posts > 0 
			&& count( $q_query->posts ) > $wp_query_args['posts_per_page'] 
		){

			// w__log( 'e:>Sticky mess...' );
			
			// w__log( $q_query->posts );

			w__log( 'Old count: '.count( $q_query->posts ) );

			// slice ##
			$sliced_array = array_slice( $q_query->posts, 0, $wp_query_args['posts_per_page'] );

			w__log( 'New count: '.count( $sliced_array ) );

			// re-assign ##
			$q_query->posts = $sliced_array;

		}
		*/
		
		// put results in the array key 'query' ##
		$array['query'] = $q_query ;

		// w__log( $array );

		// filter and return array ##
		return willow\get\method::prepare_return( $args, $array );

    }

  	/**
     * Get Post object by post_meta query
     *
     * @since       1.0.4
     * @return      Object      $args
     */
    public function posts_by_meta( $args = array() ){

        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = ( object ) \wp_parse_args( $args, $this->plugin->config->get(['context' => 'query', 'task' => 'get_post_by_meta' ]) );

        // grab page - polylang will take care of language selection ##
        $post_args = array(
            'meta_query'        => array(
                array(
                    'key'       => $args->meta_key,
                    'value'     => $args->meta_value
                )
            ),
            'post_type'         => $args->post_type,
            'posts_per_page'    => $args->posts_per_page,
            'order'				=> $args->order,
            'orderby'			=> $args->orderby
        );

        #pr( $args );

        // run query ##
        $posts = \get_posts( $post_args );

        // check results ##
        if ( ! $posts || \is_wp_error( $posts ) ) return false;

        // test it ##
        #pr( $posts[0] );
        #pr( $args->posts_per_page );

        // if we've only got a single item - shuffle up the array ##
        if ( 1 === $args->posts_per_page && $posts[0] ) { return $posts[0]; }

        // kick back results ##
        return $posts;

	}

	/**
     * Get Post object by post_meta query
     *
     * @since       1.0.4
     * @return      Object      $args
     */
    public static function post_id_by_title( $args = null ){

		/*
        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = \wp_parse_args( $args, core\config::get(['context' => 'query', 'task' => 'post_id_by_title' ]) );

        // grab page - polylang will take care of language selection ##
        $post_args = array(
            'meta_query'        => array(
                array(
                    'key'       => $args->meta_key,
                    'value'     => $args->meta_value
                )
            ),
            'post_type'         => $args->post_type,
            'posts_per_page'    => $args->posts_per_page,
            'order'				=> $args->order,
            'orderby'			=> $args->orderby
		);
		*/

		// test ##
		// w__log( $args );

		// sanity ##
		if(
			is_null( $args )
			// || ! is_array( $args )
			// || ! isset( $args[0] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

        // run query ##
        $post = \get_page_by_title( $args );

        // check results ##
        if ( ! $post || \is_wp_error( $post ) ) return false;

        // test it ##
        // w__log( $post );

        // kick back result->ID ##
        return $post->ID;

	}

	/**
     * Get Post object by post_meta query
     *
     * @since       1.0.4
     * @return      Object      $args
     */
    public static function post_id_by_path( $args = null ){

		/*
        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = \wp_parse_args( $args, core\config::get(['context' => 'query', 'task' => 'post_id_by_title' ]) );

        // grab page - polylang will take care of language selection ##
        $post_args = array(
            'meta_query'        => array(
                array(
                    'key'       => $args->meta_key,
                    'value'     => $args->meta_value
                )
            ),
            'post_type'         => $args->post_type,
            'posts_per_page'    => $args->posts_per_page,
            'order'				=> $args->order,
            'orderby'			=> $args->orderby
		);
		*/

		// test ##
		// w__log( $args );

		// sanity ##
		if(
			is_null( $args )
			// || ! is_array( $args )
			// || ! isset( $args[0] )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

        // run query ##
        $post = \get_page_by_path( $args );

        // check results ##
        if ( ! $post || \is_wp_error( $post ) ) return false;

        // test it ##
        // w__log( $post );

        // kick back result->ID ##
        return $post->ID;

	}
	
    /**
    * Get post with title %like% search term
    *
    * @param       $title          Post title to search for
    * @param       $method         wpdb method to use to retrieve results
    * @param       $columns        Array of column rows to retrieve
    *
    * @since       0.3
    * @return      Mixed           Array || False
    */
    public static function posts_with_title_like( $title = null, $method = 'get_col', $columns = array ( 'ID' ) ){

        // sanity check ##
        if ( ! $title ) { return false; }

        // global $wpdb ##
        global $wpdb;

        // First escape the $columns, since we don't use it with $wpdb->prepare() ##
        $columns = \esc_sql( $columns );

        // now implode the values, if it's an array ##
        if( is_array( $columns ) ){
            $columns = implode( ', ', $columns ); // e.g. "ID, post_title" ##
        }

        // run query ##
        $results = $wpdb->$method (
                $wpdb->prepare (
                "
                    SELECT $columns
                    FROM $wpdb->posts
                    WHERE {$wpdb->posts}.post_title LIKE %s
                "
                #,   esc_sql( '%'.like_escape( trim( $title ) ).'%' )
                ,   \esc_sql( '%'.$wpdb->esc_like( trim( $title )  ).'%' )
                )
            );

        #var_dump( $results );

        // return results or false ##
        return $results ? $results : false ;

	}
	
	/**
     * Check if a page has children
     *
     * @since       1.3.0
     * @param       integer         $post_id
     * @return      boolean
     */
    public static function has_children( $post_id = null ){

        // nothing to do here ##
        if ( is_null ( $post_id ) ) { return false; }

        // meta query to allow for inclusion and exclusion of certain posts / pages ##
        $meta_query =
                array(
                    array(
                        'key'       => 'program_sub_group',
                        'value'     => '',
                        'compare'   => '='
                    )
                );

        // query for child or sibling's post ##
        $wp_args = array(
            'post_type'         => 'page',
            'orderby'           => 'menu_order',
            'order'             => 'ASC',
            'posts_per_page'    => -1,
            'meta_query'        => $meta_query,
        );

        #pr( $wp_args );

        $object = new \WP_Query( $wp_args );

        // nothing found - why? ##
        if ( 0 === $object->post_count ) { return false; }

        // get children ##
        $children = \get_pages(
            array(
                'child_of'      => $post_id,
                'meta_key'      => '',
                'meta_value'    => '',
            )
        );

        // count 'em ##
        if( count( $children ) == 0 ) {

            // No children ##
            return false;

        } else {

            // Has Children ##
            return true;

        }

    }

}	
