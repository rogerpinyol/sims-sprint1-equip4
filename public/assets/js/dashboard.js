/**
 * Client Dashboard Enhancement
 * Real-world application of event listeners for interactive map and vehicle filtering
 * 
 * Event handlers used:
 * - load: Initialize dashboard and map
 * - resize: Adjust map and sidebar on window resize
 * - scroll: Handle sidebar scroll position
 * - change: Real-time filter updates
 * - click: Vehicle selection and map interactions
 * - error: Handle map tile loading errors
 * - focus/blur: Search field interactions
 */

class DashboardHandler {
    constructor() {
        this.vehicles = [];
        this.markers = {};
        this.selectedVehicle = null;
        this.map = null;
        this.userLocation = null;
        this.filterState = {
            statuses: ['available', 'booked', 'maintenance', 'charging'],
            searchText: ''
        };
        
        // Event: load - Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    /**
     * Initialize dashboard
     */
    init() {
        console.log('🚀 Initializing dashboard...');
        
        // Set up event handlers
        this.setupEventHandlers();
        
        // Load vehicles data
        this.loadVehicles();
        
        // Event: resize - Handle window resize
        window.addEventListener('resize', () => this.onResize());
        
        // Event: scroll - Track sidebar scroll
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            const vehiclesList = sidebar.querySelector('#vehiclesList, #vehiclesTableWrap');
            if (vehiclesList) {
                vehiclesList.addEventListener('scroll', () => this.onScroll());
            }
        }
        
        // Event: error - Handle tile loading errors
        window.addEventListener('error', (e) => this.onError(e), true);
        
        console.log('✅ Dashboard initialized');
    }

    /**
     * Setup all event handlers
     */
    setupEventHandlers() {
        // Sidebar toggle buttons
        const btnOpen = document.getElementById('btnOpenSidebar');
        const btnClose = document.getElementById('btnCloseSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const sidebar = document.getElementById('sidebar');

        // Event: click - Open sidebar
        if (btnOpen) {
            btnOpen.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (overlay) {
                    overlay.classList.remove('pointer-events-none', 'opacity-0');
                }
            });
        }

        // Event: click - Close sidebar
        if (btnClose) {
            btnClose.addEventListener('click', () => {
                this.closeSidebar();
            });
        }

        // Event: click - Close sidebar via overlay
        if (overlay) {
            overlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }

