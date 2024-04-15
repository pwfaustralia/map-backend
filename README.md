# PWF Australia MAP API

This project provides a Dockerized setup for Laravel development, equipped with PHP 8.2, Apache, and MySQL.

## Getting Started

Follow these steps to set up your development environment:

1. Clone this repository to your local machine:
   ```
   git clone https://github.com/pwfaustralia/map-backend
   ```
2. Navigate to the project directory:

   ```
   cd map-backend
   ```

3. Start the Docker containers:

   ```
   docker-compose up -d
   ```

4. Run `composer install` to install Laravel's dependencies.

5. copy the generated keys in laravel/app/secrets/oauth to .env.

6. Create passport keys:

   ```
   php artisan passport:keys
   ```

7. Copy the generated keys in laravel/app/secrets/oauth to .env.

8. Run migration and seeders:

   ```
   php artisan migrate:fresh --seed
   ```

9. Create personal access client:

   ```
   php artisan passport:client --personal
   ```

10. Copy the client id and client secrets to .env.

11. After the installation is complete, exit the container's terminal:
    ```
    exit
    ```

Now, your Laravel project should be up and running on port 8080. You can access it in your web browser at `http://localhost:8080`.

## Features

- Laravel Framework: Utilize the power of Laravel to build modern web applications.
- PHP 8.2: Benefit from the latest PHP features and improvements.
- Apache Web Server: Host your Laravel project with the Apache web server.
- MySQL Database: Manage your application's data with MySQL.
- Dockerized Environment: Ensure consistency and easy setup across different systems.
- Database Seeding and Migrations: Simplify database management during development.
- Sample Application: Start with a sample Laravel application or build from scratch.
- Composer Dependencies: Easily add and manage Composer packages for your Laravel app.

## Contributing

We welcome contributions! If you have suggestions, bug fixes, or new features to add, please submit a pull request or open an issue on this repository.

## License

This project is open-source and licensed under the GNU General Public License (GNU GPL). Feel free to use, modify, and distribute it in accordance with the terms of the GNU GPL.

Happy coding with Laravel, PHP 8.2, Apache, and MySQL!
