<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class BannerController
{
    public function getBanners()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM banners WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $banners = [];
            while ($row = $result->fetch_assoc()) {
                $bannerPath = BANNER_URL . $row['image'];
                $banners[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'image' => $row['image'],
                    'bannerPath' => $bannerPath,
                    'link' => $row['link'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            return [
                'statuscode' => 200,
                'status' => 'success',
                'banners' => $banners
            ];
        }
        return [
            'statuscode' => 401,
            'status' => 'error',
            'message' => 'No banners found.'
        ];
    }
}
