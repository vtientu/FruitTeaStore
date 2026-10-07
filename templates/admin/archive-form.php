<form
    method="post"
    action="index.php"
    class="ml-3 inline-block"
    data-confirm="Ẩn mục này khỏi cửa hàng?"
>
    <?php csrfField(); ?>
    <input type="hidden" name="action" value="admin_archive" />
    <input type="hidden" name="entity" value="<?= e($entity) ?>" />
    <input type="hidden" name="id" value="<?= e($id) ?>" />
    <button class="text-muted-foreground underline">Ẩn</button>
</form>
