<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class RewardsController
{
    public function createReward($referrerId, $referredId, $rewardAmount)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("INSERT INTO referral_rewards (referrer_id, referred_id, reward_amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iid", $referrerId, $referredId, $rewardAmount);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Reward created successfully.'];
        }
        return ['status' => 'error', 'message' => 'Reward creation failed.'];
    }

    public function getRewards()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT id, referrer_id, referred_id, reward_amount, created_at FROM referral_rewards");
        $stmt->execute();
        $result = $stmt->get_result();
        $rewards = [];
        while ($row = $result->fetch_assoc()) {
            $rewards[] = $row;
        }
        return ['status' => 'success', 'rewards' => $rewards];
    }

    public function updateReward($id, $referrerId, $referredId, $rewardAmount)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("UPDATE referral_rewards SET referrer_id = ?, referred_id = ?, reward_amount = ? WHERE id = ?");
        $stmt->bind_param("iidi", $referrerId, $referredId, $rewardAmount, $id);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Reward updated successfully.'];
        }
        return ['status' => 'error', 'message' => 'Reward update failed.'];
    }

    public function deleteReward($id)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("DELETE FROM referral_rewards WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Reward deleted successfully.'];
        }
        return ['status' => 'error', 'message' => 'Reward deletion failed.'];
    }
    //fetch rewards for a user
    public function getRewardsForUser($userId)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT id, referrer_id, referred_id, reward_amount, created_at FROM referral_rewards WHERE referrer_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ['status' => 'error', 'message' => 'No rewards found for this user.'];
        }
        $rewards = [];
        while ($row = $result->fetch_assoc()) {
            $rewards[] = $row;
        }
        return ['status' => 'success', 'rewards' => $rewards];

    }
}