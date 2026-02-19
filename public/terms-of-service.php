<?php
// terms-of-service.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();
?>
<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div class="container" style="max-width: 850px; margin-top: 2rem; margin-bottom: 3rem;">

    <div style="text-align: center; margin-bottom: 2.5rem;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📜</div>
        <h1 style="font-size: 2rem; margin-bottom: 0.25rem;">Terms of Service</h1>
        <p style="color: var(--secondary); font-size: 0.9rem;">Last updated: <?= date('F d, Y') ?></p>
    </div>

    <div style="background: var(--surface); border-radius: var(--radius); padding: 2.5rem; box-shadow: var(--shadow); line-height: 1.8; color: #374151;">

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📋 1. Agreement to Terms</h2>
            <p>By accessing or using the <strong>RentRide</strong> platform ("Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, you must not use our Service. These Terms apply to all users of the platform including renters, drivers, and administrators.</p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">👤 2. Eligibility</h2>
            <p>To use RentRide, you must:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Be at least <strong>18 years old</strong></li>
                <li>Hold a <strong>valid Indian driving license</strong> (for self-drive bookings)</li>
                <li>Provide accurate registration information including name, email, phone number, and address</li>
                <li>Have a valid payment method (credit/debit card, UPI, or net banking)</li>
                <li>Not be barred from receiving services under Indian law</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🚗 3. Booking & Rental Terms</h2>
            
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">3.1 Booking Process</h3>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Select a vehicle, choose pickup/delivery dates, and complete payment to confirm a booking</li>
                <li>Bookings are subject to vehicle availability and may be declined if a conflict exists</li>
                <li>Vehicle allocation is on a first-come, first-served basis</li>
            </ul>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">3.2 Delivery Options</h3>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li><strong>Pickup from Shop:</strong> Collect the vehicle from the selected RentRide outlet. Bring your driving license and ID proof</li>
                <li><strong>Home Delivery:</strong> Vehicle delivered to your address for a flat fee of <strong>₹500</strong>. A driver will contact you before delivery</li>
            </ul>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">3.3 Pricing</h3>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Rental charges are calculated on a per-day basis</li>
                <li><strong>Weekend surge:</strong> 10% additional on Saturdays and Sundays</li>
                <li><strong>Long-term discount:</strong> 15% off for rentals exceeding 7 days</li>
                <li><strong>Driver mode:</strong> Optional professional driver at ₹500/day</li>
                <li>All prices are in Indian Rupees (₹) and inclusive of applicable taxes</li>
            </ul>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">3.4 Security Deposit</h3>
            <ul style="padding-left: 1.5rem;">
                <li>A <strong>₹5,000 refundable security deposit</strong> is collected with each booking</li>
                <li>Deposit is refunded within 5–7 business days after vehicle return, subject to inspection</li>
                <li>Deductions may apply for damages, excessive mileage, or late returns</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">⚠️ 4. Renter Responsibilities</h2>
            <ul style="padding-left: 1.5rem;">
                <li>Operate the vehicle safely and in compliance with Indian traffic laws</li>
                <li>Return the vehicle in the same condition as received (normal wear and tear excluded)</li>
                <li>Report any accidents, breakdowns, or damages immediately to RentRide</li>
                <li>Do <strong>not</strong> use the vehicle for illegal activities, racing, or off-road driving</li>
                <li>Do <strong>not</strong> sub-rent, loan, or transfer the vehicle to any third party</li>
                <li>Keep the vehicle locked and secured when not in use</li>
                <li>Return the vehicle with a fuel level equal to or greater than at the time of pickup</li>
                <li>Pay all traffic challan fines, parking tickets, and tolls incurred during the rental</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🔧 5. Damages & Penalties</h2>
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; font-size: 0.9rem;">
                <strong>⚠️ Important:</strong> The renter is financially responsible for any damage to the vehicle during the rental period beyond normal wear and tear.
            </div>
            <ul style="padding-left: 1.5rem;">
                <li><strong>Minor damages</strong> (scratches, dents): Deducted from security deposit</li>
                <li><strong>Major damages</strong> (engine, body, mechanical): Renter liable for full repair costs</li>
                <li><strong>Late return:</strong> ₹200/hour penalty for delayed returns</li>
                <li><strong>Traffic violations:</strong> All fines are the renter's responsibility</li>
                <li><strong>Fuel shortage:</strong> ₹150 per litre refueling charge</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🚫 6. Cancellation Policy</h2>
            <p>Please refer to our <a href="/refund-policy.php" style="color: var(--primary); font-weight: 600;">Refund Policy</a> for detailed cancellation and refund terms.</p>
            <ul style="padding-left: 1.5rem;">
                <li>Bookings can be cancelled before the start date through your dashboard</li>
                <li>Cancellation is not allowed once the rental period has started</li>
                <li>Refund processing time is 5–7 business days</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🛡️ 7. Limitation of Liability</h2>
            <ul style="padding-left: 1.5rem;">
                <li>RentRide is not liable for personal injury, loss, or damage arising from vehicle use</li>
                <li>Maximum liability is limited to the booking amount paid</li>
                <li>We are not responsible for delays caused by weather, traffic, or force majeure events</li>
                <li>Service availability is subject to fleet availability and operational conditions</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🔄 8. Modifications</h2>
            <p>RentRide reserves the right to modify these Terms at any time. Changes will be effective immediately upon posting on this page. Continued use of the Service after changes constitutes acceptance of the updated Terms. We recommend reviewing this page periodically.</p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">⚖️ 9. Governing Law</h2>
            <p>These Terms shall be governed by and construed in accordance with the laws of <strong>India</strong>. Any disputes arising from these Terms or the Service shall be subject to the exclusive jurisdiction of the courts in <strong>Mumbai, Maharashtra</strong>.</p>
        </section>

        <section>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📧 10. Contact Information</h2>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
                <p style="margin-bottom: 0.25rem;"><strong>RentRide India Pvt. Ltd.</strong></p>
                <p style="margin-bottom: 0.25rem;">📍 123 Business Park, Andheri East, Mumbai 400069</p>
                <p style="margin-bottom: 0.25rem;">📧 legal@rentride.in</p>
                <p style="margin-bottom: 0;">📞 +91 22 2631 5500</p>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
