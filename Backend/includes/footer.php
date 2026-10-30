    </main>
    <!-- Main Content Ends -->

    <!-- Back to Top Button -->
    <button type="button" class="back-to-top" id="backToTopBtn" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Global Toast Container -->
    <div id="toast-container"></div>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand & Info -->
                <div class="footer-col">
                    <div class="footer-brand">
                        <i class="fas fa-bag-shopping"></i> BD <span>Bazar</span>
                    </div>
                    <p class="footer-bio">
                        BD Bazar is your trusted online shopping destination in Bangladesh. Experience authentic electronics, trendy fashion apparel, and premium sports bicycles delivered directly to your doorstep with Cash on Delivery.
                    </p>
                    <div class="footer-social-links">
                        <a href="https://facebook.com" target="_blank" rel="noopener" class="footer-social-btn" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com" target="_blank" rel="noopener" class="footer-social-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://twitter.com" target="_blank" rel="noopener" class="footer-social-btn" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" class="footer-social-btn" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Navigation -->
                <div class="footer-col">
                    <h3 class="footer-col-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?= asset_url('index.php') ?>"><i class="fas fa-angle-right"></i> Home</a></li>
                        <li><a href="<?= asset_url('products.php') ?>"><i class="fas fa-angle-right"></i> All Products</a></li>
                        <li><a href="<?= asset_url('about.php') ?>"><i class="fas fa-angle-right"></i> About Our Company</a></li>
                        <li><a href="<?= asset_url('contact.php') ?>"><i class="fas fa-angle-right"></i> Contact & Support</a></li>
                        <li><a href="<?= asset_url('orders.php') ?>"><i class="fas fa-angle-right"></i> Track Orders</a></li>
                    </ul>
                </div>

                <!-- Categories -->
                <div class="footer-col">
                    <h3 class="footer-col-title">Shop by Category</h3>
                    <ul class="footer-links">
                        <li><a href="<?= asset_url('products.php?category=1') ?>"><i class="fas fa-angle-right"></i> Electronics & Phones</a></li>
                        <li><a href="<?= asset_url('products.php?category=2') ?>"><i class="fas fa-angle-right"></i> Fashion & Apparel</a></li>
                        <li><a href="<?= asset_url('products.php?category=3') ?>"><i class="fas fa-angle-right"></i> Bikes & Rides</a></li>
                        <li><a href="<?= asset_url('products.php?category=4') ?>"><i class="fas fa-angle-right"></i> Audio & Accessories</a></li>
                        <li><a href="<?= asset_url('products.php?category=5') ?>"><i class="fas fa-angle-right"></i> Laptops & Computers</a></li>
                    </ul>
                </div>

                <!-- Customer Care & Security -->
                <div class="footer-col">
                    <h3 class="footer-col-title">Customer Care</h3>
                    <ul class="footer-links">
                        <li><span><i class="fas fa-map-marker-alt" style="color:var(--primary); margin-right:8px;"></i> <?= e(SITE_ADDRESS) ?></span></li>
                        <li><span><i class="fas fa-phone-alt" style="color:var(--primary); margin-right:8px;"></i> <?= e(SITE_PHONE) ?></span></li>
                        <li><span><i class="fas fa-envelope" style="color:var(--primary); margin-right:8px;"></i> <?= e(SITE_EMAIL) ?></span></li>
                        <li><span><i class="fas fa-clock" style="color:var(--primary); margin-right:8px;"></i> Sat – Thu: 9:00 AM – 9:00 PM</span></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright & Payment Methods -->
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All Rights Reserved. Built for Web Engineering University Project.</p>
                <div style="display:flex; align-items:center; gap:1rem; font-size:1.4rem; color:#94a3b8;">
                    <i class="fa-solid fa-money-bill-wave" title="Cash on Delivery"></i>
                    <i class="fa-brands fa-cc-visa" title="Visa"></i>
                    <i class="fa-brands fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fa-solid fa-shield-halved" title="100% Secure Shopping"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Variables & Core Scripts -->
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        const CSRF_TOKEN = "<?= csrf_token() ?>";
    </script>
    <script src="<?= asset_url('frontend/js/script.js') ?>"></script>
    <script src="<?= asset_url('frontend/js/cart.js') ?>"></script>
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>

