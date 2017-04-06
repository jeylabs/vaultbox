## Requirements
 * php >= 5.4
 * Laravel 5
 * requires [intervention/image](https://github.com/Intervention/image) (to make thumbs, crop and resize images).

## Installation
1. Install package 

    ```bash
    composer require Jeylabs/vaultbox
    ```

1. Edit `config/app.php` :

    Add service providers

    ```php
    Jeylabs\Vaultbox\VaultboxServiceProvider::class,
    Intervention\Image\ImageServiceProvider::class,
    ```

    And add class aliases

    ```php
    'Image' => Intervention\Image\Facades\Image::class,
    ```

    Code above is for Laravel 5.1.
    In Laravel 5.0 should leave only quoted class names.

1. Publish the package's config and assets :

    ```bash
    php artisan vendor:publish --tag=Vaultbox_config
    php artisan vendor:publish --tag=Vaultbox_public
    ```
    
1. Ensure that the files & images directories (in `config/Vaultbox.php`) are writable by your web server(run commands like `chown` or `chmod`).
