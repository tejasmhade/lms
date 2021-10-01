# LMS
Library Management System

# Requirements
Create APIs for library management system using Laravel and Mysql:(User and Books module)

1. Create APIs for Users module (register, login, profile with CRUD operations) with all possible validations using ACID Properties.
2. Create APIs for Book management (CRUD Operations) using ACID Properties.
3. Create the migration files for all the tables.
4. Create API for users renting the book and return the book
5. Add JWT authentication for all routes except login and register using middleware.
6. Create an API to display user-wise rented book records.
7. Upload the task and APIs postman on git.

Please refer the below Schema for database creation

users:
- u_id PRIMARY KEY AUTO INCREMENT
- firstname string(255) numbers not allowed
- lastname string(255) numbers not allowed
- mobile integer(10) unique
- email string(255) unique
- age tinyinteger(3)
- gender enum(m,f,o)
- city string(255)

books:
- b_id PRIMARY KEY AUTO INCREMENT
- book_name string(255) unique
- author string(255)
- cover_image string(255)

books_rented:
- id PRIMARY KEY AUTO INCREMENT
- u_id (primary key of users table)
- b_id (primary key of book table)
- issued_on timestamp
- returned_on timestamp

# System Requirements
1. PHP 7.3 or above
2. Laravel 8
3. Mysql 5.7 or above

# How to run project
1. Clone/Download the project.
2. Update .env file and setup database configuration.
3. Composer Install and generate application key.
4. Configure JWT package by running below commands
- php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
- php artisan jwt:secret
5. Run Migrations

# Postman Collection for APIs
https://www.getpostman.com/collections/3e0c5ba42196afe5fa27

