<?php
/**
 * Plugin Name:       Block Visibility
 * Description:       Hide unused blocks from the post editor
 * Version:           1.0.0
 * Author:            Kevin Marsden
 * Author URI:        https://kmarsden.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'block_visibility_settings_page' );
add_filter( 'allowed_block_types_all', 'block_visibility_remove_blocks', 10, 2 );
add_filter( 'plugin_action_links_remove-unneeded-blocks/remove-unneeded-blocks.php', 'block_visibility_add_plugin_settings_link');
add_action( 'admin_init', 'block_visibility_register_settings' );


function block_visibility_add_plugin_settings_link( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=manage_blocks' ) ) . '">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

function block_visibility_register_settings() {
    register_setting( 'block_visibility_options', 'block_visibility_options', array(
        'type'              => 'array',
        'sanitize_callback' => 'block_visibility_sanitize_block_selction',
        'default'           => NULL
    ) );
}

// Custom sanitization callback function to ensure it is an array
function block_visibility_sanitize_block_selction( $input ) {
    if ( ! is_array( $input ) ) {
        $input = array();
    }
    return $input;
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
    function block_visibility_remove_blocks( $allowed_block_types, $block_editor_context ) {
    
    $disallowed_blocks = get_option( 'block_visibility_options', array() );

    // Get all registered blocks if $allowed_block_types is not already set.
    if ( ! is_array( $allowed_block_types ) || empty( $allowed_block_types ) ) {
        $registered_blocks   = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $allowed_block_types = array_keys( $registered_blocks );
    }

    $filtered_blocks = array_values( array_diff( $allowed_block_types, $disallowed_blocks ) );
    return $filtered_blocks;

}

// Add the menu item under "Settings"
function block_visibility_settings_page() {
    add_options_page( 'Block Visibility', 'Block Visiblity', 'manage_options', 'manage_blocks', 'block_visibility_callback' );
}

// Display the settings page content
function block_visibility_callback() {
    ?>
    <div class="wrap">
        <h2>Block Settings</h2>
        <p>Select the blocks to hide from the post editor. If a block is hidden that is already in use, it will still be visible on the frontend and backend.</p>
        <form action="options.php" method="post" >
                <?php 
                settings_fields( 'block_visibility_options' );

                $blocks_to_hide = get_option( 'block_visibility_options' );
                $block_types = \WP_Block_Type_Registry::get_instance()->get_all_registered(); 
                ?>
                <ul class="registered-blocks" style="display: flex; flex-direction: row; flex-wrap: wrap;">
                <?php foreach ( $block_types as $block_type ) : ?>
                    <li style="width:30%;">
                        <label>
                            <input type="checkbox" name="block_visibility_options[]" value="<?php echo esc_attr( $block_type->name ); ?>" <?php if ( in_array( $block_type->name, $blocks_to_hide ) ) { echo "checked"; }?>>
                                <?php echo esc_html( $block_type->name ); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
                </ul><?php

                submit_button( 'Save Changes', 'primary'); ?>
        </form>
    </div>
    <?php
}
