<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class DealerController
{
    /**
     * Fetch all products for dealer
     * @return array Response with status and products data
     */
    public function fetchProductsForDealer($dealer_id)
    {
        $connection = getDbConnection();

        // if (!isset($_SESSION['user'])) {
        //     return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        // }
        // exit;
        $dealer_id = $dealer_id;
        $stmt = $connection->prepare("
        SELECT p.product_id, p.dealer_id, p.category_id, p.product_name,b.brand_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
               pi.image_id, pi.image_url, pi.is_primary,
               pa.pf_id, pa.category_id AS attribute_category_id, pa.pf_name,
               pav.value,
               pca.custom_attribute_id, pca.attribute_name, pca.attribute_value,
               pub.marketplace, pub.website, pub.own_website
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
        LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
        LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
        LEFT JOIN product_publish pub ON p.product_id = pub.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.dealer_id = ?
        ");
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();

        $rows = $stmt->get_result();
        error_log(print_r($rows, true)); // Log the rows for inspection
        if ($rows) {
            $products = [];
            foreach ($rows as $row) {
                $productId = $row['product_id'];
                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'product_id' => $row['product_id'],
                        'dealer_id' => $row['dealer_id'],
                        'category_id' => $row['category_id'],
                        'product_name' => $row['product_name'],
                        'product_description' => $row['product_description'],
                        'price' => $row['price'],
                        'color' => $row['color'],
                        'product_condition' => $row['product_condition'],
                        'brand_id' => $row['brand_id'],
                        'brand_name' => $row['brand_name'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'created_at' => $row['created_at'] ?? null,
                        'updated_at' => $row['updated_at'] ?? null,
                        'images' => [],
                        'combined_attributes' => [],
                        'custom_attributes' => [],
                        'publish_info' => null,
                    ];
                }

                // Add image if not already added
                if ($row['image_id'] && !in_array($row['image_id'], array_column($products[$productId]['images'], 'image_id'))) {
                    $products[$productId]['images'][] = [
                        'image_id' => $row['image_id'],
                        'image_url' => IMAGES_URL . $row['image_url'],
                        'is_primary' => $row['is_primary'],
                    ];
                }

                // Add combined attribute if not already added
                if ($row['pf_id'] && !in_array($row['pf_id'], array_column($products[$productId]['combined_attributes'], 'pf_id'))) {
                    $products[$productId]['combined_attributes'][] = [
                        'pf_id' => $row['pf_id'],
                        'category_id' => $row['attribute_category_id'],
                        'pf_name' => $row['pf_name'],
                        'value' => $row['value'],
                    ];
                }

                // Add custom attribute if not already added
                if ($row['custom_attribute_id'] && !in_array($row['custom_attribute_id'], array_column($products[$productId]['custom_attributes'], 'custom_attribute_id'))) {
                    $products[$productId]['custom_attributes'][] = [
                        'custom_attribute_id' => $row['custom_attribute_id'],
                        'attribute_name' => $row['attribute_name'],
                        'attribute_value' => $row['attribute_value'],
                    ];
                }

                // Add publish info if not already added
                if (!$products[$productId]['publish_info']) {
                    $products[$productId]['publish_info'] = [
                        'marketplace' => $row['marketplace'],
                        'website' => $row['website'],
                        'own_website' => $row['own_website'],
                    ];
                }
            }
            return ['status' => 200, 'message' => 'Products fetched successfully', 'data' => array_values($products)];
        } else {
            return ['status' => 404, 'message' => 'No products found'];
        }
    }
    public function fetchProductsForDealerOldCars($dealer_id)
    {
        $connection = getDbConnection();

        // if (!isset($_SESSION['user'])) {
        //     return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        // }
        // exit;
        $dealer_id = $dealer_id;
        $category_id = 19;
        $stmt = $connection->prepare("
        SELECT p.product_id, p.dealer_id, p.category_id, p.product_name,b.brand_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
               pi.image_id, pi.image_url, pi.is_primary,
               pa.pf_id, pa.category_id AS attribute_category_id, pa.pf_name,
               pav.value,
               pca.custom_attribute_id, pca.attribute_name, pca.attribute_value,
               pub.marketplace, pub.website, pub.own_website
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
        LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
        LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
        LEFT JOIN product_publish pub ON p.product_id = pub.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.dealer_id = ? and p.category_id = ?
        ");
        $stmt->bind_param("ii", $dealer_id, $category_id);
        $stmt->execute();

        $rows = $stmt->get_result();
        error_log(print_r($rows, true)); // Log the rows for inspection
        if ($rows) {
            $products = [];
            foreach ($rows as $row) {
                $productId = $row['product_id'];
                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'product_id' => $row['product_id'],
                        'dealer_id' => $row['dealer_id'],
                        'category_id' => $row['category_id'],
                        'product_name' => $row['product_name'],
                        'product_description' => $row['product_description'],
                        'price' => $row['price'],
                        'color' => $row['color'],
                        'product_condition' => $row['product_condition'],
                        'brand_id' => $row['brand_id'],
                        'brand_name' => $row['brand_name'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'created_at' => $row['created_at'] ?? null,
                        'updated_at' => $row['updated_at'] ?? null,
                        'images' => [],
                        'combined_attributes' => [],
                        'custom_attributes' => [],
                        'publish_info' => null,
                    ];
                }

                // Add image if not already added
                if ($row['image_id'] && !in_array($row['image_id'], array_column($products[$productId]['images'], 'image_id'))) {
                    $products[$productId]['images'][] = [
                        'image_id' => $row['image_id'],
                        'image_url' => IMAGES_URL . $row['image_url'],
                        'is_primary' => $row['is_primary'],
                    ];
                }

                // Add combined attribute if not already added
                if ($row['pf_id'] && !in_array($row['pf_id'], array_column($products[$productId]['combined_attributes'], 'pf_id'))) {
                    $products[$productId]['combined_attributes'][] = [
                        'pf_id' => $row['pf_id'],
                        'category_id' => $row['attribute_category_id'],
                        'pf_name' => $row['pf_name'],
                        'value' => $row['value'],
                    ];
                }

                // Add custom attribute if not already added
                if ($row['custom_attribute_id'] && !in_array($row['custom_attribute_id'], array_column($products[$productId]['custom_attributes'], 'custom_attribute_id'))) {
                    $products[$productId]['custom_attributes'][] = [
                        'custom_attribute_id' => $row['custom_attribute_id'],
                        'attribute_name' => $row['attribute_name'],
                        'attribute_value' => $row['attribute_value'],
                    ];
                }

                // Add publish info if not already added
                if (!$products[$productId]['publish_info']) {
                    $products[$productId]['publish_info'] = [
                        'marketplace' => $row['marketplace'],
                        'website' => $row['website'],
                        'own_website' => $row['own_website'],
                    ];
                }
            }
            return ['status' => 200, 'message' => 'Products fetched successfully', 'data' => array_values($products)];
        } else {
            return ['status' => 404, 'message' => 'No products found'];
        }
    }
    public function fetchProductsForDealerNewCars($dealer_id)
    {
        $connection = getDbConnection();

        // if (!isset($_SESSION['user'])) {
        //     return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        // }
        // exit;
        $dealer_id = $dealer_id;
        $category_id = 2;
        $stmt = $connection->prepare("
        SELECT p.product_id, p.dealer_id, p.category_id, p.product_name,b.brand_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
               pi.image_id, pi.image_url, pi.is_primary,
               pa.pf_id, pa.category_id AS attribute_category_id, pa.pf_name,
               pav.value,
               pca.custom_attribute_id, pca.attribute_name, pca.attribute_value,
               pub.marketplace, pub.website, pub.own_website
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
        LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
        LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
        LEFT JOIN product_publish pub ON p.product_id = pub.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.dealer_id = ? and p.category_id = ?
        ");
        $stmt->bind_param("ii", $dealer_id, $category_id);
        $stmt->execute();

        $rows = $stmt->get_result();
        error_log(print_r($rows, true)); // Log the rows for inspection
        if ($rows) {
            $products = [];
            foreach ($rows as $row) {
                $productId = $row['product_id'];
                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'product_id' => $row['product_id'],
                        'dealer_id' => $row['dealer_id'],
                        'category_id' => $row['category_id'],
                        'product_name' => $row['product_name'],
                        'product_description' => $row['product_description'],
                        'price' => $row['price'],
                        'color' => $row['color'],
                        'product_condition' => $row['product_condition'],
                        'brand_id' => $row['brand_id'],
                        'brand_name' => $row['brand_name'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'created_at' => $row['created_at'] ?? null,
                        'updated_at' => $row['updated_at'] ?? null,
                        'images' => [],
                        'combined_attributes' => [],
                        'custom_attributes' => [],
                        'publish_info' => null,
                    ];
                }

                // Add image if not already added
                if ($row['image_id'] && !in_array($row['image_id'], array_column($products[$productId]['images'], 'image_id'))) {
                    $products[$productId]['images'][] = [
                        'image_id' => $row['image_id'],
                        'image_url' => IMAGES_URL . $row['image_url'],
                        'is_primary' => $row['is_primary'],
                    ];
                }

                // Add combined attribute if not already added
                if ($row['pf_id'] && !in_array($row['pf_id'], array_column($products[$productId]['combined_attributes'], 'pf_id'))) {
                    $products[$productId]['combined_attributes'][] = [
                        'pf_id' => $row['pf_id'],
                        'category_id' => $row['attribute_category_id'],
                        'pf_name' => $row['pf_name'],
                        'value' => $row['value'],
                    ];
                }

                // Add custom attribute if not already added
                if ($row['custom_attribute_id'] && !in_array($row['custom_attribute_id'], array_column($products[$productId]['custom_attributes'], 'custom_attribute_id'))) {
                    $products[$productId]['custom_attributes'][] = [
                        'custom_attribute_id' => $row['custom_attribute_id'],
                        'attribute_name' => $row['attribute_name'],
                        'attribute_value' => $row['attribute_value'],
                    ];
                }

                // Add publish info if not already added
                if (!$products[$productId]['publish_info']) {
                    $products[$productId]['publish_info'] = [
                        'marketplace' => $row['marketplace'],
                        'website' => $row['website'],
                        'own_website' => $row['own_website'],
                    ];
                }
            }
            return ['status' => 200, 'message' => 'Products fetched successfully', 'data' => array_values($products)];
        } else {
            return ['status' => 404, 'message' => 'No products found'];
        }
    }
    public function fetchProductsForDealerSpareParts($dealer_id)
    {
        $connection = getDbConnection();

        // if (!isset($_SESSION['user'])) {
        //     return ['statuscode' => 401, 'status' => 'error', 'message' => 'Please login to continue.'];
        // }
        // exit;
        $dealer_id = $dealer_id;
        $category_id = 4;
        $stmt = $connection->prepare("
        SELECT p.product_id, p.dealer_id, p.category_id, p.product_name,b.brand_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
               pi.image_id, pi.image_url, pi.is_primary,
               pa.pf_id, pa.category_id AS attribute_category_id, pa.pf_name,
               pav.value,
               pca.custom_attribute_id, pca.attribute_name, pca.attribute_value,
               pub.marketplace, pub.website, pub.own_website
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id
        LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
        LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
        LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
        LEFT JOIN product_publish pub ON p.product_id = pub.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.dealer_id = ? and p.category_id = ?
        ");
        $stmt->bind_param("ii", $dealer_id, $category_id);
        $stmt->execute();

        $rows = $stmt->get_result();
        error_log(print_r($rows, true)); // Log the rows for inspection
        if ($rows) {
            $products = [];
            foreach ($rows as $row) {
                $productId = $row['product_id'];
                if (!isset($products[$productId])) {
                    $products[$productId] = [
                        'product_id' => $row['product_id'],
                        'dealer_id' => $row['dealer_id'],
                        'category_id' => $row['category_id'],
                        'product_name' => $row['product_name'],
                        'product_description' => $row['product_description'],
                        'price' => $row['price'],
                        'color' => $row['color'],
                        'product_condition' => $row['product_condition'],
                        'brand_id' => $row['brand_id'],
                        'brand_name' => $row['brand_name'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'created_at' => $row['created_at'] ?? null,
                        'updated_at' => $row['updated_at'] ?? null,
                        'images' => [],
                        'combined_attributes' => [],
                        'custom_attributes' => [],
                        'publish_info' => null,
                    ];
                }

                // Add image if not already added
                if ($row['image_id'] && !in_array($row['image_id'], array_column($products[$productId]['images'], 'image_id'))) {
                    $products[$productId]['images'][] = [
                        'image_id' => $row['image_id'],
                        'image_url' => IMAGES_URL . $row['image_url'],
                        'is_primary' => $row['is_primary'],
                    ];
                }

                // Add combined attribute if not already added
                if ($row['pf_id'] && !in_array($row['pf_id'], array_column($products[$productId]['combined_attributes'], 'pf_id'))) {
                    $products[$productId]['combined_attributes'][] = [
                        'pf_id' => $row['pf_id'],
                        'category_id' => $row['attribute_category_id'],
                        'pf_name' => $row['pf_name'],
                        'value' => $row['value'],
                    ];
                }

                // Add custom attribute if not already added
                if ($row['custom_attribute_id'] && !in_array($row['custom_attribute_id'], array_column($products[$productId]['custom_attributes'], 'custom_attribute_id'))) {
                    $products[$productId]['custom_attributes'][] = [
                        'custom_attribute_id' => $row['custom_attribute_id'],
                        'attribute_name' => $row['attribute_name'],
                        'attribute_value' => $row['attribute_value'],
                    ];
                }

                // Add publish info if not already added
                if (!$products[$productId]['publish_info']) {
                    $products[$productId]['publish_info'] = [
                        'marketplace' => $row['marketplace'],
                        'website' => $row['website'],
                        'own_website' => $row['own_website'],
                    ];
                }
            }
            return ['status' => 200, 'message' => 'Products fetched successfully', 'data' => array_values($products)];
        } else {
            return ['status' => 404, 'message' => 'No products found'];
        }
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
      
public function Dealerconnect($dealer_id)
{
    // Step 1: Get the database connection
    $connection = getDbConnection();
    $dealer_id = $dealer_id;
    $marketplace = 1;

    $stmt = $connection->prepare("
    SELECT p.product_id, p.dealer_id, p.category_id, p.product_name,b.brand_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
           pi.image_id, pi.image_url, pi.is_primary,
           pa.pf_id, pa.category_id AS attribute_category_id, pa.pf_name,
           pav.value,
           pca.custom_attribute_id, pca.attribute_name, pca.attribute_value,
           pub.marketplace, pub.website, pub.own_website
    FROM products p
    LEFT JOIN product_images pi ON p.product_id = pi.product_id
    LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
    LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
    LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
    LEFT JOIN product_publish pub ON p.product_id = pub.product_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    WHERE p.dealer_id != ? AND pub.marketplace = ?");
    $stmt->bind_param("ii", $dealer_id, $marketplace);
    $stmt->execute();

    $rows = $stmt->get_result();
    error_log(print_r($rows, true)); // Log the rows for inspection
    if ($rows) {
        $products = [];
        foreach ($rows as $row) {
            $productId = $row['product_id'];
            if (!isset($products[$productId])) {
                $products[$productId] = [
                    'product_id' => $row['product_id'],
                    'dealer_id' => $row['dealer_id'],
                    'category_id' => $row['category_id'],
                    'product_name' => $row['product_name'],
                    'product_description' => $row['product_description'],
                    'price' => $row['price'],
                    'color' => $row['color'],
                    'product_condition' => $row['product_condition'],
                    'brand_id' => $row['brand_id'],
                    'brand_name' => $row['brand_name'],
                    'product_image' => THUMBNAIL_URL . $row['product_image'],
                    'is_featured' => $row['is_featured'],
                    'product_features' => $row['product_features'],
                    'top_features' => $row['top_features'],
                    'stand_out_features' => $row['stand_out_features'],
                    'created_at' => $row['created_at'] ?? null,
                    'updated_at' => $row['updated_at'] ?? null,
                    'images' => [],
                    'combined_attributes' => [],
                    'custom_attributes' => [],
                    'publish_info' => null,
                ];
            }

            // Add image if not already added
            if ($row['image_id'] && !in_array($row['image_id'], array_column($products[$productId]['images'], 'image_id'))) {
                $products[$productId]['images'][] = [
                    'image_id' => $row['image_id'],
                    'image_url' => IMAGES_URL . $row['image_url'],
                    'is_primary' => $row['is_primary'],
                ];
            }

            // Add combined attribute if not already added
            if ($row['pf_id'] && !in_array($row['pf_id'], array_column($products[$productId]['combined_attributes'], 'pf_id'))) {
                $products[$productId]['combined_attributes'][] = [
                    'pf_id' => $row['pf_id'],
                    'category_id' => $row['attribute_category_id'],
                    'pf_name' => $row['pf_name'],
                    'value' => $row['value'],
                ];
            }

            // Add custom attribute if not already added
            if ($row['custom_attribute_id'] && !in_array($row['custom_attribute_id'], array_column($products[$productId]['custom_attributes'], 'custom_attribute_id'))) {
                $products[$productId]['custom_attributes'][] = [
                    'custom_attribute_id' => $row['custom_attribute_id'],
                    'attribute_name' => $row['attribute_name'],
                    'attribute_value' => $row['attribute_value'],
                ];
            }

            // Add publish info if not already added
            if (!$products[$productId]['publish_info']) {
                $products[$productId]['publish_info'] = [
                    'marketplace' => $row['marketplace'],
                    'website' => $row['website'],
                    'own_website' => $row['own_website'],
                ];
            }
        }
        return ['status' => 200, 'message' => 'Products fetched successfully', 'data' => array_values($products)];
    } else {
        return ['status' => 404, 'message' => 'No products found'];
    }
}
    

    public function getAmount($userId)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT user_id, balance FROM wallets WHERE user_id = ?");
        $stmt->bind_param("i", $userId); // Bind the user_id parameter to the query
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return [
                'status' => 'success',
                'message' => 'Balance found successfully',
                'data' => $result->fetch_all(MYSQLI_ASSOC)
            ];
        }
        return [
            'status' => 'error',
            'message' => 'No balance found for this user',
            'data' => []
        ];
    }

    public function getExpiredAdvertisements($userId)
    {
        $connection = getDbConnection();
        $currentDate = date('Y-m-d H:i:s'); // Get the current datetime
    
        // Prepare the SQL query to get expired advertisements
        $stmt = $connection->prepare(
            "SELECT id, title, description, image, link, start_datetime, end_datetime, created_by, created_at, updated_at 
             FROM advertisements 
             WHERE end_datetime < ? AND created_by = ?"
        );
    
        // Bind the current datetime for the condition and the user_id for created_by
        $stmt->bind_param("si", $currentDate, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $advertisements = [];
            // Loop through the result and modify each row
            while ($row = $result->fetch_assoc()) {
                // Prepare the image URL
                $row['image'] = ADVERTISEMENT_URL . $row['image'];
                // Add the advertisement to the result array
                $advertisements[] = $row;
            }
    
            return [
                'status' => 'success',
                'message' => 'Expired advertisements found successfully',
                'data' => $advertisements
            ];
        }
    
        return [
            'status' => 'error',
            'message' => 'No expired advertisements found',
            'data' => []
        ];
    }
    

    public function getRunningAndUpcomingAdvertisements($userId)
    {
        $connection = getDbConnection();
        $currentDate = date('Y-m-d H:i:s'); // Get the current datetime
    
        // Prepare the SQL query to get running and upcoming advertisements
        $stmt = $connection->prepare(
            "SELECT id, title, description, image, link, start_datetime, end_datetime, created_by, created_at, updated_at
             FROM advertisements 
             WHERE (start_datetime <= ? AND end_datetime >= ?) 
                OR start_datetime > ? 
             AND created_by = ?"
        );
    
        // Bind the current datetime for conditions and the user_id for created_by
        $stmt->bind_param("ssss", $currentDate, $currentDate, $currentDate, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $advertisements = [];
            // Loop through the result and build the advertisements array
            while ($row = $result->fetch_assoc()) {
                // Prepare the image URL
                $row['image'] = ADVERTISEMENT_URL . $row['image'];
                // Add the advertisement to the result array
                $advertisements[] = $row;
            }
    
            return [
                'status' => 'success',
                'message' => 'Running and upcoming advertisements found successfully',
                'data' => $advertisements
            ];
        }
    
        return [
            'status' => 'error',
            'message' => 'No running or upcoming advertisements found for this user',
            'data' => []
        ];
    }
    
}



#SELECT `id`, `product_id`, `marketplace`, `website`, `own_website`, `created_at`, `updated_at` FROM `product_publish` WHERE 1
#SELECT `product_id`, `dealer_id`, `category_id`, `brand_id`, `product_name`, `product_description`, `price`, `color`, `product_image`, `product_condition`, `top_features`, `stand_out_features`, `is_featured`, `product_features`, `inspection_request`, `inspection_status`, `created_at`, `updated_at` FROM `products` WHERE 1
#
