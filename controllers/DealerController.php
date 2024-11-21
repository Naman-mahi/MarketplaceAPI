<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class DealerController
{
    /**
     * Fetch all products for dealer
     * @return array Response with status and products data
     */
    public function fetchProductsForDealer()
    {
        $connection = getDbConnection();

        if (!isset($_SESSION['user'])) {
            return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        }

        $dealer_id = $_SESSION['user']['user_id'];

        $stmt = $connection->prepare("SELECT * FROM products 
            JOIN product_publish ON products.product_id = product_publish.product_id 
            WHERE products.dealer_id = ?");
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            return [
                'statuscode' => 200,
                'status' => 'success',
                'message' => 'Products fetched successfully.',
                'data' => $products
            ];
        }

        return [
            'statuscode' => 404,
            'status' => 'error',
            'message' => 'No products found.'
        ];
    }

    public function fetchProductById($product_id)
    {
        $connection = getDbConnection();

        if (!isset($_SESSION['user'])) {
            return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        }

        $dealer_id = $_SESSION['user']['user_id'];

        $stmt = $connection->prepare("SELECT * FROM products 
            JOIN product_publish ON products.product_id = product_publish.product_id 
            WHERE products.dealer_id = ? AND products.product_id = ?");
        $stmt->bind_param("ii", $dealer_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            return [
                'statuscode' => 200,
                'status' => 'success',
                'message' => 'Product fetched successfully.',
                'data' => $product
            ];
        }

        return [
            'statuscode' => 404,
            'status' => 'error',
            'message' => 'Product not found.'
        ];
    }

    public function fetchProductsByCategory($category_id)
    {
        $connection = getDbConnection();

        if (!isset($_SESSION['user'])) {
            return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        }

        $dealer_id = $_SESSION['user']['user_id'];

        $stmt = $connection->prepare("SELECT * FROM products 
            JOIN product_publish ON products.product_id = product_publish.product_id 
            WHERE products.dealer_id = ? AND products.category_id = ?");
        $stmt->bind_param("ii", $dealer_id, $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            return [
                'statuscode' => 200,
                'status' => 'success',
                'message' => 'Products fetched successfully.',
                'data' => $products
            ];
        }

        return [
            'statuscode' => 404,
            'status' => 'error',
            'message' => 'No products found in this category.'
        ];
    }

    public function fetchAllProducts()
    {
        $connection = getDbConnection();

        if (!isset($_SESSION['user'])) {
            return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        }

        $marketplace = 1;

        $stmt = $connection->prepare("SELECT * FROM products 
            JOIN product_publish ON products.product_id = product_publish.product_id 
            WHERE product_publish.marketplace = ?");
        $stmt->bind_param("i", $marketplace);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            return [
                'statuscode' => 200,
                'status' => 'success',
                'message' => 'Products fetched successfully.',
                'data' => $products
            ];
        }

        return [
            'statuscode' => 404,
            'status' => 'error',
            'message' => 'No products found in this category.'
        ];
    }

    //I want total count of products for dealer
    public function statistics($dealer_id)
    {
        $connection = getDbConnection();

        // Step 1: Get total product count for the dealer
        $stmt = $connection->prepare("SELECT COUNT(*) as total_products FROM products WHERE dealer_id = ?");
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalProducts = $result->fetch_assoc()['total_products'];

        // Step 2: Get total different counts of marketplace, website, and own_website from product_publish
        $stmt = $connection->prepare("SELECT 
                                      COUNT(DISTINCT marketplace) as total_marketplace,
                                      COUNT(DISTINCT website) as total_website,
                                      COUNT(DISTINCT own_website) as total_own_website
                                   FROM product_publish 
                                   JOIN products ON products.product_id = product_publish.product_id
                                   WHERE products.dealer_id = ?");
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $publishData = $result->fetch_assoc();

        // Step 3: Get counts for new car, spare parts, and old car categories
        // Assuming there's a 'category' field in the 'products' table that indicates the product type (new_car, spare_parts, old_car)
        $stmt = $connection->prepare("SELECT 
                                      COUNT(CASE WHEN category_id = 2 THEN 1 END) as total_new_car,
                                      COUNT(CASE WHEN category_id = 4 THEN 1 END) as total_spare_parts,
                                      COUNT(CASE WHEN category_id = 19 THEN 1 END) as total_old_car
                                   FROM products 
                                   WHERE dealer_id = ?");
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categoryData = $result->fetch_assoc();

        // Return an array with all counts
        return [
            'total_products' => $totalProducts,
            'total_marketplace' => $publishData['total_marketplace'],
            'total_website' => $publishData['total_website'],
            'total_own_website' => $publishData['total_own_website'],
            'total_new_car' => $categoryData['total_new_car'],
            'total_spare_parts' => $categoryData['total_spare_parts'],
            'total_old_car' => $categoryData['total_old_car']
        ];
    }

    //dealer products
    public function Dealerconnect()
    {
        $connection = getDbConnection();
        $marketplace = 1;
        $stmt = $connection->prepare("SELECT * FROM products 
        join product_publish on products.product_id = product_publish.product_id 
        WHERE product_publish.marketplace = ?");
        $stmt->bind_param("i", $marketplace);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return [
                'status' => 'success',
                'message' => 'Products found successfully',
                'data' => $result->fetch_all(MYSQLI_ASSOC)
            ];
        }
        return [
            'status' => 'error', 
            'message' => 'No products found',
            'data' => []
        ];
    }
}



#SELECT `id`, `product_id`, `marketplace`, `website`, `own_website`, `created_at`, `updated_at` FROM `product_publish` WHERE 1
#SELECT `product_id`, `dealer_id`, `category_id`, `brand_id`, `product_name`, `product_description`, `price`, `color`, `product_image`, `product_condition`, `top_features`, `stand_out_features`, `is_featured`, `product_features`, `inspection_request`, `inspection_status`, `created_at`, `updated_at` FROM `products` WHERE 1
#
