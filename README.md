# Larablog

Larablog is a full-stack blogging platform built with Laravel as the backend API, Next.js for the user-facing frontend, and React for the admin panel. It provides a complete content management system with role-based access control (admin, editor, author), user authentication, a beautiful interactive dashboard, and real-time features powered by Laravel Reverb.

This document provides the necessary instructions to set up and run the project locally.

## Prerequisites

Before proceeding, ensure the following tools are installed on your system:

- PHP (with Composer)
- Node.js and npm
- MySQL or another supported database
- Git

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Shahrokh1383/larablog.git
cd larablog
```

The repository contains three main directories:

- `back-end` – Laravel backend API
- `User` – Next.js user frontend
- `Admin` – React admin panel

---

## Backend Setup (Laravel API)

Navigate to the API directory and install PHP dependencies:

```bash
cd back-end
composer install
```

### Install Broadcasting (if not already installed)

If Laravel Reverb is not installed, run the following command to set up broadcasting:

```bash
php artisan install:broadcasting
```

### Environment Configuration

Copy the contents of `.env.example` into a new `.env` file:

```bash
cp .env.example .env
```

Then fill in the following values in the `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="LaraBlog@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ADMIN_ADDRESS="LaraBlog@gmail.com"
SMTP_SINK_API_URL=http://127.0.0.1:5000/api

GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
GITHUB_REDIRECT_URI=http://localhost:8000/api/oauth/github/callback

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/api/oauth/google/callback

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=127.0.0.1
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=6001
REVERB_PORT=6001
REVERB_SCHEME=http
```

Fill in the OAuth credentials (GitHub and Google) and Reverb keys as needed. You can generate random strings for the Reverb keys.

### Generate Application Key

Run the following command to generate the application key:

```bash
php artisan key:generate
```

### Create Storage Symlink

Run the following command to create a symbolic link for storage:

```bash
php artisan storage:link
```

### Run Migrations

Run database migrations:

```bash
php artisan migrate
```

### Optional: Seed Database with Fake Data

To populate the database with sample data, run:

```bash
php artisan migrate:fresh --seed
```

If you want to exclude certain seeders, open `database/seeders/DatabaseSeeder.php` and comment out the sections you do not wish to run before executing the command.

### Start the Backend Services

You need to run several processes in separate terminal windows:

1. Start the Laravel development server:

   ```bash
   php artisan serve
   ```

2. Start the queue worker:

   ```bash
   php artisan queue:work
   ```

3. Start Reverb (WebSocket server):

   ```bash
   php artisan reverb:start --debug
   ```

4. Start the Laravel scheduler:

   ```bash
   php artisan schedule:work
   ```

Keep all four terminals running while developing.

---

## User Frontend Setup (Next.js)

Open a new terminal and navigate to the `User` directory:

```bash
cd ../User
npm install
```

### Environment Configuration

Copy the contents of `.env.example.local` into a new `.env.local` file:

```bash
cp .env.example.local .env.local
```

Fill in the following values:

```env
NEXT_PUBLIC_OAUTH_API_BASE_URL=http://localhost:8000/api
NEXT_PUBLIC_REVERB_APP_KEY=from .env back-end
NEXT_PUBLIC_REVERB_HOST=127.0.0.1
NEXT_PUBLIC_REVERB_PORT=6001
```

The `NEXT_PUBLIC_REVERB_APP_KEY` should match the `REVERB_APP_KEY` you set in the Laravel `.env` file.

### Copy TypeScript Environment Declaration

Copy the contents of `next-env.d.ts.example` into `next-env.d.ts`:

```bash
cp next-env.d.ts.example next-env.d.ts
```

### Run the Development Server

```bash
npm run dev
```

The frontend will be available at `http://localhost:3000`.

---

## Admin Panel Setup (React)

Navigate to the `Admin` directory:

```bash
cd ../Admin
npm install
npm run dev
```

The admin panel will run on a separate port (usually `http://localhost:5173`). Check the terminal output for the exact address.

---

## Email Configuration

The project uses SMTP for sending emails. You have two options:

### Option 1: Companion SMTP Server

The project is designed to work with a companion SMTP server that captures emails and exposes them via a simple API. Clone and run the companion project from:

[https://github.com/Shahrokh1383/smtp-server](https://github.com/Shahrokh1383/smtp-server)

Follow the instructions in that repository to set it up. Once running, ensure the Laravel `.env` values for `MAIL_*` and `SMTP_SINK_API_URL` point to the correct address (defaults are `127.0.0.1:1025` for SMTP and `127.0.0.1:5000/api` for the sink API).

### Option 2: Real SMTP Service

You can use any real SMTP service (e.g., Gmail, Mailgun, SendGrid). Update the `MAIL_*` values in the Laravel `.env` file with the correct credentials and host. Remove or adjust the `SMTP_SINK_API_URL` if not needed.

---

## Running the Full Stack

To run the entire application locally, you need to have the following processes active in separate terminals:

1. Laravel API server (`php artisan serve`)
2. Laravel queue worker (`php artisan queue:work`)
3. Laravel Reverb server (`php artisan reverb:start --debug`)
4. Laravel scheduler (`php artisan schedule:work`)
5. Next.js user frontend (`npm run dev` inside `User`)
6. React admin panel (`npm run dev` inside `Admin`)
7. Optional: Companion SMTP server (if using the provided smtp-server)

Make sure all services are running and configured correctly.

## Additional Notes

- The project uses OAuth for social login; ensure your GitHub and Google OAuth credentials are correctly set and the redirect URIs match the ones in the Laravel `.env`.
- For real-time features (like notifications or live updates), Reverb must be running and the frontend must have the correct Reverb key and host.
- Seeding the database is optional but recommended for testing the UI with sample content.

---

## Contributing

If you wish to contribute to this project, you must first read the full project constitution document located at [constitution.md](doc/constitution.md). This document contains the guidelines, coding standards, and contribution workflow that all contributors are expected to follow.