        // Status filter form
        const filterForm = document.getElementById('statusFilterForm');
        if (filterForm) {
            // Event: change - Update filters in real-time
            const checkboxes = filterForm.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => this.onFilterChange());
            });

            // Event: submit - Apply filters
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }

        // Recenter button
        const btnRecenter = document.getElementById('btnRecenter');
        if (btnRecenter) {
            // Event: click - Recenter map on user location
            btnRecenter.addEventListener('click', () => this.recenterMap());
        }

        // Add search functionality
        this.addSearchField();
    }

    /**
     * Add search field with event handlers
     */
    addSearchField() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        const filterSection = sidebar.querySelector('.p-3.border-t');
        if (!filterSection) return;

        const searchDiv = document.createElement('div');
        searchDiv.className = 'mb-3 pb-3 border-b border-slate-200';
        searchDiv.innerHTML = `
            <label class="text-xs text-slate-500 block mb-1">Search vehicles</label>
            <input 
                type="text" 
                id="vehicleSearch" 
                class="w-full px-2 py-1 text-sm border rounded focus:ring-2 focus:ring-blue-500" 
                placeholder="Search by model or VIN..."
            />
        `;
        
        filterSection.insertBefore(searchDiv, filterSection.firstChild);

        const searchInput = document.getElementById('vehicleSearch');
        
        // Event: focus - Clear placeholder, highlight field
        searchInput.addEventListener('focus', () => {
            searchInput.classList.add('ring-2', 'ring-blue-500');
        });

        // Event: blur - Remove highlight
        searchInput.addEventListener('blur', () => {
            searchInput.classList.remove('ring-2', 'ring-blue-500');
        });

        // Event: input - Real-time search
        searchInput.addEventListener('input', (e) => {
            this.filterState.searchText = e.target.value.toLowerCase();
            this.applyFilters();
        });
    }

    /**
     * Close sidebar (mobile)
     */
    closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (overlay) {
            overlay.classList.add('pointer-events-none', 'opacity-0');
        }
    }

    /**
     * Event: resize - Adjust map size and sidebar
     */
    onResize() {
        // Invalidate map size if Leaflet is available
        if (this.map && this.map.invalidateSize) {
            setTimeout(() => {
                this.map.invalidateSize();
            }, 100);
        }

        // On desktop, ensure sidebar is visible
        if (window.innerWidth >= 768) {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full');
            }
        }

        console.log(`📐 Window resized: ${window.innerWidth}x${window.innerHeight}`);
    }

    /**
     * Event: scroll - Track scroll position
     */
    onScroll() {
        const vehiclesList = document.querySelector('#vehiclesList, #vehiclesTableWrap');
        if (!vehiclesList) return;

        const scrollPercentage = (vehiclesList.scrollTop / (vehiclesList.scrollHeight - vehiclesList.clientHeight)) * 100;
        
        // Log scroll milestones
        if (scrollPercentage > 75 && !this.scrolledToBottom) {
            console.log('📜 User scrolled to bottom of vehicle list');
            this.scrolledToBottom = true;
        }
    }

    /**
     * Event: error - Handle errors gracefully
     */
    onError(event) {
        if (event.target && event.target.tagName === 'IMG') {
            console.warn('Image loading error:', event.target.src);
        }
        
        // Handle Leaflet tile errors
        if (event.message && event.message.includes('tile')) {
            console.warn('Map tile loading error');
        }
    }

    /**
     * Event: change - Filter checkbox changed
     */
    onFilterChange() {
        const checkboxes = document.querySelectorAll('#statusFilterForm input[type="checkbox"]:checked');
        this.filterState.statuses = Array.from(checkboxes).map(cb => cb.value);
        
        console.log('🔍 Filters changed:', this.filterState);
        
        // Show visual feedback
        this.showFilterFeedback();
    }

    /**
     * Show visual feedback when filters change
     */
    showFilterFeedback() {
        const form = document.getElementById('statusFilterForm');
        if (!form) return;

        const badge = document.createElement('span');
        badge.className = 'inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded absolute top-0 right-0 animate-pulse';
        badge.textContent = 'Filters updated';
        
        form.style.position = 'relative';
        form.appendChild(badge);
        
        setTimeout(() => badge.remove(), 2000);
    }

    /**
     * Apply filters to vehicle list
     */
    applyFilters() {
        const filteredVehicles = this.vehicles.filter(vehicle => {
            // Status filter
            if (!this.filterState.statuses.includes(vehicle.status)) {
                return false;
            }
            
            // Search filter
            if (this.filterState.searchText) {
                const searchText = this.filterState.searchText;
                const model = (vehicle.model || '').toLowerCase();
                const vin = (vehicle.vin || '').toLowerCase();
                
                if (!model.includes(searchText) && !vin.includes(searchText)) {
                    return false;
                }
            }
            
            return true;
        });

        this.renderVehicles(filteredVehicles);
        this.updateMapMarkers(filteredVehicles);
        
        console.log(`✅ Applied filters: ${filteredVehicles.length}/${this.vehicles.length} vehicles shown`);
    }

    /**
     * Load vehicles from API
     */
    async loadVehicles() {
        try {
            const response = await fetch('/client/api/vehicles');
            
            if (!response.ok) {
                throw new Error('Failed to load vehicles');
            }
            
            this.vehicles = await response.json();
            this.applyFilters(); // Initial render
            
            console.log(`📦 Loaded ${this.vehicles.length} vehicles`);
        } catch (error) {
            console.error('Error loading vehicles:', error);
            this.showError('Failed to load vehicles. Please refresh the page.');
        }
    }

    /**
     * Render vehicles list
     */
    renderVehicles(vehicles) {
        const mobileList = document.getElementById('vehiclesList');
        const desktopTable = document.getElementById('vehiclesTableWrap');

        if (!vehicles || vehicles.length === 0) {
            const emptyMessage = '<div class="text-slate-500 text-sm">No vehicles found</div>';
            if (mobileList) mobileList.innerHTML = emptyMessage;
            if (desktopTable) desktopTable.innerHTML = emptyMessage;
            return;
        }

        // Render mobile cards
        if (mobileList) {
            mobileList.innerHTML = vehicles.map(vehicle => this.renderVehicleCard(vehicle)).join('');
            
            // Attach click handlers
            mobileList.querySelectorAll('[data-vehicle-id]').forEach(card => {
                card.addEventListener('click', () => {
                    const vehicleId = card.dataset.vehicleId;
                    this.selectVehicle(vehicleId);
                });
            });
        }

        // Render desktop table
        if (desktopTable) {
            desktopTable.innerHTML = this.renderVehicleTable(vehicles);
            
            // Attach click handlers
            desktopTable.querySelectorAll('[data-vehicle-id]').forEach(row => {
                row.addEventListener('click', () => {
                    const vehicleId = row.dataset.vehicleId;
                    this.selectVehicle(vehicleId);
                });
            });
        }
    }

    /**
     * Render vehicle card (mobile)
     */
    renderVehicleCard(vehicle) {
        const statusColors = {
            available: 'bg-green-100 text-green-800',
            booked: 'bg-yellow-100 text-yellow-800',
            maintenance: 'bg-orange-100 text-orange-800',
            charging: 'bg-blue-100 text-blue-800'
        };

        return `
            <div 
                class="p-3 rounded-lg border border-slate-200 hover:border-blue-500 cursor-pointer transition" 
                data-vehicle-id="${vehicle.id}"
            >
                <div class="flex justify-between items-start mb-2">
                    <div class="font-medium">${vehicle.model || 'Unknown'}</div>
                    <span class="px-2 py-1 rounded text-xs ${statusColors[vehicle.status] || 'bg-gray-100 text-gray-800'}">
                        ${vehicle.status}
                    </span>
                </div>
                <div class="text-xs text-slate-500">
                    <div>VIN: ${vehicle.vin || 'N/A'}</div>
                    <div>Battery: ${vehicle.battery_capacity || 0} kWh</div>
                </div>
            </div>
        `;
    }

    /**
     * Render vehicle table (desktop)
     */
    renderVehicleTable(vehicles) {
        return `
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 border-b">
                    <tr>
                        <th class="text-left py-2">Model</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-right py-2">Battery</th>
                    </tr>
                </thead>
                <tbody>
                    ${vehicles.map(vehicle => `
                        <tr 
                            class="border-b hover:bg-slate-50 cursor-pointer" 
                            data-vehicle-id="${vehicle.id}"
                        >
                            <td class="py-2">${vehicle.model || 'Unknown'}</td>
                            <td class="py-2">
                                <span class="px-2 py-1 rounded text-xs bg-${this.getStatusColor(vehicle.status)}-100 text-${this.getStatusColor(vehicle.status)}-800">
                                    ${vehicle.status}
                                </span>
                            </td>
                            <td class="py-2 text-right">${vehicle.battery_capacity || 0} kWh</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    /**
     * Get status color
     */
    getStatusColor(status) {
        const colors = {
            available: 'green',
            booked: 'yellow',
            maintenance: 'orange',
            charging: 'blue'
        };
        return colors[status] || 'gray';
    }

    /**
     * Select vehicle and highlight on map
     */
    selectVehicle(vehicleId) {
        this.selectedVehicle = vehicleId;
        console.log(`🚗 Selected vehicle: ${vehicleId}`);
        
        // Highlight on map
        if (this.markers[vehicleId]) {
            const marker = this.markers[vehicleId];
            marker.openPopup();
            
            if (this.map) {
                this.map.setView(marker.getLatLng(), 15, {
                    animate: true,
                    duration: 0.5
                });
            }
        }
        
        // Close mobile sidebar after selection
        if (window.innerWidth < 768) {
            this.closeSidebar();
        }
    }

    /**
     * Update map markers based on filtered vehicles
     */
    updateMapMarkers(vehicles) {
        // Implementation would update Leaflet markers
        // This is a placeholder for the actual map integration
        console.log(`🗺️ Updated map with ${vehicles.length} markers`);
    }

    /**
     * Recenter map on user location
     */
    recenterMap() {
        if (navigator.geolocation) {
            const btn = document.getElementById('btnRecenter');
            if (btn) {
                btn.classList.add('animate-pulse');
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    if (this.map) {
                        this.map.setView([this.userLocation.lat, this.userLocation.lng], 14, {
                            animate: true
                        });
                    }
                    
                    console.log('📍 Recentered on user location');
                    
                    if (btn) {
                        btn.classList.remove('animate-pulse');
                    }
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    this.showError('Unable to get your location');
                    
                    if (btn) {
                        btn.classList.remove('animate-pulse');
                    }
                }
            );
        } else {
            this.showError('Geolocation not supported by your browser');
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-in';
        errorDiv.textContent = message;
        
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => errorDiv.remove(), 300);
        }, 3000);
    }
}

// Initialize dashboard handler
const dashboardHandler = new DashboardHandler();
