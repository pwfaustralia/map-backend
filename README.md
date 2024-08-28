## Initial Setup

1. Clone GitHub repo:
   ```bash
   git clone https://github.com/pwfaustralia/map-backend
   cd map-backend
   ```
2. Install Composer dependencies:

   ```bash
   docker exec laravel-app composer install
   ```

3. Run the database migrations:

   ```bash
   docker exec laravel-app php artisan migrate --force
   ```

4. Generate Passport keys:

   ```bash
   docker exec laravel-app php artisan passport:keys
   ```

5. Set up the environment file:

   ```bash
   cd laravel
   cp .env.example .env
   ```

6. Retrieve and configure OAuth keys:

   - View the private key:

     ```bash
     cat app/secrets/oauth/oauth-private.key
     ```

     Copy the entire private key and paste it into the `.env` file.

   - View the public key:
     ```bash
     cat app/secrets/oauth/oauth-public.key
     ```
     Copy the entire public key and paste it into the `.env` file.

7. Re-run the database migrations:

   ```bash
   docker exec laravel-app php artisan migrate
   ```

8. Create a personal access client for Laravel Passport:

   ```bash
   docker exec laravel-app php artisan passport:client --personal
   ```

   Copy the `Client ID` and `Client Secret`, and paste them into the `.env` file.

9. Set correct ownership of the storage directory:

```bash
docker exec laravel-app chown -R www-data:www-data ./storage
```

11. Seed the database:

```bash
docker exec laravel-app php artisan db:seed
```

12. Importing Typesense collections:

- Client Model
  ```bash
  docker exec laravel-app php artisan scout:import "App\Models\Client"
  ```

## Known Issues

[Typesense `scout:import` issue](https://github.com/laravel/scout/issues/822)

## Features

- Laravel Framework: Utilize the power of Laravel to build modern web applications.

- PHP 8.2: Benefit from the latest PHP features and improvements.

- Apache Web Server: Host your Laravel project with the Apache web server.

- MySQL Database: Manage your application's data with MySQL.
- Typesense: Fast, typo-tolerant search engine optimized for instant search-as-you-type experiences and ease of use.

- Dockerized Environment: Ensure consistency and easy setup across different systems.

- Database Seeding and Migrations: Simplify database management during development.

- Sample Application: Start with a sample Laravel application or build from scratch.

- Composer Dependencies: Easily add and manage Composer packages for your Laravel app.
