<?php get_header(); ?>

<div class="recipe-container">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    
        <article id="post-<?php the_ID(); ?>" <?php post_class('recipe-entry'); ?>>

            <!-- Title -->
            <h1 class="recipe-title"><?php the_title(); ?></h1>

            <!-- Tags & Meal Type -->
            <div class="recipe-tags">
                <?php
                $meal_types = get_the_terms(get_the_ID(), 'meal_type');
                $tags = get_the_terms(get_the_ID(), 'recipe_tag');
                ?>
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

            <!-- Image -->
            <?php if (has_post_thumbnail()) : ?>
                <div class="recipe-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <!-- Meta Info -->
            <div class="recipe-meta">
                <ul class="meta-list">
                    <li>Prep Time: <strong><?php echo get_field('prep_time_minutes') ?: 'N/A'; ?> mins</strong></li>
                    <li>Cook Time: <strong><?php echo get_field('cook_time_minutes') ?: 'N/A'; ?> mins</strong></li>
                    <li>Servings: <strong><?php echo get_field('servings') ?: 'N/A'; ?></strong></li>
                    <li>Difficulty: <strong><?php echo get_field('difficulty') ?: 'N/A'; ?></strong></li>
                </ul>
            </div>
<section class="ingredients-instructions-section">
            <!-- Ingredients -->
            <section class="ingredients-section">
                <h2>Ingredients</h2>
                <div>
                   <p>For <?php echo get_field('servings') ?: 'N/A'; ?> servings</p>
                   <p class="calories"><?php echo get_field('calories') ?: 'N/A'; ?> cal</p>
                </div>
                <ul class="ingredient-list">
                    <?php if (have_rows('ingredients')) : while (have_rows('ingredients')) : the_row(); ?>
                        <li>
                            <span><?php the_sub_field('ingredient'); ?></span>
                        </li>
                    <?php endwhile; else: ?>
                        <li>No ingredients listed.</li>
                    <?php endif; ?>
                </ul>
            </section>

            <!-- Instructions -->
            <section class="instructions-section">
                <h2>Instructions</h2>
                <ol class="instruction-steps">
                    <?php
                    $instructions = get_field('instructions');
                    if ($instructions):
                        foreach ($instructions as $i => $step):
                            $stepNumber = $i + 1;
                            echo "<li><span class='step-number'>" . $stepNumber . "</span>";
                            echo _e($step['step'], 'recipe');
                            echo "</li>";
                        endforeach;
                    else:
                        echo '<li>No instructions provided.</li>';
                    endif;
                    ?>
                </ol>
            </section>
</section>
        </article>

    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>