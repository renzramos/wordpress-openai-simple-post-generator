<?php
// Hook to add settings menu
add_action( 'admin_menu', 'openai_add_settings_page' );

function openai_add_settings_page() {
    add_menu_page(
        'Generate Post', // Page title
        'Generate Post', // Menu title
        'manage_options',  // Capability
        'rnzdevspcg-openai-settings', // Menu slug
        'openai_settings_page', // Callback function
        'dashicons-admin-generic', // Icon
        100 // Position
    );
}

// Display the settings page
function openai_settings_page() {
    ?>
    <div class="wrap">
        <h1>OpenAI API Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'openai_settings_group' );
            do_settings_sections( 'openai-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'openai_register_settings' );

function openai_register_settings() {

    register_setting( 'openai_settings_group', 'openai_api_key' );
    register_setting( 'openai_settings_group', 'openai_topics_key' );

    add_settings_section(
        'openai_settings_section',
        'API Settings',
        'openai_settings_section_callback',
        'openai-settings'
    );

    add_settings_field(
        'openai_api_key',
        'OpenAI API Key',
        'openai_api_key_field_callback',
        'openai-settings',
        'openai_settings_section'
    );
    add_settings_field(
        'openai_topics_key',
        'OpenAI Topics',
        'openai_topics_key_field_callback',
        'openai-settings',
        'openai_settings_section'
    );
}

function openai_settings_section_callback() {
    echo '<p>Enter your OpenAI API credentials below:</p>';
}

function openai_api_key_field_callback() {
    $api_key = get_option( 'openai_api_key' );
    echo '<input type="password" name="openai_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
}
function openai_topics_key_field_callback() {
    $openai_topics_key = get_option( 'openai_topics_key' );
    echo '<textarea rows="30" type="text" name="openai_topics_key"class="regular-text">' . esc_attr( $openai_topics_key ) . '</textarea>';
}
