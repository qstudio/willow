<?php

// if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

$options = array (
    'q__willow_options'
);

// For Single site
if ( ! is_multisite() ) {
    
    // delete options ##
    delete_options( $options );
    
} 
// For Multisite
else 
{
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        delete_options( $options, 'delete_site_option' );  
    }
    switch_to_blog( $original_blog_id );
}

function delete_options( $options = null, $function = 'delete_option' ) {
    
    if ( ! $options ) { return; }
    
    foreach( $options as $option ) {
        
        call_user_func( $function, $option );
        
    }
    
}
