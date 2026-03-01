/**
 * Skeleton Screen Generator
 * Returns HTML strings for different page skeletons.
 */

export function getSkeleton(page) {
    if (page === 'menu') {
        return `
            <div class="skeleton-menu-page"> 
                <div class="skeleton-menu-container">
                    <!-- Title Placeholder -->
                    <div class="skeleton-title-placeholder skeleton"></div>

                    <div class="skeleton-menu-layout">
                        <!-- Sidebar -->
                        <div class="skeleton-sidebar skeleton"></div>
                        
                        <!-- Content Wrapper -->
                        <div class="skeleton-menu-content">
                            <!-- Search Placeholder -->
                            <div class="skeleton-search-placeholder skeleton"></div>
                            
                            <!-- Grid -->
                            <div class="skeleton-grid">
                                ${Array(6).fill(`
                                    <div class="skeleton-card skeleton">
                                        <div class="skeleton-card-img"></div>
                                        <div class="skeleton-card-body">
                                            <div class="skeleton-text title" style="width: 70%;"></div>
                                            <div class="skeleton-text" style="width: 40%;"></div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    if (page === 'tables') {
        return `
            <div class="skeleton-tables-page">
                <div class="tables-intro">
                    <div class="skeleton-title-placeholder skeleton" style="margin: 0 auto 20px; width: 400px;"></div>
                    <div class="skeleton-text skeleton" style="width: 300px; margin: 0 auto 40px; height: 1em;"></div>
                </div>
                <div class="skeleton-map-layout">
                    <div class="skeleton-map skeleton"></div>
                    <div class="skeleton-legend skeleton"></div>
                </div>
            </div>
        `;
    }

    if (page === 'home') {
        return `
            <section class="skeleton-hero">
                <div class="skeleton-hero-left skeleton"></div>
                <!-- Right side text block -->
                <div class="skeleton-hero-right">
                    <div class="skeleton-text subtitle skeleton" style="width: 30%; height: 0.9rem; margin-bottom: 15px;"></div>
                    <div class="skeleton-text title skeleton" style="width: 90%; height: 4.5rem; margin-bottom: 25px;"></div>
                    <div class="skeleton-text skeleton" style="width: 100%; height: 1.2rem; margin-bottom: 10px;"></div>
                    <div class="skeleton-text skeleton" style="width: 95%; height: 1.2rem; margin-bottom: 10px;"></div>
                    <div class="skeleton-text skeleton" style="width: 80%; height: 1.2rem; margin-bottom: 35px;"></div>
                    <div class="skeleton-btn skeleton" style="width: 160px; height: 50px; border-radius: 30px;"></div>
                </div>
            </section>
        `;
    }

    if (page === 'about') {
        return `
            <div class="skeleton-about-page">
                <div class="skeleton-about-container">
                    <!-- Quote -->
                    <div class="skeleton-text skeleton" style="width: 50%; height: 1.2rem; margin: 0 auto 3rem auto;"></div>
                    
                    <!-- Title & Icon Row -->
                    <div class="skeleton-about-hero">
                         <div class="skeleton-text title skeleton" style="width: 40%; height: 5rem;"></div>
                         <div class="skeleton-circle skeleton"></div>
                    </div>

                    <!-- Description -->
                    <div class="skeleton-about-desc">
                        <div class="skeleton-text skeleton" style="width: 80%; margin: 0 auto;"></div>
                        <div class="skeleton-text skeleton" style="width: 90%; margin: 0 auto;"></div>
                        <div class="skeleton-text skeleton" style="width: 70%; margin: 0 auto 2rem auto;"></div>
                        
                        <div class="skeleton-text skeleton" style="width: 85%; margin: 0 auto;"></div>
                        <div class="skeleton-text skeleton" style="width: 75%; margin: 0 auto;"></div>
                    </div>

                    <!-- Social Buttons -->
                    <div class="skeleton-social-row">
                         <div class="skeleton-btn skeleton-small skeleton"></div>
                         <div class="skeleton-btn skeleton-small skeleton"></div>
                         <div class="skeleton-btn skeleton-small skeleton"></div>
                    </div>
                </div>
            </div>
        `;
    }

    // Default Skeleton
    return `
        <div style="padding: 120px 20px; max-width: 900px; margin: 0 auto;">
            <div class="skeleton skeleton-text title"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text subtitle"></div>
            <div class="skeleton skeleton-img" style="margin-top: 40px; height: 300px;"></div>
        </div>
    `;
}
