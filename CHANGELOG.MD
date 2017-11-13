# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Unreleases

## [0.3.1] - 2017-11-13
- Added function `restrict_user_management_by_role()`.
- Added documentation for `restrict_user_management_by_role()`.

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
