<?php

function recipe_render_block($attributes) {
    if (!isset($attributes['recipeId']) || empty($attributes['recipeId'])) {
        return '<p>Please select a recipe.</p>';
    }

    $post_id = $attributes['recipeId'];
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'recipe') {
        return '<p>Invalid recipe selected.</p>';
    }

    setup_postdata($post);
    ob_start();
    ?>

    <div class="gutenberg-recipe-block">
        <h2><?php echo esc_html(get_the_title($post_id)); ?></h2>

        <?php if (has_post_thumbnail($post_id)) : ?>
            <div class="recipe-image">
                <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
            </div>
        <?php endif; ?>

        <div class="recipe-summary">
            <?php echo apply_filters('the_content', $post->post_content); ?>
        </div>

        <div class="recipe-meta">
            <ul class="meta-list">
                <li><strong>Prep Time:</strong> <?php echo get_field('prep_time_minutes', $post_id); ?> mins</li>
                <li><strong>Cook Time:</strong> <?php echo get_field('cook_time_minutes', $post_id); ?> mins</li>
                <li><strong>Servings:</strong> <?php echo get_field('servings', $post_id); ?></li>
                <li><strong>Difficulty:</strong> <?php echo get_field('difficulty', $post_id); ?></li>
            </ul>
        </div>

        <?php
        $meal_types = get_the_terms($post_id, 'meal_type');
        $tags = get_the_terms($post_id, 'recipe_tag');
        ?>

        <div class="recipe-tags">
            <?php if ($meal_types): ?>
                <div class="meal-type">
                    <strong>Meal Type:</strong>
                    <?php foreach ($meal_types as $type): ?>
                        <span class="tag"><?php echo esc_html($type->name); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($tags): ?>
                <div class="tags">
                    <strong>Tags:</strong>
                    <?php foreach ($tags as $tag): ?>
                        <span class="tag"><?php echo esc_html($tag->name); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ingredients -->
        <section class="ingredients-section">
            <h3>Ingredients</h3>
            <ul>
                <?php if (have_rows('ingredients', $post_id)) : while (have_rows('ingredients', $post_id)) : the_row(); ?>
                    <li>
                        <?php the_sub_field('quantity'); ?> 
                        <?php the_sub_field('unit'); ?> 
                        <?php the_sub_field('ingredient_name'); ?>
                    </li>
                <?php endwhile; else: ?>
                    <li>No ingredients found.</li>
                <?php endif; ?>
            </ul>
        </section>

        <!-- Instructions -->
        <section class="instructions-section">
            <h3>Instructions</h3>
            <ol>
                <?php
                $instructions = get_field('instructions', $post_id);
                foreach ($instructions as $i => $step):
                    echo "<li><span>" . ($i + 1) . ".</span> " . esc_html($step) . "</li>";
                endforeach;
                ?>
            </ol>
        </section>
    </div>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}