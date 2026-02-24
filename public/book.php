<?php
// book.php
require_once __DIR__ . '/../apps/backend/config/db.php';
require_once __DIR__ . '/../apps/backend/config/shops.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$user = $db->getUserById($_SESSION['user_id']);
if (($user['role'] ?? 'user') === 'blacklisted') {
    $_SESSION['flash_error'] = "Your account has been blacklisted and cannot make new bookings. Please contact support.";
    header("Location: /my-bookings.php");
    exit;
}

$vehicleId = $_GET['id'] ?? null;
if (!$vehicleId) {
    header("Location: /");
    exit;
}

$vehicle = $db->getVehicle($vehicleId);

if (!$vehicle) {
    $_SESSION['flash_error'] = "Vehicle not found.";
    header("Location: /");
    exit;
}

if (($vehicle['availability_status'] ?? 'Available') === 'Maintenance') {
    $_SESSION['flash_error'] = "Vehicle is currently under maintenance and cannot be booked.";
    header("Location: /");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    if (strtotime($endDate) <= strtotime($startDate)) {
        $error = "End date must be after start date";
    } elseif (strtotime($startDate) < strtotime('today')) {
        $error = "Booking cannot be in the past";
    } elseif ($db->checkConflict($vehicleId, $startDate, $endDate)) {
        $error = "Vehicle is already booked for these dates.";
    } else {
        // Smart Feature: Dynamic Pricing
        $basePrice = $db->calculateDynamicPrice($vehicleId, $startDate, $endDate);
        
        // Smart Feature: Driver Mode
        $withDriver = isset($_POST['with_driver']);
        $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
        $totalPrice = $db->calculateTotalWithDriver($basePrice, $withDriver, $days);
        
        // Delivery Option
        $deliveryMode = $_POST['delivery_mode'] ?? 'pickup';
        $deliveryAddress = '';
        $pickupShop = '';
        if ($deliveryMode === 'home_delivery') {
            $deliveryAddress = trim($_POST['delivery_address'] ?? '');
            $totalPrice += 500;
        } else {
            $pickupShop = $_POST['pickup_shop'] ?? '';
        }
        
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        
        $bookingId = $db->createBooking($_SESSION['user_id'], $vehicleId, $startDate, $endDate, $totalPrice, 5000, $deliveryMode, $deliveryAddress, $pickupShop, $customerPhone);
        
        // Redirect to payment page
        header("Location: /payment.php?booking_id=" . $bookingId);
        exit;
    }
}
?>

