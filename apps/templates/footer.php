    </main>
    <footer style="margin-top: auto; padding: 3rem 0 2rem; background: #0f172a; color: #94a3b8;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h4 style="color: white; margin-bottom: 1rem; font-size: 1.1rem;">
                        <span style="background: linear-gradient(135deg, #4f46e5, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">RentRide</span>
                    </h4>
                    <p style="font-size: 0.875rem; line-height: 1.6;">Premium vehicle rentals across India. Transparent pricing, instant booking, and 24/7 support.</p>
                </div>
                <div>
                    <h5 style="color: white; margin-bottom: 0.75rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Quick Links</h5>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                        <a href="/" style="color: #94a3b8; text-decoration: none;">Browse Vehicles</a>
                        <a href="/register.php" style="color: #94a3b8; text-decoration: none;">Create Account</a>
                        <a href="/login.php" style="color: #94a3b8; text-decoration: none;">Login</a>
                    </div>
                </div>
                <div>
                    <h5 style="color: white; margin-bottom: 0.75rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Cities</h5>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                        <span>Mumbai · Delhi · Bangalore</span>
                        <span>Chennai · Hyderabad · Pune</span>
                        <span>Jaipur · Kolkata · Ahmedabad</span>
                    </div>
                </div>
                <div>
                    <h5 style="color: white; margin-bottom: 0.75rem; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em;">Contact</h5>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.875rem;">
                        <span>📧 support@rentride.in</span>
                        <span>📞 +91 98765 43210</span>
                        <span>🕐 24/7 Customer Support</span>
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid #1e293b; padding-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <p style="font-size: 0.8rem; margin: 0;">&copy; <?= date('Y') ?> RentRide. All rights reserved.</p>
                <div style="display: flex; gap: 1.5rem; font-size: 0.8rem;">
                    <a href="/privacy-policy.php" style="color: #94a3b8; text-decoration: none;">Privacy Policy</a>
                    <a href="/terms-of-service.php" style="color: #94a3b8; text-decoration: none;">Terms of Service</a>
                    <a href="/refund-policy.php" style="color: #94a3b8; text-decoration: none;">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.add('loader-hidden');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500); // Match transition time
            }
        });
    </script>
    <script src="/assets/js/chatbot.js"></script>
</body>
</html>
