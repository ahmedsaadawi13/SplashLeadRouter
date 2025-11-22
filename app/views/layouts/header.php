<!-- FILE: /app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? View::escape($pageTitle) . ' - ' : ''; ?>SplashLeadRouter</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="/dashboard">SplashLeadRouter</a>
            </div>
            <div class="navbar-menu">
                <a href="/dashboard">Dashboard</a>
                <a href="/leads">Leads</a>

                <?php if (in_array($_SESSION['user_role'], ['tenant_admin', 'platform_admin'])): ?>
                <a href="/agents">Agents</a>
                <a href="/zones">Zones</a>
                <a href="/routing-rules">Routing Rules</a>
                <?php endif; ?>

                <div class="navbar-user">
                    <span><?php echo View::escape($_SESSION['user_name']); ?></span>
                    <small>(<?php echo View::escape($_SESSION['user_role']); ?>)</small>
                    <a href="/logout">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
            <?php
            $success = View::flash('success');
            $error = View::flash('error');
            View::clearOld();
            ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo View::escape($success); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo View::escape($error); ?>
            </div>
            <?php endif; ?>
