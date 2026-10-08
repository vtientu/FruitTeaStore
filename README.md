# Mộc Trà · WebPHP

Website bán trà trái cây cho bài tập sinh viên, dùng **PHP thuần 8.2+, MySQL/MariaDB, Tailwind CSS và JavaScript thuần**. Backend xử lý ngay trong PHP; code chia thành các hàm, pages và components. Không cần Composer hoặc Node.js để chạy: CSS đã build sẵn.

## Chạy bằng XAMPP trên Windows

1. Cài XAMPP có PHP 8.2 trở lên, bật **Apache và MySQL** trong Control Panel.
2. Đặt source vào **`C:\xampp\htdocs\WebPHP`**, với `index.php` nằm trực tiếp bên trong.
3. Mở **http://localhost/WebPHP/** bằng trình duyệt.
4. Ở trang cài đặt, nhập tên, email và mật khẩu admin rồi bấm **Tạo database và tài khoản admin**. Website tự tạo các bảng, menu mẫu và topping. Không cần import SQL thủ công.
5. Đăng nhập bằng tài khoản vừa tạo, chọn **Admin** trên thanh điều hướng. Khách hàng tự đăng ký tài khoản riêng.

Clone bằng Git:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/vtientu/FruitTeaStore.git WebPHP
```

XAMPP mặc định dùng `htdocs`. Đường dẫn `C:\Xampp\localhost\WebPHP` chỉ đúng nếu giảng viên đã đổi Apache `DocumentRoot` thành `C:/Xampp/localhost`. Không mở file PHP trực tiếp hoặc bằng Live Server.

### Kết nối database

Mặc định trong `app/config.php`: host `127.0.0.1`, port `3306`, database `fruit_tea_store`, user `root`, mật khẩu trống (XAMPP mặc định). Nếu máy bạn khác, tạo **`app/config.local.php`**:

```php
<?php
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'fruit_tea_store',
    'username' => 'root',
    'password' => 'mat-khau-mysql-cua-ban',
];
```

File này được Git bỏ qua. Tài khoản MySQL cần quyền tạo database/bảng khi cài đặt. Trang cài đặt chỉ nhận thao tác từ localhost và đóng lại sau khi đã có admin; không có mật khẩu admin mặc định.

- Không kết nối được database: bật MySQL, kiểm tra port/mật khẩu và extension `pdo_mysql` trong `php.ini`, sau đó restart Apache.
- Apache không chạy: xem Logs, kiểm tra cổng 80/443. Nếu dùng cổng 8080, mở `http://localhost:8080/WebPHP/`.
- Lỗi 404: kiểm tra DocumentRoot và vị trí `index.php`, tránh giải nén lồng thêm thư mục.
- Giỏ hàng không được lưu: cho phép cookie và kiểm tra `session.save_path` có quyền ghi.

Có thể chạy bằng `php -S 127.0.0.1:8080` tại thư mục project nếu đã có PHP với PDO MySQL và MySQL đang chạy. Mở http://127.0.0.1:8080/. Không cần URL rewrite.

## Chức năng

**Khách hàng:** đăng ký/đăng nhập, tìm và lọc menu, chọn size/đường/đá/topping, sửa giỏ hàng, áp voucher, đặt COD, xem lịch sử/chi tiết/trạng thái đơn, hủy đơn đang chờ và đánh giá món đã mua sau khi hoàn thành.

**Admin:** dashboard doanh thu, tạo/sửa/ẩn sản phẩm, danh mục, topping và voucher; tìm/lọc đơn hàng, cập nhật trạng thái; tìm và khóa/mở tài khoản khách hàng. Ẩn dữ liệu thay vì xóa vĩnh viễn để giữ lịch sử đơn.

Quy tắc đơn giản:

- Size L thêm 10.000đ; giá topping do admin đặt. Tối đa 20 ly mỗi dòng giỏ hàng.
- Phí giao hàng 20.000đ; miễn phí khi tiền món trước giảm giá từ 150.000đ.
- Mỗi đơn dùng một voucher, giảm cố định hoặc phần trăm trên tiền món; có mức tối thiểu, hạn dùng và giới hạn lượt. Đơn hủy vẫn tính lượt đã dùng.
- Đơn đi theo thứ tự **Chờ xác nhận → Đang chuẩn bị → Đang giao → Hoàn thành**. Admin có thể hủy ở hai trạng thái đầu; khách chỉ hủy khi chờ xác nhận.
- Giá món và tùy chọn được lưu vào đơn: sửa sản phẩm không thay đổi hóa đơn cũ. Giỏ hàng được kiểm tra lại khi giá hoặc trạng thái sản phẩm thay đổi.
- Một khách có một đánh giá cho mỗi món, có thể sửa lại. Chỉ đơn hoàn thành mới được đánh giá.

