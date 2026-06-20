<?php
/**
 * Common Header Include
 * Kids Menu Planner Application
 */
require_once __DIR__ . '/../config/session.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Kids Menu Planner - Plan healthy meals for your child">
    <meta name="theme-color" content="#8FBC8F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Menu Planner">
    <title><?php echo isset($pageTitle) ? escapeHTML($pageTitle) . ' - ' : ''; ?>Niyog's Menu Planner</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="assets/icons/icon-192.png">
    <link rel="apple-touch-icon" href="assets/icons/icon-192.png">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="app-container">
        <!-- Navigation Bar -->
        <nav class="navbar">
            <div class="nav-content">
                <h1 class="app-title">🍽️ Niyog's Menu</h1>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="main-content">
