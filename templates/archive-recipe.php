<?php get_header(); ?>

<div class="archive-recipe-container">
    <h1>All Recipes</h1>

    <!-- Filters -->
    <div class="filters">
        <form id="recipe-filter-form">
            <select name="meal_type" id="meal_type">
                <option value="">All Meal Types</option>
                <?php
                $meal_types = get_terms(['taxonomy' => 'meal_type']);
                foreach ($meal_types as $type):
                    echo '<option value="' . $type->slug . '">' . $type->name . '</option>';
                endforeach;
                ?>
            </select>

            <select name="recipe_tag" id="recipe_tag">
                <option value="">All Tags</option>
                <?php
                $tags = get_terms(['taxonomy' => 'recipe_tag']);
                foreach ($tags as $tag):
                    echo '<option value="' . $tag->slug . '">' . $tag->name . '</option>';
                endforeach;
                ?>
            </select>

            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- Results -->
    <div id="recipe-results">
        <?php
        $default_query = new WP_Query(array('post_type' => 'recipe', 'posts_per_page' => -1));
        if ($default_query->have_posts()):
            while ($default_query->have_posts()): $default_query->the_post();
                echo '<div class="recipe-card">';
                echo '<a href="' . get_permalink() . '"><h3>' . get_the_title() . '</h3></a>';
                echo '<div>' . get_the_excerpt() . '</div>';
                echo '</div>';
            endwhile;
        else:
            echo '<p>No recipes found.</p>';
        endif;
        ?>
    </div>
</div>

<style>
    .archive-recipe-container {
        max-width: 960px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .filters select {
        margin-right: 10px;
        padding: 8px;
    }

    .recipe-card {
        border-bottom: 1px solid #eee;
        padding: 20px 0;
    }
</style>

<script>
    jQuery(document).ready(function ($) {
        $('#recipe-filter-form').on('submit', function (e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: recipe_ajax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'recipe_filter',
                    meal_type: $('#meal_type').val(),
                    recipe_tag: $('#recipe_tag').val()
                },
                success: function (response) {
                    $('#recipe-results').html(response.data);
                }
            });
        });
    });
</script>

<?php get_footer(); ?>