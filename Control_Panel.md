# Ayra control panel: routes and call flow

This map reflects the current `main` branch. Check the live route registry with `php artisan route:list --except-vendor`. Routes in `routes/web.php` use Laravel's `web` middleware; Livewire actions run through the framework's `POST /livewire/update` endpoint after the initial page request.

## Storefront and customer routes

| Method and path | Route name | Entry point and function | View or result | Access |
| --- | --- | --- | --- | --- |
| `GET /` | `home` | `App\Livewire\StoreFront::render()`; `products()`, `categories()`, `colors()`, `sizes()`, `suggestions()`, and `recentlyViewed()` supply data | `livewire.store-front` | Public |
| `GET /dashboard` | `dashboard` | Laravel redirect route | Redirects to `/` | Public |
| `GET /products/{product}` | `product.show` | `App\Livewire\ShowProduct::mount()` and `render()` | `livewire.product` | Public; unpublished products return 404 |
| `GET /cart` | `cart.show` | `App\Livewire\ShowCart::render()` | `livewire.show-cart` | Public; `CartFactory::make()` finds the session or customer cart |
| `GET /checkout` | `checkout.form` | `App\Livewire\Checkout::mount()` and `render()` | `livewire.checkout` | Signed-in, verified customer |
| `GET /checkout/success` | `checkout.success` | Closure in `routes/web.php` calls `HandleCheckoutSessionCompleted::handle($sessionId, auth()->id())` when a valid Stripe session ID is supplied | `checkout.success` | Signed-in, verified customer |
| `GET /checkout/checkout/cancel` | `checkout.cancel` | Laravel view route | `checkout.cancel` | Public; retained for the configured Stripe cancel URL |
| `GET /checkout/failed` | `checkout.failed` | Laravel view route | `checkout.cancel` | Public |
| `GET /checkout-status` | `checkout.status` | `App\Livewire\CheckoutStatus::mount()` and `render()`; `order()` looks up the session's order for the current customer | `livewire.checkout-status` | Signed-in, verified customer |
| `GET /my-orders` | `my.orders` | `App\Livewire\Myorders::render()`; `orders()` reads the current customer's orders | `livewire.myorders` | Signed-in, verified customer |
| `GET /order/{orderId}` | `order.show` | `App\Livewire\ShowOrder::mount()` and `render()`; `order()` loads through the current customer | `livewire.show-order` | Signed-in, verified customer; cannot read another customer's order |

The customer checkout group uses `auth:sanctum`, Jetstream's authenticated-session middleware, and `verified`. Public product browsing and the bag do not require login.

### Livewire interactions

| Page | User action | Function and next step |
| --- | --- | --- |
| Storefront | Search, filter, sort, clear filters | `StoreFront::updated()`, `setCategory()`, `clearFilters()`, then `products()` reruns the catalogue query |
| Product | Choose a color | `ShowProduct::selectColor()` updates available sizes and color-tagged `galleryImages()`, then dispatches `product-color-changed` to swap the main image |
| Product | Add to bag | `ShowProduct::addToCart()` validates the selected variant and calls `AddProductVariantToCart::add()` |
| Bag | Change quantity, remove, add recommendation | `ShowCart::increment()`, `decrement()`, `deleteItem()`, or `addRecommendation()`; the last calls `AddProductVariantToCart::add()` |
| Bag | Checkout | `ShowCart::checkout()` redirects to `checkout.form` |
| Checkout | Submit card payment | `Checkout::submit()` calls `CreateStripeCheckoutSession::createFromCart()` and redirects to Stripe Checkout |
| Checkout | Submit cash on delivery | `Checkout::submit()` calls `CreateCashOnDeliveryOrder::handle()`, creates the order, clears the bag, and redirects to `order.show` |

The `Product::imageForColor()` model method supplies the matching image in the bag and checkout summary. The `images.color` column associates photos with variant colors; untagged photos are shared fallback images.

## Admin routes

Admin authentication uses the separate `admins` table, `App\Models\Admin`, and the `admin` session guard in `config/auth.php`. `EnsureAdminAuthenticated` protects the product routes and redirects guests to `/admin/login`. Customer sign-in alone does not grant admin access.

| Method and path | Route name | Function | View or result |
| --- | --- | --- | --- |
| `GET /admin/login` | `admin.login` | `Admin\AuthController::showLogin()` | `admin.login` |
| `POST /admin/login` | `admin.login.submit` | `Admin\AuthController::login()` | Authenticates with the `admin` guard and redirects to the intended admin page; limited to 5 attempts per minute |
| `GET /admin` | `admin.dashboard` | Protected Laravel redirect route | Redirects to `/admin/products` |
| `POST /admin/logout` | `admin.logout` | `Admin\AuthController::logout()` | Signs out the admin session and redirects to admin login |
| `GET /admin/products` | `admin.products.index` | `Admin\ProductController::index()` | `admin.products.index`; searchable, paginated list |
| `GET /admin/products/create` | `admin.products.create` | `Admin\ProductController::create()` | `admin.products.form` in create mode |
| `POST /admin/products` | `admin.products.store` | `Admin\ProductController::store()` → private `save()` | Validates, creates product/variants/images, redirects to edit |
| `GET /admin/products/{product}/edit` | `admin.products.edit` | `Admin\ProductController::edit()` | `admin.products.form` in edit mode |
| `PUT /admin/products/{product}` | `admin.products.update` | `Admin\ProductController::update()` → private `save()` | Updates name, description, category, price, publication, variants, and images |

