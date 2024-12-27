<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

// /controllers/ProductController.php
class ProductController
{


    public function getProducts()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features,p.inspection_request,p.inspection_status, b.brand_name, d.city, p.created_at, p.updated_at,
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
            left join brands b on p.brand_id = b.brand_id
            left join dealers d on p.dealer_id = d.user_id
            LEFT JOIN product_publish pub ON p.product_id = pub.product_id
            WHERE pub.website = 1
            ");
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
                        'city' => $row['city'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'inspection_request' => $row['inspection_request'],
                        'inspection_status' => $row['inspection_status'],
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
    public function getProductsCars()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, b.brand_name, d.city, p.inspection_request, p.inspection_status, p.created_at, p.updated_at,
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
            LEFT JOIN brands b on p.brand_id = b.brand_id
            LEFT JOIN dealers d on p.dealer_id = d.user_id
            WHERE p.category_id = 2
            AND pub.website = 1
            ");
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
                        'city' => $row['city'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'inspection_request' => $row['inspection_request'],
                        'inspection_status' => $row['inspection_status'],
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
    public function getProductsSpareparts()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features,b.brand_name, d.city, p.inspection_request,p.inspection_status, p.created_at, p.updated_at,
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
            LEFT JOIN brands b on p.brand_id = b.brand_id
            LEFT JOIN dealers d on p.dealer_id = d.user_id
            WHERE p.category_id = 4
             AND pub.website = 1
            ");
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
                        'city' => $row['city'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'inspection_request' => $row['inspection_request'],
                        'inspection_status' => $row['inspection_status'],
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
    public function getProductsOldCars()
    {
        $connection = getDbConnection();
        // Capture GET parameters, with defaults if not set
        $make = isset($_GET['make']) ? $_GET['make'] : null;
        $model = isset($_GET['model']) ? $_GET['model'] : null;
        $price = isset($_GET['price']) ? $_GET['price'] : null;
        $city = isset($_GET['city']) ? $_GET['city'] : null;
        $category_id = 19;  // Assuming the category_id is always 19 for old cars, as per your original query

        // Start building the SQL query
        $sql = "
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features,p.inspection_request,p.inspection_status, p.created_at, p.updated_at,
                   pi.image_id, pi.image_url, pi.is_primary,
                   pa.pf_id, pa.category_id AS attribute_category_id, pa.pf_name,
                   pav.value,
                   pca.custom_attribute_id, pca.attribute_name, pca.attribute_value,
                   pub.marketplace, pub.website, pub.own_website,
                   d.city, b.brand_name
            FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id
            LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
            LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
            LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
            LEFT JOIN product_publish pub ON p.product_id = pub.product_id
            LEFT JOIN dealers d ON p.dealer_id = d.user_id
            LEFT JOIN brands b on p.brand_id = b.brand_id
            WHERE p.category_id = ?
            AND pub.website = 1
        ";

        // Array to hold the parameters
        $params = [$category_id];

        // Modify the query based on the provided parameters
        if ($make) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $make;  // assuming make refers to brand_id
        }

        if ($model) {
            $sql .= " AND p.product_name LIKE ?";
            $params[] = '%' . $model . '%';  // Assuming model is part of the product name
        }

        if ($price) {
            $sql .= " AND p.price <= ?";
            $params[] = $price;  // assuming price is an upper limit
        }

        if ($city) {
            $sql .= " AND d.city = ?";
            $params[] = $city;
        }

        // Adding filters for product attributes based on GET parameters
        $filters = [
            'mileage' => 'Mileage (MPG)',
            'engine_power' => 'Engine Power (HP)',
            'num_doors' => 'Number of Doors',
            'fuel_type' => 'Fuel Type',
            'transmission' => 'Transmission Type',
            'tire_size' => 'Tire Size',
            'warranty_period' => 'Warranty Period (years)',
            'safety_rating' => 'Safety Rating',
            'model_attr' => 'Model',
            'engine_type' => 'Engine Type'
        ];

        // Loop through the filter options
        foreach ($filters as $param_name => $pf_name) {
            if (isset($_GET[$param_name])) {
                $sql .= " AND pa.pf_name = ? AND pav.value = ?";
                $params[] = $pf_name;  // Filtering based on the attribute name
                $params[] = $_GET[$param_name];  // Filtering based on the selected attribute value
            }
        }

        // Prepare the statement
        $stmt = $connection->prepare($sql);

        // Bind the parameters dynamically
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);

        // Execute the query
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
                        'city' => $row['city'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'top_features' => $row['top_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'inspection_request' => $row['inspection_request'],
                        'inspection_status' => $row['inspection_status'],
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

    public function updateProduct($id, $name, $price)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("UPDATE products SET name = ?, price = ? WHERE product_id = ?");
        $stmt->bind_param("sdi", $name, $price, $id);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Product updated.'];
        }
        return ['status' => 'error', 'message' => 'Update failed.'];
    }

    public function deleteProduct($id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Product deleted.'];
        }
        return ['status' => 'error', 'message' => 'Deletion failed.'];
    }

    public function getProductById($product_id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_description, p.product_image, p.is_featured, p.product_features, p.price, p.color, p.top_features, p.stand_out_features,b.brand_name, d.city, p.inspection_request,p.inspection_status, p.created_at, p.updated_at,
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
            LEFT JOIN brands b on p.brand_id = b.brand_id
            LEFT JOIN dealers d on p.dealer_id = d.user_id
            WHERE p.product_id = ?
            AND pub.website = 1
            ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        $rows = $stmt->get_result();
        error_log(print_r($rows, true)); // Log the rows for inspection
        if ($rows && $rows->num_rows > 0) {
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
                        'city' => $row['city'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'top_features' => $row['top_features'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'stand_out_features' => $row['stand_out_features'],
                        'inspection_request' => $row['inspection_request'],
                        'inspection_status' => $row['inspection_status'],
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
            return ['status' => 200, 'message' => 'Product fetched successfully', 'data' => array_values($products)];
        } else {
            return ['status' => 404, 'message' => 'Product not found'];
        }
    }

    //get products by dealer id
    public function getProductsByDealerId($dealer_id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM products WHERE dealer_id = ?");
        $stmt->bind_param("i", $dealer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return ['status' => 200, 'message' => 'Products fetched successfully', 'data' => $result->fetch_all(MYSQLI_ASSOC)];
        } else {
            return ['status' => 404, 'message' => 'No products found for this dealer'];
        }
    }
    //list of brands
    public function getBrands()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM brands");
        $stmt->execute();
        $result = $stmt->get_result();
        return ['status' => 200, 'message' => 'Brands fetched successfully', 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }

    public function getFeaturedProducts()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_image, p.product_condition, p.is_featured, p.product_features, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features,b.brand_name, d.city, p.inspection_request,p.inspection_status, p.created_at, p.updated_at,
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
            LEFT JOIN brands b on p.brand_id = b.brand_id
            LEFT JOIN dealers d on p.dealer_id = d.user_id
            WHERE p.is_featured = 1
             AND pub.website = 1
            ");
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
                        'city' => $row['city'],
                        'product_image' => THUMBNAIL_URL . $row['product_image'],
                        'is_featured' => $row['is_featured'],
                        'product_features' => $row['product_features'],
                        'inspection_request' => $row['inspection_request'],
                        'inspection_status' => $row['inspection_status'],
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
            return ['status' => 200, 'message' => 'Featured products fetched successfully', 'data' => array_values($products)];
        } else {
            return ['status' => 404, 'message' => 'No featured products found'];
        }
    }


    public function getSimilarProductsById($product_id)
    {
        $connection = getDbConnection();
    
        // First, fetch the original product details (to get the category, name, description, color, price, features, brand, and more)
        $stmt = $connection->prepare("
            SELECT p.product_id, p.product_name, p.product_description, p.category_id, p.price, p.color, p.product_features, p.brand_id
            FROM products p
            LEFT JOIN product_publish pub ON p.product_id = pub.product_id
            WHERE p.product_id = ?
            AND pub.website = 1
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
    
        if (!$product) {
            return ['status' => 404, 'message' => 'Product not found'];
        }
    
        // Extract product details
        $category_id = $product['category_id'];
        $product_name = $product['product_name'];
        $product_description = $product['product_description'];
        $price = $product['price'];
        $color = $product['color'];
        $product_features = $product['product_features'];
        $brand_id = $product['brand_id'];
    
        // Prepare SQL to fetch similar products
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.product_name, p.product_description, p.price, p.product_image, p.color, p.product_features, p.inspection_request, p.inspection_status, 
                   b.brand_name, d.city, pi.image_id, pi.image_url, pi.is_primary, pa.pf_id, pa.pf_name, pav.value, 
                   pca.custom_attribute_id, pca.attribute_name, pca.attribute_value, p.category_id, p.product_condition, p.brand_id, p.is_featured, p.top_features, p.stand_out_features
            FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id
            LEFT JOIN product_publish pub ON p.product_id = pub.product_id
            LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
            LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
            LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
            LEFT JOIN brands b on p.brand_id = b.brand_id
            LEFT JOIN dealers d on p.dealer_id = d.user_id
            WHERE p.category_id = ? 
            AND pub.website = 1
            AND p.product_id != ? 
            AND (p.product_name LIKE ? OR p.product_description LIKE ? OR p.color LIKE ? OR p.price BETWEEN ? AND ? OR p.product_features LIKE ?)
        ");
    
        $like_name = "%" . $product_name . "%";  // Add wildcard for product name matching
        $like_description = "%" . $product_description . "%";  // Add wildcard for product description matching
        $like_color = "%" . $color . "%";  // Add wildcard for color matching
        $price_min = $price * 0.8;  // 80% of the original price as the minimum
        $price_max = $price * 1.2;  // 120% of the original price as the maximum
        $like_features = "%" . $product_features . "%";  // Add wildcard for product features matching
    
        $stmt->bind_param("iissssss", $category_id, $product_id, $like_name, $like_description, $like_color, $price_min, $price_max, $like_features);
        $stmt->execute();
        $rows = $stmt->get_result();
    
        $similarProducts = [];
    
        while ($row = $rows->fetch_assoc()) {
            $similarProductId = $row['product_id'];
    
            if (!isset($similarProducts[$similarProductId])) {
                $similarProducts[$similarProductId] = [
                    'product_id' => $row['product_id'],
                    'dealer_id' => $row['dealer_id'],
                    'category_id' => $row['category_id'],
                    'product_name' => $row['product_name'],
                    'product_description' => $row['product_description'],
                    'product_condition' => $row['product_condition'],
                    'brand_id' => $row['brand_id'],
                    'brand_name' => $row['brand_name'],
                    'city' => $row['city'],
                    'is_featured' => $row['is_featured'],
                    'top_features' => $row['top_features'],
                    'stand_out_features' => $row['stand_out_features'],
                    'price' => $row['price'],
                    'color' => $row['color'],
                    'product_features' => $row['product_features'],
                    'inspection_request' => $row['inspection_request'],
                    'inspection_status' => $row['inspection_status'],
                    'product_image' => THUMBNAIL_URL . $row['product_image'],
                    'images' => [],
                    'combined_attributes' => [],
                    'custom_attributes' => [],
                ];
            }
    
            // Handle images - Avoid duplicates using associative arrays
            if ($row['image_id'] && !isset($similarProducts[$similarProductId]['images'][$row['image_id']])) {
                $similarProducts[$similarProductId]['images'][$row['image_id']] = [
                    'image_id' => $row['image_id'],
                    'image_url' => IMAGES_URL . $row['image_url'],
                    'is_primary' => $row['is_primary'],
                ];
            }
    
            // Handle combined attributes - Avoid duplicates using associative arrays
            if ($row['pf_id'] && !isset($similarProducts[$similarProductId]['combined_attributes'][$row['pf_id']])) {
                $similarProducts[$similarProductId]['combined_attributes'][$row['pf_id']] = [
                    'pf_id' => $row['pf_id'],
                    'pf_name' => $row['pf_name'],
                    'value' => $row['value'],
                ];
            }
    
            // Handle custom attributes - Avoid duplicates using associative arrays
            if ($row['custom_attribute_id'] && !isset($similarProducts[$similarProductId]['custom_attributes'][$row['custom_attribute_id']])) {
                $similarProducts[$similarProductId]['custom_attributes'][$row['custom_attribute_id']] = [
                    'custom_attribute_id' => $row['custom_attribute_id'],
                    'attribute_name' => $row['attribute_name'],
                    'attribute_value' => $row['attribute_value'],
                ];
            }
        }
    
        // Return the response
        if (!empty($similarProducts)) {
            return [
                'status' => 200,
                'data' => array_values($similarProducts), // Return the products as an indexed array
            ];
        } else {
            return [
                'status' => 404,
                'message' => 'No similar products found',
            ];
        }
    }
    
    public function trackProductView($productId, $userId = null)
    {

        $publicIp = file_get_contents('https://api.ipify.org');
        if ($publicIp === false) {
            $publicIp = 'Unknown';
        }
        $localIp = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $anonymousId = null;
        if ($userId === null) {
            $anonymousId = hash('sha256', $localIp . $userAgent);
        }
        $viewDate = date("Y-m-d H:i:s");
        $connection = getDbConnection();
        if ($userId) {

            $sql = "SELECT COUNT(*) FROM product_views WHERE user_id = ? AND product_id = ? AND public_ip = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('iis', $userId, $productId, $publicIp);
        } else {

            $sql = "SELECT COUNT(*) FROM product_views WHERE anonymous_id = ? AND product_id = ? AND public_ip = ?";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('sss', $anonymousId, $productId, $publicIp);
        }

        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($viewCount);
        $stmt->fetch();

        if ($viewCount > 0) {
            return ['status' => 'error', 'message' => 'Duplicate product view detected.'];
        }
        if ($userId) {

            $sql = "INSERT INTO product_views (user_id, product_id, public_ip, local_ip, user_agent, view_date)
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('iissss', $userId, $productId, $publicIp, $localIp, $userAgent, $viewDate);
        } else {

            $sql = "INSERT INTO product_views (anonymous_id, product_id, public_ip, local_ip, user_agent, view_date)
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param('ssssss', $anonymousId, $productId, $publicIp, $localIp, $userAgent, $viewDate);
        }


        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Product view recorded successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to record product view.'];
        }
    }

    public function bookmarkProduct($productId, $userId)
    {
        // Ensure the user is logged in
        if (!$userId) {
            return ['status' => 'error', 'message' => 'User must be logged in to bookmark a product.'];
        }

        // Get the database connection
        $connection = getDbConnection();

        // Check if the product is already bookmarked by the user
        $sql = "SELECT COUNT(*) FROM product_bookmarks WHERE user_id = ? AND product_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($bookmarkCount);
        $stmt->fetch();

        // If the product is already bookmarked, return an error message
        if ($bookmarkCount > 0) {
            return ['status' => 'error', 'message' => 'Product is already bookmarked.'];
        }

        // Insert into the database if no duplicate was found
        $sql = "INSERT INTO product_bookmarks (user_id, product_id) VALUES (?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ii', $userId, $productId);

        // Execute the query and return a response
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Product bookmarked successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to bookmark the product.'];
        }
    }
    
