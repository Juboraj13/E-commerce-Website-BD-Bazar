-- ========================================================
-- ShopEase — Database Seed Data
-- ========================================================

USE `ecommerce_db`;

-- Truncate existing data to allow clean re-import
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `cart`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `messages`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Users (Admin password: 'Admin123!', Customer password: 'Customer123!')
-- --------------------------------------------------------
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `address`, `role`) VALUES
(1, 'System Administrator', 'admin@shopease.com', '$2y$10$oomS3fWfceRzkfHLoqWCzOqsY50tJlJhuDgYOfPRFd/J/NHcuTs/2', '+8801711000000', 'ShopEase Head Office, Banani, Dhaka-1213', 'admin'),
(2, 'Sarah Rahman', 'customer@shopease.com', '$2y$10$mYgWq83EeKd/Cmgx47IQ5.MDB.Pp6LbNngmxy76/c3P3/VDTDQ9d2', '+8801812345678', 'House 42, Road 11, Dhanmondi, Dhaka', 'customer'),
(3, 'Tahmid Hasan', 'tahmid@example.com', '$2y$10$mYgWq83EeKd/Cmgx47IQ5.MDB.Pp6LbNngmxy76/c3P3/VDTDQ9d2', '+8801998765432', 'Flat 5B, Green Valley, Nasirabad, Chattogram', 'customer');

-- --------------------------------------------------------
-- Categories
-- --------------------------------------------------------
INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Electronics & Smartphones', 'Premium flagship smartphones, laptops, smart accessories and tablets.'),
(2, 'Fashion & Apparel', 'Trendy urban fashion, casual wear, jackets, and timeless styles.'),
(3, 'Bikes & Rides', 'Premium bicycles, sports commuter bikes, gear cycles and parts.'),
(4, 'Audio & Sound', 'High fidelity wireless headphones, earbuds and portable sound.'),
(5, 'Laptops & Computers', 'High performance laptops, ultra-books and productivity computers.');

-- --------------------------------------------------------
-- Products (22 realistic products with real uploaded images)
-- --------------------------------------------------------
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `is_featured`) VALUES
(1, 1, 'iPhone 15 Pro Max - Blue Titanium', 'Apple iPhone 15 Pro Max featuring A17 Pro chip, aerospace-grade titanium frame, and unmatched 5x optical zoom camera system.', 174999.00, 15, 'iphone-15-pro-max-blue-titanium.jpeg', 1),
(2, 1, 'iPhone 15 Pro - 512GB Blue', 'Super Retina XDR OLED 120Hz display with action button and USB-C connectivity. Built for creators and professionals.', 158000.00, 12, 'iphone-15-pro-512gb-blue.jpeg', 1),
(3, 1, 'iPhone 15 Plus - 128GB Black', 'Dynamic Island, 48MP main camera with 2x telephoto, and all-day battery life in a vibrant, durable color-infused glass design.', 118500.00, 20, 'iphone-15-plus-128gb-black.jpeg', 0),
(4, 1, 'iPhone 14 Pro - 256GB Gold', 'A16 Bionic chip, Always-On display, 48MP camera, and emergency satellite connectivity in stunning gold finish.', 134999.00, 8, 'iphone-14-pro-256gb-gold.jpeg', 1),
(5, 1, 'Google Pixel 8 Pro - Obsidian', 'Google Tensor G3 chip, upgraded camera system with Pro controls, and brilliant Super Actua display.', 99500.00, 14, 'google-pixel-8-pro-obsidian-1745.jpeg', 1),
(6, 1, 'Google Pixel 7 Pro - Snow', 'Stunning telephoto camera, refined curved design, and all-day adaptive battery life powered by Google Tensor G2.', 68000.00, 18, 'google-pixel-7-pro-snow825.jpeg', 0),
(7, 5, 'MacBook Air M2 - Midnight 8/256GB', 'Redesigned around next-generation M2 chip. Strikingly thin, 13.6-inch Liquid Retina display and up to 18 hours battery life.', 128500.00, 10, 'MacBook-Air-M2-Midnight-8137.webp', 1),
(8, 5, 'MacBook Air M1 - Space Gray 8/256GB', 'Apple breakthrough M1 chip gives unbelievable speed and efficiency. Fanless silent design with Retina display.', 88000.00, 25, 'MacBook-Air-M1-13-inch-8256GB-Space-Gray-9682.webp', 0),
(9, 4, 'Apple AirPods (2nd Gen) with Charging Case', 'Effortless setup, high quality audio and voice-activated Siri with lightning fast wireless connectivity.', 14500.00, 30, 'AirPod 2nd Gen.jpg', 1),
(10, 1, 'Apple Watch Series - Midnight Aluminum', 'Advanced health sensors, comprehensive fitness metrics, crack-resistant front crystal and water resistance up to 50m.', 44500.00, 16, 'apple-watch-pcq.jpg', 0),
(11, 1, 'iPad Pro 11-inch Liquid Retina', 'Ultra-fast performance with Apple Silicon, ProMotion 120Hz display, and compatibility with Apple Pencil and Magic Keyboard.', 96500.00, 10, 'ipad-pro.jpg', 0),
(12, 1, 'Tecno Spark Go 2024 - Dynamic Edition', 'Budget champ with 90Hz smooth punch-hole screen, stereo dual speakers, and large 5000mAh battery.', 10999.00, 40, 'TechnoSparkGo2.png', 0),
(13, 3, 'Velocity Phantom R1 - Road Sports Bike', 'Lightweight 6061 alloy frame, 21-speed Shimano Tourney gears, front disc brakes, and aerodynamic road wheels.', 24500.00, 12, '1.jpg', 1),
(14, 3, 'TrailMaster Apex 29er Mountain Bike', 'Full suspension hydraulic mountain bike built for rugged off-road tracks and steep mountain terrains.', 28900.00, 8, '2.jpg', 0),
(15, 3, 'Urban Commuter Pro - City Hybrid Bicycle', 'Comfortable upright riding posture, puncture-resistant tires, integrated luggage carrier and splash guards.', 18500.00, 15, '3.jpg', 1),
(16, 3, 'Thunderbolt Sport MTB - Dual Disc', 'High tensile steel rigid frame, quick-release front wheel, double-wall alloy rims and responsive disc brakes.', 21500.00, 10, '5.jpg', 0),
(17, 3, 'AeroGlide Carbon Edition Bicycle', 'Ultra lightweight carbon fiber hybrid frame for high speed urban cruising and weekend fitness rides.', 39500.00, 5, '6.jpg', 0),
(18, 2, 'Classic Vintage Denim Jacket', 'Premium washed cotton denim jacket with reinforced seams, branded brass buttons, and regular comfort fit.', 3200.00, 35, '1.webp', 1),
(19, 2, 'Urban Streetwear Oversized Hoodie', 'Heavyweight 380 GSM fleece hoodie with double-lined hood, kangaroo pocket, and minimal embroidered chest logo.', 2450.00, 40, '2.webp', 1),
(20, 2, 'Tailored Slim-Fit Oxford Shirt', '100% breathable Egyptian cotton shirt with button-down collar, perfect for business meetings or casual weekends.', 1850.00, 50, '3.webp', 0),
(21, 2, 'Minimalist Puffer Winter Vest', 'Water-repellent insulated quilted vest with soft microfiber lining and secure zippered hand pockets.', 2950.00, 22, '4.webp', 0),
(22, 2, 'Casual Cotton Utility Cargo Pants', 'Multi-pocket tactical design with elasticated cuffs and durable ripstop stretch cotton fabric.', 2150.00, 28, '5.webp', 0);

