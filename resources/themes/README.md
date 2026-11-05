# Ayra storefront theme packages

Themes are installed at `/admin/site-builder/themes`. A ZIP can contain the files at its root or inside one folder. The folder name does not need to match the slug.

```
my-theme/
  theme.info.json
  theme.css
  thumbnail.webp
  assets/
    campaign.webp
    brand.woff2
```

`theme.info.json` is a Drupal-style information file in JSON. The required fields are `slug`, `name`, `author`, and `description`. `slug` must be lowercase words separated by hyphens and must be unique. `version` and `thumbnail` are optional. When set, `thumbnail` must name a valid `thumbnail.png`, `thumbnail.jpg`, `thumbnail.jpeg`, or `thumbnail.webp` in the ZIP.

```json
{
  "slug": "studio-orange",
  "name": "Studio orange",
  "author": "Your studio",
  "description": "A bright, sharp storefront for a modern brand.",
  "version": "1.0",
  "thumbnail": "thumbnail.webp"
}
```

The ZIP can contain the manifest, CSS, one thumbnail, and up to 50 total files including images (`.png`, `.jpg`, `.jpeg`, `.webp`) or WOFF2 fonts in `assets/`. PHP, Blade, JavaScript and arbitrary files are rejected. Themes are installed inactive. Activate a theme in the admin to use it site-wide. The active slug is stored in `site_settings`; switching back to **Smart-minima-commerce** restores the original appearance. Theme styles do not alter product or page data.

`theme.css` loads after `resources/css/app.css` and `resources/css/ayra.css` on storefront pages. It also loads after the base account styles on login and registration pages. The base styles supply layout and responsive behavior, so theme CSS can focus on visual changes. A theme may override every component; these hooks are a useful starting point:

```css
:root {
  --ayra-green: #202020; /* primary controls and selected options */
  --ayra-deep: #101010;  /* headings */
  --ayra-cream: #faf7f0; /* page background */
  --ayra-line: #d8d3cb;  /* borders */
  --ayra-ink: #171717;   /* body copy */
}

/* Shared header and footer */
.ayra-nav { background: #202020; color: #fff; }
.ayra-nav .ayra-brand, .ayra-nav-links a { color: #fff; }
.ayra-footer { background: #202020; color: #fff; }
.ayra-hero-copy { background-image: url('assets/campaign.webp'); }
@font-face { font-family: 'Brand'; src: url('assets/brand.woff2') format('woff2'); }

/* Homepage hero, collection, filters and product cards */
.ayra-hero-copy { background: #202020; color: #fff; }
.ayra-hero-copy h1 { color: #fff; }
.ayra-collection-card { border-radius: 0; }
.ayra-filter-panel { background: #fff; }
.ayra-product-card { background: transparent; }
.ayra-product-image { border-radius: 0; }

/* Detail, cart, checkout, success/failure and orders */
.ayra-detail-info h1 { text-transform: uppercase; }
.ayra-gallery-main { border-radius: 0; }
.ayra-cart-list, .ayra-checkout-summary, .ayra-order-panel,
.ayra-result-card { border-radius: 0; background: #fff; }
.ayra-button { background: #f06b54; color: #171717; }

/* Page builder sections and account pages */
.ayra-builder-hero { background: #202020; }
.ayra-builder-header-text { background: #f06b54; }
body.store-body .auth-side { background: #202020; }
body.store-body .auth-card button[type=submit] { background: #f06b54; }
```

When overriding a dark background, also set text, link, and button colors so they remain readable. Built-in Bold contemporary is a complete example in `resources/themes/bold-contemporary/theme.css`. Theme assets are served at `/theme-assets/{slug}/theme.css`, `/theme-assets/{slug}/thumbnail.webp`, and `/theme-assets/{slug}/assets/{filename}`; no asset build is needed for an uploaded theme.
