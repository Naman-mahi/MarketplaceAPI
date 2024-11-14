# Marketplace-API

## Description
This project is a RESTful API for a marketplace application. It provides endpoints for managing products, users, rewards, referrals, coupons, advertisements, banners and dealer products.

## Features
- User authentication and authorization
- Product listing and management
- Coupon system
- Rewards and referral program
- Advertisement management
- Banner management
- Dealer product management
- Search and filtering capabilities

## Technologies Used
- PHP 8.2
- MySQL
- RESTful API architecture
- JSON for data exchange

## API Endpoints
BASE URL: http://localhost/KENZAPI/
live version: https://api.intencode.com/

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
- `GET BASE_URL/newcars`: Get new cars
- `GET BASE_URL/spareparts`: Get spare parts
- `GET BASE_URL/oldcars`: Get old cars
- `GET BASE_URL/featured-products`: Get featured products
- `GET BASE_URL/brands`: Get all brands
- `GET BASE_URL/city`: Get all cities

### Dealer Routes
- `GET BASE_URL/dealer-products`: Get dealer's products
- `GET BASE_URL/dealer-products-by-category/{id}`: Get dealer's products by category
- `GET BASE_URL/dealer-product-by-id/{id}`: Get dealer's product by ID
- `GET BASE_URL/dealer-connect`: Get all products for dealer marketplace

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
- `GET BASE_URL/advertisements`: Get all advertisements
- `GET BASE_URL/advertisementsbyid/{id}`: Get advertisement by ID

### Banner Routes
- `GET BASE_URL/banners`: Get all banners
