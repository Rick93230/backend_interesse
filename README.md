<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

### Passport
After creating the database for this project, run:

```bash
php artisan passport:install
```
This will create the client (client_id and secret) needed to obtain the token for API requests.

### Seeders
To create test users and roles, run the following commands:

```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Migrations
Once the previous steps are completed, run the migrations with the following command:

```bash
php artisan migrate
```

### Note
This project was built with Laravel 8 and PHP 8.3.