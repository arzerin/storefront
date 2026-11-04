# Ayra commands

Run commands from the repository root. The first section is for installing and operating this existing project. The scaffolding section shows the Artisan commands that create the corresponding file types; those files already exist, so do not rerun the generators unless you are adding new code.

## Install and run the existing project

```bash
composer install
npm ci
cp .env.example .env                 # only on a new checkout; keep an existing .env
php artisan key:generate             # only when APP_KEY is empty
php artisan migrate
php artisan storage:link             # creates public/storage if absent
php artisan db:seed --class=AyraDemoCatalogSeeder
npm run build
```

Set `APP_URL` and database settings in `.env` for the target machine. For the default SQLite configuration, create the file first with `touch database/database.sqlite` if it does not exist. Configure mail and Stripe in `.env`; never commit credentials. Laravel Cashier reads `STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_WEBHOOK_SECRET`. This app also reads `STRIPE_SUCCESS_URL`, `STRIPE_CANCEL_URL`, and `STRIPE_SHIPPING_RATE_ID` from `config/services.php`. The success/cancel URLs should point to the routes in `Control_Panel.md` for the deployed host.

For local development without Herd:

```bash
php artisan serve
npm run dev
php artisan queue:work              # separate terminal, when queue jobs are needed
php artisan schedule:work           # separate terminal, when scheduled jobs are needed
```

With Herd, serve the repository through its configured local hostname and run `npm run dev` for asset changes. Use `npm run build` for production assets. `DatabaseSeeder` creates unrelated sample records; use `AyraDemoCatalogSeeder` for the Ayra demo catalogue. It calls `AyraExpandedCatalogSeeder` for additional products, `AyraCollectionsSeeder` for curated collections, and `AyraManufacturersSeeder` for sample manufacturer assignments; rerunning the command is safe.

## Existing PHP packages and AdminLTE assets

`composer install` installs the versions locked in `composer.lock`, including Laravel 11, Jetstream, Livewire, Cashier, and MoneyPHP. `npm ci` installs the locked frontend packages in `package-lock.json`.

If creating a new project with the same dependencies, these are the package commands to use before writing the application code:

```bash
composer require laravel/jetstream livewire/livewire laravel/cashier moneyphp/money
npm install admin-lte@4.10.0 bootstrap@^5.3.8 bootstrap-icons@^1.13.2
```

AdminLTE is loaded through `resources/css/admin.css`, `resources/js/admin.js`, and the Vite entries in `vite.config.js`. The customer storefront uses its separate Ayra styles.

## Artisan scaffolding equivalents

These examples create the kinds of controllers, models, migrations, middleware, Livewire components, views, actions, listeners, and seeders used here. Edit the generated files afterward to add the behavior described in `Control_Panel.md`.

```bash
# Admin controllers, middleware, and separate admin model/table
php artisan make:controller Admin/AuthController
php artisan make:controller Admin/ProductController
php artisan make:middleware EnsureAdminAuthenticated
php artisan make:model Admin -m

# Storefront and order models with migration stubs
php artisan make:model Product -m
php artisan make:model ProductVariant -m
php artisan make:model Image -m
php artisan make:model Cart -m
php artisan make:model CartItem -m
php artisan make:model Order -m
php artisan make:model OrderItem -m

# Later schema changes
php artisan make:migration add_storefront_fields_to_products --table=products
php artisan make:migration add_color_to_images --table=images
php artisan make:migration add_ayra_checkout_fields_to_orders --table=orders

# Livewire page components (each also gets a Blade view)
php artisan livewire:make StoreFront
php artisan livewire:make ShowProduct
php artisan livewire:make ShowCart
php artisan livewire:make Checkout
php artisan livewire:make CheckoutStatus
php artisan livewire:make Myorders
php artisan livewire:make ShowOrder
php artisan livewire:make NavigationCart

# Webshop action/service classes
php artisan make:class Actions/Webshop/AddProductVariantToCart
php artisan make:class Actions/Webshop/CreateCashOnDeliveryOrder
php artisan make:class Actions/Webshop/CreateStripeCheckoutSession
php artisan make:class Actions/Webshop/HandleCheckoutSessionCompleted
php artisan make:class Actions/Webshop/MigrateSessionCart

# Listener, seeder, console jobs, and non-Livewire views
php artisan make:listener StripeEventListener
php artisan make:seeder AyraDemoCatalogSeeder
php artisan make:seeder AyraExpandedCatalogSeeder
php artisan make:seeder AyraCollectionsSeeder
php artisan make:seeder AyraManufacturersSeeder
php artisan make:model Category -m
php artisan make:model Collection -m
php artisan make:model QuantityDiscount -m
php artisan make:model Manufacturer -m
php artisan make:controller Admin/CategoryController
php artisan make:controller Admin/CollectionController
php artisan make:controller Admin/ManufacturerController
php artisan make:command AbandonedCart
php artisan make:command RemoveInactiveSessionCarts
php artisan make:view layouts.admin
php artisan make:view admin.login
php artisan make:view admin.products.index
php artisan make:view admin.products.form
php artisan make:view admin.categories.index
php artisan make:view admin.categories.edit
php artisan make:view admin.collections.index
php artisan make:view admin.collections.form
php artisan make:view admin.manufacturers.index
php artisan make:view admin.manufacturers.form
php artisan make:view checkout.success
php artisan make:view checkout.cancel
```

The `App\Actions\Webshop` classes are this project's service layer; there is no separate `app/Services` directory. The actual code also defines model relationships, route declarations, validation, authentication guards, image uploads, and the Stripe listener registration through Laravel's event discovery. Generators create stubs only.

## Admin account provisioning

The current local `admins` table already has the requested admin account, with a hashed password. Fresh databases need a separate admin record after migration. For one-off provisioning, run `php artisan tinker`, then enter:

```php
$password = \Laravel\Prompts\password('New admin password');
\App\Models\Admin::updateOrCreate(
    ['email' => 'a.r.zerin@gmail.com'],
    ['name' => 'A. R. Zerin', 'password' => $password]
);
unset($password);
```

The `Admin` model hashes assigned passwords. The prompt keeps the password out of shell history and seeders; change temporary credentials before deployment. Customer records in `users` are separate from admin records in `admins`.

## Inspect and verify

```bash
php artisan route:list --except-vendor
php artisan route:list --path=admin
php artisan event:list
php artisan schedule:list
php artisan migrate:status
php artisan test
npm run build
php artisan view:cache
php artisan optimize:clear              # when cached configuration/routes need a refresh
```

`php artisan migrate:fresh` drops all tables and data; use it only on a disposable test database. Use `php artisan migrate` for a database containing real products, customers, or orders.


## Site settings, blocks and footer menu

```bash
# Generate the site content building blocks (already included in this repository)
php artisan make:migration create_site_content_tables
php artisan make:model SiteSetting
php artisan make:model SiteBlock
php artisan make:model FooterMenuItem
php artisan make:controller Admin/SiteSettingsController
php artisan make:controller Admin/SiteBlockController
php artisan make:controller Admin/FooterMenuController
php artisan make:seeder AyraSiteContentSeeder

# Apply schema and load default Ayra content on an existing install
php artisan migrate
php artisan db:seed --class=AyraSiteContentSeeder
php artisan storage:link

# Verify
php artisan test tests/Feature/SiteContentManagementTest.php
```

The Blade views were added under `resources/views/admin`, `resources/views/components` and the existing storefront/layout directories; Laravel has no built-in `make:view` command. The seeder uses `firstOrCreate`, so rerunning it keeps content edited in the admin.
