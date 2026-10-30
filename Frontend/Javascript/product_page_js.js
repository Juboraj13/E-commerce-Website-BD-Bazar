// Product pages JavaScript for Electronics, Fashion, and Bikes

// Product filtering and sorting functionality
class ProductManager {
    constructor() {
        this.products = [];
        this.filteredProducts = [];
        this.currentFilters = {
            priceMin: 0,
            priceMax: Infinity,
            category: 'all',
            availability: 'all'
        };
        this.currentSort = 'default';
        
        this.init();
    }
    
    init() {
        this.loadProducts();
        this.createFilterPanel();
        this.initializeProductGrid();
        this.bindEvents();
    }
    
    loadProducts() {
        const productElements = document.querySelectorAll('.product');
        this.products = Array.from(productElements).map((element, index) => {
            const title = element.querySelector('.product-title').textContent.trim();
            const priceText = element.querySelector('.product-price').textContent.trim();
            const image = element.querySelector('img').src;
            const price = this.extractPrice(priceText);
            const isOutOfStock = priceText.toLowerCase().includes('out of stock');
            
            return {
                id: index,
                title,
                price,
                priceText,
                image,
                element,
                isOutOfStock,
                category: this.detectCategory(title)
            };
        });
        
        this.filteredProducts = [...this.products];
    }
    
    extractPrice(priceText) {
        const matches = priceText.match(/[\d,]+/);
        return matches ? parseFloat(matches[0].replace(/,/g, '')) : 0;
    }
    
    detectCategory(title) {
        const lowerTitle = title.toLowerCase();
        
        // Electronics categories
        if (lowerTitle.includes('iphone') || lowerTitle.includes('phone')) return 'phone';
        if (lowerTitle.includes('macbook') || lowerTitle.includes('laptop')) return 'laptop';
        if (lowerTitle.includes('watch')) return 'watch';
        if (lowerTitle.includes('airpod') || lowerTitle.includes('headphone')) return 'audio';
        if (lowerTitle.includes('ipad') || lowerTitle.includes('tablet')) return 'tablet';
        
        // Bike categories
        if (lowerTitle.includes('yamaha')) return 'yamaha';
        if (lowerTitle.includes('honda')) return 'honda';
        if (lowerTitle.includes('suzuki')) return 'suzuki';
        if (lowerTitle.includes('royal enfield')) return 'royal-enfield';
        
        return 'other';
    }
    
    createFilterPanel() {
        const main = document.querySelector('main');
        const productsSection = document.querySelector('.products');
        
        if (!main || !productsSection || document.querySelector('.filter-panel')) return;
        
        const filterPanel = document.createElement('div');
        filterPanel.className = 'filter-panel';
        filterPanel.innerHTML = `
            <div class="container">
                <div class="filter-controls">
                    <div class="filter-group">
                        <label>Sort by:</label>
                        <select id="sortSelect">
                            <option value="default">Default</option>
                            <option value="name">Name (A-Z)</option>
                            <option value="price-low">Price (Low to High)</option>
                            <option value="price-high">Price (High to Low)</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Price Range:</label>
                        <div class="price-range">
                            <input type="number" id="minPrice" placeholder="Min" min="0">
                            <span>-</span>
                            <input type="number" id="maxPrice" placeholder="Max" min="0">
                            <button id="applyPriceFilter">Apply</button>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Availability:</label>
                        <select id="availabilityFilter">
                            <option value="all">All Products</option>
                            <option value="in-stock">In Stock</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button id="clearFilters">Clear All</button>
                        <button id="toggleView">Grid View</button>
                    </div>
                    
                    <div class="results-count">
                        <span id="resultsCount">Showing ${this.products.length} products</span>
                    </div>
                </div>
            </div>
        `;
        
        main.insertBefore(filterPanel, productsSection);
        this.addFilterStyles();
    }
    
    addFilterStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .filter-panel {
                background: #f8f9fa;
                padding: 20px 0;
                margin-bottom: 30px;
                border-radius: 8px;
            }
            
            .filter-controls {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                align-items: center;
                justify-content: space-between;
            }
            
            .filter-group