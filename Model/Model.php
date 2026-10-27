<?php
require_once __DIR__ . '/Connect.php';

class data_shop
{
    // =========================
    // SẢN PHẨM
    // =========================
    public function select_shop()
    {
        $sql =
            'SELECT p.*,c.name AS category FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC';
        return query($sql);
    }

    public function select_shop_id($id)
    {
        $sql = 'SELECT * FROM products WHERE id=?';
        return query($sql, [$id]);
    }

    public function select_available_products($lock = false)
    {
        $sql =
            'SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id=p.category_id WHERE p.active=1 AND c.active=1 ORDER BY p.id' .
            ($lock ? ' FOR UPDATE' : '');
        return query($sql);
    }

    public function insert_shop(
        $category_id,
        $name,
        $description,
        $price,
        $color,
        $fruit,
        $tag,
        $type,
        $active,
    ) {
        $sql =
            'INSERT INTO products(category_id,name,description,price,color,fruit,tag,type,active) VALUES(?,?,?,?,?,?,?,?,?)';
        return query($sql, [
            $category_id,
            $name,
            $description,
            $price,
            $color,
            $fruit,
            $tag,
            $type,
            $active,
        ]);
    }

    public function update_shop_id(
        $category_id,
        $name,
        $description,
        $price,
        $color,
        $fruit,
        $tag,
        $type,
        $active,
        $id,
    ) {
        $sql =
            'UPDATE products SET category_id=?,name=?,description=?,price=?,color=?,fruit=?,tag=?,type=?,active=? WHERE id=?';
        return query($sql, [
            $category_id,
            $name,
            $description,
            $price,
            $color,
            $fruit,
            $tag,
            $type,
            $active,
            $id,
        ]);
    }

    public function hide_item($section, $params = [])
    {
        if (!in_array($section, ['products', 'categories', 'toppings', 'vouchers'], true)) {
            throw new InvalidArgumentException('Mục không hợp lệ.');
        }
        $sql = 'UPDATE ' . $section . ' SET active=0 WHERE id=?';
        return query($sql, $params);
    }

    // =========================
    // DANH MỤC VÀ TOPPING
    // =========================
    public function select_categories()
    {
        $sql = 'SELECT * FROM categories ORDER BY id';
        return query($sql);
    }

    public function select_active_categories()
    {
        $sql = 'SELECT name FROM categories WHERE active=1 ORDER BY id';
        return query($sql);
    }

    public function select_category_id($id)
    {
        $sql = 'SELECT id FROM categories WHERE id=?';
        return query($sql, [$id]);
    }

    public function select_catalog($entity)
    {
        if (!in_array($entity, ['categories', 'toppings'], true)) {
            throw new InvalidArgumentException('Danh mục không hợp lệ.');
        }
        $sql = 'SELECT * FROM ' . $entity . ' ORDER BY id DESC';
        return query($sql);
    }

    public function select_catalog_id($entity, $params = [])
    {
        if (!in_array($entity, ['categories', 'toppings'], true)) {
            throw new InvalidArgumentException('Danh mục không hợp lệ.');
        }
        $sql = 'SELECT * FROM ' . $entity . ' WHERE id=?';
        return query($sql, $params);
    }

    public function insert_category($name, $active)
    {
        $sql = 'INSERT INTO categories(name,active) VALUES(?,?)';
        return query($sql, [$name, $active]);
    }

    public function update_category($name, $active, $id)
    {
        $sql = 'UPDATE categories SET name=?,active=? WHERE id=?';
        return query($sql, [$name, $active, $id]);
    }

    public function select_available_toppings()
    {
        $sql = 'SELECT * FROM toppings WHERE active=1 ORDER BY id';
        return query($sql);
    }

    public function select_toppings_for_update()
    {
        $sql = 'SELECT * FROM toppings WHERE active=1 ORDER BY id FOR UPDATE';
        return query($sql);
    }

    public function insert_topping($name, $price, $active)
    {
        $sql = 'INSERT INTO toppings(name,price,active) VALUES(?,?,?)';
        return query($sql, [$name, $price, $active]);
    }

    public function update_topping($name, $price, $active, $id)
    {
        $sql = 'UPDATE toppings SET name=?,price=?,active=? WHERE id=?';
        return query($sql, [$name, $price, $active, $id]);
    }

