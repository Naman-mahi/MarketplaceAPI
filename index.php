<?php
// Load configuration and dependencies
include __DIR__ . '/config/config.php';
include __DIR__ . '/config/database.php';
include __DIR__ . '/controllers/UserController.php';
include __DIR__ . '/controllers/ProductController.php';
include __DIR__ . '/controllers/CouponController.php';
include __DIR__ . '/controllers/RewardsController.php';
include __DIR__ . '/controllers/AdvertisementsController.php';
include __DIR__ . '/controllers/BannerController.php';
include __DIR__ . '/controllers/CommanController.php';
include __DIR__ . '/controllers/DealerController.php';

// Function to retrieve the API key from request headers or query string
function getApiKey()
{
    // Check for API key in Authorization header
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        // Extract the API key from 'Authorization: Bearer <key>'
        preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
        return $matches[1] ?? null;
    }

    // If API key is sent as a query parameter
    if (isset($_GET['api_key'])) {
        return $_GET['api_key'];
    }

    // If no API key found
    return null;
}

// Function to validate the API key against the file
function isValidApiKey($apiKey)
{
    // Path to the file where API keys are stored
    $apiKeysFile = __DIR__ . '/config/api_keys.txt';

    // Check if the file exists
    if (!file_exists($apiKeysFile)) {
        return false;
    }

    // Read the contents of the API keys file
    $apiKeys = file($apiKeysFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check if the provided API key exists in the file
    return in_array($apiKey, $apiKeys);
}

// Get the API key from the request
$apiKey = getApiKey();

// Validate the API key
if ($apiKey === null || !isValidApiKey($apiKey)) {
    // Invalid API key - respond with an error
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['statuscode' => 401, 'status' => 'error', 'message' => 'Unauthorized: Invalid API key']);
    exit();
}


// Instantiate controllers
$userController = new UserController();
$productController = new ProductController();
$couponController = new CouponController();
$rewardsController = new RewardsController();
$advertisementsController = new AdvertisementsController();
$bannerController = new BannerController();
$commanController = new CommanController();
$dealerController = new DealerController();
// Set response headers
$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'http://192.168.1.2',  // Local network IP or any other specific allowed domain
    'http://example.com',  // Example of an external allowed domain
    'http://another-domain.com'  // Another allowed domain
];
header('Content-Type: application/json');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // If the origin is not in the allowed list, either set to '*' or deny access
    header("Access-Control-Allow-Origin: *"); // You can use '*' to allow any origin if you want
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse request method and URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = explode('/', trim(str_replace(parse_url(BASE_URL, PHP_URL_PATH), '', $_SERVER['REQUEST_URI']), '/'));

