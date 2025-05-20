<?php
function recipe_admin_menu() {
    add_menu_page(
        'Import Recipes',
        'Import Recipes',
        'manage_options',
        'import-recipes',
        'recipe_render_admin_page',
        'dashicons-download',
        99
    );
}
add_action('admin_menu', 'recipe_admin_menu');

function recipe_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>Import Recipes</h1>
        <button id="import_recipes_button" class="button button-primary">Import Recipes</button>
        <p id="import_status"></p>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            $('#import_recipes_button').on('click', function () {
                $(this).prop('disabled', true).text('Importing...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'import_recipes'
                    },
                    success: function (response) {
                        $('#import_recipes_button').prop('disabled', false).text('Import Recipes');
                        $('#import_status').html('<strong>Status:</strong> ' + response.data.message);
                    },
                    error: function (xhr) {
                        $('#import_status').html('<strong>Error:</strong> Could not import recipes.');
                        $('#import_recipes_button').prop('disabled', false).text('Import Recipes');
                    }
                });
            });
        });
    </script>
    <?php
}