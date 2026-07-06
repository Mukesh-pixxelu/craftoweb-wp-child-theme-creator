<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin menu.
 */

function cwctc_register_admin_menu() {

	add_menu_page(
		'Child Theme Creator',
		'Child Theme Creator',
		'manage_options',
		'cwctc-child-theme-creator',
		'cwctc_admin_page',
        'dashicons-admin-customizer',
        60
	);

}
add_action( 'admin_menu', 'cwctc_register_admin_menu' );





/**
 * Admin page callback.
 */
function cwctc_admin_page() {

    // Process form
    cwctc_create_child_theme();

    // Render page
    cwctc_render_admin_page();

}


function cwctc_create_child_theme() {
       

    if ( isset( $_POST['cwctc_submit'] ) ) {

        // Verify nonce
        check_admin_referer( 'cwctc_create_child_theme', 'cwctc_nonce' );

        // Permission check
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'craftoweb-wp-child-theme-creator' ) );
        }

        // Sanitize input
        $child_theme_name = sanitize_text_field( wp_unslash( $_POST['cwctc_theme_name'] ) );
        $author           = sanitize_text_field( wp_unslash( $_POST['cwctc_author'] ) );
        $description      = sanitize_textarea_field( wp_unslash( $_POST['cwctc_description'] ) );

        // Validate empty
        if ( empty( $child_theme_name ) ) {
            echo '<div class="notice notice-error"><p>Please enter a valid child theme name.</p></div>';
            return;
        }

        $parent_theme = get_template();

        $theme_slug = sanitize_title( $child_theme_name );
        $child_theme_path = get_theme_root() . '/' . $theme_slug;

        // Prevent overwrite
        if ( file_exists( $child_theme_path ) ) {
            echo '<div class="notice notice-error"><p>Child theme already exists.</p></div>';
            return;
        }

        // Create folder
        $created = mkdir( $child_theme_path, 0755, true );

        if ( ! $created ) {
            echo '<div class="notice notice-error"><p>Failed to create child theme folder.</p></div>';
            return;
        }

        // style.css
        $style_css = "/*
        Theme Name: {$child_theme_name}
        Theme URI: https://craftoweb.com
        Author: {$author}
        Description: {$description}
        Version: 1.0
        Template: {$parent_theme}
        */";

        $css_file = file_put_contents( $child_theme_path . '/style.css', $style_css );

        if ( ! $css_file ) {

            rmdir( $child_theme_path );

            echo '<div class="notice notice-error">
                    <p>Failed to create style.css. Child theme creation has been rolled back.</p>
                </div>';

            return;
        }

        // functions.php
        $functions_php = "<?php
            add_action('wp_enqueue_scripts', function() {
                wp_enqueue_style(
                    'parent-style',
                    get_template_directory_uri() . '/style.css'
                );
            });
        ";

        $functions_file = file_put_contents( $child_theme_path . '/functions.php', $functions_php );        

        if ( ! $functions_file ) {

            if ( file_exists( $child_theme_path . '/style.css' ) ) {
                unlink( $child_theme_path . '/style.css' );
            }

            rmdir( $child_theme_path );

            echo '<div class="notice notice-error">
                    <p>Failed to create functions.php. Child theme creation has been rolled back.</p>
                </div>';

            return;
        }

        // Copy parent screenshot
        $parent_theme_path = get_theme_root() . '/' . $parent_theme;

        $parent_screenshot = $parent_theme_path . '/screenshot.png';

        $child_screenshot = $child_theme_path . '/screenshot.png';

        if ( file_exists( $parent_screenshot ) ) {

            copy( $parent_screenshot, $child_screenshot );

        }

        // SUCCESS (NO redirect + NO conflict)
        echo '<div class="notice notice-success is-dismissible">
                <p>🎉 Child Theme created successfully!</p>
              </div>';
    }   

}



function cwctc_render_admin_page() {

    // Current active theme
    $current_theme = wp_get_theme();

    $is_child_theme = (bool) $current_theme->parent();

    // Parent theme
    $parent_theme = $current_theme->parent() ? $current_theme->parent() : $current_theme;

    // Default values
    $default_child_theme_name = $parent_theme->get( 'Name' ) . ' Child';

    $default_description = sprintf(
        'Child theme of %s.',
        $parent_theme->get( 'Name' )
    );
    ?>

    <div class="wrap">

        <h1>CraftoWeb WP Child Theme Creator</h1>

        <hr>

        <p>
            <strong>Current Active Theme:</strong>
            <?php echo esc_html( $current_theme->get( 'Name' ) ); ?>
        </p>

        <p>
            <strong>Parent Theme:</strong>
            <?php echo esc_html( $parent_theme->get( 'Name' ) ); ?>
        </p>

        <hr>

        <h2>Create Child Theme</h2>

        <?php if ( $is_child_theme ) : ?>

            <div class="notice notice-warning inline">
                <p>
                    <strong>Warning:</strong>
                    The current active theme is already a child theme.
                    Please activate
                    <strong><?php echo esc_html( $parent_theme->get( 'Name' ) ); ?></strong>
                    before creating a new child theme.
                </p>
            </div>

        <?php endif; ?>

        <form method="post">

            <?php wp_nonce_field( 'cwctc_create_child_theme', 'cwctc_nonce' ); ?>

            <table class="form-table">

                <tr>
                    <th>Child Theme Name</th>
                    <td>
                        <input
                            type="text"
                            name="cwctc_theme_name"
                            class="regular-text"
                            value="<?php echo esc_attr( $default_child_theme_name ); ?>"
                            <?php disabled( $is_child_theme ); ?>
                        >
                    </td>
                </tr>

                <tr>
                    <th>Author</th>
                    <td>
                        <input
                            type="text"
                            name="cwctc_author"
                            class="regular-text"
                            value="CraftoWeb"
                            <?php disabled( $is_child_theme ); ?>
                        >
                    </td>
                </tr>

                <tr>
                    <th>Description</th>
                    <td>
                        <textarea
                            name="cwctc_description"
                            class="large-text"
                            rows="4"
                            <?php disabled( $is_child_theme ); ?>
                            ><?php echo esc_textarea( $default_description ); ?>
                        </textarea>
                    </td>
                </tr>

            </table>

            <?php
                submit_button(
                    'Create Child Theme',
                    'primary',
                    'cwctc_submit',
                    false,
                    array(
                        'disabled' => $is_child_theme,
                    )
                );
            ?>

        </form>

    </div>

    <?php
}