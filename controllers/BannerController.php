<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class BannerController
{

    //get banners   
    public function getBanners()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM banners WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $banners = [];
            while ($row = $result->fetch_assoc()) {
                $banners[] = $row;
            }
            return ['statuscode' => 200, 'status' => 'success', 'banners' => $banners];
        }
        return ['statuscode' => 401, 'status' => 'error', 'message' => 'No banners found.'];
    }
}
