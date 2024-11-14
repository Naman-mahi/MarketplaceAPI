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

    /**
     * Fetch product by ID for dealer
     * @param int $product_id Product ID to fetch
     * @return array Response with status and product data
     */
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

    /**
     * Fetch products by category for dealer
     * @param int $category_id Category ID to filter by
     * @return array Response with status and products data
     */
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
    /**
     * Fetch All products for dealer
     * @return array Response with status and products data
     */
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
}

#SELECT `id`, `product_id`, `marketplace`, `website`, `own_website`, `created_at`, `updated_at` FROM `product_publish` WHERE 1
#SELECT `product_id`, `dealer_id`, `category_id`, `brand_id`, `product_name`, `product_description`, `price`, `color`, `product_image`, `product_condition`, `top_features`, `stand_out_features`, `is_featured`, `product_features`, `inspection_request`, `inspection_status`, `created_at`, `updated_at` FROM `products` WHERE 1
#