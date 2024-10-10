<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class UserController
{

    public function register($email, $password, $firstName, $lastName, $mobileNumber, $referralCode = null)
    {
        $connection = getDbConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO users (email, password_hash, first_name, last_name, mobile_number, referral_code, user_status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
        $stmt->bind_param("ssssss", $email, $hashedPassword, $firstName, $lastName, $mobileNumber, $referralCode);
        if ($stmt->execute()) {
            return ['statuscode' => 200, 'status' => 'success', 'message' => 'User registered successfully.'];
        }
        return ['statuscode' => 500, 'status' => 'error', 'message' => 'Registration failed.'];
    }

    public function login($email, $password)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT email, user_status, password_hash, first_name, last_name, mobile_number, profile_pic, referral_code FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['statuscode' => 404, 'status' => 'error', 'message' => 'Email address not found.'];
        }

        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password_hash'])) {
            return ['statuscode' => 401, 'status' => 'error', 'message' => 'Incorrect password.'];
        }

        if ($user['user_status'] !== 'active') {
            return ['statuscode' => 401, 'status' => 'error', 'message' => 'Account is not active.'];
        }

        unset($user['password_hash']);
        return ['statuscode' => 200, 'status' => 'success', 'message' => 'Login successful.', 'user' => $user];
    }

    public function getUserProfile($userId)
    {
        $connection = getDbConnection();

        // First, fetch user data
        $stmt = $connection->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if the user is a dealer
            if ($user['role'] === 'dealer') {
                // Fetch dealer data
                $dealerStmt = $connection->prepare("
                    SELECT *
                    FROM `dealers` 
                    WHERE user_id = ?
                ");
                $dealerStmt->bind_param("i", $userId);
                $dealerStmt->execute();
                $dealerResult = $dealerStmt->get_result();

                if ($dealerResult->num_rows === 1) {
                    $dealerData = $dealerResult->fetch_assoc();
                    $user = array_merge($user, $dealerData);
                }
            }

            return ['statuscode' => 200, 'status' => 'success', 'user' => $user];
        }
        return ['statuscode' => 404, 'status' => 'error', 'message' => 'User not found.'];
    }

    public function updateUserProfile($userId, $updateData)
    {
        $connection = getDbConnection();
        $allowedFields = ['first_name', 'last_name', 'mobile_number', 'profile_pic'];
        $updates = [];
        $types = '';
        $values = [];

        foreach ($updateData as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $types .= 's';
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return ['statuscode' => 400, 'status' => 'error', 'message' => 'No valid fields to update.'];
        }

        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = ?";
        $stmt = $connection->prepare($sql);
        $types .= 'i';
        $values[] = $userId;
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return ['statuscode' => 200, 'status' => 'success', 'message' => 'Profile updated successfully.'];
        }
        return ['statuscode' => 500, 'status' => 'error', 'message' => 'Profile update failed.'];
    }
    //forgot password
    public function forgotPassword($email)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['statuscode' => 404, 'status' => 'error', 'message' => 'Email address not found.'];
        }

        $user = $result->fetch_assoc();
        return ['statuscode' => 200, 'status' => 'success', 'message' => 'Password reset email sent.', 'user' => $user];
    }
    //reset password
    public function resetPassword($email, $password)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        if ($stmt->execute()) {
            return ['statuscode' => 200, 'status' => 'success', 'message' => 'Password reset successful.'];
        }
    }
    //fetch otp
    public function fetchOtp($email)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT otp FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            return ['statuscode' => 200, 'status' => 'success', 'message' => 'OTP fetched successfully.', 'otp' => $user['otp']];
        }
    }
    //verify otp
    public function verifyOtp($email, $otp)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT otp FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['otp'] === $otp) {
                return ['statuscode' => 200, 'status' => 'success', 'message' => 'OTP verified successfully.'];
            }
        }
        return ['statuscode' => 401, 'status' => 'error', 'message' => 'Invalid OTP.'];
    }
}
