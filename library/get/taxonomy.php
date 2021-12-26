<?php

namespace willow\get;

use willow;
use willow\core\helper as h;

class taxonomy {

	private
		$plugin = null // this
	;

	/**
     * Construct
     */
    public function __construct( willow\plugin $plugin ){

		// grab passed plugin object ## 
		$this->plugin = $plugin;

	}
	
	/**
	 * We need a generic get_taxonomy_terms method.. which distributes, based on post type and any passed tax / term ## 
	 */
	public function terms( array $args = null ){

		// global arg validator ##
		if ( ! $args = \willow()->render->args->prepare( $args ) ){ 
	   
			// w__log( 'Bailing..' ); 
		
			return false; 
		
		}

		// try and get terms ##
		if ( 
			! $terms = \get_terms( $args['query_args'] )
		){
	
			w__log( 'd:>No terms found for taxonomy: '.$args['args']->taxonomy );
	
			return false;
	
		}

		// to highlight any active term, we get to know the first term->term_id of the current post ##
		$active_term_id = '';
		if ( 
			$object_terms = willow\get\post::object_terms([ 
				'config' 		=> [ 
					'post'		=> $args['config']['post']
				],
				'taxonomy'		=> 'category',
				'args' 			=> [
					'number'	=> 1
				]
			])
				
		){

			// w__log( 'e:>Returned terms good' );

			// we expect an array of WP_Term objects - validate ##
			if (
				! \is_wp_error( $object_terms )
				&& is_array( $object_terms )
				&& isset( $object_terms[0] )
				&& $object_terms[0] instanceof \WP_Term
			){

				// w__log( 'e:>Term object good, getting ID' );

				$active_term_id = $object_terms[0]->term_id; 

			}

		}

		// w__log( $terms );

		// prepare return array ##
		$array = [];
		$count = 0;

		foreach ( $terms as $term ) {

			if (
				! is_object( $term )
				|| ! $term instanceof \WP_Term
			) {

				w__log( 'e:>Error in returned term' );

				continue;

			}

			// array key ##
			// $key = $term->term_id;

			// add values ##
			$array[ $count ]['permalink'] = \get_term_link( $term );
			$array[ $count ]['slug'] = $term->slug;
			$array[ $count ]['active'] = $term->term_id === $active_term_id ? ' active' : '' ; // are we viewing this term ##
			$array[ $count ]['title'] = $term->name;

			// iterate ##
			$count ++;

		}	

		// w__log( $array );
		$array = [ 'terms' => $array ];

		// return ##
		return willow\core\prepare::return( $args, $array );

	}

	public function category( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// $args->ID = $the_post->post_parent;
		if ( 
			! $terms = willow\get\post::object_terms([ 
				'config' 		=> [ 
					'post'		=> $args['config']['post'] ?: null
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

		// create an empty array ##
		$array = [];

		// add values ##
		$array['permalink'] = \get_category_link( $terms[0] );
		$array['slug'] = $terms[0]->slug;
		$array['title'] = $terms[0]->name;

		// test ##
		// w__log( $array );

		// return ##
		return willow\core\prepare::return( $args, $array );

	}
	
	
	public function categories( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// $args->ID = $the_post->post_parent;
		if ( 
			! $terms = willow\get\post::object_terms([ 
				'config' 		=> [ 
					'post'		=> $args['config']['post'] ?: null
				],
				'taxonomy'		=> 'category',
				'args' 			=> [
					'number'	=> 0 // all ## https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
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

		// create an empty array ##
		$array = [];
		$i = 0;

		foreach( $terms as $term ){

			// add values ##
			$array[$i]['permalink'] = \get_category_link( $term );
			$array[$i]['slug'] = $term->slug;
			$array[$i]['title'] = $term->name;

			$i ++;

		}

		// test ##
		// w__log( $array );

		// return ##
		return willow\core\prepare::return( $args, $array );

	}

	public function tag( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// $args->ID = $the_post->post_parent;
		if ( 
			! $terms = willow\get\post::object_terms([ 
				'config' 		=> [ 
					'post'		=> $args['config']['post'] ?: null
				],
				'taxonomy'		=> 'post_tag',
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

		// create an empty array ##
		$array = [];

		// add values ##
		$array['permalink'] = \get_category_link( $terms[0] );
		$array['slug'] = $terms[0]->slug;
		$array['title'] = $terms[0]->name;

		// test ##
		// w__log( $array );

		// return ##
		return willow\core\prepare::return( $args, $array );

	}

	public function tags( $args = null ){

		// sanity ##
		if (
			is_null( $args )
			|| ! is_array( $args )
		){

			w__log( 'e:>Error in passed args' );

			return false;

		}

		// $args->ID = $the_post->post_parent;
		if ( 
			! $terms = willow\get\post::object_terms([ 
				'config' 		=> [ 
					'post'		=> $args['config']['post'] ?: null
				],
				'taxonomy'		=> 'post_tag',
				'args' 			=> [
					'number'	=> 0 // all ## https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
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

		// create an empty array ##
		$array = [];
		$i = 0;

		foreach( $terms as $term ){

			// add values ##
			$array[$i]['tag_permalink'] = \get_category_link( $term );
			$array[$i]['tag_slug'] = $term->slug;
			$array[$i]['tag_title'] = $term->name;

			$i ++;

		}

		// test ##
		// w__log( $array );

		// return ##
		return willow\core\prepare::return( $args, $array );

	}

}