    // =========================
    // VOUCHER
    // =========================
    public function select_vouchers()
    {
        $sql = 'SELECT * FROM vouchers ORDER BY id DESC';
        return query($sql);
    }

    public function select_voucher_id($id)
    {
        $sql = 'SELECT * FROM vouchers WHERE id=?';
        return query($sql, [$id]);
    }

    public function select_voucher_code($lock, $params = [])
    {
        $sql = 'SELECT * FROM vouchers WHERE code=?' . ($lock ? ' FOR UPDATE' : '');
        return query($sql, $params);
    }

    public function select_voucher_usage($id)
    {
        $sql = 'SELECT used_count FROM vouchers WHERE id=?';
        return query($sql, [$id]);
    }

    public function insert_voucher(
        $code,
        $kind,
        $value,
        $min_total,
        $expires_on,
        $max_uses,
        $active,
    ) {
        $sql =
            'INSERT INTO vouchers(code,kind,value,min_total,expires_on,max_uses,active) VALUES(?,?,?,?,?,?,?)';
        return query($sql, [$code, $kind, $value, $min_total, $expires_on, $max_uses, $active]);
    }

    public function update_voucher(
        $code,
        $kind,
        $value,
        $min_total,
        $expires_on,
        $max_uses,
        $active,
        $id,
    ) {
        $sql =
            'UPDATE vouchers SET code=?,kind=?,value=?,min_total=?,expires_on=?,max_uses=?,active=? WHERE id=?';
        return query($sql, [
            $code,
            $kind,
            $value,
            $min_total,
            $expires_on,
            $max_uses,
            $active,
            $id,
        ]);
    }

    public function use_voucher($code)
    {
        $sql = 'UPDATE vouchers SET used_count=used_count+1 WHERE code=?';
        return query($sql, [$code]);
    }

