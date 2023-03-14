# CLOTHES SHOP SERVER

This is a server for a clothes shop, built using PHP.

## Features

The server provides the following API endpoints:

-   `api/user/login` to authenticate a user and get an access token
-   `api/user/register` to register a new user
-   `api/users/{frame}` to get a list of all users (for admin users only)
-   Udating...

## Prerequisites

Before running the server, make sure you have the following software installed:

-   PHP 7.0 or later
-   MySQL server
-   Apache server (optional)

## Installation

To install and run the server, follow these steps:

1. Clone the repository to your local machine.
2. Create a new MySQL database and import the ClothesShop.sql (in config directory) file to create the necessary tables.
3. Modify configDB.php in folder config with your MySQL database credentials.
4. Start the server by running the command `php -S localhost:8000` in the server directory of the project.
5. You can now access the API endpoints by making requests to http://localhost:8000/.

## Authentication

Some API endpoints require authentication using an access token. To get an access token, you must first authenticate by sending a POST request to the `api/user/login` endpoint with your email and password. The server will respond with a JSON object containing the access token, which you must include in subsequent requests using the Authorization header with the value `Bearer <token>`.

## Author

-   Le Van Lam
-   Email: vanlam.cs76@gmail.com
-   GitHub: VanLamCS
