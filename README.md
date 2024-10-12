
# Delivered.ws - Email Deliverability Reports for Laravel

## Overview

**Delivered.ws** is a PHP package designed for Laravel to help manage and monitor email deliverability using Mailgun. This package provides functionality to send emails, track email delivery, opens, and clicks, as well as handle webhooks to receive updates on the status of emails.

## Features

- Send emails with Mailgun's API
- Track email delivery, opens, and clicks
- Store email status for reporting
- Handle Mailgun webhooks for real-time status updates
- Generate email deliverability reports
- Download email reports as CSV files

## Installation

1. Install the package via composer:
   ```bash
   composer require deliveredws/delivered
   ```

2. Publish the package configuration:
   ```bash
   php artisan vendor:publish --provider="DeliveredWs\DeliveredServiceProvider"
   ```

3. Set your Mailgun credentials in your `.env` file:
   ```env
   MAILGUN_DOMAIN=your-mailgun-domain
   MAILGUN_SECRET=your-mailgun-api-key
   MAIL_FROM_ADDRESS=your-email@example.com
   MAIL_FROM_NAME="Your Name"
   ```

4. Run the migrations to create the necessary tables:
   ```bash
   php artisan migrate
   ```

5. Set up webhooks for tracking email events such as "delivered," "failed," "opened," "clicked," etc. by configuring Mailgun to point to your webhook routes.

## Usage

### Sending Emails

You can send an email using the `EmailController` in two ways:

#### Option 1: Via Artisan Command

To send a test email:
```bash
php artisan email:test <recipient@example.com> --name="Recipient Name" --subject="Welcome to Delivered!"
```

Example:
```bash
php artisan email:test eric.mugerwa@outlook.com --name="Eric Mugerwa" --subject="Welcome to Mailgun Sandbox!"
```

#### Option 2: Via API Endpoint

You can also send emails using the API endpoint. Make a `POST` request to `/api/send-email` with the following JSON payload:

```bash
curl -X POST http://localhost:8000/api/send-email \
-H "Content-Type: application/json" \
-H "Accept: application/json" \
-d '{
  "to": "eric.mugerwa@outlook.com",
  "name": "Eric Mugerwa",
  "subject": "Test Subject",
  "body": "Welcome to Delivered Staging."
}'
```

### Checking Email Status

To check the status of an email by recipient email address, make a `GET` request to the `email-status` endpoint:

```bash
http://127.0.0.1:8000/api/email-status?to=eric.mugerwa@outlook.com
```

This will return the current status of the email and any relevant error messages if the email failed to send.

### Webhook Testing

You can simulate real-time email event tracking by setting up webhooks for Mailgun. Here’s how you can test webhooks using Ngrok:

1. Run the Laravel development server:
   ```bash
   php artisan serve
   ```

2. Use Ngrok to expose your local environment:
   ```bash
   ngrok config add-authtoken <your-ngrok-token>
   ngrok http 8000
   ```

3. Use the Ngrok forwarding URL (e.g., `https://<subdomain>.ngrok-free.app`) to set up your Mailgun webhooks. This allows Mailgun to send event updates (delivered, opened, clicked, etc.) to your local environment.

### Available Endpoints

1. **Send Email:**
   ```bash
   POST /api/send-email
   ```

2. **Check Email Status:**
   ```bash
   GET /api/email-status?to=<recipient-email>
   ```

### Additional Commands

Clear Laravel cache for routes and configuration:

```bash
php artisan route:clear
php artisan config:clear
```

## Webhook Setup

To track email events such as delivered, failed, opened, and clicked, you need to configure Mailgun to send webhooks to your application.

1. Set up your webhook routes in the Mailgun dashboard for events like "delivered", "failed", "opened", and "clicked".

2. Point them to your application's Ngrok URL if testing locally:
    - Example: `https://<subdomain>.ngrok-free.app/webhook`

3. You can define your own webhook logic within the application to handle these events and update email statuses in your database.

## Downloading Email Reports

You can download email deliverability reports as a CSV by visiting your app’s dashboard and clicking the **Download** button. This will generate a CSV with the following fields:

- ID
- Message ID
- To
- To (Name)
- Subject
- From
- From (Name)
- Mailgun Domain
- Status
- Sent

## Conclusion

The **Delivered.ws** package makes it easy to send emails through Mailgun, track their delivery, and handle real-time email events. By following this guide, you'll be able to set up the package, send test emails, and track their deliverability for comprehensive email reporting.
