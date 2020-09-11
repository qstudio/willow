<?php

namespace willow\get;

// Q ##
use willow\core;
use willow\core\helper as h;
use willow\get;

class has extends \willow\get {

	
	/**
     * Check if a page has children
     *
     * @since       1.3.0
     * @param       integer         $post_id
     * @return      boolean
     */
    public static function children( $post_id = null )
    {

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
