# Ayra theme

This folder is the code-owned theme for the site builder. `theme.php` lists its page templates. The matching Blade views live under `views/site-builder/pages/`. The site builder only accepts template keys from this manifest and renders them through Laravel's view system.

The current storefront CSS remains in `resources/css/ayra.css`; the manifest and view folders are the first step toward a self-contained theme package. Theme code is deployed through Git, while page content and media choices are managed in the admin. A future theme can have its own folder and manifest; switching the active storefront theme is not yet exposed because Ayra is the only installed theme.
