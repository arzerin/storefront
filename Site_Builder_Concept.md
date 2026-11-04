# Ayra site builder concept

## Goal

Let a store owner assemble polished marketing pages without editing Blade files, while preserving Ayra's storefront design and working commerce flows. The builder should manage the homepage and new campaign/content pages. Product details, cart, checkout, order, login, and account pages remain application pages; they may share global branding but their transactional layout and logic stay controlled by the application.

## What PWCS contributes

The PWCS Site Builder menu grouped site information, theme/templates, block configuration, menus, and CMS pages (`source-backup/pwcs/pwcs_block.php`). Its blocks had named placements, display weight, and visibility rules (`source-backup/pwcs/pwcs.module`). The page links pointed to `advancepages/siteadmin/sitepage`, whose implementation is not present in this backup. The useful idea is a single workflow for content, layout, and navigation—not a direct copy of Drupal's forms or its arbitrary PHP/HTML rendering.

Ayra already has site settings, positioned text blocks, and footer links. Those controls are the starting point. They cannot yet rearrange the homepage, create pages, preview unpublished changes, or choose visual section types.

## Owner experience

Admin navigation: **Site Builder** → Pages, Home, Media, Header menu, Footer menu, Site settings, Reusable blocks.

1. Open **Home** or create a page such as `/about`, `/journal/autumn-edit`, or `/shipping`.
2. Choose a starting layout: editorial landing, collection campaign, or simple information page.
3. Add sections from a small palette; drag to reorder (with keyboard-accessible move controls).
4. Edit copy, images, buttons, linked collection/products, and a few approved style choices in a side panel.
5. See desktop and mobile previews using the same components as the public page.
6. Save a draft, preview privately, then publish. The previous published revision stays available for rollback.

The editor should show realistic product cards and collection images, not placeholders where real catalogue data exists. A section with no published products should show an editor warning and should not break the live page.

## First section palette

| Section | Owner-editable fields | Data source |
| --- | --- | --- |
| Hero | Eyebrow, headline, text, image, alignment, one or two buttons | Uploaded media |
| Benefits strip | 2–5 short selling points | Section content |
| Editorial split | Image, heading, text, button, image side | Uploaded media |
| Collection grid | Heading, selected collections, card count | Existing published collections |
| Product grid | Heading, category/collection/manufacturer or selected products, count | Existing published products and current pricing |
| Rich text | Heading and formatted prose from an allowlisted editor | Section content |
| Image banner | Image, overlay text, button, contrast choice | Uploaded media |
| FAQ | Ordered question/answer pairs | Section content |
| Reusable block | Reference to a managed block | Existing site blocks |

Product prices, discounts, stock, links, and variant images must come from the catalogue at render time. The builder must not store copies of those values.

## Data and rendering

- `site_pages`: title, unique slug, page kind (`home` or `content`), SEO title/description, published revision, timestamps.
- `site_page_revisions`: page ID, revision number, section JSON, author, creation time, publish time. A draft is a revision that is not the published revision.
- `media_assets`: file path, alt text, dimensions, owner, upload date. Reuse assets rather than uploading the same image repeatedly.
- Section JSON holds a stable section ID, an allowlisted type, versioned field data, and layout options. A server-side registry validates and renders each type through an Ayra Blade component.
- `site_blocks` remains useful for reusable snippets. Existing site settings remain global identity/configuration; footer menu items remain navigation data.
- A catch-all content route resolves only published page slugs and is registered after commerce/system routes. Reserved paths (`admin`, `products`, `cart`, `checkout`, `login`, `my-orders`, etc.) cannot be used as page slugs.

Publishing should validate all sections, save the revision and update the published pointer in one transaction. Public rendering reads only the published revision. Draft preview requires admin authentication and never changes the live page. Preview links can expire; they should not be indexed. Keep a prior revision for one-click rollback.

## Design guardrails

Ayra should offer curated layouts and design tokens instead of unrestricted HTML/CSS. Each section can choose options such as light/dark background, content width, and image alignment, but typography, spacing, buttons, and responsive behavior stay in the theme. This keeps pages coherent and mobile-ready. All rich text is sanitized; ordinary text is escaped. Uploaded images require type/size validation and meaningful alt text. Footer and CTA links use the same safe URL rules as the current footer menu.

The public page should be server rendered for SEO and speed. Interactive catalogue filters can stay Livewire, but the builder should not require Livewire for every static section. Cache published page output or section data with invalidation on publish; dynamic product prices should remain fresh.

## Delivery sequence

1. **Foundation:** page/revision models, reserved slug rules, section registry, public route, draft/preview/publish/rollback, and an admin page list.
2. **Homepage builder:** migrate the current hero, benefits, collections, and product discovery into section definitions without changing the public design. Add edit/reorder controls and mobile/desktop preview.
3. **Marketing pages:** add the editorial, banner, text, FAQ, and product/collection sections, plus media library and SEO fields.
4. **Navigation and refinement:** header menu management, reusable blocks, section duplication, templates, accessibility polish, and publish history.

The first usable release should complete steps 1 and 2 together. A page list alone would not let the owner build the storefront.

## Acceptance criteria

- The current homepage looks and behaves the same after migration.
- An admin can create a draft page, add/reorder sections, preview it privately, publish it, and roll back.
- A campaign product grid reflects current product visibility and sale/quantity pricing.
- A published page has a stable URL, title/description, image alt text, and correct mobile layout.
- Invalid links, unsupported section types, script content, and reserved slugs are rejected.
- Cart, checkout, and account journeys keep working after homepage and page routing changes.
