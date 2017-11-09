<?php
/**
 * Plugin Name: Geniem Roles
 * Plugin URI: https://github.com/devgeniem/wp-geniem-roles
 * Description: WordPress plugin to edit and create roles in code
 * Version: 0.2.5
 * Author: Timi-Artturi Mäkelä / Anttoni Lahtinen / Ville Siltala / Geniem Oy
 * Author URI: https://geniem.fi
 **/

namespace Geniem;

/**
 * Geniem Roles
 */
final class Roles {

    /**
     * Roles.
     * @var [type]
     */
    protected static $roles;

    /**
     * Singleton Geniem Roles instance.
     * @var [type]
     */
    private static $instance;

    /**
     * Init roles singleton.
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new Roles();
        }
        return self::$instance;
    }

    /**
     * Roles __constuctor
     */
    public function __construct() {

        // Actions
        add_action( 'init', array( __CLASS__, 'init' ) );
        add_action( 'init', array( __CLASS__, 'add_options_page' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'geniem_roles_styles' ) );
    }

    /**
     * Enqueue styles
     *
     * @param string $hook Current menu slug.
     * @return void
     */
    public static function geniem_roles_styles( $hook ) {
        $allowed = [
            'toplevel_page_wp-geniem-roles',
            'geniem-roles_page_wp-geniem-roles-slugs',
        ];

        // Skip enqueue geniem-roles-styles if not on wp-geniem-roles menu page
        if ( in_array( $hook, $allowed ) ) {
            wp_enqueue_style( 'geniem_roles_styles', plugin_dir_url( __FILE__ ) . 'geniem-roles-styles.css', false, '1.0.6' );
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function init() {
        // Do the code.
        self::load_current_roles();
    }

    /**
     * Loads all active roles
     */
    public static function load_current_roles() {
        // Get global wp_roles containing roles, role_objects and role_names.
        global $wp_roles;

        // Loop through existing role_objects.
        foreach ( $wp_roles->role_objects as $role ) {
            $display_name = '';
            // Loop through role_names table to get display name.
            foreach ( $wp_roles->role_names as $key => $value ) {
                if( $key === $role->name ) {
                    $display_name = $value;
                }
            }
            // Create Role instance.
            self::$roles[ $role->name ] = new Role( $role->name, $display_name );
        }
    }

    /**
     * Returns role instances created from active roles.
     *
     * @return array $roles;
     */
    public static function get_roles() {
        $roles = self::$roles;
        return $roles;
    }

    /**
     * Create new roles
     *
     * @param string  $name
     * @param string  $display_name Translated later
     * @param boolean $caps
     */
    public static function create( $name, $display_name, $caps ) {

        // If role already exists return it
        if ( self::role_exists( $name ) ) {
            $role = self::$roles[ $name ];
            return $role;
        }
        else {
            // Merge capabilities.
            $caps = \wp_parse_args( $caps, Role::get_default_caps() );

            // Add role.
            \add_role( $name, $display_name, $caps );

            $role_instance = new Role( $name, $display_name );
            return $role_instance;
        }
    }
    /**
     * Check if role exists.
     */
    public static function role_exists( $slug ) {
        $role = \get_role( $slug );
        return $role !== null;
    }
    /**
     * Remove roles.
     * @param string $name
     */
    public static function remove_role( $name ) {

        // If role exists remove role
        if ( self::role_exists( $name ) ) {
            remove_role( $name );
            unset( self::$roles[ $name ] );
        }
    }

    /**
     * Rename a role with new_name
     *
     * @param [type] $slug
     * @param [type] $new_name
     * @return void
     */
    public static function rename( $slug, $new_name ) {
        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            self::$roles = new WP_Roles();
        }

        // Rename role
        $wp_roles->roles[$slug]['name']   = $new_name;
    }

    /**
     * Add caps to the role
     *
     * @param string $role_slug
     * @param [type] $caps
     * @return no return
     */
    public static function add_caps( $role_slug = '', $caps ) {

        if ( ! empty( $role_slug ) || ! empty( $caps ) ) {
            $role = \get_role( $role_slug );

            // Loop through caps
            if ( is_array( $caps ) && ! empty( $caps ) ) {
                foreach ( $caps as $cap ) {
                    // Add the capability.
                    $role->add_cap( $cap );
                }
            }
        }
        else {
            error_log( 'called Geniem/remove_caps without parameters' );
        }
    }

    /**
     * Remove role caps
     */
    public static function remove_caps( $role_slug = '', $caps ) {

        if ( ! empty( $role ) || ! empty( $caps ) ) {
            $role = \get_role( $role_slug );

            // Remove desired caps from a role.
            if ( is_array( $caps ) && ! empty( $caps ) ) {
                foreach ( $caps as $cap ) {
                    // Remove the capability.
                    $role->remove_cap( $cap );
                }
            }
        }
        else {
            error_log( 'called Geniem/remove_caps without parameters' );
        }
    }

    /**
     * If role exists return the role else returns false.
     * insert int or string
     *
     * @param string $slug Role slug.
     */
    public static function get( $slug ) {

        // Get from cache
        if ( isset( self::$roles[ $slug ] ) ) {
            return self::$roles[ $slug ];
        }
        else {
            // Variables
            $name   = self::$roles[ $slug ]['name'];
            $cap    = self::$roles[ $slug ]['capabilities'];

            // Instace of Role
            $role   = new Role( $slug, $name, $cap );

            return $role;
        }
    }

    /**
     * Remove menu pages from a role.
     * note: All menu page slugs can be found from the admin Geniem Roles -> Menu slugs.
     *
     * @param string $role_slug Role slug.
     * @param string $menu_pages Menu page slug.
     * @return void
     */
    public static function remove_menu_pages( $role_slug = '', $menu_pages = null ) {

        // Run in admin_menu hook when called outside class
        add_action( 'admin_init', function() use ( $role_slug, $menu_pages ) {

            // user object
            $user = wp_get_current_user();

            /**
             * Remove menu pages by role
             * Note: Some plugins cannot be removed in admin_menu -hook so we have to do it in admin_init.
             * In admin_init hook we have to check if not doing ajax to avoid errors.
             */
            if ( in_array( $role_slug, $user->roles, true ) && ! wp_doing_ajax() ) {

                if ( ! empty( $menu_pages ) ) {

                    // If multiple menu pages in array.
                    if ( is_array( $menu_pages ) && ! empty( $menu_pages ) ) {
                        foreach ( $menu_pages as $main_lvl_key => $menu_page ) {

                            // If there are submenu pages to be removed.
                            if ( is_array( $menu_page ) ) {
                                foreach ( $menu_page as $submenu_item ) {

                                    \remove_submenu_page( $main_lvl_key, $submenu_item );
                                }
                            } else {
                                \remove_menu_page( $menu_page );
                            } // End if().
                        } // End foreach().
                        // If only one item to be removed.
                    } elseif ( is_string( $menu_pages ) ) {
                        \remove_menu_page( $menu_pages );
                        // Removed menu page isn't valid.
                    } else {
                        return false;
                    }
                } else {
                    error_log( 'remove_role_menu_pages called without valid $menu_pages' );
                }
            }
        });
    }

    /**
     * Remove submenu pages by parent_slug and role.
     * Remove_menu_pages can also remove submenu pages with associative array.
     *
     * @param [type] $role_slug
     * @param [type] $parent_slug
     * @param [type] $menu_pages
     * @return void
     */
    public static function remove_submenu_pages( $role_slug = '', $parent_slug = '', $menu_pages = null ) {

        // Run in admin_menu hook when called outside class
        add_action( 'admin_init', function() use ( $role_slug, $parent_slug, $menu_pages ) {

            // user object
            $user = wp_get_current_user();

            // remove submenu pages by role
            // Note: have to check if not doing ajax to avoid errors in admin_init hook
            if ( in_array( $role_slug, $user->roles, true ) && ! wp_doing_ajax() ) {

                if ( ! empty( $menu_pages ) ) {

                    // if multiple menu pages in array
                    if ( is_array( $menu_pages ) && ! empty( $menu_pages ) ) {
                        foreach ( $menu_pages as $menu_page ) {
                            remove_submenu_page( $parent_slug, $menu_page );
                        }
                    }
                    // If not array remove page by slug
                    else {
                        remove_submenu_page( $parent_slug, $menu_pages );
                    }

                }
                else {
                    error_log( 'remove_role_menu_pages called without valid $menu_pages' );
                }
            }
        });
    }

    /**
     * Add a user to the Super admin user list in WordPress Multisite
     *
     * @return No return.
     */
    public static function grant_super_admin_cap( $user_id ) {
        grant_super_admin( $user_id );
    }

    /**
     * Adds WP Ga options settings
     */
    public static function add_options_page() {
        if ( is_admin() ) {
            // Run in admin_menu hook when called outside class
            add_action( 'admin_menu', function() {

                \add_menu_page(
                    __( 'Geniem Roles', 'wp-geniem-roles' ), // page title
                    __( 'Geniem Roles', 'wp-geniem-roles' ), // menu title
                    'activate_plugins',                      // capability
                    'wp-geniem-roles',                       // menu slug
                    array( __CLASS__, 'geniem_roles_html' ), // render function
                    'dashicons-universal-access',
                    80
                );

                \add_submenu_page(
                    'wp-geniem-roles',                                   // parent menu slug
                    __( 'Geniem Roles: Menu slugs', 'wp-geniem-roles' ), // page title
                    __( 'Menu slugs', 'wp-geniem-roles' ),               // menu title
                    'activate_plugins',                                  // capability
                    'wp-geniem-roles-slugs',                             // menu slug
                    array( __CLASS__, 'geniem_roles_slug_html' )         // render function
                );
            });
        }
    }

    /**
     * Geniem roles printable html
     *
     * @return void
     */
    public static function geniem_roles_html() {

        echo '<div class="geniem-roles">';
        echo '<h1 class="dashicons-before dashicons-universal-access"> '. __( 'Geniem roles', 'geniem-roles' ) . '</h1>';
        echo '<p>'. __( 'This page lists all current roles and their enabled capabilities.', 'geniem-roles' ) . '</p>';

        // Do not list cap if in $legacy_caps
        $legacy_caps = [ 'level_10', 'level_9', 'level_8', 'level_7', 'level_6', 'level_5', 'level_4', 'level_3', 'level_2', 'level_1', 'level_0' ];

        if ( ! empty( self::$roles ) ) {

            $i = 1;
            echo '<div class="geniem-roles__wrapper">';
            // Roles
            foreach ( self::$roles as $role ) {

                // Single role wrap
                echo '<div class="geniem-roles__single-role">';

                // Name
                echo '<h2>' . $role->display_name . '</h2>';

                // Caps
                echo '<ul>';
                if ( ! empty( $role->capabilities ) ) {
                    foreach ( $role->capabilities as $key => $value ) {

                        $formated_cap = \str_replace( '_', ' ', $key );

                        if ( ! in_array( $key, $legacy_caps ) && $value !== false ) {
                            echo '<li>' . $formated_cap . '</li>';
                        }
                    }
                }
                echo '</ul>';
                echo '</div>'; // geniem-roles__single-role

            } // foreach ends
        }
        echo '</div>';
        echo '<div>'; // wrapper ends
    }

    /**
     * Geniem roles menu items slug list.
     *
     * @return void
     */
    public static function geniem_roles_slug_html() {
        $menu_list = self::get_menu_list();

        echo '<div class="geniem-roles">';
        echo '<h1 class="dashicons-before dashicons-universal-access"> ' . __( 'Geniem roles', 'geniem-roles' ) . '</h1>';
        echo '<p>'. __( 'This page lists all admin menu slugs.', 'geniem-roles' ) . '</p>';
        echo '<div class="geniem-roles__wrapper">';
        echo '<div class="geniem-roles__slugs">';
        echo '<table>';
        foreach ( $menu_list as $menu ) {
            echo '<tr>';
            echo '<td>' . $menu->label . '</td>';
            echo '<td>' . $menu->path . '</td>';
            echo '</tr>';

            foreach ( $menu->children as $child_menu ) {
                echo '<tr class="child-menu">';
                echo '<td>' . $child_menu->label . '</td>';
                echo '<td>' . $child_menu->path . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    public static function get_menu_list() {
        global $menu, $submenu;

        $menu_list = [];
        foreach ( $menu as $i => $menu_data ) {
            if ( $menu_data[0] ) {
                $parent_menu = (object) [
                    'label' => strip_tags( $menu_data[0] ),
                    'path' => $menu_data[2],
                    'children' => [],
                ];

                $menu_list[ $parent_menu->path ] = $parent_menu;
            }
        }
        foreach ( $submenu as $i => $menu_data ) {
            if ( array_key_exists( $i, $menu_list ) ) {
                $sub_menus = array_map( function( $menu ) {
                    $item = (object) [
                        'label' => strip_tags( $menu[0] ),
                        'path' => $menu[2],
                    ];

                    return $item;

                }, $menu_data);
                $menu_list[ $i ]->children = $sub_menus;
            }
        }

        return $menu_list;
    }

    /**
     * Add function to map_meta_cap which disallows certain actions for role in specifed posts.
     *
     * @param string $role_slug Role slug
     * @param array  $blocked_posts
     * @param string $capability
     */
    public static function restrict_post_edit( $role_slug, $blocked_posts, $capability ) {
        // TODO
        // Vertaile onko blocked_posts int vai string
        // Tee käsittelyt slugille ja post id:lle
        $current_user = wp_get_current_user();
        // TODO
        // Add filter
        $current_user_roles = $current_user->roles;
        // Add function to map_meta_cap which disallows certain actions for role in specifed posts.
        // Check if we need to restrict current user.
        if ( in_array( $role_slug, $current_user_roles, true ) ) {
            /**
             * Map_meta_cap arguments.
             * $caps (array) Returns the user's actual capabilities.
             * $cap (string) Capability name.
             * $user_id (int) The user ID.
             * $args (array) Adds the context to the cap. Typically the object ID.
             */
            \add_filter( 'map_meta_cap', function ( $caps, $cap, $user_id, $args ) use ( $blocked_posts, $role_slug, $capability ) {
                // $args[0] is the post id.
                if ( $cap === $capability && in_array( $args[ 0 ], $blocked_posts, true ) ) {
                    // This is default Wordpress way to restrict access.
                    $caps[] = 'do_not_allow';
                }
                return $caps;
            }, 10, 4 );
        }
    }

    /*
     * TODO: Filters gettext_with_context to translate role names.
     */
}

/**
 * Class Role which handles a intance of a single editable role
 */
class Role {

    /**
     * Role name
     */
    public $name;

    /**
     * Role display name
     */
    public $display_name;


    /**
     * Capabilities
     */
    public $capabilities;

    /**
     * Get default caps for roles
     */
    public static function get_default_caps() {

        $defaults = array(
            // Network (Super Admin)
            'create_sites'              => false,
            'delete_sites'              => false,
            'manage_network'            => false,
            'manage_sites'              => false,
            'manage_network_users'      => false,
            'manage_network_plugins'    => false,
            'manage_network_themes'     => false,
            'manage_network_options'    => false,

            // CSS
            'edit_css'                  => false,

            // Users
            'add_users'                 => false,
            'create_users'              => false,
            'delete_users'              => false,
            'edit_users'                => false,
            'list_users'                => false,
            'promote_users'             => false,

            // WordPress default capabilities
            'activate_plugins'          => false,
            'delete_others_pages'       => false,
            'delete_others_posts'       => false,
            'delete_pages'              => false,
            'delete_posts'              => false,
            'delete_private_pages'      => false,
            'delete_private_posts'      => false,
            'delete_published_pages'    => false,
            'delete_published_posts'    => false,
            'edit_dashboard'            => false,
            'edit_others_pages'         => false,
            'edit_others_posts'         => false,
            'edit_pages'                => false,
            'edit_posts'                => false,
            'edit_private_pages'        => false,
            'edit_private_posts'        => false,
            'edit_published_pages'      => false,
            'edit_published_posts'      => false,
            'edit_theme_options'        => false,
            'export'                    => false,
            'import'                    => false,
            'manage_categories'         => false,
            'manage_links'              => false,
            'manage_options'            => false,
            'moderate_comments'         => false,
            'publish_pages'             => false,
            'publish_posts'             => false,
            'read_private_pages'        => false,
            'read_private_posts'        => false,
            'read'                      => false,
            'remove_users'              => false,
            'switch_themes'             => false,
            'upload_files'              => false,
        );

        // filter default caps
        return apply_filters( 'geniem/roles/default_roles', $defaults );
    }

    /**
     * Role constructor.
     *
     * @param string $name Role name.
     * @param string $display_name Display name for the role.
     */
    public function __construct( $name, $display_name ) {

        $role       = \get_role( $name );

        // Get role
        if ( $role ) {
            // Set values.
            $this->capabilities = $role->capabilities;
            $this->name         = $role->name;
            $this->display_name = $display_name;
        }
        // Create new role
        else {
            $this->capabilities = self::get_default_caps();
            $this->name = $name;
            $this->display_name = $display_name;
        }
    }

    /**
     * Remove a role
     *
     * @return void
     */
    public function remove() {
        Roles::remove_role( $this->name );
    }

    /**
     * Remove menu pages
     *
     * @param array $menu_pages Mixed array of removable admin menu items.
     * Array value can be a string or
     * assoaciative array item 'parent_slug' => [ 'submenu_item1_slug', 'submenu_item2_slug' ].
     * @return void
     */
    public function remove_menu_pages( $menu_pages ) {
        Roles::remove_menu_pages( $this->name, $menu_pages );
    }

    /**
     * Remove menu pages
     *
     * @param array $parent_slug Parent menu item slug.
     * @param array $menu_pages Array of strings to remove submenu pages.
     * @return void
     */
    public function remove_submenu_pages( $parent_slug, $menu_pages ) {
        Roles::remove_submenu_pages( $this->name, $parent_slug, $menu_pages );
    }

    /**
     * Add capabilities for a role
     * Makes db changes do not run everytime.
     *
     * @param [type] $role_slug
     * @param [type] $cap
     * @return void
     */
    public function add_caps( $caps ) {
        Roles::add_caps( $this->name, $caps );
    }

    /**
     * Remove capabilities for a role
     * Makes db changes do not run everytime.
     *
     * @param [type] $role_slug
     * @param [type] $cap
     * @return void
     */
    public function remove_caps( $caps ) {
        Roles::remove_caps( $this->name, $caps );
    }

    /**
     * Get all caps for by role.
     *
     * @param string $slug 
     * @return void
     */
    public function get_caps( $slug ) {
        return get_role( $slug )->capabilities;
    }

    /**
     * Rename a role.
     *
     * @param string $new_display_name Display name for a role.
     * @return void
     */
    public function rename( $new_display_name ) {
        return Roles::rename( $this->name, $new_display_name );
    }

    /**
     * Block post edit view by post ids
     *
     * @param string $blocked_posts
     * @param string $capability
     * @return void
     */
    public function restrict_post_edit( $blocked_posts, $capability ) {
        return Roles::restrict_post_edit( $this->name, $blocked_posts, $capability );
    }

}

/**
 * Returns the Geniem Roles singleton.
 *
 * @return object
 */
function roles() {
    return Roles::instance();
}
