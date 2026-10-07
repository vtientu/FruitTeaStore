<footer class="mt-10 bg-secondary px-6 py-10 md:px-12">
    <div class="mx-auto max-w-[1208px]">
        <div class="flex flex-wrap items-center gap-8 md:gap-16">
            <a href="<?= e(url()) ?>" class="font-display text-5xl font-bold tracking-tighter">
                mộc
                <span class="mt-1 block font-sans text-[7px] font-normal tracking-[2px]">
                    TRÀ TRÁI CÂY TƯƠI
                </span>
            </a>
            <p class="text-xs leading-loose text-muted-foreground">
                Một ngụm tươi, một ngày vui.
                <br />
                Gửi bạn chút dịu dàng từ thiên nhiên.
            </p>
            <a class="text-xs md:ml-auto" href="<?= e(url('menu')) ?>">Chọn một ly trà ↗</a>
        </div>
        <div class="mt-7 flex justify-between gap-4 border-t pt-6 text-[9px] text-muted-foreground">
            <span>© <?= date('Y') ?> Mộc Trà.</span>
            <span>Website mẫu · WebPHP</span>
        </div>
    </div>
</footer>
