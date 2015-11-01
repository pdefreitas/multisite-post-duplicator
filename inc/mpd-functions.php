<?php

function mpd_get_post_types_to_ignore(){

    $post_types_to_ignore   = array(

        'revision',
        'nav_menu_item', 
        'attachment'

    );

    return $post_types_to_ignore;
}
function mpd_get_some_postypes_to_show_options(){
	
    $post_types             = array();
	$options  	            = get_option( 'mdp_settings' );
    $post_types_to_ignore   = mpd_get_post_types_to_ignore();

	foreach ($options as $key => $value) {

        if (substr($key, 0, 28) == "meta_box_post_type_selector_" && !in_array($value, $post_types_to_ignore)) {

            $post_types[] = $value;

        }

    }

    return $post_types;

}

function mpd_get_postype_decision_from_options(){

	$options      = get_option( 'mdp_settings' ); 
  	$post_types   = $options['meta_box_show_radio'] ? $options['meta_box_show_radio'] : get_post_types('','names');
    
    if($options['meta_box_show_radio']){

        if($options['meta_box_show_radio'] == 'all'){

            $post_types = get_post_types('','names');

        }elseif($options['meta_box_show_radio'] == 'none'){

            $post_types = null;

        }elseif($options['meta_box_show_radio'] == 'some'){

            $post_types = mpd_get_some_postypes_to_show_options();  

        }
        

    }else{

        $post_types  = get_post_types('','names');

    }

    return $post_types;
}

function mpd_get_prefix(){

      $options  = get_option( 'mdp_settings' ); 
      $prefix   = $options['mdp_default_prefix'] ? $options['mdp_default_prefix'] : 'Copy of';

      return $prefix;
      
}

function mpd_get_featured_image_from_source($post_id){

    $thumbnail_id   = get_post_thumbnail_id($post_id);
    $image          = wp_get_attachment_image_src($thumbnail_id, 'full' );

    if($image){

        $image_details = array(

            'url'           => $image[0],
            'alt'           => get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ),
            'post_title'    => get_post_field('post_title', $thumbnail_id),
            'description'   => get_post_field('post_content', $thumbnail_id),
            'caption'       => get_post_field('post_excerpt', $thumbnail_id),
            'post_name'     => get_post_field('post_name', $thumbnail_id)

        );

        return $image_details;

    }
    
}

function mpd_set_featured_image_to_destination($destination_id, $image_details){

    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_details['url']);
    $filename   = basename($image_details['url']);

    if( wp_mkdir_p( $upload_dir['path'] ) ) {

        $file = $upload_dir['path'] . '/' . $filename;

    } else {

        $file = $upload_dir['basedir'] . '/' . $filename;

    }

    file_put_contents( $file, $image_data );

    $wp_filetype = wp_check_filetype( $filename, null );

    $new_file_url = $upload_dir['url'] . '/' . $filename;

    $attachment = array(

        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => $image_details['post_title'],
        'post_content'   => $image_details['description'],
        'post_status'    => 'inherit',
        'post_excerpt'   => $image_details['caption'],
        'post_name'      => $image_details['post_name'],
        //'guid'           => $new_file_url

    );

    // Create the attachment
    $attach_id = wp_insert_attachment( $attachment, $file, $destination_id );

    // Add any alt text;
    if($image_details['alt']){

         update_post_meta($attach_id,'_wp_attachment_image_alt', $image_details['alt']);

    }
   
    // Include image.php
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Define attachment metadata
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

    // Assign metadata to attachment
    wp_update_attachment_metadata( $attach_id, $attach_data );

    // And finally assign featured image to post
    set_post_thumbnail( $destination_id, $attach_id );
    
}

function mpd_get_images_from_the_content($post_id){

    $html = get_post_field( 'post_content', $post_id);

    $doc = new DOMDocument();
    @$doc->loadHTML($html);

    $tags = $doc->getElementsByTagName('img');
    
    if($tags){

        $images_objects_from_post = array();

        foreach ($tags as $tag) {

            preg_match("/(?<=wp-image-)\d+/", $tag->getAttribute('class'),$matches);
            $image_obj = get_post($matches[0]);
            $images_objects_from_post[$matches[0]] = $image_obj;

        }

        return $images_objects_from_post;
    }
    
}

