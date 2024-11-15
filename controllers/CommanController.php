<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

// /controllers/CommanController.php
class CommanController
{
    // list of brands
    public function getBrands()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM brands");
        $stmt->execute();
        $result = $stmt->get_result();
        return ['status' => 200, 'message' => 'Brands fetched successfully', 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }

    // list of cities from dealers table
    public function getCities()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT DISTINCT city FROM dealers WHERE 1");
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch all distinct cities
        $cities = [];
        while ($row = $result->fetch_assoc()) {
            $cities[] = $row['city'];
        }

        return [
            'status' => 200,
            'message' => 'Cities fetched successfully',
            'data' => $cities
        ];
    }

    // Get cities along with the dealer count in each city
    public function getCitiesWithDealerCount()
    {
        $connection = getDbConnection();

        // SQL query to get distinct cities and dealer count per city
        $stmt = $connection->prepare("
        SELECT city, COUNT(*) AS dealer_count
        FROM dealers
        GROUP BY city
    ");

        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch cities and dealer counts
        $cities = [];
        while ($row = $result->fetch_assoc()) {
            $cities[] = [
                'city' => $row['city'],
                'dealer_count' => $row['dealer_count']
            ];
        }

        return [
            'status' => 200,
            'message' => 'Cities and dealer counts fetched successfully',
            'data' => $cities
        ];
    }
    public function getCarByMake($make)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
                SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_description,p.brand_id, p.product_image, p.is_featured, p.product_features, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
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
                WHERE p.brand_id = ?
                ");
        $stmt->bind_param("s", $make);
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
                            'brand_id' => $row['brand_id'],
                            'product_image' => 'https://kenzwheels.com/marketplace/Manage/uploads/ProductThumbnail/'.$row['product_image'],
                            'top_features' => $row['top_features'],
                            'is_featured' => $row['is_featured'],
                            'product_features' => $row['product_features'],
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
                            'image_url' => 'https://kenzwheels.com/marketplace/Manage/uploads/ProductImages/'.$row['image_url'],
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
                return ['status' => 404, 'message' => 'No products found for this make'];
            }
        }
    

    public function getInspectedCars($product_id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT inspection_request,inspection_status FROM products WHERE product_id = ? AND inspection_request = 1");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return ['status' => 200, 'message' => 'Inspected cars fetched successfully', 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }

    public function getInspectedReport($product_id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM vehicle_inspection WHERE car_id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return ['status' => 200, 'message' => 'Inspected report fetched successfully', 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }
    public function getInspectedReportPoints($inspection_id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM inspection_car_points WHERE inspection_id = ?");
        $stmt->bind_param('i', $inspection_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return ['status' => 200, 'message' => 'Inspected report points fetched successfully', 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }
}
