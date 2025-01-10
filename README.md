# Tides - Open Source Video Platform

[![Laravel](https://github.com/rrze-mmz/tides/actions/workflows/build.yml/badge.svg?branch=develop)](https://github.com/stefanosgeo/tides/actions/workflows/build.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Tides is an open-source video platform built on the Laravel framework. It offers a flexible and customizable solution
for managing and streaming videos, making it ideal for developers seeking a robust platform for video content delivery.

This README provides instructions for setting up the development environment and installing Tides on your local machine.

## Features and Components

Tides leverages modern technologies and components to deliver a seamless video management experience:

- **Tailwind CSS**: A utility-first CSS framework for creating responsive and modern user interfaces.
- **Vidstack Player**: A lightweight, customizable HTML5 video player with a sleek design and powerful features.

### Prerequisites

Ensure you have the following installed:

- **PHP 8.2+**
- **Composer**
- **SQLite** (or any other supported database)
- **Node.js & npm**

### Installation Steps

1. Clone the repository:

   ```
    git clone https://github.com/your-username/tides.git
    cd tides
      ```
2. Install PHP dependencies using Composer:
   ```
   composer install
      ```

3. Copy the example environment file and configure your environment:
   ```
   cp .env.example .env
      ```
4. Generate a new application key:
   ```
   php artisan key:generate
      ```

5. Create an SQLite database file (or configure your preferred database):
   ```
   touch /tmp/tides.sqlite
      ```
   Ensure your .env file is updated with the correct database connection settings:

   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=/tmp/tides.sqlite
      ```
6. Run database migrations:
   ```
   php artisan migrate
      ```
7. Install front-end dependencies
    ```
    npm install
    npm run dev
      ```
8. Start the development server
   ```
   php artisan serve
      ```

## Contributing

We welcome contributions from the community! If you would like to contribute, please fork the repository and submit a
pull request. For major changes, please open an issue first to discuss what you would like to change.

## License

Tides is open-source software licensed under the ECL 2.0 (Educational Community License). See the [LICENSE](LICENSE)
file for more details.

Thank you for your interest in Tides! If you encounter any issues or have any questions, feel free to reach out or
create a GitHub issue. We appreciate your support and contributions in making Tides a powerful video platform.

