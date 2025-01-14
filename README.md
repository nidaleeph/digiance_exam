# Project Setup Instructions

## Install npm Dependencies

To install the necessary npm dependencies, run the following command:

```bash
npm install
```

## Install Composer Dependencies

To install the Composer dependencies, execute:

```bash
composer install
```

## Create Your Environment File

1. Copy the `.env.example` file and create your own `.env` file:
    ```bash
    cp .env.example .env
    ```
2. Edit the `.env` file to configure your database connection and add the following keys:
    ```env
    STRIPE_KEY=your_stripe_key
    STRIPE_SECRET=your_stripe_secret
    VITE_STRIPE_PUBLIC_KEY=your_public_stripe_key
    ```

## Set the Application Key

To set the encryption key for your application, run:

```bash
php artisan key:generate --ansi
```

Then refresh config

```bash
php artisan config:clear
```

```bash
php artisan config:cache
```

## Populate the Database with Seeders

To migrate the database and seed initial data, use:

```bash
php artisan migrate:fresh --seed
```

## Serve the Application Locally

1. Start the backend server:
    ```bash
    php artisan serve
    ```
2. Start the frontend development server:
    ```bash
    npm run dev
    ```

Your application should now be running locally and accessible via the provided URLs.
