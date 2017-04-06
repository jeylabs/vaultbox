## Upgrade instructions

  1. Please backup your own `config/Vaultbox.php` before upgrading.
  
  1. Run commands:

      ```bash
      composer update Jeylabs/vaultbox
      php artisan vendor:publish --tag=Vaultbox_view --force
      php artisan vendor:publish --tag=Vaultbox_public --force
      php artisan vendor:publish --tag=Vaultbox_config --force
      ```
 
  1. Clear browser cache if page is broken after upgrading.

