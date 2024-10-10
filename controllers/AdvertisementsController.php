<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class AdvertisementsController
{
    public function createAdvertisement($title, $description, $image, $link, $startDate, $endDate)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("INSERT INTO advertisements (title, description, image, link, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $description, $image, $link, $startDate, $endDate);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Advertisement created successfully.'];
        }
        return ['status' => 'error', 'message' => 'Advertisement creation failed.'];
    }

    public function getAdvertisements()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT id, title, description, image, link, start_date, end_date FROM advertisements");
        $stmt->execute();
        $result = $stmt->get_result();
        $advertisements = [];
        while ($row = $result->fetch_assoc()) {
            $advertisements[] = $row;
        }
        return ['status' => 'success', 'advertisements' => $advertisements];
    }

    public function updateAdvertisement($id, $title, $description, $image, $link, $startDate, $endDate)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("UPDATE advertisements SET title = ?, description = ?, image = ?, link = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $title, $description, $image, $link, $startDate, $endDate, $id);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Advertisement updated successfully.'];
        }
        return ['status' => 'error', 'message' => 'Advertisement update failed.'];
    }

    public function deleteAdvertisement($id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("DELETE FROM advertisements WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Advertisement deleted successfully.'];
        }
        return ['status' => 'error', 'message' => 'Advertisement deletion failed.'];
    }

    public function getAdvertisementsForUser($userId)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT id, title, description, image, link, start_date, end_date FROM advertisements WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ['status' => 'error', 'message' => 'No advertisements found for this user.'];
        }
        $advertisements = [];
        while ($row = $result->fetch_assoc()) {
            $advertisements[] = $row;
        }
        return ['status' => 'success', 'advertisements' => $advertisements];
    }
}