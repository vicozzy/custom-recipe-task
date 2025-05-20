const { registerBlockType } = wp.blocks;
const { InspectorControls, PanelBody, SelectControl } = wp.components;
const { useState, useEffect } = wp.element;

registerBlockType('recipe/block', {
    title: 'Recipe Block',
    icon: 'carrot',
    category: 'widgets',
    attributes: {
        recipeId: {
            type: 'number',
            default: 0,
        },
    },

    edit: (props) => {
        const { attributes, setAttributes } = props;
        const [recipes, setRecipes] = useState([]);

        useEffect(() => {
            fetch(wp.apiUrl + '/wp/v2/recipe')
                .then(res => res.json())
                .then(data => setRecipes(data));
        }, []);

        return (
            <div>
                <InspectorControls>
                    <PanelBody title="Recipe Settings">
                        <SelectControl
                            label="Select a Recipe"
                            value={attributes.recipeId}
                            options={[
                                { label: 'Select...', value: 0 },
                                ...recipes.map(r => ({ label: r.title.rendered, value: r.id })),
                            ]}
                            onChange={(val) => setAttributes({ recipeId: parseInt(val) })}
                        />
                    </PanelBody>
                </InspectorControls>

                <p>Select a recipe to display.</p>
            </div>
        );
    },

    save: () => null,
});