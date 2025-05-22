jQuery(document).ready(function ($) {
    $('#recipe-filter-form').on('submit', function (e) {
        e.preventDefault();

        var mealType = $('#meal_type').val();
        var recipeTag = $('#recipe_tag').val();

        $.ajax({
            url: recipe_ajax.ajaxurl,
            type: 'GET',
            data: {
                action: 'recipe_filter',
                meal_type: mealType,
                recipe_tag: recipeTag
            },
            success: function (response) {
                $('#recipe-results').html(response.data);
            },
            error: function () {
                $('#recipe-results').html('<p>Error loading recipes.</p>');
            }
        });
    });
});