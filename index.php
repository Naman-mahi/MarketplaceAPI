<?php
/*
include config
*/
include __DIR__ . '/config/config.php';
include __DIR__ . '/config/database.php';
include __DIR__ . '/controllers/UserController.php';
include __DIR__ . '/controllers/ProductController.php';
include __DIR__ . '/controllers/CouponController.php';
include __DIR__ . '/controllers/RewardsController.php';
$userController = new UserController();
$productController = new ProductController();
$couponController = new CouponController();
$rewardsController = new RewardsController();
/*
Set headers
*/
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

/*
Handle preflight requests
*/
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}



$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = explode('/', trim(str_replace(parse_url(BASE_URL, PHP_URL_PATH), '', $_SERVER['REQUEST_URI']), '/'));

/*
********
user routes
********
*/
if ($requestUri[0] === 'register' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->register(
        $data['email'],
        $data['password'],
        $data['firstName'],
        $data['lastName'],
        $data['mobileNumber'],
        $data['referralCode'] ?? null
    ));
} elseif ($requestUri[0] === 'login' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->login($data['email'], $data['password']));
} elseif ($requestUri[0] === 'profile' && $requestMethod === 'GET' && isset($requestUri[1])) {
    echo json_encode($userController->getUserProfile($requestUri[1]));
} elseif ($requestUri[0] === 'update-profile' && $requestMethod === 'POST' && isset($requestUri[1])) {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->updateUserProfile($requestUri[1], $data['firstName'], $data['lastName'], $data['mobileNumber'], $data['profilePic']));
} elseif ($requestUri[0] === 'forgot-password' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->forgotPassword($data['email']));
} elseif ($requestUri[0] === 'reset-password' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->resetPassword($data['email'], $data['password']));
} elseif ($requestUri[0] === 'fetch-otp' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->fetchOtp($data['email']));
} elseif ($requestUri[0] === 'verify-otp' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($userController->verifyOtp($data['email'], $data['otp']));


/*
********
product routes
********
*/
} elseif ($requestUri[0] === 'products' && $requestMethod === 'GET') {
    echo json_encode($productController->getProducts());
} elseif ($requestUri[0] === 'product' && $requestMethod === 'GET' && isset($requestUri[1])) {
    echo json_encode($productController->getProductById($requestUri[1]));
} elseif ($requestUri[0] === 'products' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($productController->createProduct($data['name'], $data['price']));
} elseif ($requestUri[0] === 'brands' && $requestMethod === 'GET') {
    echo json_encode($productController->getBrands());


/*
********
coupon routes
********
*/
} elseif ($requestUri[0] === 'coupons' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($couponController->createCoupon($data['code'], $data['discount'], $data['expiration']));
} elseif ($requestUri[0] === 'apply-coupon' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($couponController->applyCoupon($data['code']));
} elseif ($requestUri[0] === 'coupons' && $requestMethod === 'GET') {
    echo json_encode($couponController->getCoupons());
} elseif ($requestUri[0] === 'coupons' && $requestMethod === 'PUT' && isset($requestUri[1])) {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($couponController->updateCoupon($requestUri[1], $data['code'], $data['discount'], $data['expiration'], $data['status']));
} elseif ($requestUri[0] === 'coupons' && $requestMethod === 'DELETE' && isset($requestUri[1])) {
    echo json_encode($couponController->deleteCoupon($requestUri[1]));
} elseif ($requestUri[0] === 'dealer-products' && $requestMethod === 'GET' && isset($requestUri[1])) {
    echo json_encode($productController->getProductsByDealerId($requestUri[1]));

/*
********
rewards routes
********
*/
} elseif ($requestUri[0] === 'rewards' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($rewardsController->createReward($data['referrerId'], $data['referredId'], $data['rewardAmount']));
} elseif ($requestUri[0] === 'rewards' && $requestMethod === 'GET') {
    echo json_encode($rewardsController->getRewards()); 
} elseif ($requestUri[0] === 'rewards' && $requestMethod === 'PUT' && isset($requestUri[1])) {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($rewardsController->updateReward($requestUri[1], $data['referrerId'], $data['referredId'], $data['rewardAmount']));
} elseif ($requestUri[0] === 'rewards' && $requestMethod === 'DELETE' && isset($requestUri[1])) {
    echo json_encode($rewardsController->deleteReward($requestUri[1]));
} elseif ($requestUri[0] === 'rewards' && $requestMethod === 'GET' && isset($requestUri[1])) {
    echo json_encode($rewardsController->getRewardsForUser($requestUri[1]));

/*
********
advertisements routes
********
*/
} elseif ($requestUri[0] === 'advertisements' && $requestMethod === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($advertisementsController->createAdvertisement($data['title'], $data['description'], $data['image'], $data['link'], $data['startDate'], $data['endDate']));
} elseif ($requestUri[0] === 'advertisements' && $requestMethod === 'GET') {
    echo json_encode($advertisementsController->getAdvertisements());
} elseif ($requestUri[0] === 'advertisements' && $requestMethod === 'PUT' && isset($requestUri[1])) {
    $data = json_decode(file_get_contents('php://input'), true);
    echo json_encode($advertisementsController->updateAdvertisement($requestUri[1], $data['title'], $data['description'], $data['image'], $data['link'], $data['startDate'], $data['endDate']));
} elseif ($requestUri[0] === 'advertisements' && $requestMethod === 'DELETE' && isset($requestUri[1])) {
    echo json_encode($advertisementsController->deleteAdvertisement($requestUri[1]));
} elseif ($requestUri[0] === 'advertisements' && $requestMethod === 'GET' && isset($requestUri[1])) {
    echo json_encode($advertisementsController->getAdvertisementsForUser($requestUri[1]));

    
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'API endpoint not found']);
}
