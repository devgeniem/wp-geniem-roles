<?php
/**
 * Plugin Name: Geniem Roles
 * Plugin URI: https://github.com/devgeniem/wp-geniem-roles
 * Description: WordPress plugin to edit and create roles in code
 * Version: 0.0.2
 * Author: Timi-Artturi Mäkelä / Geniem Oy
 * Author URI: https://geniem.fi
 **/

namespace Geniem;


final class Roles {

    /**
     * Singleton DustPress instance
     *
     * @var [type]
     */
    private static $instance;

    /**
     * Roles
     *
     * @var [type]
     */
    protected static $roles;

    /**
     * Init roles singletone
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
    protected function __construct() {

        // Get all current roles
        self::$roles = self::get_current_roles();

        // Actions
        add_action( 'init', [ $this, __NAMESPACE__ . '\Roles::init' ] );

        // Todo: figure out why wont run in the hook (maybe singleton problem)
        add_action( 'admin_init', [ $this, __NAMESPACE__ . '\Roles::remove_role_menu_pages' ] );

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

    /*
     * Filters gettext_with_context
     */
    public function translate_role_names() {

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
    public static function remove_role_menu_pages( $role_slug, $menu_page ) {

        $user = wp_get_current_user();

        if ( in_array( $role_slug, $user->roles, true ) && ! wp_doing_ajax() ) {
            // Todo loop passed menu pages and remove all pages at once
            remove_menu_page( $menu_page );
        }
    }

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
            'activate_plugins'              => false,
            'delete_others_pages'           => false,
            'delete_others_posts'           => false,
            'delete_pages'                  => false,
            'delete_posts'                  => false,
            'delete_private_pages'          => false,
            'delete_private_posts'          => false,
            'delete_published_pages'        => false,
            'delete_published_posts'        => false,
            'edit_dashboard'                => false,
            'edit_others_pages'             => false,
            'edit_others_posts'             => false,
            'edit_pages'                    => false,
            'edit_posts'                    => false,
            'edit_private_pages'            => false,
            'edit_private_posts'            => false,
            'edit_published_pages'          => false,
            'edit_published_posts'          => false,
            'edit_theme_options'            => false,
            'export'                        => false,
            'import'                        => false,
            'list_users'                    => false,
            'manage_categories'             => false,
            'manage_links'                  => false,
            'manage_options'                => false,
            'moderate_comments'             => false,
            'promote_users'                 => false,
            'publish_pages'                 => false,
            'publish_posts'                 => false,
            'read_private_pages'            => false,
            'read_private_posts'            => false,
            'read'                          => false,
            'remove_users'                  => false,
            'switch_themes'                 => false,
            'upload_files'                  => false
        );

        // filter default roles
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
     */
    public function add_cap( $role_slug, $cap ) {
        if ( in_array( $role_slug, (array) $user->roles ) ) {

        }
    }

    /**
     * Makes db changes do not run everytime.
     */
    public function remove_cap( $cap ) {

    }

    /*
     * Get all caps for a role.
     */
    public function get_caps( $slug ) {
        return get_role( $slug )->capabilities;
    }

    /**
     * add_super_admin_cap
     *
     * @return no return
     */
    public function add_super_admin_cap() {

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
