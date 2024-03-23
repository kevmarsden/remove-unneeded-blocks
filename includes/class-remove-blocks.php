<?php

class Remove_Blocks {

    /**
     * Remove_Blocks constructor
     */
    public function __construct( ) {
        add_filter( 'allowed_block_types_all',  array( $this, 'disallow_blocks' ), 10, 2 );
    }

    /**
     * Filters the list of allowed block types based on user capabilities.
     *
     * This function checks if the current user has the 'edit_theme_options' capability.
     * If the user does not have this capability, certain blocks are removed from the
     * list of allowed block types in the Editor.
     *
     * @param array|bool $allowed_block_types Array of block type slugs, or boolean to enable/disable all.
     * @param object     $block_editor_context The current block editor context.
     *
     * @return array The filtered list of allowed block types. If the current user does not have
     *               the 'edit_theme_options' capability, the list will exclude the disallowed blocks.
     */
    public function disallow_blocks( $allowed_block_types, $block_editor_context ) {
        
        $disallowed_blocks = array(
            'acf/cm-ld-legislator-card',
        );

        // Get all registered blocks if $allowed_block_types is not already set.
        if ( ! is_array( $allowed_block_types ) || empty( $allowed_block_types ) ) {
            $registered_blocks   = WP_Block_Type_Registry::get_instance()->get_all_registered();
            $allowed_block_types = array_keys( $registered_blocks );
        }

        $filtered_blocks = array();
        // Loop through each block in the allowed blocks list and add it to filtered list if it's not disallowed
        foreach ( $allowed_block_types as $block ) {
            if ( ! in_array( $block, $disallowed_blocks, true ) ) {
                $filtered_blocks[] = $block;
            }
        }

        // Return the filtered list of allowed blocks
        return $filtered_blocks;

    }


}

new Remove_Blocks();
