<?php

function recipe_filter_ajax_handler() {
    $args = array(
        'post_type' => 'recipe',
        'posts_per_page' => -1,
        'tax_query' => array(),
    );

    if (!empty($_GET['meal_type'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'meal_type',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['meal_type']),
        );
    }

    if (!empty($_GET['recipe_tag'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'recipe_tag',
            'field' => 'slug',
            'terms' => sanitize_text_field($_GET['recipe_tag']),
        );
    }

    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()):
        while ($query->have_posts()): $query->the_post();
            $output .= '<div class="recipe-card">';
            $output .= '<a href="' . get_permalink() . '"><h3>' . get_the_title() . '</h3></a>';
            $output .= '<div>' . get_the_excerpt() . '</div>';
            $output .= '</div>';
        endwhile;
    else:
        $output .= '<p>No recipes found.</p>';
    endif;

    wp_send_json_success($output);
    wp_die();
}

add_action('wp_ajax_recipe_filter', 'recipe_filter_ajax_handler');
add_action('wp_ajax_nopriv_recipe_filter', 'recipe_filter_ajax_handler');