function mpd_process_post_media_attachements($destination_id, $post_media_attachments, $attached_images_alt_tags, $source_id, $new_blog_id ){
   
   $image_count = 0;
   $old_image_ids = array_keys($post_media_attachments);

   foreach ($post_media_attachments as $post_media_attachment) {

            $image_data             = file_get_contents($post_media_attachment->guid);
            $image_URL_info         = pathinfo($post_media_attachment->guid);
            $image_URL_without_EXT  = $image_URL_info['dirname'] ."/". $image_URL_info['filename'];
            $image_URL_without_EXT  = str_replace(get_blog_details($new_blog_id)->path, get_blog_details($source_id)->path, $image_URL_without_EXT);
            $filename               = basename($post_media_attachment->guid);


            $upload_dir = wp_upload_dir();

            if( wp_mkdir_p( $upload_dir['path'] ) ) {

                $file = $upload_dir['path'] . '/' . $filename;

            } else {

                $file = $upload_dir['basedir'] . '/' . $filename;

            }

            $new_file_url = $upload_dir['url'] . '/' . $filename;

            file_put_contents( $file, $image_data );

            $wp_filetype = wp_check_filetype( $filename, null );

            $attachment = array(

                'post_mime_type' => 'image/jpeg',
                'post_title'     => sanitize_file_name( $filename ),
                'post_content'   => $post_media_attachment->post_content,
                'post_status'    => 'inherit',
                'post_excerpt'   => $post_media_attachment->post_excerpt,
                'post_name'      => $post_media_attachment->post_name,
                'guid'           => $new_file_url

            );

            // Create the attachment
            $attach_id = wp_insert_attachment( $attachment, $file, $destination_id );


            //Add any alt text;
            if($attached_images_alt_tags){

                  update_post_meta($attach_id,'_wp_attachment_image_alt', $attached_images_alt_tags[$image_count]);

            }
           
            // Include image.php
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Define attachment metadata
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );

            // Assign metadata to attachment
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $new_image_URL_without_EXT  = mpd_get_image_new_url_without_extension($attach_id, $source_id, $new_blog_id, $new_file_url);

            $old_content                = get_post_field('post_content', $destination_id);
            $middle_content             = str_replace($image_URL_without_EXT, $new_image_URL_without_EXT, $old_content);
            $update_content             = str_replace('wp-image-'. $old_image_ids[$image_count], 'wp-image-' . $attach_id, $middle_content);

            $post_update = array(

                'ID'           => $destination_id,
                'post_content' => $update_content
             
            );

            wp_update_post( $post_update );

            $image_count++;
   }
}

function mpd_get_image_new_url_without_extension($attach_id, $source_id, $new_blog_id, $new_file_url){

        $new_image_URL_with_EXT     = pathinfo($new_file_url);
        $new_image_URL_without_EXT  = $new_image_URL_with_EXT['dirname'] ."/". $new_image_URL_with_EXT['filename'];
        $new_image_URL_without_EXT  = str_replace(get_blog_details($source_id)->path, get_blog_details($new_blog_id)->path, $new_image_URL_without_EXT);

        return $new_image_URL_without_EXT;
        
}

function mpd_get_image_alt_tags($post_media_attachments){

    if($post_media_attachments){

        $alt_tags_to_be_copied = array();

        $attachement_count = 0;

        foreach ($post_media_attachments as $post_media_attachment) {

            $alt_tag = get_post_meta($post_media_attachment->ID, '_wp_attachment_image_alt', true);

            if($alt_tag){
                $alt_tags_to_be_copied[$attachement_count] = $alt_tag;
                $attachement_count++;
            }

        }

        return $alt_tags_to_be_copied;

    }

}

function mpd_checked_lookup($options, $option_key, $option_value){

    if(isset($options[$option_key])){

        $checkedLookup = checked( $options[$option_key], $option_value, false);

    }elseif(!$options){

        $checkedLookup = 'checked="checked"';

    }else{

        $checkedLookup = '';

    };

    echo $checkedLookup;


}

function mdp_make_admin_notice($site_name, $site_url){

    $message ='<div class="updated"><p>'. __('You succesfully duplicated this post to','mpd') ." ". $site_name.'. <a href="'.$site_url.'">'.__('Edit duplicated post','mpd').'</a></p></div>';

    $option_value = get_option('mpd_admin_notice');

    if($option_value){

        $message = $option_value . $message;

    }      

    return $message;
    
}

function mpd_plugin_admin_notices(){

    if($notices= get_option('mpd_admin_notice')){

         echo $notices;

    }

    delete_option('mpd_admin_notice');

}

