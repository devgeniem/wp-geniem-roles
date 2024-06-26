# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleased

## Released

## [1.6.1] - 2024-05-20
- Fix method param and return types.

## [1.6.0] - 2024-01-31
- Removed role default caps false values.
- Default caps has been listed in wiki page from now on.

## [1.5.2] - 2022-11-23
- Fixed role renaming `rename()` (props [@jmaekki](https://github.com/jmaekki)).

## [1.5.1] - 2022-05-31
- Optimized admin screenshot.

## [1.5.0] - 2021-11-11
- `grant_post_edit()` hotfix to handle only post type related caps inside map_meta_cap().
- Added parameter $post_types to the function `grant_post_edit()`.

## [1.4.0] - 2021-08-31
- grant_post_edit() to grant posts with the given ids and caps.
- You can also define caps for the blocked posts.

## [1.3.1] - 2021-01-19
- Removed hook that resets roles on wp-geniem-roles admin page. This might cause undesired situations if users reset roles by accident. Use these methods instead https://github.com/devgeniem/wp-geniem-roles#reset-roles-with-wp-geniem-roles.

## [1.3.0] - 2020-12-10
- Added a method `remove_admin_bar_nodes()`. This method removes admin bar nodes by node id.

## [1.2.2] - 2020-10-02

## Fixed
- Fixed functions `add_caps()` and `remove_caps()` for the default WordPress roles.

## [1.2.1] - 2019-10-04

## Modified
- Added global `$menu` variable empty check when removing menu pages.

## [1.2.0] - 2019-10-04

## Modified
- Adds functions which makes possible to restrict roles available templates.

## [1.1.2] - 2019-10-02

## Modified
- Adds support for array of capabilities for `restrict_post_edit()`.

## [1.1.1] - 2019-09-09

## Modified
- Fixes a bug with a cap `add_user` and function `restrict_user_management_by_role()`.

## [1.1.0] - 2019-08-20

### Added
- Support for resetting roles on multisite with CLI command `wp eval "\Geniem\Roles::reset_roles();"`

## [1.0.3] - 2019-03-01

### Modified
- Version bump.

## [1.0.2] - 2019-01-25

### Added
- Reset roles on wp-geniem-roles admin page on load.

### Modified
- Fixed wp-geniem-roles option page rename issue.
- remove_menu_pages() removes now also admin_bar menu pages.

## [1.0.1] - 2018-10-18

### Modified
- Better documentation.
- Escaping strings.
- Role reset function `reset_to_default_roles()` to reset roles to the WordPress standard roles.
- Role reseting function for development `reset_roles()` to reset roles after changes.

## [user-management-documentation] - 2018-04-26

### Modified
- Enhanced documentation for `get_default_caps()` and `README.md` heading for networks.

## [0.4.0] - 2018-03-26

### Added
- Added function `restrict_user_management_by_role()`.
- Added documentation for `restrict_user_management_by_role()`.
- Hotfix for role rename function.
- Added support for removing WordPress customizer menu page `customize.php` from the admin menu. Changes implemented in the function `remove_menu_pages()`.

## [0.3] - 2017-11-12

### Modified
- Singleton is now woken in plugin.php. User doesn't need to create singleton in the theme code anymore. Singleton is initialized when Geniem Roles has been activated.
- Function `load_current_roles()` runs now on `setup_theme` hook. Global `$wp_roles` isn't avalaible before `setup_theme` hook.
- `remove_menu_pages()` can now remove also submenu pages. See the documentation for the details.
- Added check for function `add_caps()`. If cap already exists for the role -> do not try to add the cap.
- Added check for function `remove_caps()`. If cap is already removed from the role -> do not try to remove the cap.

### Removed
- Removed function `init()`.
- Removed function `remove_submenu_pages()`. `remove_menu_pages()` does now the same thing in more intuitive way.
- Removed documentation for `remove_submenu_pages()`.

## [0.2.4] - 2017-09-25

### Added
- Menu slugs page that lists all admin menu page slugs

## [0.2.3] - 2017-09-11

### Added
- CHANGELOG.MD

### Changed
- Documentation: How to remove plugin pages from admin menu with function `remove_menu_pages()`.
