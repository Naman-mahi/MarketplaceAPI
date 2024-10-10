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

//in route give me full route includeing the base url

## API Endpoints
BASE URL: http://localhost/marketplace-api/
live version: https://marketplace-api-production.up.railway.app/

### User Routes
- `POST baseurl/register`: Register a new user
- `POST baseurl/login`: User login
- `GET baseurl/profile/{id}`: Get user profile
- `POST baseurl/update-profile/{id}`: Update user profile
- `POST baseurl/forgot-password`: Initiate forgot password process
- `POST baseurl/reset-password`: Reset user password
- `POST baseurl/fetch-otp`: Fetch OTP for verification
- `POST baseurl/verify-otp`: Verify OTP

### Product Routes
- `GET baseurl/products`: Get all products
- `GET baseurl/product/{id}`: Get product by ID
- `POST baseurl/products`: Create a new product
- `GET baseurl/brands`: Get all brands
- `GET baseurl/dealer-products/{id}`: Get products by dealer ID

### Coupon Routes
- `POST baseurl/coupons`: Create a new coupon
- `POST baseurl/apply-coupon`: Apply a coupon
- `GET baseurl/coupons`: Get all coupons
- `PUT baseurl/coupons/{id}`: Update a coupon
- `DELETE baseurl/coupons/{id}`: Delete a coupon

### Rewards Routes
- `POST baseurl/rewards`: Create a new reward
- `GET baseurl/rewards`: Get all rewards
- `PUT baseurl/rewards/{id}`: Update a reward
- `DELETE baseurl/rewards/{id}`: Delete a reward
- `GET baseurl/rewards/{id}`: Get rewards for a specific user

### Advertisement Routes
- `POST baseurl/advertisements`: Create a new advertisement
- `GET baseurl/advertisements`: Get all advertisements
- `PUT baseurl/advertisements/{id}`: Update an advertisement
- `DELETE baseurl/advertisements/{id}`: Delete an advertisement
- `GET baseurl/advertisements/{id}`: Get advertisements for a specific user