<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div class="container" style="max-width: 800px; margin-top: 2rem;">
    <div style="background: var(--surface); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);">
        <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" alt="Car" style="width: 100%; height: 300px; object-fit: cover;">
        
        <div style="padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($vehicle['vehicle_name']) ?></h1>
                    <span style="background: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 99px; font-size: 0.875rem; font-weight: 500; display: inline-block; margin-bottom: 1rem;"><?= htmlspecialchars($vehicle['vehicle_type']) ?></span>
                    <p style="color: var(--secondary)"><?= htmlspecialchars($vehicle['description']) ?></p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">₹<?= number_format($vehicle['price_per_day']) ?></div>
                    <div style="color: var(--secondary)">per day</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" style="background: #f8fafc; padding: 2rem; border-radius: var(--radius); display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label><strong>Contact Number</strong></label>
                    <input type="tel" name="customer_phone" class="form-control" required placeholder="+91 98765 43210" pattern="[+]?[0-9\s]{10,15}" title="Enter a valid phone number">
                </div>

                <div class="form-group" style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; background: white; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 0.5rem;">
                    <label for="with_driver" style="margin-bottom: 0; cursor: pointer;">
                        <strong>Needs Driver?</strong> (+₹500/day)
                    </label>
                    <input type="checkbox" name="with_driver" id="with_driver" style="width: 20px; height: 20px;">
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="margin-bottom: 0.5rem; display: block;"><strong>Delivery Option</strong></label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="flex: 1; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; background: white; padding: 0.75rem; border-radius: 0.5rem; border: 2px solid #e2e8f0; transition: border-color 0.2s;">
                            <input type="radio" name="delivery_mode" value="pickup" checked onchange="toggleDeliveryBook()" style="accent-color: var(--primary);">
                            <span>Pickup from Shop</span>
                        </label>
                        <label style="flex: 1; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; background: white; padding: 0.75rem; border-radius: 0.5rem; border: 2px solid #e2e8f0; transition: border-color 0.2s;">
                            <input type="radio" name="delivery_mode" value="home_delivery" onchange="toggleDeliveryBook()" style="accent-color: var(--primary);">
                            <span>Home Delivery (+₹500)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="pickupShopBook" style="grid-column: 1 / -1;">
                    <label><strong>Select Pickup Shop</strong></label>
                    <select name="pickup_shop" id="pickup_shop_book" class="form-control" required>
                        <option value="">-- Select a shop --</option>
                        <?php 
                        $vehCity = $vehicle['location'] ?? 'Mumbai';
                        $cityShops = $SHOPS[$vehCity] ?? [];
                        foreach ($cityShops as $shop): ?>
                            <option value="<?= $shop['id'] ?>|<?= htmlspecialchars($shop['name']) ?>|<?= htmlspecialchars($shop['address']) ?>|<?= $shop['phone'] ?>|<?= $shop['lat'] ?>|<?= $shop['lng'] ?>">
                                <?= htmlspecialchars($shop['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="shopAddrBook" style="display:block; margin-top:4px; color:#475569; font-size:0.75rem;"></small>
                </div>

                <div class="form-group" id="deliveryAddrBook" style="display: none; grid-column: 1 / -1;">
                    <label>Delivery Address</label>
                    <textarea name="delivery_address" id="delivery_addr_input" class="form-control" rows="2" placeholder="Enter your full delivery address..."></textarea>
                </div>
                
                <div style="grid-column: 1 / -1; background: #f1f5f9; padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #64748b;">Rental Cost</span>
                        <span id="rental-cost">₹0.00</span>
                    </div>
                    <div id="driver-row" style="display: none; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #64748b;">Driver Service</span>
                        <span id="driver-cost">₹0.00</span>
                    </div>
                    <div id="delivery-row" style="display: none; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #64748b;">Delivery Charge</span>
                        <span>+₹500.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #16a34a;">
                         <span>Refundable Security Deposit</span>
                         <span>+₹5,000.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e2e8f0; font-size: 1.25rem; font-weight: 700;">
                        <span>Total Payable</span>
                        <span id="total-price" style="color: var(--primary);">₹0.00</span>
                    </div>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; font-size: 0.8rem; color: var(--secondary); line-height: 1.4;">
                        <input type="checkbox" name="agree_terms" required style="margin-top: 2px; accent-color: var(--primary); min-width: 16px;">
                        I agree to the <a href="/terms-of-service.php" target="_blank" style="color: var(--primary); font-weight: 600;">Terms of Service</a>,&nbsp;<a href="/privacy-policy.php" target="_blank" style="color: var(--primary); font-weight: 600;">Privacy Policy</a>&nbsp;and&nbsp;<a href="/refund-policy.php" target="_blank" style="color: var(--primary); font-weight: 600;">Refund Policy</a>
                    </label>
                </div>

                <div style="grid-column: 1 / -1; display: flex; justify-content: flex-end; align-items: center;">
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const driverInput = document.getElementById('with_driver');
    const rentalDisplay = document.getElementById('rental-cost');
    const driverDisplay = document.getElementById('driver-cost');
    const driverRow = document.getElementById('driver-row');
    const deliveryRow = document.getElementById('delivery-row');
    const totalDisplay = document.getElementById('total-price');
    const dailyPrice = <?= $vehicle['price_per_day'] ?>;

    function updatePrice() {
        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            
            if (end <= start) {
                totalDisplay.textContent = 'Invalid Dates';
                return;
            }

            let rentalTotal = 0;
            let current = new Date(start);

            while (current < end) {
                let dailyRate = dailyPrice;
                const day = current.getDay(); // 0 is Sun, 6 is Sat
                if (day === 0 || day === 6) dailyRate *= 1.10;
                rentalTotal += dailyRate;
                current.setDate(current.getDate() + 1);
            }

            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

            if (diffDays > 7) rentalTotal *= 0.85;
            
            rentalDisplay.textContent = `₹${rentalTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
            if (diffDays > 7) rentalDisplay.innerHTML += ' <span style="font-size:0.7rem; color:green;">(-15%)</span>';

            let driverCost = 0;
            if (driverInput.checked) {
                driverCost = 500 * diffDays;
                driverDisplay.textContent = `+₹${driverCost.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
                driverRow.style.display = 'flex';
            } else {
                driverRow.style.display = 'none';
            }

            const deliveryMode = document.querySelector('input[name="delivery_mode"]:checked').value;
            let deliveryCost = (deliveryMode === 'home_delivery') ? 500 : 0;
            deliveryRow.style.display = (deliveryCost > 0) ? 'flex' : 'none';

            let grandTotal = rentalTotal + driverCost + deliveryCost + 5000; // 5000 is deposit
            totalDisplay.textContent = `₹${grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        }
    }

    startInput.addEventListener('change', updatePrice);
    endInput.addEventListener('change', updatePrice);
    driverInput.addEventListener('change', updatePrice);

    function toggleDeliveryBook() {
        const mode = document.querySelector('input[name="delivery_mode"]:checked').value;
        const addrGroup = document.getElementById('deliveryAddrBook');
        const addrInput = document.getElementById('delivery_addr_input');
        const shopGroup = document.getElementById('pickupShopBook');
        const shopSelect = document.getElementById('pickup_shop_book');
        
        if (mode === 'home_delivery') {
            addrGroup.style.display = 'block';
            addrInput.required = true;
            shopGroup.style.display = 'none';
            shopSelect.required = false;
        } else {
            addrGroup.style.display = 'none';
            addrInput.required = false;
            shopGroup.style.display = 'block';
            shopSelect.required = true;
        }
        updatePrice();
    }

    document.getElementById('pickup_shop_book')?.addEventListener('change', function() {
        const p = this.value.split('|');
        const display = document.getElementById('shopAddrBook');
        if (p.length < 3) { display.innerHTML = ''; return; }
        let html = 'Loc: ' + p[2];
        if (p[3]) html += '<br>Phone: <a href="tel:' + p[3].replace(/\s/g,'') + '" style="color:#4f46e5; text-decoration:none;">' + p[3] + '</a>';
        if (p[4] && p[5]) html += ' &bull; <a href="https://www.google.com/maps/dir/?api=1&destination=' + p[4] + ',' + p[5] + '" target="_blank" style="color:#16a34a; text-decoration:none; font-weight:600;">Navigate</a>';
        display.innerHTML = html;
    });
</script>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
