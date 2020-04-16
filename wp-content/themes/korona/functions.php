<?php
include 'inc/frontend.php';

add_filter( 'allowed_block_types', 'allowed_block_types' );

function allowed_block_types( $allowed_blocks ) {

    return array(
        'core/html',
        'core/paragraph',
        'core/spacer',
        'core/separator',
        'core/shortcode',
        'core/freeform',
        'acf/section-accordion-open',
        'acf/section-accordion-open-body',
        'acf/section-accordion-close',
        'acf/section-accordion-close-body',
        'acf/section-tab-open',
        'acf/section-tab-section-open',
        'acf/section-tab-section-close',
        'acf/section-tab-close',
        'acf/section-announce',
        'acf/section-button',
        'acf/section-heading',
        'acf/section-hidden',
        'acf/section-lists',
        'acf/section-inset'
    );

}

    //add_action('get_header', 'korona_filter_head');
    //
    //function korona_filter_head() {
    //    remove_action('wp_head', '_admin_bar_bump_cb');
    //}

    /**
     * Register and Enqueue Styles.
     */
    function korona_register_styles () {

        $theme_version = wp_get_theme()->get( 'Version' );

        wp_enqueue_style( 'korona-style', get_stylesheet_uri(), array (), $theme_version );
        //	wp_style_add_data( 'twentytwenty-style', 'rtl', 'replace' );
        //
        //	// Add output of Customizer settings as inline style.
        //	wp_add_inline_style( 'twentytwenty-style', twentytwenty_get_customizer_css( 'front-end' ) );
        //
        //	// Add print CSS.
        //	wp_enqueue_style( 'twentytwenty-print-style', get_template_directory_uri() . '/print.css', null, $theme_version, 'print' );

    }

    add_action( 'wp_enqueue_scripts', 'korona_register_styles' );

    /**
     * Register and Enqueue Scripts.
     */
    function korona_register_scripts () {

        $theme_version = wp_get_theme()->get( 'Version' );

        wp_enqueue_script( 'korona-js', '/wp-content/themes/korona/assets/js/index.js', array (), $theme_version, TRUE );
        wp_enqueue_script( 'korona-js-autocomplete', '/wp-content/themes/korona/assets/js/autocomplete.js', array (), $theme_version, TRUE );
        wp_enqueue_script( 'korona-js-upsvr-emails', '/wp-content/themes/korona/assets/js/upsvr-emails.js', array (), $theme_version, TRUE );
    }

    add_action( 'wp_enqueue_scripts', 'korona_register_scripts' );

    add_theme_support( 'title-tag' );
    // Add backend styles for Gutenberg.
    add_action('enqueue_block_editor_assets', 'gutenberg_editor_assets');

    function gutenberg_editor_assets() {
        // Load the theme styles within Gutenberg.
        wp_enqueue_style('my-gutenberg-editor-styles', '/wp-content/themes/korona/assets/css/gutenberg-editor-styles.css', FALSE);
    }
    /**
     * get values of meta boxes
     *
     * @param $value
     * @return bool|mixed|string
     */

    function gov_back_button_get_meta ( $value ) {
        global $post;

        $field = get_post_meta( $post->ID, $value, TRUE );
        if ( !empty( $field ) ) {
            return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
        } else {
            return FALSE;
        }
    }

    /**
     * add metaboxes
     */

    function gov_back_button_add_meta_box () {
        add_meta_box(
            'gov_back_button',
            __( 'Tlačidlo späť', 'gov_back_button' ),
            'gov_back_button_html',
            'page',
            'normal',
            'high'
        );
    }

    add_action( 'add_meta_boxes', 'gov_back_button_add_meta_box' );

    /**
     * display metaboxes
     *
     * @param $post
     */
    function gov_back_button_html ( $post ) {
        wp_nonce_field( '_gov_back_button_nonce', 'gov_back_button_nonce' ); ?>

        <p>
            <label for="gov_back_button_text"><?php _e( 'Text pre tlačidlo späť', 'gov_back_button' ); ?></label>
            <input type="text" name="gov_back_button_text" id="gov_back_button_text"
                   value="<?php echo gov_back_button_get_meta( 'gov_back_button_text' ); ?>">
        </p>
        <p>
        <label for="gov_back_button_url"><?php _e( 'URL pre tlačidlo späť (ak ostane prázdne, späť sa nezobrazí)', 'gov_back_button' ); ?></label>
        <input type="text" name="gov_back_button_url" id="gov_back_button_url"
               value="<?php echo gov_back_button_get_meta( 'gov_back_button_url' ); ?>">
        </p><?php
    }

    /**
     * save metaboxes
     *
     * @param $post_id
     */
    function gov_back_button_save ( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( !isset( $_POST['gov_back_button_nonce'] ) || !wp_verify_nonce( $_POST['gov_back_button_nonce'], '_gov_back_button_nonce' ) )
            return;
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        if ( isset( $_POST['gov_back_button_text'] ) )
            update_post_meta( $post_id, 'gov_back_button_text', esc_attr( $_POST['gov_back_button_text'] ) );
        if ( isset( $_POST['gov_back_button_url'] ) )
            update_post_meta( $post_id, 'gov_back_button_url', esc_attr( $_POST['gov_back_button_url'] ) );
    }

    add_action( 'save_post', 'gov_back_button_save' );

    /**
     * Block functions
     */
    require get_template_directory() . '/inc/register-blocks.php';

    function wpb_custom_new_menu() {
        register_nav_menus(
            array(
                'primary_menu' => __( 'Primary menu' ),
                'footer-menu' => __( 'Footer menu' )
            )
        );
    }
    add_action( 'init', 'wpb_custom_new_menu' );

    function add_classes_on_li($classes, $item, $args) {
        $classes[] = 'idsk-header__navigation-item';
        return $classes;
    }
    add_filter('nav_menu_css_class','add_classes_on_li',1,3);

    function add_menuclass($ulclass) {
        return preg_replace('/<a /', '<a class="idsk-header__link"', $ulclass);
    }
    add_filter('wp_nav_menu','add_menuclass');

    remove_filter('widget_text_content', 'wpautop');

    if ( function_exists('register_sidebar') ) {
        register_sidebar(array(
                'name' => 'Footer widget first',
                'id' => 'sidebar-1',
                'before_widget' => '<span class="idsk-footer__licence-description">',
                'after_widget' => '</span>',
                'before_title' => '',
                'after_title' => '',
            )
        );
        register_sidebar(array(
                'name' => 'Footer widget second',
                'id' => 'sidebar-2',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '',
                'after_title' => '',
            )
        );
    }
