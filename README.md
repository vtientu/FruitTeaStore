# Mộc Trà · WebPHP

Website mẫu bán trà trái cây bằng **PHP thuần 8.2+**, HTML, Tailwind CSS và JavaScript thuần. Không dùng React, Vite hoặc shadcn. CSS đã được build sẵn và commit trong `assets/css/app.css`, nên chạy website không cần Node.js hay Composer.

## Chạy bằng XAMPP trên Windows

1. Cài XAMPP có PHP 8.2 trở lên. Mở **XAMPP Control Panel** và Start **Apache**.
2. Mở `http://localhost/` để xác nhận Apache đang phục vụ website.
3. Đặt source vào **`C:\xampp\htdocs\WebPHP`**. File `index.php` phải nằm trực tiếp trong thư mục này.
4. Truy cập **`http://localhost/WebPHP/`**.

Clone trực tiếp bằng Git:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/vtientu/FruitTeaStore.git WebPHP
```

Nếu đã tải ZIP, giải nén và đổi tên thư mục thành `WebPHP`; tránh lồng thành `WebPHP\FruitTeaStore-main\index.php`.

**Lưu ý đường dẫn của lớp:** `C:\Xampp\localhost\WebPHP` chỉ hoạt động khi Apache đã được cấu hình DocumentRoot là `C:/Xampp/localhost`. XAMPP mặc định dùng `htdocs`, không dùng thư mục `localhost`. Xem **Apache → Config → httpd.conf**, kiểm tra `DocumentRoot` và khối `<Directory>` tương ứng; dùng đường dẫn do giảng viên cấu hình nếu có. Không cần thay cấu hình mặc định chỉ để chạy project.

MySQL chưa cần bật: bản này sử dụng sản phẩm mẫu và PHP session, chưa có database hoặc đăng nhập/admin.

### Nếu không chạy được

- Apache không khởi động: xem Apache Logs, kiểm tra cổng 80/443 có bị ứng dụng khác chiếm không. Nếu cấu hình cổng 8080, địa chỉ sẽ là `http://localhost:8080/WebPHP/`.
- Lỗi 404: kiểm tra DocumentRoot và vị trí `index.php`.
- Trình duyệt hiển thị mã PHP: truy cập qua Apache bằng URL phía trên, không mở file bằng `file://` hoặc Live Server.
- Không lưu được giỏ hàng: cho phép cookie; kiểm tra `session.save_path` trong `php.ini` tồn tại và có quyền ghi.

Môi trường thực thi của tác vụ là Linux, không có quyền truy cập XAMPP trên máy Windows cá nhân. Đã kiểm tra bằng PHP 8.4 built-in server ở đường dẫn con `/WebPHP/`; việc xác nhận Apache/XAMPP Windows thực tế cần thực hiện theo các bước trên.

## Chạy bằng PHP CLI

Tại thư mục project:

```sh
php -S 127.0.0.1:8080
```

Mở `http://127.0.0.1:8080/`. Không cần rewrite URL hoặc virtual host; điều hướng dùng `index.php?page=...`.

## Cấu trúc

```text
WebPHP/
├── index.php                  # Entry point và điều phối template
├── app/
│   ├── bootstrap.php          # Session, route whitelist, khởi tạo
│   ├── products.php           # Dữ liệu sản phẩm mẫu
│   ├── cart.php               # Quy tắc giá/phí giao hàng, kiểm tra dữ liệu
│   ├── actions.php            # Xử lý POST: giỏ hàng và đặt đơn
│   └── helpers.php            # Escape HTML, URL, CSRF, render, redirect
├── templates/
│   ├── layout/                # Header và footer
│   ├── components/            # Ly trà, card sản phẩm, tóm tắt đơn
│   └── pages/                 # Home, menu, product, cart, checkout, orders, story
├── assets/
│   ├── css/app.css            # CSS đã build, dùng trực tiếp trên XAMPP
│   └── js/app.js              # Cập nhật giá hiển thị; không bắt buộc để đặt đơn
├── resources/                 # Source Tailwind và minh họa ly trà
└── tests/cart_test.php         # Kiểm tra tính giá và validation
```

## Chức năng và giới hạn

- Tìm/lọc sản phẩm, chọn size/đường/đá/topping, thêm/sửa/xóa giỏ hàng, tính phí giao hàng, đặt đơn COD mẫu và xem đơn gần nhất.
- PHP session giữ giỏ hàng và đơn mẫu khi tải lại trang; chưa lưu lâu dài trong database. Session khác không dùng chung dữ liệu.
- Form vẫn hoạt động khi tắt JavaScript. JavaScript chỉ giúp cập nhật giá hiển thị trước khi gửi; giá thật luôn tính lại ở PHP theo sản phẩm mẫu.
- Có kiểm tra input phía server, CSRF token và escape dữ liệu xuất ra HTML. Số lượng tối đa 20 ly mỗi dòng.
- Chưa có admin, đăng nhập, thanh toán online, gửi đơn thật hoặc cập nhật vận chuyển.
- Font Google có fallback khi không truy cập mạng. `.htaccess` dành cho Apache; built-in server không đọc các quy tắc này.

## Phát triển giao diện (tùy chọn)

Chỉ cần Node.js khi sửa source Tailwind, không cần cho việc chạy PHP:

```sh
npm ci
npm run build:css
npm run watch:css
```

Sau khi thay class trong template PHP, chạy lại `npm run build:css` và commit cả CSS đầu ra. Form controls dùng HTML chuẩn với Tailwind; shadcn/ui phụ thuộc React nên không được giữ lại trong bản PHP thuần.

```sh
npm run format
npm run format:check
php tests/cart_test.php
```
