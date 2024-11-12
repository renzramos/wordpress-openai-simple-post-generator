<?php
// Hook to add settings menu
add_action( 'admin_menu', 'openai_add_settings_page' );

function openai_add_settings_page() {
    add_menu_page(
        'OpenAI Settings', // Page title
        'OpenAI Settings', // Menu title
        'manage_options',  // Capability
        'rnzdev-spcg-settings', // Menu slug
        'rnzdev-spcg_settings_page', // Callback function
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
}

function openai_settings_section_callback() {
    echo '<p>Enter your OpenAI API credentials below:</p>';
}

function openai_api_key_field_callback() {
    $api_key = get_option( 'openai_api_key' );
    echo '<input type="text" name="openai_api_key" value="' . esc_attr( $api_key ) . '" class="regular-text">';
}
