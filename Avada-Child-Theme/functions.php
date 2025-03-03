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
function upload_image_from_url($image_url, $post_id) {
    // If no image URL, return false
    if (empty($image_url)) {
        return false;
    }

    // Get WordPress upload directory
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);

    if ($image_data) {
        // Save image to the uploads directory
        $file = $upload_dir['path'] . '/' . $filename;
        file_put_contents($file, $image_data);

        if (file_exists($file)) {
            // Prepare the attachment
            $file_type = wp_check_filetype($filename);
            if (empty($file_type['type']) || !in_array($file_type['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
                return false; // If the file is not a valid image type, return false
            }

            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . basename($file),
                'post_mime_type' => $file_type['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            // Insert the attachment
            $attachment_id = wp_insert_attachment($attachment, $file, $post_id);

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            return $attachment_id;
        }
    }

    return false;
}

function create_business_from_avada_form_submission($form_data) {
    $form_id = intval($form_data['submission']['form_id']);

    // Check if this is the Create Your Business form
    if ($form_id != 5) {
        return;
    }

    // Extract form data
    $form_data_array = $form_data['data'];
    // error_log(print_r($form_data_array, true));

    // Set business name as the Post title
    $post_title = sanitize_text_field($form_data_array['business_name'] ?? 'Unnamed Business');

    // Create post data
    $post_data = array(
        'post_title' => $post_title,
        'post_status' => 'draft',
        'post_author' => 0,
        'post_type' => 'businesses',
    );

    // Insert post
    $post_id = wp_insert_post($post_data);

    // Check insert post worked
    if (is_wp_error($post_id) || !$post_id) {
        return;
    }

    // List of taxonomy fields
    $taxonomy_fields = [
        'decor-ambiance-category',
        'drink-services-category',
        'entertainment-category',
        'planning-management-category',
        'food-services-category',
        'photography-video-category',
        'transport-logistics-category',
        'unique-category',
        'type-of-event'
    ];
  
    // Taxonomy 'industry-type' (select box)
    if (!empty($form_data_array['industry-type'])) {
        $industry_slug = $form_data_array['industry-type'];
        $term = get_term_by('slug', $industry_slug, 'industry-type');
        if ($term) {
            $result = wp_set_object_terms($post_id, (int) $term->term_id, 'industry-type');
        }
    }

    // Other taxonomy fields (checkboxes)
    foreach ($taxonomy_fields as $taxonomy) {
        if (!empty($form_data_array[$taxonomy]) && is_array($form_data_array[$taxonomy])) {
            $term_ids = [];

            foreach ($form_data_array[$taxonomy] as $slug) {
                if ($taxonomy === 'type-of-event') {
                    $taxonomy = str_replace('-', '_', $taxonomy);
                }

                $term = get_term_by('slug', $slug, $taxonomy);
                if ($term) {
                    $term_ids[] = (int) $term->term_id;
                } else {
                    error_log("Taxonomy term not found: {$slug} in taxonomy {$taxonomy}");
                }
            }

            if (!empty($term_ids)) {
                $result = wp_set_object_terms($post_id, $term_ids, $taxonomy);
            }
        }
    }

    // Mapping slugs to labels for type_of_event
    $event_labels = [
        'anniversaries' => 'Anniversaries',
        'baby-showers' => 'Baby Showers',
        'birthday-parties' => 'Birthday Parties',
        'charity-galas' => 'Charity Galas',
        'corporate-events' => 'Corporate Events',
        'engagement-parties' => 'Engagement Parties',
        'festivals' => 'Festivals',
        'garden-parties' => 'Garden Parties',
        'house-parties' => 'House Parties',
        'private-parties' => 'Private Parties',
        'product-launches' => 'Product Launches',
        'seasonal-events' => 'Seasonal Events',
        'weddings' => 'Weddings'
    ];

    // Update Event Type ACF
    // The same form data is used to set taxonomies above, but this is for setting the Advanced Custom Field
    if (!empty($form_data_array['type-of-event']) && is_array($form_data_array['type-of-event'])) {
        $event_types = [];

        foreach ($form_data_array['type-of-event'] as $slug) {
            if (isset($event_labels[$slug])) {
                $event_types[] = $event_labels[$slug];
            } else {
                error_log("Unknown event type slug: {$slug}");
            }
        }

        if (!empty($event_types)) {
            update_field('key_facts_type_of_event', $event_types, $post_id);
        }
    }


    // Upload images
    $main_featured_image_url = $form_data_array['main_featured_image'] ?? '';
    $main_featured_image_id = upload_image_from_url($main_featured_image_url, $post_id);

    $featured_image_2_url = $form_data_array['featured_image_2'] ?? '';
    $featured_image_2_id = upload_image_from_url($featured_image_2_url, $post_id);

    $featured_image_3_url = $form_data_array['featured_image_3'] ?? '';
    $featured_image_3_id = upload_image_from_url($featured_image_3_url, $post_id);

    $business_logo_url = $form_data_array['business_logo'] ?? '';
    $business_logo_id = upload_image_from_url($business_logo_url, $post_id);

    $business_photos_urls = isset($form_data_array['business_photos']) ? explode('|', $form_data_array['business_photos']) : [];
    $business_photos_ids = [];
    if (!empty($business_photos_urls)) {
        foreach ($business_photos_urls as $url) {
            $url = trim($url); // Remove spaces
            if (!empty($url)) {
                $image_id = upload_image_from_url($url, $post_id);
                if ($image_id) {
                    $business_photos_ids[] = $image_id;
                } else {
                    error_log('Failed to upload image: ' . $url);
                }
            }
        }
    }

    // Set featured image
    if ($main_featured_image_id) {
        set_post_thumbnail($post_id, $main_featured_image_id);
    }

    // Save images to Business post
    if ($business_logo_id) {
        update_field('business_logo', $business_logo_id, $post_id);
    }
    if ($featured_image_2_id) {
        update_field('featured_image_2', $featured_image_2_id, $post_id);
    }
    if ($featured_image_3_id) {
        update_field('featured_image_3', $featured_image_3_id, $post_id);
    }
    if (!empty($business_photos_ids)) {
        update_field('business_photos', $business_photos_ids, $post_id);
    }

    // Save ACF fields in array
    $acf_data = array(
        // Business Details
        'business_details_established_date' => sanitize_text_field($form_data_array['established_date']),
        'business_details_minimum_spend' => sanitize_text_field($form_data_array['business_details_minimum_spend']),
        'business_details_per_type' => sanitize_text_field($form_data_array['business_details_per_type']),
        'business_details_business_description' => sanitize_textarea_field($form_data_array['describe_your_business']),
        'business_details_address' => sanitize_text_field($form_data_array['business_details_address']),
        
        // Key Facts
        'key_facts_minimum_size' => sanitize_text_field($form_data_array['key_facts_minimum_size']),
        'key_facts_maximum_size' => sanitize_text_field($form_data_array['key_facts_maximum_size']),

        'key_facts_have_booking_policy' => sanitize_text_field($form_data_array['booking_policy']),
        'key_facts_booking_notice' => sanitize_text_field($form_data_array['about_booking_policy']),
        'key_facts_about_booking_policy' => sanitize_text_field($form_data_array['key_facts_about_booking_policy']),
        
        'key_facts_have_cancellation_policy' => sanitize_text_field($form_data_array['cancellation_policy']),
        'key_facts_cancellation_policy' => sanitize_text_field($form_data_array['about_cancellation_policy']),
        'key_facts_about_cancellation_policy' => sanitize_text_field($form_data_array['key_facts_about_cancellation_policy']),

        'key_facts_any_licenses_or_insurance' => sanitize_text_field($form_data_array['any_licenses_or_insurance']),
        'key_facts_select_types_of_licenses_and_insurance' => isset($form_data_array['select_types_of_licenses_and_insurance']) ? array_map('sanitize_text_field', $form_data_array['select_types_of_licenses_and_insurance']) : [],

        // Contact Information
        'contact_information_business_handle' => sanitize_text_field($form_data_array['contact_information_business_handle']),
        'contact_information_phone_number' => sanitize_text_field($form_data_array['contact_information_phone_number']),
        'contact_information_email_address' => sanitize_text_field($form_data_array['contact_information_email_address']),
        'contact_information_website_link' => sanitize_text_field($form_data_array['contact_information_website_link']),

        // Social Media
        'social_media_instagram' => sanitize_text_field($form_data_array['social_media_instagram']),
        'social_media_facebook' => sanitize_text_field($form_data_array['social_media_facebook']),
        'social_media_tiktok' => sanitize_text_field($form_data_array['social_media_tiktok']),
        'social_media_twitter' => sanitize_text_field($form_data_array['social_media_twitter']),
        'social_media_youtube' => sanitize_text_field($form_data_array['social_media_youtube']),
        'social_media_linkedin' => sanitize_text_field($form_data_array['social_media_linkedin']),
        'social_media_pinterest' => sanitize_text_field($form_data_array['social_media_pinterest']),
    );

    // Update ACF fields
    foreach ($acf_data as $field_name => $field_value) {
        $result = update_field($field_name, $field_value, $post_id);
        if (!$result) {
            error_log("Failed to update ACF Field: {$field_name}");
        }
    }

    // die();
}

add_action('fusion_form_submission_data', 'create_business_from_avada_form_submission');
