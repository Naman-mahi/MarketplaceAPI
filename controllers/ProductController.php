<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

// /controllers/ProductController.php
class ProductController
{
    public function createProduct($name, $price)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
        $stmt->bind_param("sd", $name, $price);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Product created.'];
        }
        return ['status' => 'error', 'message' => 'Product creation failed.'];
    }

    public function getProducts()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_image, p.product_condition, p.brand_id, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
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
                        'product_image' => $row['product_image'],
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
                        'image_url' => $row['image_url'],
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
            SELECT p.product_id, p.dealer_id, p.category_id, p.product_name, p.product_description, p.price, p.color, p.top_features, p.stand_out_features, p.created_at, p.updated_at,
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
            WHERE p.product_id = ?
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
                        'image_url' => $row['image_url'],
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
}
