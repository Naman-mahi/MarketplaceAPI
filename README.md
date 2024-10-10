# Marketplace-API

## Description
This project is a RESTful API for a marketplace application. It provides endpoints for managing products, users, rewards, referrals, coupons, and advertisements.

## Features
- User authentication and authorization
- Product listing and management
- Coupon system
- Rewards and referral program
- Advertisement management
- Search and filtering capabilities

## Technologies Used
- PHP 8.2
- MySQL
- RESTful API architecture
- JSON for data exchange


## API Endpoints
BASE URL: http://localhost/marketplace-api/
live version: https://marketplace-api-production.up.railway.app/

### User Routes
- `POST BASE_URL/register`: Register a new user
- `POST BASE_URL/login`: User login
- `GET BASE_URL/profile/{id}`: Get user profile
- `POST BASE_URL/update-profile/{id}`: Update user profile
- `POST BASE_URL/forgot-password`: Initiate forgot password process
- `POST BASE_URL/reset-password`: Reset user password
- `POST BASE_URL/fetch-otp`: Fetch OTP for verification
- `POST BASE_URL/verify-otp`: Verify OTP

### Product Routes
- `GET BASE_URL/products`: Get all products
- `GET BASE_URL/product/{id}`: Get product by ID
- `POST BASE_URL/products`: Create a new product
- `GET BASE_URL/brands`: Get all brands
- `GET BASE_URL/dealer-products/{id}`: Get products by dealer ID

### Coupon Routes
- `POST BASE_URL/coupons`: Create a new coupon
- `POST BASE_URL/apply-coupon`: Apply a coupon
- `GET BASE_URL/coupons`: Get all coupons
- `PUT BASE_URL/coupons/{id}`: Update a coupon
- `DELETE BASE_URL/coupons/{id}`: Delete a coupon

### Rewards Routes
- `POST BASE_URL/rewards`: Create a new reward
- `GET BASE_URL/rewards`: Get all rewards
- `PUT BASE_URL/rewards/{id}`: Update a reward
- `DELETE BASE_URL/rewards/{id}`: Delete a reward
- `GET BASE_URL/rewards/{id}`: Get rewards for a specific user

### Advertisement Routes
- `POST BASE_URL/advertisements`: Create a new advertisement
- `GET BASE_URL/advertisements`: Get all advertisements
- `PUT BASE_URL/advertisements/{id}`: Update an advertisement
- `DELETE BASE_URL/advertisements/{id}`: Delete an advertisement
- `GET BASE_URL/advertisements/{id}`: Get advertisements for a specific user