-- --------------------------------------------------------
-- Sample Orders
-- --------------------------------------------------------
INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `shipping_name`, `shipping_phone`, `shipping_address`, `payment_method`, `status`, `order_date`) VALUES
(1001, 2, 175079.00, 'Sarah Rahman', '+8801812345678', 'House 42, Road 11, Dhanmondi, Dhaka', 'Cash on Delivery', 'delivered', '2026-10-15 14:30:00'),
(1002, 2, 26350.00, 'Sarah Rahman', '+8801812345678', 'House 42, Road 11, Dhanmondi, Dhaka', 'Cash on Delivery', 'processing', '2026-10-25 10:15:00'),
(1003, 3, 24580.00, 'Tahmid Hasan', '+8801998765432', 'Flat 5B, Green Valley, Nasirabad, Chattogram', 'Cash on Delivery', 'pending', '2026-10-29 16:45:00');

-- --------------------------------------------------------
-- Sample Order Items
-- --------------------------------------------------------
INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1001, 1, 1, 174999.00),
(2, 1002, 13, 1, 24500.00),
(3, 1002, 20, 1, 1850.00),
(4, 1003, 13, 1, 24500.00);

-- --------------------------------------------------------
-- Sample Messages
-- --------------------------------------------------------
INSERT INTO `messages` (`message_id`, `user_name`, `email`, `subject`, `message`, `is_read`, `date`) VALUES
(1, 'Tanvir Ahmed', 'tanvir@example.com', 'Product Inquiry - MacBook Air M2', 'Hello, do you provide official Apple warranty with the MacBook Air M2 models? How long does delivery to Sylhet take?', 1, '2026-10-20 11:20:00'),
(2, 'Nusrat Jahan', 'nusrat@gmail.com', 'Return Policy for Fashion Items', 'I want to purchase the Vintage Denim Jacket. If the size does not fit, what is your exchange procedure?', 0, '2026-10-28 09:40:00'),
(3, 'Farhan Kabir', 'farhan@outlook.com', 'Bulk Order for Velocity Bicycles', 'We are looking to order 5 units of the Velocity Phantom R1 for our club. Can you offer a corporate discount?', 0, '2026-10-29 18:05:00');

