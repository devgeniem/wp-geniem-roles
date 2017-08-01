# Geniem Roles
Wrapper classes for WordPress role creation and editing.

## Installation
Move file geniem-roles.php to WordPress mu-plugins folder.
Create file to your theme where you call wrapper class functions. See the examples from below.

## Composer installation
```
require devgeniem/wp-geniem-roles
```

## Examples

### Create a new role with capabilities
All new roles capabilities defaults to `false`. So we add just capabilities that we want to apply for the role. See the example code for a hands on example.

```php
/**
 * A Geniem roles example
 * Create a new role
 */

// Init Geniem\Roles
$roles_instance = new \Geniem\Roles();

// Caps to add to the new role
$new_role_caps = array(
    "activate_plugins"              => true,
    "delete_others_pages"           => true,
    "delete_others_posts"           => true
);

// Create a new role "testrole"
$new_role = new \Geniem\Role( 'testrole', 'Test role', $new_role_caps );


// Check if role throws a WordPress error
if ( is_wp_error( $new_role ) ) {
    error_log( $new_role->get_error_messages() );
}
```

### Remove caps from role
```php
// Define removable caps in an array
$admin_removable_caps = [
    'edit_users',
    'delete_users',
    'create_users'
];

// Run function remove_caps for desired wp role
$roles_instance::remove_caps( 'administrator', $admin_removable_caps );
```

### Add caps for role
```php
// Define removable caps in an array
$admin_add_caps = [
    'edit_users',
    'delete_users',
    'create_users'
];

// Run function remove_caps for desired wp role
$roles_instance::add_caps( 'administrator', $admin_add_caps );
```

### Grant super admin cap for a user
```php
$roles_instance::grant_super_admin_cap( 1 );
```

### Remove menu pages by role
You can remove single admin menu page with `string` value or multiple pages with `array` value.

```
// Removable admin pages array
$admin_removable_admin_pages = [
    'edit.php', // posts
    'edit.php?post_type=page' //  pages
];

// Remove multiple menu pages remove_role_menu_pages( $role_slug, $menu_pages )
$roles::remove_role_menu_pages( 'administrator', $admin_removable_admin_pages );

// Remove single menu page
$roles::remove_role_menu_pages( 'administrator', 'edit.php' );
```

### Remove submenu pages by role and parent page
You can remove single admin submenu page with `string` value or multiple pages with `array` value.

```
// An array of removable submenu pages
$admin_removable_submenu_pages = [
    'nav-menus.php'
];

// Remove multiple submenu pages remove_role_submenu_pages( $role_slug, $parent_slug, $menu_pages )
$roles::remove_role_submenu_pages( 'administrator', 'themes.php', $admin_removable_submenu_pages );

// Remove single submenu page
$roles::remove_role_submenu_pages( 'administrator', 'themes.php', 'nav-menus.php' );
```

TODO
### Translations
