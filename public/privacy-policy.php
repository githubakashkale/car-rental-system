<?php
// privacy-policy.php
require_once __DIR__ . '/../apps/backend/config/db.php';
session_start();
?>
<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div class="container" style="max-width: 850px; margin-top: 2rem; margin-bottom: 3rem;">

    <div style="text-align: center; margin-bottom: 2.5rem;">
        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔒</div>
        <h1 style="font-size: 2rem; margin-bottom: 0.25rem;">Privacy Policy</h1>
        <p style="color: var(--secondary); font-size: 0.9rem;">Last updated: <?= date('F d, Y') ?></p>
    </div>

    <div style="background: var(--surface); border-radius: var(--radius); padding: 2.5rem; box-shadow: var(--shadow); line-height: 1.8; color: #374151;">

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📋 1. Introduction</h2>
            <p>Welcome to <strong>RentRide</strong> ("we", "our", "us"). We are committed to protecting and respecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our vehicle rental platform at <strong>rentride.in</strong> and associated services.</p>
            <p>By accessing or using our services, you agree to the collection and use of information in accordance with this policy.</p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📦 2. Information We Collect</h2>
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">2.1 Personal Information</h3>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Full Name, Email Address, and Phone Number</li>
                <li>Residential Address and City</li>
                <li>Driving License Number and validity</li>
                <li>Payment details (processed securely through Razorpay)</li>
                <li>Government-issued ID for identity verification</li>
            </ul>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">2.2 Usage Information</h3>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Booking history and preferences</li>
                <li>Vehicle search patterns and interests</li>
                <li>IP address, browser type, and device information</li>
                <li>Delivery addresses provided for home delivery bookings</li>
            </ul>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text);">2.3 Location Data</h3>
            <ul style="padding-left: 1.5rem;">
                <li>Pickup/delivery locations selected during booking</li>
                <li>GPS data for navigation to our shop locations</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🎯 3. How We Use Your Information</h2>
            <ul style="padding-left: 1.5rem;">
                <li><strong>Service Delivery:</strong> Processing bookings, payments, and vehicle allocation</li>
                <li><strong>Communication:</strong> Sending booking confirmations, driver assignments, and support updates</li>
                <li><strong>Verification:</strong> Validating driving license and identity for vehicle rental compliance</li>
                <li><strong>Home Delivery:</strong> Sharing your delivery address with assigned drivers for vehicle drop-off</li>
                <li><strong>Improvement:</strong> Analyzing usage patterns to improve our services and vehicle fleet</li>
                <li><strong>Legal Compliance:</strong> Meeting regulatory requirements under Indian motor vehicle laws</li>
                <li><strong>Security:</strong> Detecting and preventing fraud and unauthorized access</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🤝 4. Information Sharing</h2>
            <p>We do <strong>not</strong> sell your personal information. We may share your data with:</p>
            <ul style="padding-left: 1.5rem;">
                <li><strong>Payment Processors:</strong> Razorpay for secure payment processing</li>
                <li><strong>Delivery Partners:</strong> Assigned drivers receive only the necessary delivery address and contact number</li>
                <li><strong>Legal Authorities:</strong> When required by law, court order, or government regulation</li>
                <li><strong>Insurance Companies:</strong> In case of accidents or damage claims</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🔐 5. Data Security</h2>
            <p>We implement industry-standard security measures including:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Password hashing using bcrypt encryption</li>
                <li>Secure HTTPS connections for all data transmission</li>
                <li>PCI-DSS compliant payment processing through Razorpay</li>
                <li>Regular security audits and vulnerability assessments</li>
                <li>Access controls limiting employee data access to role-based needs</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">⏰ 6. Data Retention</h2>
            <p>We retain your personal data for as long as your account is active or as needed to provide services. Booking records are retained for a minimum of <strong>3 years</strong> for legal and accounting purposes. You may request deletion of your account and associated data by contacting our support team.</p>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">✅ 7. Your Rights</h2>
            <p>Under applicable Indian data protection laws, you have the right to:</p>
            <ul style="padding-left: 1.5rem;">
                <li>Access and review your personal data</li>
                <li>Correct inaccurate or incomplete data</li>
                <li>Request deletion of your account</li>
                <li>Withdraw consent for data processing</li>
                <li>Lodge a complaint with the relevant data protection authority</li>
            </ul>
        </section>

        <section style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">🍪 8. Cookies</h2>
            <p>We use essential cookies to manage user sessions and authentication. We do not use third-party advertising cookies. By using our platform, you consent to the use of essential cookies required for service functionality.</p>
        </section>

        <section>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem;">📧 9. Contact Us</h2>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.25rem;">
                <p style="margin-bottom: 0.25rem;"><strong>RentRide India Pvt. Ltd.</strong></p>
                <p style="margin-bottom: 0.25rem;">📍 123 Business Park, Andheri East, Mumbai 400069</p>
                <p style="margin-bottom: 0.25rem;">📧 privacy@rentride.in</p>
                <p style="margin-bottom: 0;">📞 +91 22 2631 5500</p>
            </div>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
