<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../EmailSender.php');

class UserController
{

    public function register($email, $password, $firstName, $lastName, $mobileNumber, $referralCode = null)
    {
        $connection = getDbConnection();

        try {
            // Start transaction
            $connection->begin_transaction();

            // Check if email already exists
            $checkEmail = $connection->prepare("SELECT email FROM users WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            if ($checkEmail->get_result()->num_rows > 0) {
                return ['statuscode' => 400, 'status' => 'error', 'message' => 'Email already exists.'];
            }

            // Check if mobile number already exists
            $checkMobile = $connection->prepare("SELECT mobile_number FROM users WHERE mobile_number = ?");
            $checkMobile->bind_param("s", $mobileNumber);
            $checkMobile->execute();
            if ($checkMobile->get_result()->num_rows > 0) {
                return ['statuscode' => 400, 'status' => 'error', 'message' => 'Mobile number already exists.'];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role_id = 4;
            $referred_by = null;

            if ($referralCode) {
                $stmt = $connection->prepare("SELECT user_id FROM users WHERE referral_code = ?");
                $stmt->bind_param("s", $referralCode);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $referred_by = $result->fetch_assoc()['user_id'];
                }
            }

            $otp = rand(100000, 999999);

            $stmt = $connection->prepare("INSERT INTO users (email, password_hash, first_name, last_name, mobile_number, referred_by, role_id, user_status, created_at, otp) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)");
            $stmt->bind_param("ssssssis", $email, $hashedPassword, $firstName, $lastName, $mobileNumber, $referred_by, $role_id, $otp);

            if (!$stmt->execute()) {
                throw new Exception('Failed to insert user data');
            }
            $user_id = $connection->insert_id;

            $newReferralCode = 'REFKENZ0' . $user_id;
            $sqlUpdateUser = $connection->prepare("UPDATE users SET referral_code = ? WHERE user_id = ?");
            $sqlUpdateUser->bind_param("si", $newReferralCode, $user_id);
            if (!$sqlUpdateUser->execute()) {
                throw new Exception('Failed to update user referral code');
            }


            // Prepare email content
            $subject = "Welcome to Kenz Wheels, $firstName! Your OTP Code Inside";
            $body = '
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your OTP Code</title>
    <style>
      body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
      }
      .email-container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background-color: #ffffff;
      }
      .otp-code {
        font-size: 2rem;
        font-weight: bold;
        color: #0d6efd;
        padding: 10px;
        border: 2px solid #0d6efd;
        border-radius: 0.25rem;
        text-align: center;
        margin: 20px 0;
      }
      .footer {
        text-align: center;
        margin-top: 20px;
        font-size: 0.9rem;
        color: #6c757d;
      }
      h2, p {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="email-container">
      <h2>Welcome, ' . $firstName . '!</h2>
      <p>Thank you for registering with Kenz Wheels. Your OTP code is below:</p>
      <div class="otp-code">' . $otp . '</div>
      <p>Please enter this code to complete your registration.</p>
      <div class="footer">
        <p>If you did not request this, please ignore this email.</p>
        <p>Thank you for choosing Kenz Wheels!</p>
        <p>Kenz Wheels</p>
        <p>www.kenzwheels.com</p>
      </div>
    </div>
  </body>
</html>';

            // Send OTP via email
            if (!sendEmail($email, $subject, $body)) {
                throw new Exception('Failed to send OTP email');
            }

            // If everything succeeded, commit the transaction
            $connection->commit();

            return [
                'statuscode' => 200,
                'status' => 'success',
                'message' => 'Registration successful! An OTP has been sent to your email.'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $connection->rollback();
            return [
                'statuscode' => 500,
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    public function login($email, $password)
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
        $stmt = $connection->prepare("SELECT user_id, email, role_id, first_name, last_name, mobile_number, profile_pic, user_status, created_at, referral_code, referred_by FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if the user is a dealer
            if ($user['role_id'] === 2) {
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
    
        // Validate the email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['statuscode' => 400, 'status' => 'error', 'message' => 'Invalid email address.'];
        }
    
        // Check if the user exists
        $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // If no user found with that email address
        if ($result->num_rows === 0) {
            return ['statuscode' => 404, 'status' => 'error', 'message' => 'Email address not found.'];
        }
    
        // Get user information (in case you need to include their name in the email, etc.)
        $user = $result->fetch_assoc();
        $userId = $user['user_id'];
        $firstName = $user['first_name'];
    
        // Generate a unique password reset token
        $resetToken = bin2hex(random_bytes(32)); // 32-byte random token
        $tokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
        // Save the token and expiration time in the database
        $stmt = $connection->prepare("UPDATE users SET reset_token = ?, reset_token_expiration = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $resetToken, $tokenExpiration, $userId);
        if (!$stmt->execute()) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Database error: Could not save reset token.'];
        }
    
        // Prepare the reset password link
        $resetLink = "https://www.kenzwheels.com/reset-password.php?token=$resetToken";
    
        // Prepare email content
        $subject = "Password Reset Request";
        $body = '
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Password Reset Request</title>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #333; }
                    .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    h2 { text-align: center; }
                    p { font-size: 1.1rem; }
                    .footer { font-size: 0.9rem; text-align: center; color: #6c757d; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h2>Password Reset Request</h2>
                    <p>Hello ' . htmlspecialchars($firstName) . ',</p>
                    <p>We received a request to reset your password. You can reset your password by clicking the link below:</p>
                    <p><a href="' . $resetLink . '" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
                    <p>If you did not request a password reset, please ignore this email.</p>
                    <div class="footer">
                        <p>Thank you for using Kenz Wheels!</p>
                        <p>www.kenzwheels.com</p>
                    </div>
                </div>
            </body>
            </html>';
    
        // Send the reset password email
        if (!sendEmail($email, $subject, $body)) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Failed to send reset email.'];
        }
    
        return ['statuscode' => 200, 'status' => 'success', 'message' => 'Password reset email sent.'];
    }

    //reset password
    public function resetPassword($email, $password)
    {
        // Get the DB connection
        $connection = getDbConnection();
    
        // Validate the email format first (simple check)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['statuscode' => 400, 'status' => 'error', 'message' => 'Invalid email address'];
        }
    
        // Hash the password before saving it to the database (use password_hash for security)
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        // Prepare and execute the password update query
        $stmt = $connection->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        if ($stmt === false) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Database error: Unable to prepare statement'];
        }
    
        $stmt->bind_param("ss", $hashedPassword, $email);
        if (!$stmt->execute()) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Password reset failed: Database update error'];
        }
    
        // Now fetch the user's first name to include in the email
        $stmt = $connection->prepare("SELECT first_name FROM users WHERE email = ?");
        if ($stmt === false) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Database error: Unable to prepare statement'];
        }
    
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Database error: Unable to fetch user info'];
        }
    
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if (!$user) {
            return ['statuscode' => 404, 'status' => 'error', 'message' => 'User not found'];
        }
    
        $firstName = $user['first_name'];
    
        // Prepare the email content
        $subject = "Welcome to Kenz Wheels, $firstName! Your Password Reset";
        $body = '
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Your Password Reset</title>
                <style>
                    body {
                        background-color: #f8f9fa;
                        font-family: Arial, sans-serif;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 20px;
                        border: 1px solid #dee2e6;
                        border-radius: 0.5rem;
                        background-color: #ffffff;
                    }
                    h2, p {
                        text-align: center;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 20px;
                        font-size: 0.9rem;
                        color: #6c757d;
                    }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <h2>Welcome, ' . htmlspecialchars($firstName) . '!</h2>
                    <p>Thank you for resetting your password. Your password has been reset successfully.</p>
                    <div class="footer">
                        <p>If you did not request this, please ignore this email.</p>
                        <p>Thank you for choosing Kenz Wheels!</p>
                        <p>Kenz Wheels</p>
                        <p>www.kenzwheels.com</p>
                    </div>
                </div>
            </body>
            </html>';
    
        // Send the email using the sendEmail function
        if (!sendEmail($email, $subject, $body)) {
            return ['statuscode' => 500, 'status' => 'error', 'message' => 'Failed to send confirmation email'];
        }
    
        return ['statuscode' => 200, 'status' => 'success', 'message' => 'Password reset successful'];
    }
    

    //verify otp
    public function verifyOtp($email, $otp)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT otp, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if (!$stmt->execute()) {
            throw new Exception('Failed to fetch OTP');
        }
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $firstName = $user['first_name'];
            if ($user['otp'] == $otp) {
                $stmt = $connection->prepare("UPDATE users SET user_status = 'active' WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                if (!$stmt->execute()) {
                    throw new Exception('Failed to update user status');
                }
                // Prepare email content
                $subject = "Welcome to Kenz Wheels, $firstName! Your OTP Verified";
                $body = '
                    <!doctype html>
                    <html lang="en">
                    <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>Your OTP Verified</title>
                    <style>
                    body {
                    background-color: #f8f9fa;
                    font-family: Arial, sans-serif;
                    }
                    .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    padding: 20px;
                    border: 1px solid #dee2e6;
                    border-radius: 0.5rem;
                    background-color: #ffffff;
                    }
                    .otp-code {
                    font-size: 2rem;
                    font-weight: bold;
                    color: #0d6efd;
                    padding: 10px;
                    border: 2px solid #0d6efd;
                    border-radius: 0.25rem;
                    text-align: center;
                    margin: 20px 0;
                    }
                    .footer {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 0.9rem;
                    color: #6c757d;
                    }
                    h2, p {
                    text-align: center;
                    }
                    </style>
                    </head>
                    <body>
                    <div class="email-container">
                    <h2>Welcome, ' . $firstName . '!</h2>
                    <p>Thank you for verifying your OTP. Your account is now active.</p>
                    <div class="footer">
                    <p>If you did not request this, please ignore this email.</p>
                    <p>Thank you for choosing Kenz Wheels!</p>
                    <p>Kenz Wheels</p>
                    <p>www.kenzwheels.com</p>
                    </div>
                    </div>
                    </body>
                    </html>';

                // Send OTP via email
                if (!sendEmail($email, $subject, $body)) {
                    throw new Exception('Failed to verify OTP email');
                }

                return ['statuscode' => 200, 'status' => 'success', 'message' => 'OTP verified successfully.'];
            }
        }
        return ['statuscode' => 401, 'status' => 'error', 'message' => 'Invalid OTP. ' . $otp];
    }
}
