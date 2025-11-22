<?php
// FILE: /app/views/auth/login.php
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - SplashLeadRouter</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>SplashLeadRouter</h1>
                <p>Real Estate Lead Routing Platform</p>
            </div>

            <?php
            $error = View::flash('error');
            $success = View::flash('success');
            ?>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo View::escape($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo View::escape($success); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="auth-form">
                <?php echo View::csrfField(); ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo View::escape(View::old('email')); ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <div class="auth-footer">
                <p>Demo Credentials:</p>
                <p><strong>Tenant Admin:</strong> ahmed@dubaiproperties.com / password</p>
                <p><strong>Agent:</strong> sarah@dubaiproperties.com / password</p>
                <p><strong>Platform Admin:</strong> admin@splashleadrouter.com / password</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php View::clearOld(); ?>
