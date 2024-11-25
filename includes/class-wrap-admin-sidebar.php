<?php
/**
 * WrapAdminSidebar class
 * 
 * Use this class to add a sidebar to admin pages.
 * 
 * Methods:
 * - init(): Initialize the class
 * - add_sidebar($args): Add a sidebar next to content identified by $main_selector
 * 
 * @package wrap
 */
class WrapAdminSidebar {
    public static function init() {
        // Scripts and styles are enqueued by add_sidebar() method when needed.
    }

    /**
     * Add a sidebar next to content identified by $main_selector
     * 
     * @param array $args {
     *      @type string $main_selector The jQuery selector for the main content
     *      @type string $page The page to add the sidebar to
     *      @type string $taxonomy The taxonomy to add the sidebar to
     * }
     */
    public static function add_sidebar($args = []) {
        global $current_screen, $pagenow;
        if ( ! empty($args['page']) && $pagenow != $args['page'] ) {
            return;
        }
        if ( ! empty($args['taxonomy']) && $current_screen->taxonomy != $args['taxonomy'] ) {
            return;
        }

        wp_enqueue_style('wrap-admin-sidebar', plugin_dir_url(__DIR__) . 'css/admin-sidebar.css', array(), '1.0.0');
        wp_enqueue_script('wrap-admin-sidebar-script', plugin_dir_url(__DIR__) . 'js/admin-sidebar.js', array('jquery'), '1.0.0', true);
        wp_add_inline_script('wrap-admin-sidebar-script', '
        jQuery(document).ready(function($){
            // Vérifier si la sidebar n\'existe pas déjà
            if ( !$(".wrap-admin-sidebar-parent").length ) {
                var $main_selectoor = $("' . $args['main_selector'] . '");
                // Create div.wrap-admin-sidebar-parent
                var $formAndSidebar = $("<div class=\'wrap-admin-sidebar-parent\'></div>");
    
                // Insert a new container before form.validate
                $("form.validate").before($formAndSidebar);
    
                // Move form.validate into the new container
                $formAndSidebar.append( $("form.validate") );

                // Add wrap-admin-sidebar-main class to $main_selector element
                $main_selectoor.addClass("wrap-admin-sidebar-main");
    
                // Move .wrap-admin-sidebar into the new container
                $formAndSidebar.append( $(".wrap-admin-sidebar") );
            }
        });
        ');
    }
}

// No need to initialize the class here, it is done in wrap.php file.
