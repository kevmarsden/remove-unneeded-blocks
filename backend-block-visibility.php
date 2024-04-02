<?php
/**
 * Plugin Name:       Backend Block Visibility
 * Description:       Hide unused blocks from the post editor
 * Version:           1.0.0
 * Author:            Kevin Marsden
 * Author URI:        https://kmarsden.com
 * Text Domain:       km-block-visibility
 * Contributors:      kevmarsden
 * Tags:              block, blocks, visibility
 * Requires at least: 5.8.0
 * Tested up to:      6.5
 * Stable tag:        1.0.0
 * Requires PHP:      7.0
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'plugin_action_links_backend-block-visibility/backend-block-visibility.php', 'km_block_visibility_add_plugin_settings_link');
add_action( 'admin_init', 'km_block_visibility_register_settings' );
add_action( 'admin_menu', 'km_block_visibility_settings_page' );
add_filter( 'allowed_block_types_all', 'km_block_visibility_remove_blocks', 10, 2 );

/**
 * Add settings link to the main plugin page
 *
 * @param array $links Plugin action links
 *
 * @return array The list of plugin action links
 */
function km_block_visibility_add_plugin_settings_link( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=blocks_visibility' ) ) . '">' . _e( 'Settings', 'km-block-visibility' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * Custom sanitization callback function to ensure input is an array.
 */
function km_block_visibility_register_settings() {
    register_setting( 'km_block_visibility_options', 'km_block_visibility_options', array(
        'type'              => 'array',
        'sanitize_callback' => 'km_block_visibility_sanitize_block_selection',
        'default'           => NULL
    ) );
}

/**
 * Add the "Block Visibility" options page under "Settings".
 */
function km_block_visibility_settings_page() {
    add_options_page( 'Block Visibility', 'Block Visibility', 'manage_options', 'blocks_visibility', 'km_block_visibility_callback' );
}

/**
 * Filters the list of allowed block types based on user capabilities.
 * 
 * @param array   $allowed_block_types array of block type slugs
 * @param object  $block_editor_context The current block editor context.
 *
 * @return array  The filtered list of allowed block types.
 */
function km_block_visibility_remove_blocks( $allowed_block_types, $block_editor_context ) {
    $disallowed_blocks = get_option( 'km_block_visibility_options', array() );

    // Get all registered blocks if $allowed_block_types is not already set.
    if ( ! is_array( $allowed_block_types ) || empty( $allowed_block_types ) ) {
        $registered_blocks   = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $allowed_block_types = array_keys( $registered_blocks );
    }

    $filtered_blocks = array_values( array_diff( $allowed_block_types, $disallowed_blocks ) );
    return $filtered_blocks;

}

/**
 * Display the block visibility settings page content.
 */
function km_block_visibility_callback() {
    ?>
    <div class="wrap">
        <h2><?php _e( 'Block Visibility Settings', 'km-block-visibility' );?></h2>
        <p><?php _e( 'Select the blocks to hide from the post editor. If a block is hidden that is already in use, it will still be visible on the frontend and backend.', 'km-block-visibility' );?></p>
        <form action="options.php" method="post" >
                <?php 
                settings_fields( 'km_block_visibility_options' );

                $blocks_to_hide = get_option( 'km_block_visibility_options' ) ?? array();
                $block_types = \WP_Block_Type_Registry::get_instance()->get_all_registered(); 
                ?>
                <ul class="registered-blocks" style="display: flex; flex-direction: row; flex-wrap: wrap;">
                <?php foreach ( $block_types as $block_type ) : ?>
                    <li style="width:30%;">
                        <label>
                            <input type="checkbox" name="km_block_visibility_options[]" value="<?php echo esc_attr( $block_type->name ); ?>" <?php if ( in_array( $block_type->name, $blocks_to_hide ) ) { echo "checked"; }?>>
                                <?php echo esc_html( $block_type->name ); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
                </ul>
                <?php submit_button( __( 'Save Changes', 'km-block-visibility' ), 'primary'); ?>
        </form>
    </div>
    <?php
}

/**
 * Custom sanitization callback function to ensure input is an array.
 *
 * @param array|bool $input Array of selected blocks.
 *
 * @return array The sanitized list of selected blocks.
 */
function km_block_visibility_sanitize_block_selection( $input ) {
    if ( ! is_array( $input ) ) {
        $input = array();
    }
    return $input;
}