    public function getBookmarks($userId)
    {
        // Ensure the user is logged in
        if (!$userId) {
            return ['status' => 'error', 'message' => 'User must be logged in to fetch bookmarks.'];
        }
    
        // Get the database connection
        $connection = getDbConnection();
    
        // Query to fetch the list of product IDs the user has bookmarked
        $sql = "SELECT product_id FROM product_bookmarks WHERE user_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($productId);
    
        // Initialize an array to store the product IDs
        $bookmarkedProducts = [];
        while ($stmt->fetch()) {
            $bookmarkedProducts[] = $productId;
        }
    
        // If no bookmarks are found, return the response
        if (count($bookmarkedProducts) == 0) {
            return ['status' => 'error', 'message' => 'No bookmarks found for this user.'];
        }
    
        // Prepare SQL to fetch details of the bookmarked products
        $productIds = implode(',', $bookmarkedProducts); // Convert the array of product IDs to a string for the query
    
        $sql = "
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_description, p.price, p.color, p.product_condition, p.brand_id,
                   b.brand_name, d.city, p.product_image, p.is_featured, p.product_features, p.top_features, p.stand_out_features, p.inspection_request,
                   p.inspection_status, p.created_at, p.updated_at, pi.image_id, pi.image_url, pi.is_primary, pa.pf_id, pa.pf_name, pav.value,
                   pca.custom_attribute_id, pca.attribute_name, pca.attribute_value
            FROM products p
            LEFT JOIN product_images pi ON p.product_id = pi.product_id
            LEFT JOIN product_publish pub ON p.product_id = pub.product_id
            LEFT JOIN product_attributes pa ON p.category_id = pa.category_id
            LEFT JOIN product_attributes_value pav ON pa.pf_id = pav.attribute_id AND p.product_id = pav.product_id
            LEFT JOIN product_custom_attributes pca ON p.product_id = pca.product_id
            LEFT JOIN brands b ON p.brand_id = b.brand_id
            LEFT JOIN dealers d ON p.dealer_id = d.user_id
            WHERE p.product_id IN ($productIds)
            AND pub.website = 1
        ";
    
        $result = $connection->query($sql);
    
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $productId = $row['product_id'];
    
            if (!isset($products[$productId])) {
                $products[$productId] = [
                    'product_id' => $row['product_id'],
                    'dealer_id' => $row['dealer_id'],
                    'category_id' => $row['category_id'],
                    'product_name' => $row['product_name'],
                    'product_description' => $row['product_description'],
                    'price' => number_format($row['price'], 2),  // Ensure price is formatted
                    'color' => $row['color'],
                    'product_condition' => $row['product_condition'],
                    'brand_id' => $row['brand_id'],
                    'brand_name' => $row['brand_name'],
                    'city' => $row['city'],
                    'product_image' => IMAGES_URL . $row['product_image'],
                    'is_featured' => $row['is_featured'],
                    'product_features' => $row['product_features'],  // Convert product features to an array
                    'top_features' => $row['top_features'],
                    'stand_out_features' => $row['stand_out_features'],
                    'inspection_request' => $row['inspection_request'],
                    'inspection_status' => $row['inspection_status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'images' => [],
                    'combined_attributes' => [],
                    'custom_attributes' => [],
                    'publish_info' => [
                        'marketplace' => 0,
                        'website' => 1,
                        'own_website' => 1
                    ]
                ];
            }
    
            // Handle images - Avoid duplicates using associative arrays
            if ($row['image_id'] && !isset($products[$productId]['images'][$row['image_id']])) {
                $products[$productId]['images'][$row['image_id']] = [
                    'image_id' => $row['image_id'],
                    'image_url' => IMAGES_URL . $row['image_url'],
                    'is_primary' => $row['is_primary'],
                ];
            }
    
            // Handle combined attributes - Avoid duplicates using associative arrays
            if ($row['pf_id'] && !isset($products[$productId]['combined_attributes'][$row['pf_id']])) {
                $products[$productId]['combined_attributes'][$row['pf_id']] = [
                    'pf_id' => $row['pf_id'],
                    'category_id' => $row['category_id'],
                    'pf_name' => $row['pf_name'],
                    'value' => $row['value'],
                ];
            }
    
            // Handle custom attributes - Avoid duplicates using associative arrays
            if ($row['custom_attribute_id'] && !isset($products[$productId]['custom_attributes'][$row['custom_attribute_id']])) {
                $products[$productId]['custom_attributes'][$row['custom_attribute_id']] = [
                    'custom_attribute_id' => $row['custom_attribute_id'],
                    'attribute_name' => $row['attribute_name'],
                    'attribute_value' => $row['attribute_value'],
                ];
            }
        }
    
        // Return the result
        return [
            'status' => 200,
            'message' => 'Bookmarks fetched successfully.',
            'data' => array_values($products)  // Return the products as an indexed array
        ];
    }

}
