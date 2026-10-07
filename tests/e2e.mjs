// Chỉ chạy trên database thử nghiệm: suite tạo tài khoản, món, voucher và đơn hàng.
import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import { randomBytes } from 'node:crypto';

if (process.env.E2E_ALLOW_WRITE !== 'test-only') {
    throw new Error('Chỉ chạy với database thử nghiệm và E2E_ALLOW_WRITE=test-only. Xem README.');
}
const base = process.env.E2E_BASE_URL || 'http://127.0.0.1:8082/WebPHP/';
const stamp = Date.now().toString();
const password = process.env.E2E_ADMIN_PASSWORD || randomBytes(16).toString('hex');
const adminEmail = process.env.E2E_ADMIN_EMAIL || `admin${stamp}@example.test`;
const browser = await chromium.launch({
    executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH || undefined,
});
let checks = 0;
function passed(label) {
    checks++;
    console.log(`PASS ${checks}: ${label}`);
}
function clean(html) {
    assert.doesNotMatch(html, /Fatal error|Warning:|Uncaught|Stack trace:/);
}
try {
    const adminContext = await browser.newContext();
    const admin = await adminContext.newPage();
    const errors = [];
    admin.on('pageerror', (error) => errors.push(error.message));
    await admin.goto(base);
    if (await admin.getByRole('heading', { name: 'Cài đặt website' }).count()) {
        await admin.getByLabel('Tên admin').fill('Admin E2E');
        await admin.getByLabel('Email admin').fill(adminEmail);
        await admin.getByLabel('Mật khẩu admin').fill(password);
        await admin.getByRole('button', { name: 'Tạo database và tài khoản admin' }).click();
    }
    await admin.goto(base + 'index.php?page=login');
    await admin.getByLabel('Email', { exact: true }).fill(adminEmail);
    await admin.getByLabel('Mật khẩu', { exact: true }).fill(password);
    await admin.getByRole('button', { name: 'Đăng nhập', exact: true }).click();
    await admin.getByRole('link', { name: 'Admin', exact: true }).waitFor();
    passed('cài đặt và đăng nhập admin');
    const adminCsrf = await admin.locator('input[name=csrf_token]').first().inputValue();
    async function adminPost(action, fields) {
        const response = await adminContext.request.post(base + 'index.php', {
            form: { action, csrf_token: adminCsrf, ...fields },
        });
        const html = await response.text();
        clean(html);
        return html;
    }
    for (const section of [
        'dashboard',
        'products',
        'categories',
        'toppings',
        'orders',
        'vouchers',
        'users',
    ]) {
        const response = await admin.goto(base + 'index.php?page=admin&section=' + section);
        assert.equal(response.status(), 200);
        clean(await admin.locator('body').innerText());
    }
    passed('7 trang admin không lỗi kể cả dữ liệu trống');
    const categoryName = 'Danh mục E2E ' + stamp;
    await adminPost('admin_category', { name: categoryName, active: '1' });
    await admin.goto(base + 'index.php?page=admin&section=categories');
    const categoryRow = admin.getByRole('row').filter({ hasText: categoryName });
    const categoryId = new URL(
        await categoryRow.getByRole('link', { name: 'Sửa' }).getAttribute('href'),
        base,
    ).searchParams.get('edit');
    const toppingName = 'Topping E2E ' + stamp;
    await adminPost('admin_topping', { name: toppingName, price: '8000', active: '1' });
    await admin.goto(base + 'index.php?page=admin&section=toppings');
    const toppingId = new URL(
        await admin
            .getByRole('row')
            .filter({ hasText: toppingName })
            .getByRole('link', { name: 'Sửa' })
            .getAttribute('href'),
        base,
    ).searchParams.get('edit');
    await admin.goto(base + 'index.php?page=admin&section=products&edit=new');
    const productName = 'Trà E2E ' + stamp;
    await admin.getByLabel('Tên sản phẩm', { exact: true }).fill(productName);
    await admin.locator('select[name=category_id]').selectOption(categoryId);
    await admin.getByLabel('Mô tả', { exact: true }).fill('Trà thử nghiệm thơm mát');
    await admin.getByRole('button', { name: 'Lưu sản phẩm' }).click();
    const productId = new URL(
        await admin
            .getByRole('row')
            .filter({ hasText: productName })
            .getByRole('link', { name: 'Sửa' })
            .getAttribute('href'),
        base,
    ).searchParams.get('edit');
    assert.ok(productId && categoryId && toppingId);
    passed('CRUD: tạo danh mục, topping và sản phẩm bằng admin');
    const coupon = 'TEST' + stamp;
    await adminPost('admin_voucher', {
        code: coupon,
        kind: 'fixed',
        value: '10000',
        min_total: '60000',
        expires_on: '2099-12-31',
        max_uses: '1',
        active: '1',
    });
    const invalidVoucher = await adminPost('admin_voucher', {
        code: 'BAD' + stamp,
        kind: 'percent',
        value: '101',
        min_total: '0',
        expires_on: '2099-12-31',
        max_uses: '1',
        active: '1',
    });
    assert.match(invalidVoucher, /không hợp lệ/);
    passed('tạo voucher và từ chối phần trăm lớn hơn 100');
    const customerContext = await browser.newContext({ javaScriptEnabled: false });
    const page = await customerContext.newPage();
    page.on('pageerror', (error) => errors.push(error.message));
    await page.goto(base + 'index.php?page=menu&q=' + encodeURIComponent(productName));
    assert.equal(await page.locator('[data-product-card]').count(), 1);
    await adminPost('admin_archive', { entity: 'categories', id: categoryId });
    await page.reload();
    assert.equal(await page.locator('[data-product-card]').count(), 0);
    await adminPost('admin_category', { id: categoryId, name: categoryName, active: '1' });
    await page.reload();
    assert.equal(await page.locator('[data-product-card]').count(), 1);
    await adminPost('admin_archive', { entity: 'products', id: productId });
    await page.reload();
    assert.equal(await page.locator('[data-product-card]').count(), 0);
    passed('danh mục/sản phẩm ẩn và khôi phục phản ánh ra cửa hàng');
    await page.goto(base + 'index.php?page=product&id=2');
    await page.getByText('L · 700ml (+10k)', { exact: true }).click();
    await page.getByRole('checkbox', { name: /Trân châu trắng/ }).check();
    await page.getByRole('button', { name: /Thêm vào giỏ/ }).click();
    assert.match(await page.getByLabel('Tổng cộng').innerText(), /86.000đ/);
    await page.getByRole('spinbutton').fill('3');
    await page.getByRole('button', { name: 'Cập nhật' }).click();
    assert.match(await page.getByLabel('Tổng cộng').innerText(), /198.000đ/);
    await page.getByRole('spinbutton').fill('1');
    await page.getByRole('button', { name: 'Cập nhật' }).click();
    await page.getByRole('link', { name: /Tiến hành đặt hàng/ }).click();
    await page.getByRole('link', { name: 'Đăng ký', exact: true }).click();
    const customerEmail = `customer${stamp}@example.test`;
    await page.getByLabel('Họ và tên').fill('Khách E2E ' + stamp);
    await page.getByLabel('Email', { exact: true }).fill(customerEmail);
    await page.getByLabel('Mật khẩu (ít nhất 8 ký tự)').fill(password);
    await page.getByLabel('Xác nhận mật khẩu').fill(password);
    await page.getByRole('button', { name: 'Tạo tài khoản', exact: true }).click();
    assert.ok(page.url().includes('checkout'));
    await page.getByLabel('Mã voucher').fill(coupon);
    await page.getByRole('button', { name: 'Áp dụng / bỏ mã' }).click();
    assert.match(await page.getByLabel('Tổng cộng').innerText(), /76.000đ/);
    await page.getByRole('link', { name: /Tiến hành đặt hàng/ }).click();
    await page.getByLabel('Số điện thoại').fill('0901234567');
    await page.getByLabel('Địa chỉ giao hàng').fill('123 Đường thử nghiệm');
    const token = await page.locator('input[name=checkout_token]').inputValue();
    const csrf = await page.locator('input[name=csrf_token]').first().inputValue();
    await page.getByRole('button', { name: /Xác nhận đặt hàng/ }).click();
    assert.match(await page.locator('main').innerText(), /Đặt hàng thành công/);
    const orderId = new URL(page.url()).searchParams.get('id');
    await page.reload();
    assert.match(await page.locator('main').innerText(), /76.000đ/);
    passed('đăng ký giữ giỏ, size/topping, ngưỡng giao hàng, voucher và lưu đơn');
    async function customerPost(action, fields = {}) {
        return customerContext.request.post(base + 'index.php', {
            form: { action, csrf_token: csrf, ...fields },
        });
    }
    const replay = await customerPost('checkout', {
        checkout_token: token,
        name: 'Khách',
        phone: '0901234567',
        address: 'test',
    });
    assert.ok(replay.url().includes('id=' + orderId));
    assert.equal((await customerContext.request.get(base + 'index.php?page=admin')).status(), 403);
    assert.equal((await customerPost('admin_product', { id: '2', price: '1' })).status(), 403);
    const invalidCsrf = await customerContext.request.post(base + 'index.php', {
        form: { action: 'add', product_id: '1', csrf_token: 'invalid' },
    });
    assert.match(await invalidCsrf.text(), /hết hạn/);
    passed('chống gửi trùng đơn, CSRF và chặn customer gọi API admin');
    const illegal = await adminPost('admin_order', { id: orderId, status: 'completed' });
    assert.match(illegal, /Không thể chuyển trạng thái/);
    for (const status of ['preparing', 'shipping', 'completed']) {
        await admin.goto(base + 'index.php?page=admin&section=orders&id=' + orderId);
        await admin.getByRole('combobox', { name: 'Trạng thái mới' }).selectOption(status);
        await admin.getByRole('button', { name: 'Cập nhật trạng thái' }).click();
        assert.match(await admin.locator('main').innerText(), /Đã lưu thay đổi/);
    }
    await customerPost('cancel_order', { order_id: orderId });
    await page.goto(base + 'index.php?page=orders&id=' + orderId);
    assert.match(await page.locator('main').innerText(), /Hoàn thành/);
    await page.getByRole('link', { name: 'Đánh giá món' }).click();
    await page.getByLabel('Cảm nhận').fill('Trà ngon <script>alert(1)</script>');
    await page.getByRole('button', { name: 'Lưu đánh giá' }).click();
    assert.equal(await page.locator('main script').count(), 0);
    assert.match(await page.locator('main').innerText(), /Đã lưu đánh giá/);
    passed('chuyển trạng thái đúng thứ tự, không hủy đơn hoàn thành, đánh giá an toàn');
    await adminPost('admin_product', {
        id: '2',
        category_id: '1',
        name: 'Trà dâu hibiscus',
        description: 'Đổi giá kiểm thử',
        price: '99000',
        color: '#efadb1',
        fruit: '🍓',
        tag: 'ĐƯỢC YÊU THÍCH',
        type: 'berry',
        active: '1',
    });
    await page.goto(base + 'index.php?page=orders&id=' + orderId);
    assert.match(await page.locator('main').innerText(), /76.000đ/);
    await adminPost('admin_product', {
        id: '2',
        category_id: '1',
        name: 'Trà dâu hibiscus',
        description: 'Dâu tươi · Trà hoa · Chút chua thanh',
        price: '49000',
        color: '#efadb1',
        fruit: '🍓',
        tag: 'ĐƯỢC YÊU THÍCH',
        type: 'berry',
        active: '1',
    });
    await customerPost('add', { product_id: '1', quantity: '2' });
    const exhausted = await customerPost('voucher', { code: coupon });
    assert.match(await exhausted.text(), /không hợp lệ/);
    await page.goto(base + 'index.php?page=checkout');
    await page.getByLabel('Số điện thoại').fill('0901234567');
    await page.getByLabel('Địa chỉ giao hàng').fill('Địa chỉ hủy thử');
    await page.getByRole('button', { name: /Xác nhận đặt hàng/ }).click();
    const cancelledId = new URL(page.url()).searchParams.get('id');
    await customerPost('cancel_order', { order_id: cancelledId });
    await page.goto(base + 'index.php?page=orders&id=' + cancelledId);
    assert.match(await page.locator('main').innerText(), /Đã hủy/);
    passed('giá lịch sử không đổi, hết lượt voucher, khách hủy đơn chờ');
    const otherContext = await browser.newContext();
    const other = await otherContext.newPage();
    await other.goto(base + 'index.php?page=register');
    const otherCsrf = await other.locator('input[name=csrf_token]').first().inputValue();
    await otherContext.request.post(base + 'index.php', {
        form: {
            action: 'register',
            csrf_token: otherCsrf,
            name: 'Khách khác',
            email: `other${stamp}@example.test`,
            password,
            password_confirm: password,
            role: 'admin',
        },
    });
    assert.equal((await otherContext.request.get(base + 'index.php?page=admin')).status(), 403);
    assert.equal(
        (await otherContext.request.get(base + 'index.php?page=orders&id=' + orderId)).status(),
        404,
    );
    await other.goto(base + 'index.php?page=product&id=2');
    const deniedReview = await otherContext.request.post(base + 'index.php', {
        form: {
            action: 'review',
            csrf_token: await other.locator('input[name=csrf_token]').first().inputValue(),
            product_id: '2',
            rating: '5',
            comment: 'Không mua',
        },
    });
    assert.match(await deniedReview.text(), /chỉ có thể đánh giá/);
    const activeCsrf = await other.locator('input[name=csrf_token]').first().inputValue();
    await otherContext.request.post(base + 'index.php', {
        form: {
            action: 'add',
            csrf_token: activeCsrf,
            product_id: '1',
            'toppings[]': toppingId,
        },
    });
    await adminPost('admin_archive', { entity: 'toppings', id: toppingId });
    await other.goto(base + 'index.php?page=cart');
    assert.match(await other.locator('main').innerText(), /Giỏ còn trống/);
    passed('topping đang trong giỏ bị ẩn: cập nhật giỏ và tiếp tục mua được');
    await otherContext.close();
    passed('không xem đơn người khác, không tự nâng quyền, không đánh giá khi chưa mua');
    await admin.goto(
        base + 'index.php?page=admin&section=users&q=' + encodeURIComponent(customerEmail),
    );
    const userRow = admin.getByRole('row').filter({ hasText: customerEmail });
    const userId = await userRow.locator('input[name=id]').inputValue();
    await adminPost('admin_user', { id: userId, active: '0' });
    const locked = await customerContext.request.get(base + 'index.php?page=orders');
    assert.ok(locked.url().includes('login'));
    await adminPost('admin_user', { id: userId, active: '1' });
    passed('khóa tài khoản vô hiệu hóa phiên đang đăng nhập');
    await adminPost('admin_archive', { entity: 'products', id: '1' });
    await page.goto(base);
    clean(await page.locator('body').innerText());
    await adminPost('admin_product', {
        id: '1',
        category_id: '1',
        name: 'Trà đào cam sả',
        description: 'Đào ngọt dịu · Cam vàng · Sả thơm',
        price: '45000',
        color: '#f8d6a0',
        fruit: '🍑',
        tag: 'BEST SELLER',
        type: 'peach',
        active: '1',
    });
    await adminPost('admin_archive', { entity: 'toppings', id: toppingId });
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(base);
    assert.equal(
        await page.evaluate(() => document.documentElement.scrollWidth > innerWidth),
        false,
    );
    await admin.goto(base + 'index.php?page=admin');
    clean(await admin.locator('body').innerText());
    await admin.screenshot({
        path: process.env.E2E_SCREENSHOT || '/tmp/webphp-admin.png',
        fullPage: true,
    });
    assert.deepEqual(errors, []);
    passed('ẩn món nổi bật không hỏng trang chủ; desktop/mobile không tràn và không lỗi JS');
    console.log(`Completed ${checks} end-to-end checks.`);
} finally {
    await browser.close();
}
