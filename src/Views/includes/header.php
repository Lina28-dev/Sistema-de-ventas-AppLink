<?php
/**
 * Template del Header para el Sistema AppLink
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Sistema-de-ventas-AppLink-main/public/assets/css/theme-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="theme-color" content="#ffffff">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { 
            min-height: 100vh; 
            background: linear-gradient(180deg, #343a40 0%, #212529 100%); 
            color: white; 
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .sidebar .nav-link { 
            color: rgba(255,255,255,0.8); 
            padding: 12px 20px; 
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { 
            background-color: #e91e63; 
            color: white; 
            border-radius: 8px;
            margin: 0 10px;
        }
        .sidebar .nav-link i { margin-right: 10px; }
        .main-content { 
            padding: 20px; 
            margin-left: 250px; 
            min-height: 100vh;
        }
        .card-stat { border-left: 4px solid #e91e63; }
        .welcome-card {
            background: #ffffff;
            color: #333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .welcome-card i {
            font-size: 3rem;
            margin-right: 20px;
            color: #e91e63;
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            color: #e91e63;
        }
        .page-title i {
            margin-right: 15px;
        }
        .real-time-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>