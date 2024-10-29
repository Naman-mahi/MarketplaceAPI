<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class AdvertisementsController
{
    private $connection;

    public function __construct()
    {
        $this->connection = getDbConnection();
    }

    public function getAdvertisements()
    {
        $stmt = $this->connection->prepare("SELECT id, title, description, image, link, start_datetime, end_datetime, created_at, updated_at FROM advertisements");
        
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'SQL prepare failed: ' . $this->connection->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $advertisements = [];
        
        while ($row = $result->fetch_assoc()) {
            $advertisements[] = $row;
        }
        
        $stmt->close();
        
        return [
            'status' => 'success',
            'advertisements' => $advertisements
        ];
    }

    public function getAdvertisementById($advertisementId)
    {
        $stmt = $this->connection->prepare("SELECT id, title, description, image, link, start_datetime, end_datetime, created_at, updated_at FROM advertisements WHERE id = ?");
        $stmt->bind_param("i", $advertisementId);
        
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'SQL prepare failed: ' . $this->connection->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['status' => 'error', 'message' => 'Advertisement not found.'];
        }

        $advertisement = $result->fetch_assoc();
        $stmt->close();
        
        return [
            'status' => 'success',
            'advertisement' => $advertisement
        ];
    }

    public function deleteAdvertisement($id)
    {
        $stmt = $this->connection->prepare("DELETE FROM advertisements WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'SQL prepare failed: ' . $this->connection->error];
        }

        if ($stmt->execute()) {
            $stmt->close();
            return ['status' => 'success', 'message' => 'Advertisement deleted successfully.'];
        }

        $stmt->close();
        return ['status' => 'error', 'message' => 'Advertisement deletion failed.'];
    }

    public function getAdvertisementsForUser($userId)
    {
        $stmt = $this->connection->prepare("SELECT id, title, description, image, link, start_datetime, end_datetime, created_at, updated_at FROM advertisements WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'SQL prepare failed: ' . $this->connection->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return ['status' => 'error', 'message' => 'No advertisements found for this user.'];
        }

        $advertisements = [];
        while ($row = $result->fetch_assoc()) {
            $advertisements[] = $row;
        }

        $stmt->close();
        
        return [
            'status' => 'success',
            'advertisements' => $advertisements
        ];
    }
}
