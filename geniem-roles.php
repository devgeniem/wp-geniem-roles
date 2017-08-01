<?php
/**
 * Plugin Name: Geniem Roles
 * Plugin URI: https://github.com/devgeniem/wp-geniem-roles
 * Description: WordPress plugin to edit and create roles in code
 * Version: 0.0.3
 * Author: Timi-Artturi Mäkelä / Geniem Oy
 * Author URI: https://geniem.fi
 **/

namespace Geniem;

/**
 * Geniem Roles
 */
class Roles {

    /**
     * Roles
     *
     * @var [type]
     */
    protected static $roles;

    /**
     * Roles __constuctor
     */
    public function __construct() {

        // Get all current roles
        self::$roles = self::get_current_roles();

        // Actions
        add_action( 'init', array( __CLASS__, 'init' ) );
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public static function init() {
        // Do the code.

    }

    /**
     * Returns all active roles
     */
    public static function get_current_roles() {
        global $wp_roles;

        // Get all current roles
        /*$all_roles = array_keys( $wp_roles->roles );*/

        return $wp_roles->roles;
    }

    /**
     * Create new roles
     * @param string $slug
     * @param  $name
     * @param boolean $caps
     */
    public static function create_role( $slug, $name, $caps ) {

        // If role already exists return WP_Error
        if ( in_array( $slug, self::$roles, true ) ) {
            new \WP_Error( 409, __( 'Role already exists!', 'geniem-roles' ) );
        }
        else {
            // merge capabilities
            $caps = array_merge( Role::get_default_caps(), $caps );

            // add roles
            // Todo: how to handle translations
            add_role( $slug, __( $name ), $caps );
        }
    }

    /**
     * Remove roles.
     * @param string $slug
     */
    public function remove_role( $slug ) {
        // If role exists remove role
        if ( in_array( $slug, self::$roles, true ) ) {
            remove_role( $slug );
        }
    }

    /**
     * Renames roles with new_name.
     */
    public function rename( $slug, $new_name ) {

        if ( ! isset( $wp_roles ) ) {
            $self::$roles = new WP_Roles();
        }

        // Rename role
        $wp_roles->roles[$slug]['name']   = $new_name;
        $wp_roles->role_names[$slug]      = $new_name;
    }

    /**
     * Remove role caps
     */
    public static function remove_caps( $role_slug = '', $caps ) {

        if ( ! empty( $role ) || ! empty( $caps ) ) {
            $role = get_role( $role_slug );

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
     * If role exists return the role
     * insert int or string
     * @param string $slug
     */
    public function get( $slug ) {

        // Get from cache
        if ( isset( self::$roles[ $slug ] ) ) {
            return self::$roles[ $slug ];
        }
    }

    /**
     * Remove menu pages from a role.
     * TODO MITENKÄ TÄTÄ VOI AJAA NIIN ETTÄ AJAUTUU admin_init :ssä remove_menu_page on pakko poistaa admin_initissä
     * @param string $role_slug
     * @param string $menu_page
     * @return void
     */
    public static function remove_role_menu_pages( $role_slug = '', $menu_pages = null ) {

        // Run in admin_menu hook when called outside class
        add_action( 'admin_menu', function() use ( $role_slug, $menu_pages ) {

            // user object
            $user = wp_get_current_user();

            // remove menu pages by role
            // Note: have to check if not doing ajax to avoid errors in admin_init hook
            if ( in_array( $role_slug, $user->roles, true ) && ! wp_doing_ajax() ) {

                if ( ! empty( $menu_pages ) ) {

                    // if multiple menu pages in array
                    if ( is_array( $menu_pages ) && ! empty( $menu_pages ) ) {
                        foreach ( $menu_pages as $menu_page ) {
                            remove_menu_page( $menu_page );
                        }
                    }
                    else {
                        remove_menu_page( $menu_pages );
                    }

                }
                else {
                    error_log( 'remove_role_menu_pages called without valid $menu_pages' );
                }
            }
        });
    }

    /**
     * Remove submenu pages by parent_slug and role
     *
     * @param [type] $role_slug
     * @param [type] $parent_slug
     * @param [type] $menu_pages
     * @return void
     */
    public static function remove_role_submenu_pages( $role_slug = '', $parent_slug = '', $menu_pages = null ) {

        // Run in admin_menu hook when called outside class
        add_action( 'admin_menu', function() use ( $role_slug, $parent_slug, $menu_pages ) {

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
     * @return no return
     */
    public static function grant_super_admin_cap( $user_id ) {
        grant_super_admin( $user_id );
    }

    /*
     * Filters gettext_with_context
     */
    /* public function translate_role_names() {
    } */

}

/**
 * Add and edit role
 */
class Role {

    /**
     * Role slug
     */
    protected $slug;

    /**
     * Capabilities
     */
    protected $capabilities;

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
            'edit_css'                  => true,

            // Users
            'add_users'                 => true,
            'create_users'              => true,
            'delete_users'              => true,
            'edit_users'                => true,
            'list_users'                => true,
            'promote_users'             => true,

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
            'list_users'                => false,
            'manage_categories'         => false,
            'manage_links'              => false,
            'manage_options'            => false,
            'moderate_comments'         => false,
            'promote_users'             => false,
            'publish_pages'             => false,
            'publish_posts'             => false,
            'read_private_pages'        => false,
            'read_private_posts'        => false,
            'read'                      => false,
            'remove_users'              => false,
            'switch_themes'             => false,
            'upload_files'              => false
        );

        // filter default caps
        return apply_filters( 'geniem/roles/default_roles', $defaults );
    }

    /**
     * Constructor
     */
    public function __construct( $slug, $name, $defaults ) {

        Roles::create_role( $slug, $name, $defaults );

        add_action( 'admin_init', __NAMESPACE__ . '\Roles::init' );
    }

    /**
     * Remove a role
     *
     * @return void
     */
    public function remove() {
        roles()->remove_role( $this->slug );
    }

    /**
     * Makes db changes do not run everytime.
     *
     * @param [type] $role_slug
     * @param [type] $cap
     * @return void
     */
    public function add_cap( $role_slug, $cap ) {
        if ( in_array( $role_slug, (array) $user->roles ) ) {

        }
    }

    /**
     * Get all caps for by role.
     *
     * @param [type] $slug
     * @return void
     */
    public function get_caps( $slug ) {
        return get_role( $slug )->capabilities;
    }
}
