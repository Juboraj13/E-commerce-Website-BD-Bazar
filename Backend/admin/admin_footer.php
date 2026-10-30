        </div> <!-- .admin-content -->
    </div> <!-- .admin-main -->
</div> <!-- .admin-layout -->

<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= asset_url('frontend/js/script.js') ?>"></script>
<?php if (isset($adminExtraJs)): ?>
    <?= $adminExtraJs ?>
<?php endif; ?>
</body>
</html>

