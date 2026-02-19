<?php
// 404.php — Custom error page
?>
<?php require __DIR__ . '/../apps/templates/header.php'; ?>

<div style="text-align: center; padding: 6rem 2rem;">
    <div style="font-size: 8rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1;">404</div>
    <h2 style="margin: 1rem 0 0.5rem; font-size: 1.5rem;">Page Not Found</h2>
    <p style="color: var(--secondary); margin-bottom: 2rem; max-width: 400px; margin-left: auto; margin-right: auto;">
        The page you're looking for doesn't exist or has been moved. Let's get you back on track.
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="/" class="btn btn-primary">🏠 Back to Home</a>
        <a href="/login.php" class="btn btn-outline">🔑 Login</a>
    </div>
</div>

<?php require __DIR__ . '/../apps/templates/footer.php'; ?>
