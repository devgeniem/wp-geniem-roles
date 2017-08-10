# Geniem Roles
Wrapper classes for developers to create and manipulate WordPress roles.

## Installation
Move plugin to your WordPress installation `plugins/` folder.

## Composer installation
command line
```
composer require devgeniem/wp-geniem-roles
```
composer.json
```json
...
"require": {
    "devgeniem/wp-geniem-roles": "^0.1.0",
    ...
}
...
```

## Examples

### Initializing Geniem Roles
First init \Geniem\Roles singleton by running a function `\Geniem\roles();`
```php
// Init Geniem\Roles singleton
\Geniem\roles();
```

### Create a new role with capabilities
All new roles capabilities defaults to `false`. So we add just capabilities that we want to apply for the role. See the example code for a hands on example.

```php
/**
 * Create a new role
 */

// Caps to be added to the new role
// all caps default to false see the details plugin.php \Geniem\Role::get_default_caps()
$new_role_caps = array(
    "activate_plugins"              => true,
    "delete_others_pages"           => true,
    "delete_others_posts"           => true
);

// Create a new role "testrole" with wanted capabilities
$new_role = \Geniem\Roles::create( 'new_role', __( 'New role', 'theme-text-domain' ), $new_role_caps );

// Check if role throws a WordPress error
if ( is_wp_error( $new_role ) ) {
    error_log( $new_role->get_error_messages() );
}
```

### Get and manipulate a role
You can call existing role from WordPress by calling function `\Geniem\Roles::get( $role_slug );`. You can use a role as an object to manipulate the role. See the example from the below.

```php
// creates a instace of \Geniem\Role
$admin = \Geniem\Roles::get( 'administrator' );
```

### Add caps for a role
```php
// Define desired capabilities for a role 'administrator'
$admin_caps = [
    'geniem_roles'
];

// add_caps takes an array off capabilities
$admin->add_caps( $admin_caps );
```

### Remove caps from a role
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

### Remove menu pages from a role
You can remove single admin menu page with `string` value or multiple pages with an `array` value.

```php
// Get a role
$admin = \Geniem\Roles::get( 'administrator' );

// Define removable admin pages array
$admin_removable_admin_pages = [
    'edit.php', // posts
    'edit.php?post_type=page' //  pages
];

// Remove multiple menu pages remove_role_menu_pages( $role_slug, $menu_pages )
$admin->remove_menu_pages( $admin_removable_admin_pages );
```

### Remove submenu pages from a role
You can remove single admin submenu page with `string` value or multiple pages with `array` value.

```php
// An array of removable submenu pages
$admin_removable_submenu_pages = [
    'nav-menus.php'
];

// Remove multiple submenu pages remove_role_submenu_pages( $role_slug, $parent_slug, $menu_pages )
$admin->remove_submenu_pages( 'administrator', 'themes.php', $admin_removable_submenu_pages );
```

### Grant super admin cap for a single user
```php
\Geniem\Roles::grant_super_admin_cap( 1 );
```

## Filters
### Filter new role default roles
`apply_filters( 'geniem/roles/default_roles', $defaults );`

### Admin side role listing
`wp-geniem-roles` creates a admin page which lists all current active roles and their capabilities in the admin side. Admin page can be seen for roles that can `can_activate_plugins`.

#### screenshot
![Admin side screenshot](docs/images/screenshot-admin.png)
