<?php
// refund-policy.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();
?>
<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div class="container" style="max-width: 850px; margin-top: 2rem; margin-bottom: 3rem;">

    <div style="text-align: center; margin-bottom: 2.5rem;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">💰</div>
        <h1 style="font-size: 2rem; margin-bottom: 0.25rem;">Refund Policy</h1>
        <p style="color: var(--secondary); font-size: 0.9rem;">Last updated: <?= date('F d, Y') ?></p>
    </div>

    <div style="background: var(--surface); border-radius: var(--radius); padding: 2.5rem; box-shadow: var(--shadow); line-height: 1.8; color: #374151;">

        <!-- Quick Summary Card -->
        <div style="background: linear-gradient(135deg, #eff6ff, #f0fdf4); border: 1px solid #bfdbfe; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 0.75rem; color: #1e40af;">📊 Quick Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <div style="text-align: center; padding: 0.75rem; background: white; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #16a34a;">100%</div>
                    <div style="font-size: 0.75rem; color: var(--secondary);">48+ hrs before trip</div>
                </div>
                <div style="text-align: center; padding: 0.75rem; background: white; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #ca8a04;">50%</div>
                    <div style="font-size: 0.75rem; color: var(--secondary);">24–48 hrs before</div>
                </div>
                <div style="text-align: center; padding: 0.75rem; background: white; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;">0%</div>
                    <div style="font-size: 0.75rem; color: var(--secondary);">Less than 24 hrs</div>
                </div>
            </div>
        </div>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📋 1. Overview</h2>
            <p>At <strong>RentRide</strong>, we strive to provide a flexible and transparent refund experience. This policy outlines the terms for cancellations, refunds, and security deposit returns for all vehicle bookings made through our platform.</p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🚫 2. Cancellation & Refund Schedule</h2>
            
            <table style="width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 0.75rem 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Cancellation Timing</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; border-bottom: 2px solid #e2e8f0;">Rental Refund</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; border-bottom: 2px solid #e2e8f0;">Deposit Refund</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;"><strong>48+ hours</strong> before trip start</td>
                        <td style="padding: 0.75rem 1rem; text-align: center; border-bottom: 1px solid #f1f5f9;"><span style="color: #16a34a; font-weight: 700;">100% refund</span></td>
                        <td style="padding: 0.75rem 1rem; text-align: center; border-bottom: 1px solid #f1f5f9;"><span style="color: #16a34a; font-weight: 700;">100% refund</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;"><strong>24–48 hours</strong> before trip start</td>
                        <td style="padding: 0.75rem 1rem; text-align: center; border-bottom: 1px solid #f1f5f9;"><span style="color: #ca8a04; font-weight: 700;">50% refund</span></td>
                        <td style="padding: 0.75rem 1rem; text-align: center; border-bottom: 1px solid #f1f5f9;"><span style="color: #16a34a; font-weight: 700;">100% refund</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;"><strong>Less than 24 hours</strong> before trip</td>
                        <td style="padding: 0.75rem 1rem; text-align: center; border-bottom: 1px solid #f1f5f9;"><span style="color: #dc2626; font-weight: 700;">No refund</span></td>
                        <td style="padding: 0.75rem 1rem; text-align: center; border-bottom: 1px solid #f1f5f9;"><span style="color: #16a34a; font-weight: 700;">100% refund</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.75rem 1rem;"><strong>After trip has started</strong></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><span style="color: #dc2626; font-weight: 700;">No refund</span></td>
                        <td style="padding: 0.75rem 1rem; text-align: center;"><span style="color: #dc2626; font-weight: 700;">No refund</span></td>
                    </tr>
                </tbody>
            </table>

            <div style="background: #fffbeb; border: 1px solid #fef08a; border-radius: 0.5rem; padding: 1rem; font-size: 0.85rem; color: #854d0e; margin-top: 0.5rem;">
                <strong>💡 Note:</strong> The cancellation time is calculated from the booking's start date and time, not the current date.
            </div>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🏦 3. Security Deposit Refund</h2>
            <p>The <strong>₹5,000 refundable security deposit</strong> is handled as follows:</p>
            <ul style="padding-left: 1.5rem;">
                <li><strong>Normal Return:</strong> Full deposit refunded within 5–7 business days after vehicle inspection</li>
                <li><strong>Minor Damage:</strong> Repair costs deducted from deposit; remaining amount refunded</li>
                <li><strong>Major Damage:</strong> Entire deposit forfeited; additional charges may apply</li>
                <li><strong>Fuel Shortage:</strong> ₹150 per litre deducted for fuel not topped up to original level</li>
                <li><strong>Late Return:</strong> ₹200/hour penalty deducted from deposit</li>
            </ul>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; margin-top: 1rem; color: var(--text);">Deposit Deduction Examples:</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 0.5rem 0; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 0.5rem 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Scenario</th>
                        <th style="padding: 0.5rem 1rem; text-align: right; border-bottom: 2px solid #e2e8f0;">Deduction</th>
                        <th style="padding: 0.5rem 1rem; text-align: right; border-bottom: 2px solid #e2e8f0;">Refunded</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 0.5rem 1rem; border-bottom: 1px solid #f1f5f9;">Clean return, no issues</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; border-bottom: 1px solid #f1f5f9;">₹0</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; border-bottom: 1px solid #f1f5f9; color: #16a34a; font-weight: 600;">₹5,000</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 1rem; border-bottom: 1px solid #f1f5f9;">Small scratch on bumper</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; border-bottom: 1px solid #f1f5f9;">₹1,500</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; border-bottom: 1px solid #f1f5f9; color: #ca8a04; font-weight: 600;">₹3,500</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 1rem; border-bottom: 1px solid #f1f5f9;">3 hours late return</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; border-bottom: 1px solid #f1f5f9;">₹600</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; border-bottom: 1px solid #f1f5f9; color: #ca8a04; font-weight: 600;">₹4,400</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.5rem 1rem;">Low fuel (10 litres short)</td>
                        <td style="padding: 0.5rem 1rem; text-align: right;">₹1,500</td>
                        <td style="padding: 0.5rem 1rem; text-align: right; color: #ca8a04; font-weight: 600;">₹3,500</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🚚 4. Home Delivery Refund</h2>
            <ul style="padding-left: 1.5rem;">
                <li>The <strong>₹500 home delivery fee</strong> is refundable only if the cancellation is made <strong>48+ hours</strong> before the trip start</li>
                <li>For cancellations within 48 hours, the delivery fee is non-refundable</li>
                <li>If RentRide fails to deliver the vehicle on time, the delivery fee is automatically refunded</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">💳 5. Payment & Refund Methods</h2>
            <ul style="padding-left: 1.5rem;">
                <li>All payments are processed securely through <strong>Razorpay</strong></li>
                <li>Refunds are credited to the <strong>original payment method</strong> used during booking</li>
                <li>Credit/Debit Card refunds: 5–7 business days</li>
                <li>UPI refunds: 2–3 business days</li>
                <li>Net Banking refunds: 5–10 business days</li>
                <li>Wallet refunds: Instant to 24 hours</li>
            </ul>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; padding: 1rem; font-size: 0.85rem; color: #166534; margin-top: 0.75rem;">
                <strong>✅ Tip:</strong> You can track your refund status on the <a href="/my-bookings.php" style="color: #4f46e5; font-weight: 600;">My Bookings</a> page. Cancelled bookings will show the refund amount and processing status.
            </div>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🔄 6. How to Cancel a Booking</h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin: 1rem 0;">
                <div style="text-align: center; padding: 1rem 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">1️⃣</div>
                    <div style="font-size: 0.8rem; font-weight: 500;">Go to My Bookings</div>
                </div>
                <div style="text-align: center; padding: 1rem 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">2️⃣</div>
                    <div style="font-size: 0.8rem; font-weight: 500;">Click on the booking</div>
                </div>
                <div style="text-align: center; padding: 1rem 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">3️⃣</div>
                    <div style="font-size: 0.8rem; font-weight: 500;">Click "Cancel Booking"</div>
                </div>
                <div style="text-align: center; padding: 1rem 0.5rem; background: #f8fafc; border-radius: 0.5rem;">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">4️⃣</div>
                    <div style="font-size: 0.8rem; font-weight: 500;">Confirm cancellation</div>
                </div>
            </div>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">❓ 7. Disputes & Exceptions</h2>
            <p>If you believe a refund was processed incorrectly or have a dispute:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Contact our support team within <strong>7 days</strong> of the transaction</li>
                <li>Provide your booking ID and payment details</li>
                <li>We will investigate and respond within <strong>48 hours</strong></li>
                <li>Exceptions may be made for medical emergencies or natural disasters (with supporting documentation)</li>
            </ul>
        </section>

        <section>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📧 8. Contact Us</h2>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
                <p style="margin-bottom: 0.25rem;"><strong>RentRide Refund Support</strong></p>
                <p style="margin-bottom: 0.25rem;">📍 123 Business Park, Andheri East, Mumbai 400069</p>
                <p style="margin-bottom: 0.25rem;">📧 refunds@rentride.in</p>
                <p style="margin-bottom: 0;">📞 +91 22 2631 5500 (Mon–Sat, 9 AM – 8 PM IST)</p>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
