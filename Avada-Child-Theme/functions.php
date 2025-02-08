<?php

function avada_lang_setup() {
    $lang = get_stylesheet_directory() . '/languages';
    load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

function theme_enqueue_scripts() {
    wp_enqueue_script('child-script', get_stylesheet_directory_uri() . '/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');

/*
Add Your Business form
*/
function create_business_from_avada_form_submission( $form_data ) {
    $form_id = intval($form_data['submission']['form_id']);

    // Check that this is the Add Your Business form
    if ($form_id != 5) {
        return;
    }

    // Check that data exists
    if ( !isset( $form_data['data'] ) || !is_array( $form_data['data'] ) ) {
        return;
    }

    // Extract form data
    $form_data_array = $form_data['data'];

    // Set business name and description
    $post_title = sanitize_text_field( $form_data_array['business_name'] ?? 'Unnamed Business' );
    // $post_content = sanitize_textarea_field( $form_data_array['describe_your_business'] ?? 'Business description' );

    // Business details
    $business_details_established_date = sanitize_text_field( $form_data_array['established_date']);
    $business_details_minimum_spend = sanitize_text_field( $form_data_array['business_details_minimum_spend']);
    $business_details_per_type = sanitize_text_field( $form_data_array['business_details_per_type']);
    $business_details_business_description = sanitize_textarea_field($form_data_array['describe_your_business']);
    $business_details_address = sanitize_text_field( $form_data_array['business_details_address']);

    // Key Facts

    // Additional Features

    // Contact Information
    $contact_information_business_handle = sanitize_text_field($form_data_array['contact_information_business_handle']);
    $contact_information_phone_number = sanitize_text_field($form_data_array['contact_information_phone_number']);
    $contact_information_email_address = sanitize_text_field($form_data_array['contact_information_email_address']);
    $contact_information_website_link = sanitize_text_field($form_data_array['contact_information_website_link']);

    // Social Media
    $social_media_instagram = sanitize_text_field($form_data_array['social_media_instagram']);
    $social_media_facebook = sanitize_text_field($form_data_array['social_media_facebook']);
    $social_media_tiktok = sanitize_text_field($form_data_array['social_media_tiktok']);
    $social_media_twitter = sanitize_text_field($form_data_array['social_media_twitter']);
    $social_media_youtube = sanitize_text_field($form_data_array['social_media_youtube']);
    $social_media_linkedin = sanitize_text_field($form_data_array['social_media_linkedin']);
    $social_media_pinterest = sanitize_text_field($form_data_array['social_media_pinterest']);

    // Business Photos

    // Highlight Video


    // Create post data
    $post_data = array(
        'post_title' => $post_title,
        'post_content' => $post_content,
        'post_status' => 'draft',
        'post_author' => 0,
        'post_type' => 'businesses',
        // 'tax_input' => array(
        //     'business_category' => sanitize_text_field($form_data_array['industry-type']),  // Replace 'business_category' with your taxonomy name
        // ),
    );

    // Insert post
    $post_id = wp_insert_post( $post_data );

    // Check if successful
    if (!is_wp_error($post_id)) {
        error_log('Post created successfully: ID ' . $post_id);

        // Save Advanced Custom Fields (ACF)
        $sanitized_form_data = array(
            // Business details
            'business_details_established_date' => $business_details_established_date,
            'business_details_minimum_spend' => $business_details_minimum_spend,
            'business_details_per_type' => $business_details_per_type,
            'business_details_business_description' => $business_details_business_description,
            'business_details_address' => $business_details_address,

            // Key Facts

            // Additional Features

            // Contact Information
            'contact_information_business_handle' => $contact_information_business_handle,
            'contact_information_phone_number' => $contact_information_phone_number,
            'contact_information_email_address' => $contact_information_email_address,
            'contact_information_website_link' => $contact_information_website_link,

            // Social Media
            'social_media_instagram' => $social_media_instagram,
            'social_media_facebook' => $social_media_facebook,
            'social_media_tiktok' => $social_media_tiktok,
            'social_media_twitter' => $social_media_twitter,
            'social_media_youtube' => $social_media_youtube,
            'social_media_linkedin' => $social_media_linkedin,
            'social_media_pinterest' => $social_media_pinterest,

            // Business Photos

            // Highlight Video

        );

        // Loop through the array and update each field
        foreach ($sanitized_form_data as $field_name => $field_value) {
            update_field($field_name, $field_value, $post_id);
        }
    } else {
        error_log('Post creation failed: ' . $post_id->get_error_message());
    }
    // die();
}

add_action( 'fusion_form_submission_data', 'create_business_from_avada_form_submission' );
