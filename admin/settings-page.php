<?php
function recipe_settings_init() {
    register_setting('recipe_settings_group', 'recipe_api_url');
    register_setting('recipe_settings_group', 'recipe_api_key');

    add_settings_section(
        'recipe_settings_section',
        __('API Settings', 'recipe-filter-block'),
        null,
        'recipe_settings'
    );

    add_settings_field(
        'recipe_api_url',
        __('API URL', 'recipe-filter-block'),
        'recipe_api_url_render',
        'recipe_settings',
        'recipe_settings_section'
    );

    add_settings_field(
        'recipe_api_key',
        __('API Access Key', 'recipe-filter-block'),
        'recipe_api_key_render',
        'recipe_settings',
        'recipe_settings_section'
    );
}
add_action('admin_init', 'recipe_settings_init');

function recipe_api_url_render() {
    $value = get_option('recipe_api_url');
    echo "<input type='text' name='recipe_api_url' value='" . esc_attr($value) . "' class='regular-text'>";
}

function recipe_api_key_render() {
    $value = get_option('recipe_api_key');
    echo "<input type='text' name='recipe_api_key' value='" . esc_attr($value) . "' class='regular-text'>";
}

function recipe_options_page() {
    ?>
    <div class="wrap">
        <h2>Recipe Import Settings</h2>
        <form action="options.php" method="post">
            <?php
            settings_fields('recipe_settings_group');
            do_settings_sections('recipe_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function recipe_add_settings_menu() {
    add_submenu_page(
        'import-recipes',
        'Settings',
        'Settings',
        'manage_options',
        'recipe-settings',
        'recipe_options_page'
    );
}
add_action('admin_menu', 'recipe_add_settings_menu');