AdminLTE styling is isolated in `layouts.admin`, `resources/css/admin.css`, and `resources/js/admin.js`. Product photos are stored on the `public` filesystem disk and served through `public/storage`. The editor refuses to remove a variant already referenced by a bag or order.

## Framework routes and events

| Endpoint or event | Handler | Purpose |
| --- | --- | --- |
| `POST /livewire/update` | Livewire `HandleRequests::handleUpdate()` | Runs the Livewire methods listed above |
| `POST /livewire/upload-file`, `GET /livewire/preview-file/{filename}` | Livewire upload and preview controllers | Temporary file handling for Livewire components |
| `POST /stripe/webhook` | Laravel Cashier `WebhookController::handleWebhook()` | Emits `WebhookReceived`; discovered `StripeEventListener::handle()` calls `HandleCheckoutSessionCompleted::handle()` for successful Checkout events |
| `GET /stripe/payment/{id}` | Cashier `PaymentController::show()` | Cashier payment page |
| `GET /api/user` | Closure in `routes/api.php` | Returns the authenticated Sanctum user |
| `GET /sanctum/csrf-cookie` | Sanctum `CsrfCookieController::show()` | SPA CSRF cookie endpoint |
| `GET /up` | Laravel health route | Application health check |

`routes/console.php` schedules `app:abandoned-cart` daily at 13:00 and `app:remove-inactive-session-carts` weekly. Stripe success return and webhook handling both use `HandleCheckoutSessionCompleted`; it checks payment status and session ownership, creates the order once, and clears the matching bag.

### Customer account routes supplied by Fortify and Jetstream

| Method and path | Package controller method | Purpose |
| --- | --- | --- |
| `GET /login`, `POST /login`, `POST /logout` | Fortify `AuthenticatedSessionController::create()`, `store()`, `destroy()` | Customer login and logout; `AppServiceProvider` configures credential checking and session-cart migration |
| `GET /register`, `POST /register` | Fortify `RegisteredUserController::create()`, `store()` | Customer registration using `Actions/Fortify/CreateNewUser` |
| `GET /forgot-password`, `POST /forgot-password` | Fortify `PasswordResetLinkController::create()`, `store()` | Request a password reset |
| `GET /reset-password/{token}`, `POST /reset-password` | Fortify `NewPasswordController::create()`, `store()` | Reset a customer password using `Actions/Fortify/ResetUserPassword` |
| `GET /user/profile` | Jetstream `UserProfileController::show()` | Customer profile page |
| `PUT /user/profile-information`, `PUT /user/password` | Fortify `ProfileInformationController::update()`, `PasswordController::update()` | Customer profile and password updates |
| `GET/POST /user/confirm-password`, `GET /user/confirmed-password-status` | Fortify password confirmation controllers | Confirm a customer's current password for sensitive changes |
| `GET/POST /two-factor-challenge` | Fortify `TwoFactorAuthenticatedSessionController::create()`, `store()` | Two-factor login challenge |
| `POST/DELETE /user/two-factor-authentication`, `POST /user/confirmed-two-factor-authentication` | Fortify two-factor authentication controllers | Enable, disable, and confirm two-factor authentication |
| `GET /user/two-factor-qr-code`, `GET/POST /user/two-factor-recovery-codes`, `GET /user/two-factor-secret-key` | Fortify QR, recovery-code, and secret-key controllers | Two-factor setup and recovery |

Fortify, Jetstream, Livewire, Sanctum, and Cashier register these package routes. Use `php artisan route:list` for exact route names, middleware, and any routes added by future package upgrades.

## Main files and data

- Routes: `routes/web.php`, `routes/api.php`, `routes/console.php`.
- Admin controllers: `app/Http/Controllers/Admin/AuthController.php`, `ProductController.php`.
- Storefront Livewire components: `app/Livewire/StoreFront.php`, `ShowProduct.php`, `ShowCart.php`, `Checkout.php`, `CheckoutStatus.php`, `Myorders.php`, `ShowOrder.php`.
- Webshop actions: `app/Actions/Webshop/` for cart updates, COD orders, Stripe Checkout, Stripe reconciliation, and session-cart migration.
- Models: `Product`, `ProductVariant`, `Image`, `Cart`, `CartItem`, `Order`, `OrderItem`, `User`, and `Admin` in `app/Models/`.
- Core tables: `products`, `product_variants`, `images`, `carts`, `cart_items`, `orders`, `order_items`, `users`, and `admins`. Migrations are in `database/migrations/`.
- Demo catalogue: `database/seeders/AyraDemoCatalogSeeder.php`.

For installation and scaffolding commands, see `cmd.md`.
