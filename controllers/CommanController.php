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
        // Execute the SQL query to fetch the necessary brand details
        $stmt = $connection->prepare("SELECT brand_id, brand_name, brand_logo, category_id, created_date FROM brands WHERE 1");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $brands = [];
    
        // Loop through each row of the results
        while ($row = $result->fetch_assoc()) {
            // Check if the brand logo is not empty, then append the full URL
            $row['brand_logo'] = !empty($row['brand_logo']) ? BRAND_URL . $row['brand_logo'] : null;
            
            // Add the row to the brands array
            $brands[] = $row;
        }
    
        // Return the response with status and data
        return [
            'status' => 200,
            'message' => 'Brands fetched successfully',
            'data' => $brands
        ];
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
                            'product_image' => 'https://kenzwheels.com/marketplace/manage/uploads/ProductThumbnail/'.$row['product_image'],
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
                            'image_url' => 'https://kenzwheels.com/marketplace/manage/uploads/ProductImages/'.$row['image_url'],
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
    
        // Fetch the result as an associative array
        $data = $result->fetch_assoc();
    
        // Define valid values for each field (you can extend this list)
        $valid_values = [
            'engine_oil_level' => ['Ok', 'Low', 'Black'],
            'engine_oil_leakage' => ['No Leakage', 'Leakage'],
            'transmission_oil_leakage' => ['No Leakage', 'Leakage'],
            'transfer_case_oil_leakage' => ['No Leakage', 'Leakage'],
            'coolant_leakage' => ['No Leakage', 'Leakage'],
            'brake_oil_leakage' => ['No Leakage', 'Leakage'],
            'power_steering_oil_leakage' => ['No Leakage', 'Leakage'],
            'differential_oil_leakage' => ['No Leakage', 'Leakage'],
            'fan_belt_condition' => ['Ok', 'Worn Out', 'Broken'],
            'engine_noise' => ['No Noise', 'Clunking', 'Rattling'],
            'engine_vibration' => ['No Vibration', 'Vibrating'],
            'exhaust_sound' => ['Ok', 'Loud', 'Clogged'],
            'radiator_condition' => ['Ok', 'Leaking', 'Damaged'],
            'transmission_electronics' => ['Ok', 'Faulty'],
            'front_right_disc_condition' => ['Ok', 'Damaged', 'Worn Out'],
            'front_left_disc_condition' => ['Ok', 'Damaged', 'Worn Out'],
            'front_right_brake_pad_condition' => ['Ok', 'Worn Out'],
            'front_left_brake_pad_condition' => ['Ok', 'Worn Out'],
            'steering_wheel_play' => ['Ok', 'Loose'],
            'ball_joints_condition' => ['Ok', 'Worn Out'],
            'z_links_condition' => ['Ok', 'Damaged'],
            'tie_rod_ends_condition' => ['Ok', 'Worn Out'],
            'shock_absorbers_condition' => ['Ok', 'Leaking'],
            'rear_suspension_bushes_condition' => ['No Damage Found', 'Damaged'],
            'rear_shocks_condition' => ['Ok', 'Leaking'],
            'steering_wheel_condition' => ['Ok', 'Loose'],
            'seats_electric_function' => ['Working', 'Not Working'],
            'seat_belts_condition' => ['Working', 'Worn Out'],
            'windows_condition' => ['Working Properly', 'Not Working'],
            'dash_controls_condition' => ['Working', 'Not Working'],
            'audio_video_condition' => ['Working', 'Not Working'],
            'rear_view_camera_condition' => ['Working', 'Not Working'],
            'trunk_bonnet_release_condition' => ['Working', 'Not Working'],
            'sun_roof_control_condition' => ['Working', 'Not Working'],
            'ac_operational' => ['Yes', 'No'],
            'blower_air_throw_condition' => ['Excellent', 'Good', 'Weak'],
            'cooling_condition' => ['Excellent', 'Good', 'Weak'],
            'heating_condition' => ['Excellent', 'Good', 'Weak'],
            'warning_lights_condition' => ['ABS Warning Light Present', 'No Warning Lights'],
            'battery_condition' => ['12V, Terminals Condition Ok', 'Faulty'],
            'instrument_cluster_condition' => ['Gauges Working', 'Not Working'],
            'trunk_lock_condition' => ['Ok', 'Faulty'],
            'windshield_condition' => ['Chip', 'Cracked', 'Ok'],
            'window_condition' => ['Ok', 'Cracked'],
            'headlights_condition' => ['Working', 'Not Working'],
            'taillights_condition' => ['Working', 'Not Working'],
            'fog_lights_condition' => ['Working', 'Not Working'],
            'tyre_brand' => ['Michelin', 'Bridgestone', 'Goodyear'],
            'tyre_tread' => ['7.0mm', '6.0mm', '5.0mm'],
            'tyre_size' => ['275/50/R21', '225/60/R16'],
            'rims_condition' => ['Alloy', 'Steel'],
            'engine_pick_feedback' => ['Ok', 'Weak', 'Powerful'],
            'gear_shifting_feedback' => ['Smooth', 'Stiff', 'Laggy'],
            'brake_pedal_operation_feedback' => ['Timely Response', 'Delayed Response', 'Hard Pedal'],
            'abs_operation_feedback' => ['Timely Response', 'Delayed Response', 'No ABS'],
            'suspension_noise_feedback' => ['No Noise', 'Clunking Noise', 'Squeaking Noise'],
            'steering_operation_feedback' => ['Smooth', 'Heavy', 'Loose'],
            'ac_heater_feedback' => ['Perfect', 'Weak', 'Not Working']
        ];
    
        // Initialize counters for total and filled fields
        $total_fields = count($valid_values);
        $filled_fields = 0;
    
        // Loop through each field and check if the value matches one of the valid values
        foreach ($valid_values as $field => $valid_opts) {
            if (in_array($data[$field], $valid_opts)) {
                $filled_fields++;
            }
        }
    
        // Calculate the percentage of completion
        $completion_percentage = ($filled_fields / $total_fields) * 100;
        $category_percentages = [
            'ENGINE / TRANSMISSION / CLUTCH' => 0,
            'BRAKES' => 0,
            'SUSPENSION/STEERING' => 0,
            'INTERIOR' => 0,
            'AC/HEATER' => 0,
            'ELECTRICAL & ELECTRONICS' => 0,
            'EXTERIOR & BODY' => 0,
            'TYRES' => 0
        ];
    
        // ENGINE / TRANSMISSION / CLUTCH (Engine oil, transmission oil, clutch feedback)
        if ($data['engine_oil_level'] == 'Ok' && $data['transmission_oil_leakage'] == 'No Leakage') {
            $category_percentages['ENGINE / TRANSMISSION / CLUTCH'] = 95;
        }
    
        // BRAKES (Brake oil, disc condition, brake pads)
        if ($data['front_right_disc_condition'] == 'Damaged' || $data['front_left_disc_condition'] == 'Damaged') {
            $category_percentages['BRAKES'] = 75; // Adjust based on conditions
        } else {
            $category_percentages['BRAKES'] = 100;
        }
    
        // SUSPENSION/STEERING (Suspension noise, steering operation)
        if ($data['suspension_noise_feedback'] == 'No Noise' && $data['steering_operation_feedback'] == 'Smooth') {
            $category_percentages['SUSPENSION/STEERING'] = 99;
        } else {
            $category_percentages['SUSPENSION/STEERING'] = 80; // Adjust based on feedback
        }
    
        // INTERIOR (Seats, seat belts, windows, dashboard)
        if ($data['seats_electric_function'] == 'Working (Right & Left Front)' && $data['windows_condition'] == 'Working Properly (All 4 Windows)') {
            $category_percentages['INTERIOR'] = 75;
        }
    
        // AC/HEATER (AC and heater condition)
        if ($data['ac_operational'] == 'Yes' && $data['blower_air_throw_condition'] == 'Excellent') {
            $category_percentages['AC/HEATER'] = 84;
        }
    
        // ELECTRICAL & ELECTRONICS (Lights, audio, battery, instruments)
        if ($data['warning_lights_condition'] == 'ABS Warning Light Present') {
            $category_percentages['ELECTRICAL & ELECTRONICS'] = 85; // Adjust if warning light is present
        } else {
            $category_percentages['ELECTRICAL & ELECTRONICS'] = 99;
        }
    
        // EXTERIOR & BODY (Body condition, windows, headlights, taillights)
        if ($data['windshield_condition'] == 'Chip (Front)') {
            $category_percentages['EXTERIOR & BODY'] = 33;
        } else {
            $category_percentages['EXTERIOR & BODY'] = 100;
        }
    
        // TYRES (Tyre condition)
        if ($data['tyre_tread'] == '7.0mm (Remaining)') {
            $category_percentages['TYRES'] = 75;
        } else {
            $category_percentages['TYRES'] = 50;
        }
    
        // Calculate the overall percentage (simple average)
        $total_percentage = array_sum($category_percentages);
        $total_categories = count($category_percentages);
        $overall_percentage = round($total_percentage / $total_categories, 2);


        // Return the response with the percentage included
        return [
            'status' => 200,
            'message' => 'Inspected report fetched successfully',
            'data' => $data,
            'completion_percentage' => round($completion_percentage, 2), // rounding to 2 decimal places
            'overall_percentage' => $overall_percentage,
            'category_percentages' => $category_percentages
        ];
    }
    

    public function getInspectedReportPoints($inspection_id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM inspection_car_points WHERE inspection_id = ?");
        $stmt->bind_param('i', $inspection_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
    
        while ($row = $result->fetch_assoc()) {
            $row['image_url'] = INSPECTION_URL . $row['image_url'];
            $data[] = $row;
        }
    
        if (count($data) > 0) {
            return [
                'status' => 200,
                'message' => 'Inspected report points fetched successfully',
                'data' => $data
            ];
        } else {
            return [
                'status' => 404,
                'message' => 'No inspected report points found'
            ];
        }
    }
    

        public function deleteuserData($user_id)
        {
            $connection = getDbConnection();
            
            // First copy the user data to deleted_users table
            $stmt = $connection->prepare("INSERT INTO deleted_users 
                (user_id, email, password_hash, first_name, last_name, mobile_number, 
                profile_pic, user_status, otp, created_at, referral_code, referred_by, 
                role_id, reset_token, reset_token_expiration)
                SELECT user_id, email, password_hash, first_name, last_name, mobile_number,
                profile_pic, user_status, otp, created_at, referral_code, referred_by,
                role_id, reset_token, reset_token_expiration 
                FROM users WHERE user_id = ?");
            
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            // Then delete from users table
            $stmt = $connection->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            
            if($stmt->affected_rows > 0) {
                return [
                    'status' => 200,
                    'message' => 'User data backed up and deleted successfully'
                ];
            } else {
                return [
                    'status' => 404, 
                    'message' => 'User not found'
                ];
            }
        }
    }
