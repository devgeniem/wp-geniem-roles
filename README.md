# Geniem Roles
Wrapper classes for WordPress role creation and editing.

## Examples

### Create a new role with capabilities
All new roles capabilities defaults to `false`. So we add just capabilities that we want to apply for the role. See the example code for a hands on example.

```php
/**
 * A Geniem roles example
 * Create a new role
 */

// Init Geniem\roles singleton class
$roles_instance = \Geniem\roles();

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

### Remove menu pages by user

### Remove caps from a role

