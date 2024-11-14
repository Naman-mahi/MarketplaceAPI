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
        $make = $connection->real_escape_string($make);
        // SQL query to get distinct cities and dealer count per city
        $stmt = $connection->prepare("
        SELECT *
        FROM products
        WHERE brand_id = ? ");

        $stmt->bind_param('s', $make);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch cities and dealer counts
        $cars = [];
        while ($row = $result->fetch_assoc()) {
            $cars[] = $row;
        }

        return [
            'status' => 200,
            'message' => 'Cars fetched successfully',
            'data' => $cars
        ];
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
