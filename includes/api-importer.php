<?php

function recipe_import_from_api() {
    $api_url = defined('RECIPE_API_URL') ? RECIPE_API_URL : get_option('recipe_api_url');
    $access_key = defined('RECIPE_API_KEY') ? RECIPE_API_KEY : get_option('recipe_api_key');

    // Fetch data from API
    $response = wp_remote_get($api_url, array(
        'headers' => array('X-Access-Key' => $access_key),
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'API request failed.'));
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['record']['recipes']) || !is_array($data['record']['recipes'])) {
        wp_send_json_error(array('message' => 'Invalid data format.'));
        return;
    }

    foreach ($data['record']['recipes'] as $item) {
        $api_recipe_id = intval($item['id']);

        // Check if recipe already exists by external_id
        $existing_recipe_query = new WP_Query(array(
            'post_type' => 'recipe',
            'meta_key' => 'external_id',
            'meta_value' => $api_recipe_id,
            'posts_per_page' => 1
        ));

        $needs_update = false;
        $update_fields = array();
        $post_id = 0;

        if ($existing_recipe_query->have_posts()) {
            // Existing recipe found
            $existing_recipe_query->the_post();
            $post_id = get_the_ID();
            wp_reset_postdata();

            // Compare fields to check for updates
            if (get_the_title($post_id) !== sanitize_text_field($item['name'])) {
                $update_fields['post_title'] = sanitize_text_field($item['name']);
                $needs_update = true;
            }

            if (get_post_field('post_content', $post_id) !== sanitize_textarea_field(implode("\n", $item['instructions']))) {
                $update_fields['post_content'] = sanitize_textarea_field(implode("\n", $item['instructions']));
                $needs_update = true;
            }

            // Compare ACF fields
            if (get_field('prep_time_minutes', $post_id) !== intval($item['prepTimeMinutes'])) {
                $update_fields['prep_time_minutes'] = intval($item['prepTimeMinutes']);
                $needs_update = true;
            }

            if (get_field('cook_time_minutes', $post_id) !== intval($item['cookTimeMinutes'])) {
                $update_fields['cook_time_minutes'] = intval($item['cookTimeMinutes']);
                $needs_update = true;
            }

            if (get_field('servings', $post_id) !== intval($item['servings'])) {
                $update_fields['servings'] = intval($item['servings']);
                $needs_update = true;
            }

            if (get_field('difficulty', $post_id) !== sanitize_text_field($item['difficulty'])) {
                $update_fields['difficulty'] = sanitize_text_field($item['difficulty']);
                $needs_update = true;
            }
$cal = get_field('calories', $post_id);
            if (get_field('calories', $post_id) !== intval($item['caloriesPerServing'])) {
                $update_fields['calories'] = intval($item['caloriesPerServing']);
                $needs_update = true;
            }

            // Instructions comparison
            $current_instructions = get_field('instructions', $post_id);
            $new_instructions = array_map(function ($instruction) {
                return [
                    'step' => $instruction
                ];
            }, $item['instructions']);

            if ($current_instructions !== $new_instructions) {
                $update_fields['instructions'] = $new_instructions;
                $needs_update = true;
            }

            // Ingredients comparison
            $current_ingredients = get_field('ingredients', $post_id);
            $new_ingredients = array_map(function ($ingredient) {
                return [
                    'ingredient' => $ingredient
                ];
            }, $item['ingredients']);

            if ($current_ingredients !== $new_ingredients) {
                $update_fields['ingredients'] = $new_ingredients;
                $needs_update = true;
            }

        } else {
            // Recipe does not exist, create it
            $post_id = wp_insert_post(array(
                'post_title' => sanitize_text_field($item['name']),
                'post_content' => sanitize_textarea_field(implode("\n", $item['instructions'])),
                'post_type' => 'recipe',
                'post_status' => 'publish'
            ));

            update_field('external_id', $api_recipe_id, $post_id); // Set external ID
            $needs_update = true;
        }

        if (!$post_id) continue;

        // Update taxonomy terms
        if (!empty($item['mealType']) && is_array($item['mealType'])) {
            wp_set_object_terms($post_id, $item['mealType'], 'meal_type', false);
        }

        if (!empty($item['tags']) && is_array($item['tags'])) {
            wp_set_object_terms($post_id, $item['tags'], 'recipe_tag', false);
        }

        // Update post if needed
        if ($needs_update && !empty($update_fields)) {
            $update_fields['ID'] = $post_id;
            wp_update_post($update_fields);
        }

        // Update ACF Fields
        update_field('prep_time_minutes', intval($item['prepTimeMinutes']), $post_id);
        update_field('cook_time_minutes', intval($item['cookTimeMinutes']), $post_id);
        update_field('servings', intval($item['servings']), $post_id);
        update_field('difficulty', sanitize_text_field($item['difficulty']), $post_id);
        update_field('calories', intval($item['caloriesPerServing']), $post_id);

        // Instructions (Repeater Field)
        $instructions = array_map(function ($instruction) {
            return [
                'step' => $instruction
            ];
        }, $item['instructions']);
        update_field('instructions', $instructions, $post_id);

        // Ingredients (Repeater Field)
        $ingredients = array_map(function ($ingredient) {
            return [
                'ingredient' => $ingredient
            ];
        }, $item['ingredients']);
        update_field('ingredients', $ingredients, $post_id);

        // Set featured image
        if (!empty($item['image'])) {
            recipe_set_featured_image($item['image'], $post_id);
        }
    }

    wp_send_json_success(array('message' => count($data['record']['recipes']) . ' recipes processed successfully.'));
}
add_action('wp_ajax_import_recipes', 'recipe_import_from_api');

// Helper Function to Set Featured Image
// Validates if image already exists to prevent duplication in media library
function recipe_set_featured_image($image_url, $post_id) {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Normalize image URL
    $clean_image_url = esc_url_raw($image_url);

    // Check if new image already exists in Media Library
    $attachment_id = attachment_url_to_postid($clean_image_url);

    if (!$attachment_id) {
        // Image doesn't exist — download and upload it
        $tmp = download_url($clean_image_url);

        if (is_wp_error($tmp)) {
            @unlink($tmp);
            return;
        }

        $file_array = array(
            'name' => basename($clean_image_url),
            'tmp_name' => $tmp
        );

        $attachment_id = media_handle_sideload($file_array, 0); // Upload to Media Library

        if (is_wp_error($attachment_id)) {
            @unlink($file_array['tmp_name']);
            return;
        }
    }

    // Get current featured image ID
    $current_thumbnail_id = get_post_thumbnail_id($post_id);

    // If there's an existing featured image
    if ($current_thumbnail_id) {
        // Optional: Compare URLs to see if it's the same image
        $current_image_url = wp_get_attachment_url($current_thumbnail_id);

        if ($current_image_url === $clean_image_url) {
            // Same image — no need to update
            if (!$attachment_id) {
                // New image wasn't uploaded, so keep the old one
                return;
            } elseif ($current_thumbnail_id === $attachment_id) {
                // Same image already set — nothing to do
                return;
            }
        }

        // Delete the old featured image from Media Library
        wp_delete_attachment($current_thumbnail_id, false); // `false` means image will be in trash instead of delete
    }

    // Set the new featured image
    if ($attachment_id) {
        set_post_thumbnail($post_id, $attachment_id);
    }
}