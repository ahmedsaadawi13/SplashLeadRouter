<?php
// FILE: /app/views/auth/register.php
$pageTitle = 'Register';
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
                <h1>Create Account</h1>
                <p>Join SplashLeadRouter</p>
            </div>

            <?php
            $error = View::flash('error');
            ?>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo View::escape($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form">
                <?php echo View::csrfField(); ?>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           value="<?php echo View::escape(View::old('name')); ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo View::escape(View::old('email')); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Minimum 6 characters</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="/login">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php View::clearOld(); ?>
