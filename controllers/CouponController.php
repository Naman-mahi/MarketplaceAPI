<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/database.php');

class CouponController
{
    public function createCoupon($code, $discount, $expiration)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("INSERT INTO coupons (code, discount_value, expiration_date, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
        $stmt->bind_param("sds", $code, $discount, $expiration);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Coupon created successfully.'];
        }
        return ['status' => 'error', 'message' => 'Coupon creation failed.'];
    }

    public function applyCoupon($code)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT discount_value, expiration_date, status FROM coupons WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if ($row['status'] !== 'active') {
                return ['status' => 'error', 'message' => 'Coupon is not active.'];
            }
            if (strtotime($row['expiration_date']) < time()) {
                return ['status' => 'error', 'message' => 'Coupon has expired.'];
            }
            return ['status' => 'success', 'discount' => $row['discount_value']];
        }
        return ['status' => 'error', 'message' => 'Invalid coupon.'];
    }

    public function getCoupons()
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("SELECT coupon_id, code, discount_value, expiration_date, status FROM coupons");
        $stmt->execute();
        $result = $stmt->get_result();
        $coupons = [];
        while ($row = $result->fetch_assoc()) {
            $coupons[] = $row;
        }
        return ['status' => 'success', 'coupons' => $coupons];
    }

    public function updateCoupon($couponId, $code, $discount, $expiration, $status)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("UPDATE coupons SET code = ?, discount_value = ?, expiration_date = ?, status = ? WHERE coupon_id = ?");
        $stmt->bind_param("sdssi", $code, $discount, $expiration, $status, $couponId);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Coupon updated successfully.'];
        }
        return ['status' => 'error', 'message' => 'Coupon update failed.'];
    }

    public function deleteCoupon($couponId)
    {
        $connection = getDbConnection();
        $stmt = $connection->prepare("DELETE FROM coupons WHERE coupon_id = ?");
        $stmt->bind_param("i", $couponId);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Coupon deleted successfully.'];
        }
        return ['status' => 'error', 'message' => 'Coupon deletion failed.'];
    }
}