// User routes
switch (true) {
    case $requestUri[0] === 'register' && $requestMethod === 'POST':
        // $data = json_decode(file_get_contents('php://input'), true);
        $data = $_POST;
        // Validate required fields
        $requiredFields = ['email', 'password', 'firstName', 'lastName', 'mobileNumber'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                echo json_encode([
                    'statuscode' => 400,
                    'status' => 'error',
                    'message' => ucfirst($field) . ' is required'
                ]);
                break 2;
            }
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'statuscode' => 400,
                'status' => 'error',
                'message' => 'Invalid email format'
            ]);
            break;
        }

        // Validate mobile number (assuming 10 digits)
        if (!preg_match('/^[0-9]{10}$/', $data['mobileNumber'])) {
            echo json_encode([
                'statuscode' => 400,
                'status' => 'error',
                'message' => 'Invalid mobile number format'
            ]);
            break;
        }

        echo json_encode($userController->register(
            $data['email'],
            $data['password'],
            $data['firstName'],
            $data['lastName'],
            $data['mobileNumber'],
            $data['referralCode'] ?? null
        ));
        break;


    case $requestUri[0] === 'login' && $requestMethod === 'POST':
        $data = $_POST;
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode([
                'statuscode' => 400,
                'status' => 'error',
                'message' => 'Email and password are required'
            ]);
            break;
        }
        echo json_encode($userController->login($data['email'], $data['password']));
        break;

    case $requestUri[0] === 'profile' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($userController->getUserProfile($requestUri[1]));
        break;

    case $requestUri[0] === 'delete-user' && $requestMethod === 'DELETE' && isset($requestUri[1]):
        echo json_encode($commanController->deleteuserData($requestUri[1]));
        break;

    case $requestUri[0] === 'referral-code' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($userController->getReferralCode($requestUri[1]));
        break;

    case $requestUri[0] === 'referral-rewards' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($userController->getReferralRewards($requestUri[1]));
        break;

    // case $requestUri[0] === 'update-profile' && $requestMethod === 'POST' && isset($requestUri[1]):
    //     $data = json_decode(file_get_contents('php://input'), true);
    //     echo json_encode($userController->updateUserProfile($requestUri[1], $data['firstName'], $data['lastName'], $data['mobileNumber'], $data['profilePic']));
    //     break;

    case $requestUri[0] === 'update-profiles' && $requestMethod === 'POST' && isset($requestUri[1]):
        // Check if the form fields are set and print them for debugging
        if (!isset($_POST['firstName'], $_POST['lastName'], $_POST['mobileNumber'])) {
            echo json_encode(['statuscode' => 400, 'status' => 'error', 'message' => 'Missing required fields in form data.']);
            break;
        }
    
        // Collect form data from $_POST
        $data = [
            'firstName' => $_POST['firstName'],
            'lastName'  => $_POST['lastName'],
            'mobileNumber' => $_POST['mobileNumber'],
        ];
    
        // Debug: Check if the data is correctly populated
        error_log('Form Data: ' . print_r($data, true));  // This will log data to the PHP error log
    
        // Call the updateUserProfile method with the form data
        echo json_encode($userController->updateUserProfile($requestUri[1], $data));
        break;

    // Assuming $requestUri contains the route, and $requestMethod is POST
    case $requestUri[0] === 'update-password' && $requestMethod === 'POST' && isset($requestUri[1]):
        $userId = $requestUri[1]; // The user ID is captured from the URL

    // Check if form data was received properly
        if (!isset($_POST['oldPassword']) || !isset($_POST['newPassword'])) {
            echo json_encode(['statuscode' => 400, 'status' => 'error', 'message' => 'Old and new passwords must be provided.']);
            break;
        }

        $oldPassword = $_POST['oldPassword'];
        $newPassword = $_POST['newPassword'];

        // Call the updatePassword method in the userController
        echo json_encode($userController->updatePassword($userId, $oldPassword, $newPassword));
        break;


    case $requestUri[0] === 'forgot-password' && $requestMethod === 'POST':
        $data = $_POST;
        echo json_encode($userController->forgotPassword($data['email']));
        break;

    case $requestUri[0] === 'reset-password' && $requestMethod === 'POST':
        $data = $_POST;
        echo json_encode($userController->resetPassword($data['password'], $data['token']));
        break;

    case $requestUri[0] === 'verify-otp' && $requestMethod === 'POST':
        $data = $_POST;
        echo json_encode($userController->verifyOtp($data['email'], $data['otp']));
        break;

        case $requestUri[0] === 'track-product-view' && $requestMethod === 'POST' && isset($requestUri[1]):
            $productId = (int) $requestUri[1]; 
            $userId = isset($_POST['userId']) ? (int) $_POST['userId'] : null; 
            $response = $productController->trackProductView($productId, $userId);
            echo json_encode($response);
            break;
        
            case $requestUri[0] === 'bookmark-product' && $requestMethod === 'POST' && isset($requestUri[1]):
                $productId = (int) $requestUri[1]; 
                $data = json_decode(file_get_contents('php://input'), true);  
                $userId = isset($data['userId']) ? (int) $data['userId'] : null; 
            
                if (!$userId) {
                    echo json_encode(['status' => 'error', 'message' => 'User must be logged in to bookmark a product.']);
                    break;
                }
            
                $response = $productController->bookmarkProduct($productId, $userId);
            
                echo json_encode($response);
        break;
        case $requestUri[0] === 'fetch-bookmarks' && $requestMethod === 'POST' && isset($requestUri[1]):
            $userId = isset($data['userId']) ? (int) $data['userId'] : null;
        
            if (!$userId) {
                echo json_encode(['status' => 'error', 'message' => 'User must be logged in to fetch bookmarks.']);
                break;
            }
        
            $response = $productController->getBookmarks($userId);
            echo json_encode($response);
            break;
        
        // Product routes
    case $requestUri[0] === 'products' && $requestMethod === 'GET':
        echo json_encode($productController->getProducts());
        break;
    case $requestUri[0] === 'newcars' && $requestMethod === 'GET':
        echo json_encode($productController->getProductsCars());
        break;
    case $requestUri[0] === 'spareparts' && $requestMethod === 'GET':
        echo json_encode($productController->getProductsSpareparts());
        break;

    case $requestUri[0] == 'oldcars' && $requestMethod == 'GET':
        echo json_encode($productController->getProductsOldCars());
        break;

    case $requestUri[0] === 'product' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($productController->getProductById($requestUri[1]));
        break;
    case $requestUri[0] === 'simular-product' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($productController->getSimilarProductsById($requestUri[1]));
        break;


    case $requestUri[0] === 'featured-products' && $requestMethod === 'GET':
        echo json_encode($productController->getFeaturedProducts());
        break;

    case $requestUri[0] === 'brands' && $requestMethod === 'GET':
        echo json_encode($commanController->getBrands());
        break;

    case $requestUri[0] === 'city' && $requestMethod === 'GET':
        echo json_encode($commanController->getCities());
        break;

        // Coupon routes
    case $requestUri[0] === 'coupons' && $requestMethod === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($couponController->createCoupon($data['code'], $data['discount'], $data['expiration']));
        break;

    case $requestUri[0] === 'apply-coupon' && $requestMethod === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($couponController->applyCoupon($data['code']));
        break;

    case $requestUri[0] === 'coupons' && $requestMethod === 'GET':
        echo json_encode($couponController->getCoupons());
        break;

    case $requestUri[0] === 'coupons' && $requestMethod === 'PUT' && isset($requestUri[1]):
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($couponController->updateCoupon($requestUri[1], $data['code'], $data['discount'], $data['expiration'], $data['status']));
        break;

    case $requestUri[0] === 'coupons' && $requestMethod === 'DELETE' && isset($requestUri[1]):
        echo json_encode($couponController->deleteCoupon($requestUri[1]));
        break;

        // Rewards routes
    case $requestUri[0] === 'rewards' && $requestMethod === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($rewardsController->createReward($data['referrerId'], $data['referredId'], $data['rewardAmount']));
        break;

    case $requestUri[0] === 'rewards' && $requestMethod === 'GET':
        echo json_encode($rewardsController->getRewards());
        break;

    case $requestUri[0] === 'rewards' && $requestMethod === 'PUT' && isset($requestUri[1]):
        $data = json_decode(file_get_contents('php://input'), true);
        echo json_encode($rewardsController->updateReward($requestUri[1], $data['referrerId'], $data['referredId'], $data['rewardAmount']));
        break;

    case $requestUri[0] === 'rewards' && $requestMethod === 'DELETE' && isset($requestUri[1]):
        echo json_encode($rewardsController->deleteReward($requestUri[1]));
        break;

    case $requestUri[0] === 'rewards' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($rewardsController->getRewardsForUser($requestUri[1]));
        break;

        // Advertisements routes
    case $requestUri[0] === 'advertisements' && $requestMethod === 'GET':
        try {
            echo json_encode($advertisementsController->getAdvertisements());
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve advertisements: ' . $e->getMessage()]);
        }
        break;

    case $requestUri[0] === 'advertisementsbyid' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($advertisementsController->getAdvertisementById($requestUri[1]));
        break;

        // Banner routes
    case $requestUri[0] === 'banners' && $requestMethod === 'GET':
        echo json_encode($bannerController->getBanners());
        break;

        // Dealer routes
    case $requestUri[0] === 'dealer-products' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->fetchProductsForDealer($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-oldcars' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->fetchProductsForDealerOldCars($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-newcars' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->fetchProductsForDealerNewCars($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-spareparts' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->fetchProductsForDealerSpareParts($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-products-by-category' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->fetchProductsByCategory($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-product-by-id' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->fetchProductById($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-connect1' && $requestMethod === 'GET':
        echo json_encode($dealerController->fetchAllProducts());
        break;
    case $requestUri[0] === 'cities-with-dealer-count' && $requestMethod === 'GET':
        echo json_encode($commanController->getCitiesWithDealerCount());
        break;
    case $requestUri[0] === 'cars-by-make' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($commanController->getCarByMake($requestUri[1]));
        break;
    case $requestUri[0] === 'inspected-cars' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($commanController->getInspectedCars($requestUri[1]));
        break;
    case $requestUri[0] === 'inspected-report' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($commanController->getInspectedReport($requestUri[1]));
        break;
    case $requestUri[0] === 'inspected-report-points' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($commanController->getInspectedReportPoints($requestUri[1]));
        break;
    case $requestUri[0] === 'statistics' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->statistics($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-connect' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->Dealerconnect($requestUri[1]));
        break;
    case $requestUri[0] === 'dealer-wallet-amount' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->getAmount($requestUri[1]));
        break;
    case $requestUri[0] === 'expired-advertisements' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->getExpiredAdvertisements($requestUri[1]));
        break;
    case $requestUri[0] === 'running-and-upcoming-advertisements' && $requestMethod === 'GET' && isset($requestUri[1]):
        echo json_encode($dealerController->getRunningAndUpcomingAdvertisements($requestUri[1]));
        break;
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'API endpoint not found']);
        break;
}
