# Billing System

## API Documentation
https://documenter.getpostman.com/view/4011331/TVzXAERD  

## Paystack
Create a paystack account and in the settings -> API Keys and Webhook tab, add the callback URL
```
{base url}/api/paystack/callback
```

## Installation and Running the Application

Ensure that you have PHP and composer installed in your computer

a. Clone this repository

```
git clone https://github.com/EphraimDev/billing-system.git
```

b. Install the project dependencies

```
composer install
```

c. Create and copy .env.example file to .env file
```
copy .env.example .env
```

d. Add your database config to your .env  

e. Add your paystack public and secret key to the .env  

f. Generate your app key
```
php artisan key:generate
```

g. Run migrations
```
php artisan migrate
```

h. Start the application
```
php artisan serve
```

## Endpoints to test

Method        | Endpoint      | Enable a user to: |
------------- | ------------- | ---------------
POST  | api/auth/signup  | Create user account  |
POST  | api/auth/login  | Login user  |
POST  | api/loan/create  | Create new loan  |
POST  | api/paystack/initialize  | Verify user's credit or debit card by debiting minimum of N50  |