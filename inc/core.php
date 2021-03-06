<?php
/**
 * 
 * This file contains the main function that processes any requested duplication
 * @since 0.1
 * @author Mario Jaconelli <mariojaconelli@gmail.com>
 */

/**
 *
 * This is the main core function on Multisite Post Duplicator that processes the duplication of a post on a network from one
 * site to another
 * 
 * @param int $post_id_to_copy The ID of the source post to copy
 * @param int $new_blog_id The ID of the destination blog to copy to.
 * @param string $post_type The destination post type.
 * @param int $post_author The ID of the requested post author from the destination site.
 * @param string $prefix Optional prefix to be used on the destination post.
 * @param string $post_status The post status for the destination ID. Has to be one of the values returned from WordPress's get_post_statuses() function
 * 
 * @return array An array containing information about the newly created post
 * 
 * Example:
 * 
 *          id           => 20,
 *          edit_url     => 'http://[...]/site1/wp-admin/post.php?post=20&action=edit',
 *          site_name    => 'Another Site'
 * 
 */
function mpd_duplicate_over_multisite($post_id_to_copy, $new_blog_id, $post_type, $post_author, $prefix, $post_status) {

    //Collect function arguments into a single variable
    $mpd_process_info = array(

        'source_id'             => $post_id_to_copy,
        'destination_id'        => $new_blog_id,
        'post_type'             => $post_type,
        'post_author'           => $post_author,
        'prefix'                => $prefix,
        'requested_post_status' => $post_status

    );

    do_action('mpd_before_core', $mpd_process_info);

    //Get plugin options
    $options    = get_option( 'mdp_settings' );
    //Get the object of the post we are copying
    $mdp_post   = get_post($mpd_process_info['source_id']);
    //Get the title of the post we are copying
    $title      = get_the_title($mdp_post);
    //Get the tags from the post we are copying
    $sourcetags = wp_get_post_tags( $mpd_process_info['source_id'], array( 'fields' => 'names' ) );
    //Get the ID of the sourse blog
    $source_blog_id  = get_current_blog_id();

    //Format the prefix into the correct format if the user adds their own whitespace
    if($mpd_process_info['prefix'] != ''){

        $mpd_process_info['prefix'] = trim($mpd_process_info['prefix']) . ' ';

    }

    //Using the orgininal post object we now want to insert our any new data based on user settings for use
    //in the post object that we will be adding to the destination site
    $mdp_post = apply_filters('mpd_setup_destination_data', array(

            'post_title'    => $mpd_process_info['prefix'] . $title,
            'post_status'   => $mpd_process_info['requested_post_status'],
            'post_type'     => $mpd_process_info['post_type'],
            'post_author'   => $mpd_process_info['post_author'],
 			'post_content'  => $mdp_post->post_content,
            'post_excerpt'  => $mdp_post->post_excerpt,
            'post_content_filtered' => $mdp_post->post_content_filtered

    ), $mpd_process_info);

    //Get all the custom fields associated with the sourse post
    $data              = get_post_custom($mdp_post);
    //Get all the meta data associated with the sourse post
    $meta_values       = get_post_meta($mpd_process_info['source_id']);
    //Get array of data associated with the featured image for this post
    $featured_image    = mpd_get_featured_image_from_source($mpd_process_info['source_id']);

    //If we are copying the sourse post to another site on the network we will collect data about those 
    //images.
    if($mpd_process_info['destination_id'] != $source_blog_id){

        $attached_images = mpd_get_images_from_the_content($mpd_process_info['source_id']);

        if($attached_images){

            $attached_images_alt_tags   = mpd_get_image_alt_tags($attached_images);
            
        }

    }else{
        
        $attached_images = false;

    }

    //Hook for actions just before we switch to the destination blog to start processing our collected data
    do_action('mpd_during_core_in_source', $mdp_post, $attached_images);
    


    ////////////////////////////////////////////////
    //Tell WordPress to work in the destination site
    switch_to_blog($mpd_process_info['destination_id']);
    ////////////////////////////////////////////////



    //Make the new post
    $post_id = wp_insert_post($mdp_post);
    //Add the source post meta to the destination post
    foreach ( $data as $key => $values) {

       foreach ($values as $value) {

           add_post_meta( $post_id, $key, $value );

        }

    }
    
    //Copy the meta data collected from the sourse post to the new post
  	foreach ($meta_values as $key => $values) {

       foreach ($values as $value) {
            //If the data is serialised we need to unserialise it before adding or WordPress will serialise the serialised data
            //...which is bad
            if(is_serialized($value)){
             
                add_post_meta( $post_id, $key, unserialize($value));

            }else{

                add_post_meta( $post_id, $key, $value );

            }
           
        }

    }
    //If there were media attached to the sourse post content then copy that over
    if($attached_images){
        //Check that the users plugin settings actually want this process to happen
        if(isset($options['mdp_copy_content_images']) || !$options ){
            
            mpd_process_post_media_attachements($post_id, $attached_images, $attached_images_alt_tags, $source_blog_id, $new_blog_id);

        }

    }
    //If there was a featured image in the sourse post then copy it over
    if($featured_image){
        //Check that the users plugin settings actually want this process to happen
        if(isset($options['mdp_default_featured_image']) || !$options ){

            mpd_set_featured_image_to_destination( $post_id, $featured_image ); 

        }

    }
    //If there were tags in the sourse post then copy them over
    if($sourcetags){
        //Check that the users plugin settings actually want this process to happen
        if(isset($options['mdp_default_tags_copy']) || !$options ){

            wp_set_post_tags( $post_id, $sourcetags );

        }
        
    }
    
    //Collect information about the new post 
    $site_edit_url = get_edit_post_link( $post_id );
    $blog_details  = get_blog_details($mpd_process_info['destination_id']);
    $site_name     = $blog_details->blogname;

    //////////////////////////////////////
    //Go back to the current blog so we can update information about the action that just took place
    restore_current_blog();
    //////////////////////////////////////

    //Use the collected information about the new post to generate a status notice and a link for the user
    $notice = mdp_make_admin_notice($site_name, $site_edit_url, $blog_details);
    //Add this collected notice to the database because the new page needs a method of getting this data
    //when the page refreshes
    update_option('mpd_admin_notice', $notice );

    //Lets also create an array to return to function call incase it is required (extensibility)
    $createdPostObject = apply_filters('mpd_returned_information', array(

        'id'           => $post_id,
        'edit_url'     => $site_edit_url,
        'site_name'    => $site_name

    ));

    do_action('mpd_end_of_core', $createdPostObject);
     
    return $createdPostObject;
 
}