Tài khoản, sản phẩm, đơn hàng và đánh giá lưu trong MySQL; giỏ hàng giữ trong session. Mật khẩu được hash, truy vấn dùng PDO prepared statements, form có CSRF và phân quyền phía server. Gửi lại cùng yêu cầu đặt đơn không tạo đơn trùng.

Phạm vi bài tập dùng COD, chưa tích hợp cổng thanh toán, đơn vị giao hàng, email khôi phục mật khẩu hoặc upload ảnh. Hình ly trà được dựng bằng CSS. shadcn/ui phụ thuộc React nên bản PHP sử dụng Tailwind với form HTML chuẩn.

## Cấu trúc để đọc code

```text
index.php                     # Entry point, render layout và page
app/
  bootstrap.php               # Session, tải dữ liệu, kiểm tra route/quyền
  config.php                  # Cấu hình MySQL mặc định
  database.php                # Kết nối PDO và hàm truy vấn
  install.php                 # Tạo bảng, dữ liệu mẫu và admin đầu tiên
  helpers.php                 # URL, escape HTML, CSRF, validation
  cart.php                    # Tính giá, phí giao hàng, voucher
  actions.php                 # Điều phối POST và xử lý giỏ hàng
  auth_actions.php            # Đăng ký, đăng nhập, đăng xuất
  order_actions.php           # Transaction đặt đơn, đánh giá
  admin_actions.php           # Xử lý form quản trị
/templates/
  layout/                     # Header, footer
  components/                 # Card, ly trà, tóm tắt/chi tiết đơn, đánh giá
  pages/                      # Các trang khách hàng, auth, setup, admin
  admin/                      # Các màn hình quản trị
/database/
  schema.sql                  # 8 bảng và khóa ngoại
  seed_products.php           # Menu mẫu cho lần cài đầu
/assets/                      # CSS đã build và JavaScript
/resources/                   # Source Tailwind và hình ly trà
/tests/                       # Kiểm tra logic và luồng trình duyệt
```

Đọc luồng đặt hàng theo thứ tự `bootstrap.php` → `actions.php` → `order_actions.php` → `templates/components/order-detail.php`. Form hoạt động khi tắt JavaScript; giá cuối cùng luôn được tính ở server.

## Format, build và kiểm tra

Node.js chỉ dùng khi phát triển giao diện hoặc chạy kiểm tra trình duyệt:

```sh
npm ci
npm run format
npm run build:css
npm run format:check
php tests/cart_test.php
```

Sau khi sửa class Tailwind trong PHP, build lại và commit `assets/css/app.css`. Có thể dùng `npm run watch:css` khi làm giao diện.

Kiểm tra trình duyệt **chỉ trên database thử nghiệm riêng** vì suite tạo tài khoản, sản phẩm, voucher và đơn hàng:

1. Trong `app/config.local.php`, đặt database là `webphp_test_suite`.
2. Chạy `php tests/reset_database.php --confirm` để xóa database thử nghiệm đó. Script chỉ chấp nhận tên bắt đầu bằng `webphp_test_`; không dùng với dữ liệu cần giữ.
3. Khởi động PHP/MySQL, cài trình duyệt bằng `npx playwright install chromium`.
4. Chạy (Linux/macOS):

```sh
E2E_ALLOW_WRITE=test-only E2E_BASE_URL=http://127.0.0.1:8080/ npm run test:e2e
```

PowerShell:

```powershell
$env:E2E_ALLOW_WRITE = 'test-only'
$env:E2E_BASE_URL = 'http://127.0.0.1:8080/'
npm run test:e2e
```

Suite tự tạo admin khi database trống. Nếu đã cài đặt, cần truyền `E2E_ADMIN_EMAIL` và `E2E_ADMIN_PASSWORD`, hoặc reset lại database thử nghiệm. Có thể đặt `PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH` nếu dùng Chromium có sẵn. Sau kiểm tra, đổi cấu hình về database làm bài của bạn.

Đã kiểm tra trên Linux với PHP 8.4, MariaDB 11.8 và Chromium, gồm chạy ở đường dẫn con `/WebPHP/`. XAMPP trên Windows cá nhân cần chạy theo hướng dẫn đầu trang; môi trường cloud không truy cập được máy cá nhân.

## Bổ sung 24 món mẫu vào website đã cài

Menu mẫu gồm 12 món trà trái cây, 5 món trà hoa và 7 món trà nguyên bản. Cài mới tự nhận đủ 24 món từ `database/seed_products.php`.

Với InfinityFree đang có dữ liệu:

1. Export database hiện tại trong phpMyAdmin để giữ bản sao.
2. Chọn đúng database của website → **Import** → chọn `database/seed_products.sql` trong source mới → **Go/Import**.
3. Mở lại thực đơn và trang Admin → Sản phẩm.

