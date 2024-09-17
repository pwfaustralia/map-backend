## Initial Setup

1. Clone GitHub repo:
   ```bash
   git clone https://github.com/pwfaustralia/map-backend
   cd map-backend
   ```
   
2. Run docker compose and accessing laravel-app terminal:
   ```bash
   docker-compose up -d
   docker exec -it laravel-app /bin/bash
   ```
   
3. Set up the environment file:

   ```bash
   cp .env.example .env
   ```
> [!IMPORTANT]
> Replace variables with "xxx" values with correct ones.
   
4. Install Composer dependencies:
   ```bash
   composer install
   ```
   - If compose install gives you an error, try installing some PHP extensions by running:
      ```bash
      docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pcntl
      ```
   - Run `composer install` again.

5. Run the database migrations:

   ```bash
   php artisan migrate --force
   ```

6. Generate Passport keys:

   ```bash
   php artisan passport:keys
   ```

7. Retrieve and configure OAuth keys:

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

8.  Create a personal access client for Laravel Passport:

   ```bash
   php artisan passport:client --personal -n
   ```

   Copy the `Client ID` and `Client Secret`, and paste them into the `.env` file.

9.  Set correct ownership of the storage directory:

```bash
chown -R www-data:www-data ./storage
```

10. Seed the database:

```bash
php artisan db:seed
```

11. Running Supervisor processes:

```bash
supervisord -c /etc/supervisor/supervisord.conf
supervisorctl reread
supervisorctl update
supervisorctl start all
supervisorctl status
```
12. Importing Typesense collections:

- Client Model
  ```bash
  php artisan scout:import "App\Models\Client"
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
