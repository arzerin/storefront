https://github.com/isimmons/lw-webshop/

composer config platform.php 8.2.0
rm -rf vendor composer.lock
composer install

composer.json should contain this:
"config": {
  "platform": {
    "php": "8.2.0"
  }
}

composer remove pestphp/pest pestphp/pest-plugin-laravel

composer require pestphp/pest:^3 pestphp/pest-plugin-laravel:^3 --dev
rm -rf vendor composer.lock
composer install

Using version ^3.8 for pestphp/pest
Using version ^3.2 for pestphp/pest-plugin-laravel

composer require laravel/jetstream
php artisan jetstream:install livewire
php artisan migrate

php artisan make:model Product -m -f
php artisan make:model ProductVariant -m -f
php artisan make:model Cart -m -f
php artisan make:model CartItem -m -f
php artisan make:model Image -m -f
php artisan make:model Order
php artisan make:model OrderItem

 php artisan migrate:fresh --seed