File SQL chỉ thêm món chưa tồn tại theo **tên**, không sửa giá/mô tả/trạng thái món đã có, không thay tài khoản hoặc đơn hàng. Import lại không thêm trùng tên. Nếu từng đổi tên món mẫu, tên gốc được xem là món còn thiếu và sẽ được thêm; món/danh mục đã ẩn vẫn giữ trạng thái ẩn. Import từng lần, không chạy đồng thời. Không import lại `schema.sql` hoặc reset database để thêm món.

Upload source mới không tự thay đổi dữ liệu database trên hosting. File SQL này cần import riêng. Nó cũng không tạo tài khoản admin cho database chưa cài đặt.

Khi sửa danh sách mẫu, tạo lại SQL từ cùng nguồn PHP:

```sh
php database/export_seed.php
php tests/seed_test.php
```

Bài kiểm tra seed tạo rồi xóa một database tạm có tên ngẫu nhiên `webphp_test_seed_*`, không thay database website; tài khoản MySQL cần quyền tạo/xóa database thử nghiệm. Chạy kiểm tra này trên local/cloud, không cần upload lên hosting.

## Vai trò các phần — dành cho người quen React

| Phần                                            | Vai trò                                                  | Liên hệ với React/JS                          |
| ----------------------------------------------- | -------------------------------------------------------- | --------------------------------------------- |
| `index.php`                                     | Điểm vào, ghép layout và page                            | Khung `App`, nhưng render trên server         |
| `app/bootstrap.php`                             | Khởi tạo session, đọc route, kiểm tra quyền, tải dữ liệu | Router và bước chuẩn bị dữ liệu               |
| `app/actions.php`                               | Nhận POST và điều phối theo `action`                     | Handler nhận form/API request                 |
| `app/auth_actions.php`                          | Đăng ký, đăng nhập, đăng xuất                            | Backend xác thực                              |
| `app/cart.php`                                  | Tính giá, kiểm tra tùy chọn, voucher                     | Hàm nghiệp vụ dùng chung                      |
| `app/order_actions.php`                         | Ghi đơn trong transaction, lưu đánh giá                  | Service xử lý đơn                             |
| `app/admin_actions.php`                         | Validate và ghi thay đổi quản trị                        | Backend CRUD                                  |
| `app/database.php`, `config.php`                | Kết nối PDO, prepared statements, cấu hình               | Database client phía backend                  |
| `app/install.php`                               | Khởi tạo bảng, admin và dữ liệu mẫu lần đầu              | Setup/seed                                    |
| `app/helpers.php`                               | Render, URL, redirect, escape, CSRF, validation          | Utilities                                     |
| `templates/layout/`                             | Header và footer                                         | Layout components                             |
| `templates/pages/`                              | Các màn hình khách hàng và khung admin                   | Route components                              |
| `templates/components/`                         | Card, ly trà, tóm tắt đơn, đánh giá                      | Reusable components, không có React lifecycle |
| `templates/admin/`                              | Các màn hình quản trị                                    | Admin pages                                   |
| `database/schema.sql`                           | Cấu trúc 8 bảng và quan hệ                               | Database schema                               |
| `database/seed_products.php`                    | Nguồn dữ liệu 24 món cho cài mới                         | Seed fixtures                                 |
| `database/export_seed.php`, `seed_products.sql` | Xuất và import các món còn thiếu vào database đã cài     | Công cụ cập nhật dữ liệu mẫu                  |
| `resources/`                                    | Source Tailwind và hình minh họa CSS                     | Source styles                                 |
| `assets/`                                       | CSS đã build và JS chạy trên trình duyệt                 | Static assets                                 |
| `tests/`                                        | Kiểm tra logic, seed và trình duyệt                      | Unit/integration/E2E checks                   |
| `package.json`, lockfile, Prettier              | Công cụ build, format, test                              | Dev tooling, không phải server Node.js        |

GET: `index.php?page=menu` → bootstrap tải sản phẩm → template tạo HTML → trình duyệt hiển thị. POST: form gửi `action` và CSRF → kiểm tra quyền/dữ liệu → cập nhật session hoặc MySQL → redirect để render lại trang. Session giữ giỏ và ID đăng nhập; MySQL giữ dữ liệu lâu dài. JS chỉ tăng tiện ích, giá cuối cùng do PHP tính.

Rà soát code: đã bỏ selector `.cup-brand svg` vì component ly trà không có SVG và sửa chú thích shadcn cũ. Các hàm xử lý, template và công cụ phát triển còn lại đều có nơi sử dụng; không xóa chỉ vì chúng không được upload lên hosting.
