<?php
add_action( 'rest_api_init', 'openai_chat_endpoint' );

function openai_chat_endpoint() {
    // register_rest_route( 'openai/v1', '/chat/', array(
    //     'methods'  => 'POST',
    //     'callback' => 'openai_chat_callback',
    //     'permission_callback' => '__return_true', // Add proper permission checks
    // ) );
    register_rest_route( 'openai/v1', '/automate/', array(
        'methods'  => 'GET',
        'callback' => 'openai_automate_callback',
    ) );
}

function openai_automate_callback( WP_REST_Request $request ) {
    $response = array();
    $openai_topics_key = get_option( 'openai_topics_key' );

    if ($openai_topics_key){
        $openai_topics_key = explode("\n", $openai_topics_key);
        $openai_topics_key = array_map('trim', $openai_topics_key);

        if (is_array($openai_topics_key)){

            $response['topic'] = $topic = array_shift($openai_topics_key);

            $api_key = get_option( 'openai_api_key' );

            if ( empty( $api_key ) ) {
                return new WP_REST_Response( 'OpenAI API key is not set.', 500 );
            }

            $content = '
            Write a seo friendly content and your response must be in json format. Including title (interesting), content (3-5 paragraphs with html headings like h2,h3, list, etc), categories (general only; 1-2) and tags. Provide your answer in JSON form. Reply with only the answer in JSON form and include no other commentary: ' . $topic;

            $response['response'] = openai_send_request($content,$api_key);

            // if (!empty($response['errors'])){
            //    // Send Email
            // }

            $formattedValue = implode("\n", $openai_topics_key);
            update_option('openai_topics_key', $formattedValue);

        }
    }
    
   
    return $response;
}





function openai_send_request( $content, $api_key ) {
    $url = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
    );

    $body = json_encode( array(
        // 'model'     => 'gpt-3.5-turbo', // Choose the model you want to use
        'model'     => 'gpt-4o', // Choose the model you want to use
        'messages'  => array(
            array( 'role' => 'user', 'content' => $content ),
        ),
        'response_format' => array(
            'type' => 'json_object', // specify the format
        ),
        'max_tokens' => 500, // Adjust as needed
    ) );

    $response = wp_remote_post( $url, array(
        'method'    => 'POST',
        'body'      => $body,
        'headers'   => $headers,
        'timeout'   => 30,  // Set timeout to 20 seconds
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( isset( $data['choices'][0]['message']['content'] ) ) {
        $content = json_decode($data['choices'][0]['message']['content'], true);
        return ['content' => $content,'create' => openai_create_post($content)];

        // return json_decode($data['choices'][0]['message']['content']);
    } else {
        return new WP_Error( 'openai_error', 'Failed to get response from OpenAI' );
    }
}

function openai_create_post($data){
    
    $title = $data['title'];
    $content = $data['content'];
    $tags = $data['tags'];

    // Create a post array with the necessary data
    $post_data = array(
        'post_title'   => sanitize_text_field($title),    // Sanitize title
        'post_content' => wp_kses_post($content),         // Sanitize content
        'post_status'  => 'publish',                       // Set post status to 'publish' or 'draft'
        // 'post_author'  => get_current_user_id(),           // Get the current user's ID as the author
        'post_type'    => 'post',                          // Set the post type (can be 'post', 'page', or custom post types)
        // 'tags_input'   => is_array($tags) ? $tags : array(), // Set the tags (ensure it's an array)
    );

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    // Check if the post was inserted successfully
    if ($post_id) {
        $response[] = 'Post created';

        wp_set_post_tags($post_id, $tags);

        // Categories
        // Now let's handle the categories
        if (!empty($data['categories'])) {
            $categories = $data['categories'];

            // Loop through the categories and create them if they don't already exist
            foreach ($categories as $category_name) {
                // Check if the category already exists
                if (!term_exists($category_name, 'category')) {
                    // Create the category
                    wp_insert_term($category_name, 'category');
                }
            }

            // Get the category IDs
            $category_ids = [];
            foreach ($categories as $category_name) {
                $category = get_term_by('name', $category_name, 'category');
                if ($category) {
                    $category_ids[] = $category->term_id;
                }
            }

            // Assign categories to the post
            wp_set_post_categories($post_id, $category_ids);

        } else {
            $response[] = "No categories found in the response.";
        }

        // The post was inserted successfully
        return $response; //"Post created successfully";
    } else {
        // There was an error inserting the post
        return "Error creating post.";
    }
}