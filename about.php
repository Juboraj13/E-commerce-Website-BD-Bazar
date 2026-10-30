<?php
/**
 * about.php — About Us Page
 * Company story, mission, core values, team and multimedia showcase.
 */
require_once __DIR__ . '/backend/config/config.php';

$pageTitle = 'About Us';
require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- About Hero -->
<section style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 4rem 0;">
    <div class="container" style="text-align: center; max-width: 720px;">
        <span class="section-tag" style="color: #60a5fa;">Our Journey & Commitment</span>
        <h1 style="font-size: 2.85rem; color: #ffffff; margin-bottom: 1rem;">Empowering Seamless E-Commerce in Bangladesh</h1>
        <p style="font-size: 1.05rem; color: #cbd5e1; line-height: 1.7;">
            <?= e(SITE_NAME) ?> was created to provide customers across Bangladesh with direct access to 100% genuine electronics, modern fashion apparel, and premium bicycles with reliable Cash on Delivery and dedicated customer support.
        </p>
    </div>
</section>

<!-- Our Story Section -->
<section class="section">
    <div class="container">
        <div class="about-story-grid" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 3.5rem; align-items: center;">
            <div>
                <span class="section-tag">Who We Are</span>
                <h2 class="section-title">Bridging Quality Products with Modern Convenience</h2>
                <p style="color: var(--text-secondary); margin-bottom: 1.25rem;">
                    Founded as a pioneering university web engineering initiative, BD Bazar represents our vision for a secure, fast, and accessible digital marketplace. We noticed how local consumers often face hurdles with counterfeit items, slow delivery times, and inflexible payment mechanisms.
                </p>
                <p style="color: var(--text-secondary); margin-bottom: 1.75rem;">
                    We solve this by partnering with vetted manufacturers, implementing end-to-end inventory tracking, and offering full Cash on Delivery with transparent return policies. Every order is packed with care and handled by reliable logistics partners.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                    <div style="border-left: 3px solid var(--primary); padding-left: 1rem;">
                        <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;">Our Mission</h4>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">To deliver world-class digital shopping experiences with unparalleled product authenticity and customer transparency.</p>
                    </div>
                    <div style="border-left: 3px solid var(--accent); padding-left: 1rem;">
                        <h4 style="font-size: 1.1rem; margin-bottom: 0.25rem;">Our Vision</h4>
                        <p style="font-size: 0.875rem; color: var(--text-muted);">To become the most reliable and beloved multi-category online platform for everyday modern lifestyle in Bangladesh.</p>
                    </div>
                </div>
            </div>

            <!-- Multimedia / Video Embed -->
            <div>
                <div style="background-color: var(--bg-surface); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
                    <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius-md);">
                        <iframe style="position: absolute; top:0; left:0; width:100%; height:100%; border:0;" 
                                src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?controls=0" 
                                title="BD Bazar Brand Story" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                    <div style="margin-top: 1rem; text-align: center;">
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Watch our warehouse operations & product quality inspection standard</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Counter Section -->
<section style="background-color: var(--secondary); color: #ffffff; padding: 3.5rem 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">
            <div>
                <div style="font-size: 2.75rem; font-weight: 800; color: #60a5fa; font-family: var(--font-heading);">15,000+</div>
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-top: 0.25rem;">Satisfied Customers</div>
            </div>
            <div>
                <div style="font-size: 2.75rem; font-weight: 800; color: #34d399; font-family: var(--font-heading);">100%</div>
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-top: 0.25rem;">Genuine Guarantee</div>
            </div>
            <div>
                <div style="font-size: 2.75rem; font-weight: 800; color: #fbbf24; font-family: var(--font-heading);">64</div>
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-top: 0.25rem;">Districts Covered</div>
            </div>
            <div>
                <div style="font-size: 2.75rem; font-weight: 800; color: #f472b6; font-family: var(--font-heading);">24/7</div>
                <div style="font-size: 0.95rem; color: #cbd5e1; margin-top: 0.25rem;">Customer Support</div>
            </div>
        </div>
    </div>
</section>

<!-- Core Pillars Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-tag">Our Values</span>
            <h2 class="section-title">Built on Four Uncompromising Pillars</h2>
            <p class="section-subtitle">The standards that guide every package we prepare and every customer we serve</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display:flex; align-items:center; justify-content:center; margin: 0 auto 1.25rem auto; font-size: 1.5rem;">
                    <i class="fas fa-shield-check"></i>
                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Authenticity First</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Direct manufacturer sourcing ensures you receive only 100% genuine devices and clothing.</p>
            </div>

            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background-color: #ecfdf5; color: var(--success); display:flex; align-items:center; justify-content:center; margin: 0 auto 1.25rem auto; font-size: 1.5rem;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Rapid Logistics</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">24-hour dispatch in Dhaka and 48–72 hours nationwide delivery across all 64 districts.</p>
            </div>

            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background-color: #fffbeb; color: var(--warning); display:flex; align-items:center; justify-content:center; margin: 0 auto 1.25rem auto; font-size: 1.5rem;">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Zero-Risk Payments</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Check your parcel at your doorstep before paying with our reliable Cash on Delivery service.</p>
            </div>

            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem 1.5rem; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background-color: #fdf2f8; color: #ec4899; display:flex; align-items:center; justify-content:center; margin: 0 auto 1.25rem auto; font-size: 1.5rem;">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;">Dedicated Care</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Prompt phone and email resolution for warranties, sizing queries, and technical inquiries.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