    // =========================
    // ĐƠN HÀNG
    // =========================
    public function select_filtered_orders($status, $search)
    {
        $conditions = [];
        $params = [];
        if (
            in_array($status, ['pending', 'preparing', 'shipping', 'completed', 'cancelled'], true)
        ) {
            $conditions[] = 'o.status=?';
            $params[] = $status;
        }
        if ($search !== '') {
            $conditions[] = '(o.code LIKE ? OR o.recipient_name LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        $sql =
            'SELECT o.* FROM orders o' .
            ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') .
            ' ORDER BY o.id DESC';
        return query($sql, $params);
    }

    public function select_order_id($id)
    {
        $sql = 'SELECT * FROM orders WHERE id=?';
        return query($sql, [$id]);
    }

    public function select_user_orders($user_id)
    {
        $sql = 'SELECT * FROM orders WHERE user_id=? ORDER BY id DESC';
        return query($sql, [$user_id]);
    }

    public function select_customer_order($id, $user_id)
    {
        $sql = 'SELECT id FROM orders WHERE id=? AND user_id=?';
        return query($sql, [$id, $user_id]);
    }

    public function select_checkout_order($checkout_token, $user_id)
    {
        $sql = 'SELECT id FROM orders WHERE checkout_token=? AND user_id=?';
        return query($sql, [$checkout_token, $user_id]);
    }

    public function select_order_items($order_id)
    {
        $sql = 'SELECT * FROM order_items WHERE order_id=? ORDER BY id';
        return query($sql, [$order_id]);
    }

    public function select_order_status($id)
    {
        $sql = 'SELECT status FROM orders WHERE id=?';
        return query($sql, [$id]);
    }

    public function insert_order(
        $code,
        $checkout_token,
        $user_id,
        $recipient_name,
        $phone,
        $address,
        $subtotal,
        $discount,
        $shipping_fee,
        $total,
        $voucher_code,
        $email,
    ) {
        $sql =
            'INSERT INTO orders(code,checkout_token,user_id,recipient_name,phone,address,subtotal,discount,shipping_fee,total,voucher_code,email) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)';
        return query($sql, [
            $code,
            $checkout_token,
            $user_id,
            $recipient_name,
            $phone,
            $address,
            $subtotal,
            $discount,
            $shipping_fee,
            $total,
            $voucher_code,
            $email,
        ]);
    }

    public function insert_order_item(
        $order_id,
        $product_id,
        $product_name,
        $options_json,
        $unit_price,
        $quantity,
    ) {
        $sql =
            'INSERT INTO order_items(order_id,product_id,product_name,options_json,unit_price,quantity) VALUES(?,?,?,?,?,?)';
        return query($sql, [
            $order_id,
            $product_id,
            $product_name,
            $options_json,
            $unit_price,
            $quantity,
        ]);
    }

    public function update_order_status($status, $id, $old_status)
    {
        $sql = 'UPDATE orders SET status=? WHERE id=? AND status=?';
        return query($sql, [$status, $id, $old_status]);
    }

    public function cancel_order($id, $user_id)
    {
        $sql = "UPDATE orders SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'";
        return query($sql, [$id, $user_id]);
    }

    // =========================
    // ĐÁNH GIÁ
    // =========================
    public function select_reviews($product_id)
    {
        $sql =
            'SELECT r.*,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? ORDER BY r.created_at DESC LIMIT 20';
        return query($sql, [$product_id]);
    }

    public function count_reviews($product_id)
    {
        $sql = 'SELECT COUNT(*) AS count,AVG(rating) AS average FROM reviews WHERE product_id=?';
        return query($sql, [$product_id]);
    }

    public function select_completed_purchase($user_id, $product_id)
    {
        $sql =
            "SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.user_id=? AND oi.product_id=? AND o.status='completed' LIMIT 1";
        return query($sql, [$user_id, $product_id]);
    }

    public function select_user_review($user_id, $product_id)
    {
        $sql = 'SELECT * FROM reviews WHERE user_id=? AND product_id=?';
        return query($sql, [$user_id, $product_id]);
    }

    public function save_review($user_id, $product_id, $rating, $comment)
    {
        $sql =
            'INSERT INTO reviews(user_id,product_id,rating,comment) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),comment=VALUES(comment)';
        return query($sql, [$user_id, $product_id, $rating, $comment]);
    }

    // =========================
    // THỐNG KÊ
    // =========================
    public function count_orders()
    {
        $sql =
            "SELECT COUNT(*) AS orders, COALESCE(SUM(status='pending'),0) AS pending, COALESCE(SUM(CASE WHEN status='completed' THEN subtotal-discount ELSE 0 END),0) AS revenue FROM orders";
        return query($sql);
    }

    public function revenue_by_day()
    {
        $sql =
            "SELECT DATE(created_at) AS day, SUM(subtotal-discount) AS revenue FROM orders WHERE status='completed' AND created_at>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY day";
        return query($sql);
    }

    public function select_best_sellers()
    {
        $sql =
            "SELECT oi.product_name,SUM(oi.quantity) AS quantity FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.status='completed' GROUP BY oi.product_id,oi.product_name ORDER BY quantity DESC LIMIT 5";
        return query($sql);
    }

    public function select_recent_orders()
    {
        $sql = 'SELECT * FROM orders ORDER BY id DESC LIMIT 8';
        return query($sql);
    }
}

// =========================
// TÀI KHOẢN VÀ PHÂN QUYỀN
// =========================
class data_user
{
    public function count_admin()
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role='admin'";
        return query($sql);
    }

    public function login($email)
    {
        $sql = 'SELECT * FROM users WHERE email=? AND active=1';
        return query($sql, [$email]);
    }

    public function insert_user($name, $email, $password_hash)
    {
        $sql = 'INSERT INTO users(name,email,password_hash) VALUES(?,?,?)';
        return query($sql, [$name, $email, $password_hash]);
    }

    public function check_user($email)
    {
        $sql = 'SELECT id FROM users WHERE email=?';
        return query($sql, [$email]);
    }

    public function update_user_status($active, $id)
    {
        $sql = "UPDATE users SET active=? WHERE id=? AND role='customer'";
        return query($sql, [$active, $id]);
    }

    public function select_active_user($id)
    {
        $sql = 'SELECT id,name,email,role FROM users WHERE id=? AND active=1';
        return query($sql, [$id]);
    }

    public function count_customers()
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role='customer'";
        return query($sql);
    }

    public function select_users($name, $email)
    {
        $sql =
            'SELECT id,name,email,role,active,created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC';
        return query($sql, [$name, $email]);
    }
}

$data = new data_shop();
$account = new data_user();
