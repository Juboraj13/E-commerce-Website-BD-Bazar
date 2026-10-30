// Home page specific JavaScript for BazarBD

// Page navigation system
const pages = {
    home: 'index.html',
    fashion: 'fashion.html',
    electronics: 'electronic.html',
    bikes: 'bike.html',
    login: 'login.html'
};

// Show page function
function showPage(pageName) {
    if (pages[pageName]) {
        window.location.href = pages[pageName];
    }
}

// Category card hover effects
function initializeCategoryCards() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Add click analytics
        card.addEventListener('click', function() {
            const categoryName = this.querySelector('h2').textContent;
            trackCategoryClick(categoryName);
        });
    });
}

// Track category clicks (for analytics)
function trackCategoryClick(categoryName) {
    // Store in localStorage for basic analytics
    const clicks = JSON.parse(localStorage.getItem('category_clicks') || '{}');
    clicks[categoryName] = (clicks[categoryName] || 0) + 1;
    localStorage.setItem('category_clicks', JSON.stringify(clicks));
    
    console.log(`Category clicked: ${categoryName}`);
}

// Create dynamic banner slider
function createBannerSlider() {
    const banners = [
        {
            title: "Welcome to BazarBD",
            subtitle: "Your trusted online marketplace in Bangladesh",
            image: "image/banner1.jpg",
            cta: "Shop Now"
        },
        {
            title: "Electronics Sale",
            subtitle: "Up to 50% off on latest gadgets",
            image: "image/banner2.jpg",
            cta: "View Electronics"
        },
        {
            title: "Fashion Week",
            subtitle: "Trending styles at best prices",
            image: "image/banner3.jpg",
            cta: "Explore Fashion"
        }
    ];
    
    // Create banner container if it doesn't exist
    const main = document.querySelector('main');
    const cardContainer = document.querySelector('.card_container');
    
    if (main && cardContainer && !document.querySelector('.banner-slider')) {
        const bannerSlider = document.createElement('div');
        bannerSlider.className = 'banner-slider';
        bannerSlider.innerHTML = `
            <div class="banner-container">
                <div class="banner-slide active">
                    <div class="banner-content">
                        <h1>Welcome to BazarBD</h1>
                        <p>Your trusted online marketplace in Bangladesh</p>
                        <button class="banner-cta" onclick="scrollToCategories()">Shop Now</button>
                    </div>
                </div>
            </div>
            <div class="banner-dots">
                <span class="dot active" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
            </div>
        `;
        
        main.insertBefore(bannerSlider, cardContainer);
        
        // Add banner styles
        addBannerStyles();
        
        // Start auto-slider
        startBannerSlider();
    }
}

// Add banner styles
function addBannerStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .banner-slider {
            position: relative;
            height: 400px;
            margin-bottom: 50px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .banner-container {
            width: 100%;
            height: 100%;
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .banner-slide {
            width: 100%;
            height: 100%;
            display: none;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        
        .banner-slide.active {
            display: flex;
        }
        
        .banner-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }
        
        .banner-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }
        
        .banner-cta {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease 0.4s both;
        }
        
        .banner-cta:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.4);
        }
        
        .banner-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .dot.active {
            background: white;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 768px) {
            .banner-slider {
                height: 300px;
            }
            
            .banner-content h1 {
                font-size: 2rem;
            }
            
            .banner-content p {
                font-size: 1rem;
            }
        }
    `;
    document.head.appendChild(style);
}

// Banner slider functionality
let currentBannerSlide = 0;
const bannerSlides = 3;

function startBannerSlider() {
    setInterval(() => {
        currentBannerSlide = (currentBannerSlide + 1) % bannerSlides;
        updateBannerContent();
    }, 5000);
}

function currentSlide(n) {
    currentBannerSlide = n - 1;
    updateBannerContent();
}

function updateBannerContent() {
    const banners = [
        {
            title: "Welcome to BazarBD",
            subtitle: "Your trusted online marketplace in Bangladesh",
            cta: "Shop Now"
        },
        {
            title: "Electronics Sale",
            subtitle: "Up to 50% off on latest gadgets",
            cta: "View Electronics"
        },
        {
            title: "Fashion Week",
            subtitle: "Trending styles at best prices",
            cta: "Explore Fashion"
        }
    ];
    
    const bannerContent = document.querySelector('.banner-content');
    const dots = document.querySelectorAll('.dot');
    
    if (bannerContent && banners[currentBannerSlide]) {
        const banner = banners[currentBannerSlide];
        bannerContent.innerHTML = `
            <h1>${banner.title}</h1>
            <p>${banner.subtitle}</p>
            <button class="banner-cta" onclick="scrollToCategories()">${banner.cta}</button>
        `;
    }
    
    // Update dots
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentBannerSlide);
    });
}

// Scroll to categories section
function scrollToCategories() {
    const categoriesSection = document.querySelector('.card_container');
    if (categoriesSection) {
        categoriesSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Add loading animation for category cards
function addLoadingAnimation() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
    });
}

// Initialize quick stats counter
function initializeStatsCounter() {
    const statsData = [
        { label: 'Happy Customers', value: 10000, suffix: '+' },
        { label: 'Products', value: 5000, suffix: '+' },
        { label: 'Categories', value: 50, suffix: '+' },
        { label: 'Years of Service', value: 5, suffix: '' }
    ];
    
    // Create stats section
    const main = document.querySelector('main');
    const footer = document.querySelector('.footer');
    
    if (main && footer && !document.querySelector('.stats-section')) {
        const statsSection = document.createElement('section');
        statsSection.className = 'stats-section';
        statsSection.innerHTML = `
            <div class="container">
                <h2>Why Choose BazarBD?</h2>
                <div class="stats-grid">
                    ${statsData.map(stat => `
                        <div class="stat-item">
                            <div class="stat-number" data-target="${stat.value}">${stat.suffix}</div>
                            <div class="stat-label">${stat.label}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        main.insertBefore(statsSection, footer);
        addStatsStyles();
        startStatsCounter();
    }
}

// Add stats styles
function addStatsStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .stats-section {
            padding: 80px 0;
            background: #f8f9fa;
            text-align: center;
        }
        
        .stats-section h2 {
            font-size: 2.5rem;
            margin-bottom: 50px;
            color: #333;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .stat-item {
            background: white;
            padding: 30px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
        }
    `;
    document.head.appendChild(style);
}

// Stats counter animation
function startStatsCounter() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.target);
                const suffix = entry.target.textContent.replace(/\d/g, '');
                animateCounter(entry.target, target, suffix);
                observer.unobserve(entry.target);
            }
        });
    });
    
    statNumbers.forEach(num => observer.observe(num));
}

function animateCounter(element, target, suffix) {
    let current = 0;
    const increment = target / 100;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current) + suffix;
    }, 20);
}

// Initialize everything when DOM loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCategoryCards();
    createBannerSlider();
    addLoadingAnimation();
    initializeStatsCounter();
    
    // Add click tracking to all links
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            const href = this.getAttribute('href');
            const text = this.textContent.trim();
            console.log(`Link clicked: ${text} -> ${href}`);
        });
    });
});

// Expose functions globally
window.showPage = showPage;
window.currentSlide = currentSlide;
window.scrollToCategories = scrollToCategories;