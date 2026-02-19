<?php
// index.php
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/shops.php';
session_start();

// Serviceable cities list
$serviceableCities = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Hyderabad', 'Pune', 'Jaipur', 'Kolkata', 'Ahmedabad', 'Pimpri Chinchwad'];

$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$autoDetected = false;

$vehicles = $db->getAllVehicles();

// Filter vehicles
if ($search || ($location && $location !== 'All')) {
    $vehicles = array_filter($vehicles, function($v) use ($search, $location) {
        if (($v['availability_status'] ?? 'Available') === 'Maintenance') {
            return false;
        }

        $matchesSearch = true;
        $matchesLocation = true;

        if ($search) {
            $matchesSearch = stripos($v['make'], $search) !== false || stripos($v['model'], $search) !== false;
        }

        if ($location && $location !== 'All') {
            $matchesLocation = isset($v['location']) && $v['location'] === $location;
        }

        return $matchesSearch && $matchesLocation;
    });
} else {
    // Default: filter out maintenance vehicles only
    $vehicles = array_filter($vehicles, function($v) {
        return ($v['availability_status'] ?? 'Available') !== 'Maintenance';
    });
}
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<style>
/* Location Detection Banner */
.location-banner {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 1px solid #bae6fd;
    border-radius: 1rem;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    animation: slideDown 0.4s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.location-banner .loc-info {
    display: flex; align-items: center; gap: 0.75rem;
}
.location-banner .loc-icon {
    font-size: 1.5rem;
}
.location-banner .loc-text h4 {
    font-size: 0.95rem; margin-bottom: 0.15rem; color: #0369a1;
}
.location-banner .loc-text p {
    font-size: 0.8rem; color: #64748b; margin: 0;
}
.location-banner .loc-actions {
    display: flex; gap: 0.5rem; align-items: center;
}
.btn-change-city {
    padding: 0.4rem 1rem; font-size: 0.8rem; font-weight: 600; border-radius: 0.5rem;
    border: 1px solid #0ea5e9; background: white; color: #0ea5e9; cursor: pointer;
    transition: all 0.2s; font-family: 'Outfit', sans-serif;
}
.btn-change-city:hover { background: #0ea5e9; color: white; transform: translateY(-1px); }
.btn-change-city:active { transform: scale(0.95); }

/* Not Serviceable Banner */
.not-serviceable-banner {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    border: 1px solid #fecaca;
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    text-align: center;
    animation: slideDown 0.4s ease;
}
.not-serviceable-banner .ns-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
.not-serviceable-banner h3 { color: #dc2626; margin-bottom: 0.25rem; }
.not-serviceable-banner p { color: #64748b; font-size: 0.9rem; margin-bottom: 1rem; }
.not-serviceable-banner .city-pills {
    display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;
}
.city-pill {
    padding: 0.35rem 0.9rem; border-radius: 2rem; font-size: 0.8rem; font-weight: 500;
    background: white; border: 1px solid #e2e8f0; color: #334155; cursor: pointer;
    transition: all 0.2s; text-decoration: none; font-family: 'Outfit', sans-serif;
}
.city-pill:hover { background: var(--primary); color: white; border-color: var(--primary); transform: translateY(-1px); }
.city-pill:active { transform: scale(0.95); }

/* Detecting spinner */
.detecting-banner {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 1rem;
    padding: 1.25rem;
    text-align: center;
    margin-bottom: 1.5rem;
    color: #64748b;
    font-size: 0.9rem;
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

/* City selector modal */
.city-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1000;
    display: none; align-items: center; justify-content: center; padding: 1rem;
}
.city-modal-overlay.active { display: flex; }
.city-modal {
    background: white; border-radius: 1.25rem; padding: 2rem; max-width: 480px; width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: slideDown 0.3s ease;
}
.city-modal h3 { text-align: center; margin-bottom: 0.5rem; }
.city-modal p { text-align: center; font-size: 0.85rem; color: #64748b; margin-bottom: 1.5rem; }
.city-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.6rem;
}
.city-grid a {
    padding: 0.75rem; text-align: center; border-radius: 0.75rem; font-weight: 600;
    font-size: 0.9rem; border: 2px solid #e2e8f0; color: #334155;
    text-decoration: none; transition: all 0.2s;
}
.city-grid a:hover { border-color: var(--primary); color: var(--primary); background: #f0f4ff; }
.city-grid a.active-city { border-color: var(--primary); background: var(--primary); color: white; }
</style>

<!-- Location Detection Banner (shown dynamically by JS) -->
<div id="locationBanner"></div>

<!-- Modern Hero Section -->
<?php if (!$search && (!$location || $location === 'All')): ?>
<div class="hero">
    <h1>Find Your Perfect Drive</h1>
    <p>Premium vehicles for every occasion. Transparent pricing. Instant booking.</p>
    
    <form action="/" method="GET" class="hero-search" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; align-items: center;">
        <select name="location" id="citySelect" class="form-control" style="border: none; padding: 1rem; border-radius: 0.5rem;">
            <option value="All">All Cities</option>
            <?php foreach ($serviceableCities as $city): ?>
                <option value="<?= $city ?>" <?= $location === $city ? 'selected' : '' ?>><?= $city ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="search" placeholder="Search by make or model..." style="border: none; padding: 1rem; border-radius: 0.5rem;" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary" style="height: 100%;">Search</button>
    </form>
</div>
<?php else: ?>
<div style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h2>
        <?php if($search): ?>Results for "<?= htmlspecialchars($search) ?>"<?php endif; ?>
        <?php if($location && $location !== 'All'): ?><span style="font-weight: 400; font-size: 1rem; color: var(--secondary);">in <?= htmlspecialchars($location) ?></span><?php endif; ?>
    </h2>
    <a href="/" class="btn btn-outline btn-sm">Clear Search</a>
</div>
<?php endif; ?>

<!-- Vehicle Listing -->
<div class="vehicle-grid">
    <?php foreach ($vehicles as $v): ?>
    <div class="vehicle-card" data-id="<?= $v['id'] ?>" data-price="<?= $v['price_per_day'] ?>" data-model="<?= htmlspecialchars($v['vehicle_name']) ?>" data-img="<?= htmlspecialchars($v['image_url']) ?>" data-location="<?= htmlspecialchars($v['location'] ?? 'Mumbai') ?>">
        <div class="vehicle-img-wrapper">
            <img src="<?= htmlspecialchars($v['image_url']) ?>" alt="<?= htmlspecialchars($v['vehicle_name']) ?>" class="vehicle-img">
            <?php if(isset($v['location'])): ?>
                <span style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; backdrop-filter: blur(4px);">
                    Location: <?= htmlspecialchars($v['location']) ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="vehicle-info">
            <div class="vehicle-header">
                <div>
                    <h3 class="vehicle-title"><?= htmlspecialchars($v['vehicle_name']) ?></h3>
                    <span style="font-size: 0.8rem; color: var(--secondary); background: #f1f5f9; padding: 2px 8px; border-radius: 10px;"><?= htmlspecialchars($v['vehicle_type']) ?></span>
                </div>
                <div class="vehicle-price">₹<?= number_format($v['price_per_day']) ?><span>/day</span></div>
            </div>
            <p class="vehicle-desc"><?= htmlspecialchars($v['description']) ?></p>
            <div class="vehicle-footer">
                <div style="font-size: 0.9rem; color: var(--secondary);">
                    Model: <?= $v['year'] ?> &bull; Available Now
                </div>
                <a href="/book.php?id=<?= $v['id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (count($vehicles) === 0): ?>
    <div style="text-align: center; padding: 4rem; color: var(--secondary);">
        <h3>No vehicles found</h3>
        <p>Try adjusting your search criteria.</p>
    </div>
<?php endif; ?>


<!-- City Selector Modal -->
<div class="city-modal-overlay" id="cityModal" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="city-modal">
        <h3>Select Your City</h3>
        <p>Choose a city to see available vehicles</p>
        <div class="city-grid">
            <?php foreach ($serviceableCities as $city): ?>
                <a href="/?location=<?= $city ?>" class="<?= $location === $city ? 'active-city' : '' ?>"><?= $city ?></a>
            <?php endforeach; ?>
            <a href="/" style="grid-column: span 3; background: #f1f5f9;">Show All Cities</a>
        </div>
    </div>
</div>

<script>
    // ===================== LOCATION AUTO-DETECTION =====================
    const SERVICEABLE_CITIES = <?= json_encode($serviceableCities) ?>;
    const currentLocation = '<?= addslashes($location) ?>';
    const bannerEl = document.getElementById('locationBanner');

    // City name aliases for better matching
    const CITY_ALIASES = {
        'bengaluru': 'Bangalore',
        'bangalore': 'Bangalore',
        'mumbai': 'Mumbai',
        'bombay': 'Mumbai',
        'new delhi': 'Delhi',
        'delhi': 'Delhi',
        'chennai': 'Chennai',
        'madras': 'Chennai',
        'hyderabad': 'Hyderabad',
        'pune': 'Pune',
        'jaipur': 'Jaipur',
        'kolkata': 'Kolkata',
        'calcutta': 'Kolkata',
        'ahmedabad': 'Ahmedabad',
        'amdavad': 'Ahmedabad'
    };

    function matchCity(detectedCity) {
        if (!detectedCity) return null;
        const lower = detectedCity.toLowerCase().trim();
        
        // Check aliases first
        if (CITY_ALIASES[lower]) return CITY_ALIASES[lower];
        
        // Check exact match
        for (const city of SERVICEABLE_CITIES) {
            if (city.toLowerCase() === lower) return city;
        }
        
        // Check partial match
        for (const city of SERVICEABLE_CITIES) {
            if (lower.includes(city.toLowerCase()) || city.toLowerCase().includes(lower)) return city;
        }
        
        return null;
    }

    function showServiceableBanner(city, rawCity) {
        bannerEl.innerHTML = `
            <div class="location-banner">
                <div class="loc-info">
                    <span class="loc-icon">📍</span>
                    <div class="loc-text">
                        <h4>Showing vehicles in ${city}</h4>
                        <p>Detected from your location${rawCity !== city ? ' (' + rawCity + ')' : ''}</p>
                    </div>
                </div>
                <div class="loc-actions">
                    <button class="btn-change-city" onclick="document.getElementById('cityModal').classList.add('active')">Change City</button>
                </div>
            </div>
        `;
    }

    function showNotServiceableBanner(rawCity) {
        let pills = SERVICEABLE_CITIES.map(c => `<a href="/?location=${c}" class="city-pill">${c}</a>`).join('');
        bannerEl.innerHTML = `
            <div class="not-serviceable-banner">
                <div class="ns-icon">🚫</div>
                <h3>We're not operating in ${rawCity} yet</h3>
                <p>RentRide is currently available in these cities. Pick one to browse vehicles:</p>
                <div class="city-pills">${pills}</div>
            </div>
        `;
    }

    function detectLocation() {
        // Skip if user already selected a city manually
        if (currentLocation && currentLocation !== 'All') {
            // Show banner for selected city
            showServiceableBanner(currentLocation, currentLocation);
            return;
        }

        // Check if we already have a cached city
        const cachedCity = sessionStorage.getItem('rentride_city');
        if (cachedCity) {
            const matched = matchCity(cachedCity);
            if (matched) {
                // Auto-redirect to cached city
                window.location.href = '/?location=' + matched;
                return;
            } else {
                showNotServiceableBanner(cachedCity);
                return;
            }
        }

        if (!navigator.geolocation) {
            // No geolocation support — show all
            return;
        }

        // Show detecting banner
        bannerEl.innerHTML = '<div class="detecting-banner">📡 Detecting your location...</div>';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`)
                    .then(res => res.json())
                    .then(data => {
                        const rawCity = data.address.city || data.address.town || data.address.village || data.address.state_district || data.address.county || '';
                        
                        // Cache detected city
                        sessionStorage.setItem('rentride_city', rawCity);
                        
                        const matched = matchCity(rawCity);
                        if (matched) {
                            // Redirect to filter by this city
                            window.location.href = '/?location=' + matched;
                        } else {
                            showNotServiceableBanner(rawCity || 'your area');
                        }
                    })
                    .catch(() => {
                        bannerEl.innerHTML = '';
                    });
            },
            function(error) {
                bannerEl.innerHTML = '';
                // Silently fail — user can select city manually
            },
            { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
        );
    }

    // Start location detection on page load
    detectLocation();


    // Shop data from PHP
    const shopsData = <?= json_encode($SHOPS) ?>;

    function populateShops(city) {
        const select = document.getElementById('pickup_shop');
        const addrDisplay = document.getElementById('shopAddress');
        select.innerHTML = '<option value="">-- Select a shop --</option>';
        addrDisplay.innerHTML = '';
        const cityShops = shopsData[city] || [];
        cityShops.forEach(shop => {
            const opt = document.createElement('option');
            opt.value = shop.id + '|' + shop.name + '|' + shop.address + '|' + shop.phone + '|' + shop.lat + '|' + shop.lng;
            opt.textContent = shop.name;
            select.appendChild(opt);
        });
        if (cityShops.length === 1) {
            select.selectedIndex = 1;
            showShopDetails(select.value, 'shopAddress');
        }
    }

    function showShopDetails(val, displayId) {
        const display = document.getElementById(displayId);
        if (!val) { display.innerHTML = ''; return; }
        const p = val.split('|');
        const addr = p[2] || '';
        const phone = p[3] || '';
        const lat = p[4] || '';
        const lng = p[5] || '';
        let html = 'Loc: ' + addr;
        if (phone) html += '<br>Phone: <a href="tel:' + phone.replace(/\s/g,'') + '" style="color:#4f46e5; text-decoration:none;">' + phone + '</a>';
        if (lat && lng) html += ' &bull; <a href="https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng + '" target="_blank" style="color:#16a34a; text-decoration:none; font-weight:600;">Navigate</a>';
        display.innerHTML = html;
    }

    // Show details when shop selected
    document.getElementById('pickup_shop')?.addEventListener('change', function() {
        showShopDetails(this.value, 'shopAddress');
    });

</script>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
