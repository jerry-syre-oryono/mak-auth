<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>MAK-AUTH</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="Mak-Logo.png">

  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- Custom Styles -->
  <style>
    /* Base & Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      zoom: 90%;
      background-image: linear-gradient(rgba(6, 78, 59, 0.45), rgba(6, 78, 59, 0.45)), url('makerere_university_tower-1 (1).jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      font-family: 'Inter', system-ui, sans-serif;
      min-height: 100vh;
    }

    /* 3D Button Style */
    .btn-3d {
      position: relative;
      transform-style: preserve-3d;
      transition: all 0.15s ease;
      box-shadow: 0 8px 0 rgba(5, 150, 105, 0.4), 0 4px 12px rgba(5, 150, 105, 0.15);
    }

    .btn-3d:active {
      transform: translateY(6px);
      box-shadow: 0 2px 0 rgba(5, 150, 105, 0.4), 0 6px 16px rgba(5, 150, 105, 0.2);
    }

    /* Input Style */
    .screenshot-input {
      background-color: #f9fafb;
      border: 1px solid #d1d5db;
      border-radius: 1.2rem;
      padding: 1rem 1.2rem;
      width: 100%;
      font-size: 1rem;
      transition: 0.15s;
    }

    .screenshot-input:focus {
      border-color: #10b981;
      background-color: #ffffff;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
      outline: none;
    }

    /* Green focus ring */
    .green-focus-ring:focus {
      outline: none;
      border-color: #10b981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2), 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Glass Panel */
    .glass-panel {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      border: 1px solid rgba(16, 185, 129, 0.2);
      border-radius: 2rem;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08), inset 0 1px 2px rgba(255, 255, 255, 0.8);
    }

    /* Card Animation */
    .card-enter {
      animation: cardEnter 0.3s ease-out;
    }

    @keyframes cardEnter {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Custom scrollbar for JSON output */
    pre {
      scrollbar-width: thin;
      scrollbar-color: #10b981 #f1f5f9;
    }

    pre::-webkit-scrollbar {
      height: 6px;
    }

    pre::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 3px;
    }

    pre::-webkit-scrollbar-thumb {
      background: #10b981;
      border-radius: 3px;
    }

    pre::-webkit-scrollbar-thumb:hover {
      background: #059669;
    }

    /* Help link style */
    .help-link {
      color: #059669;
      font-weight: 500;
      text-decoration: underline dotted rgba(16, 185, 129, 0.5);
      transition: color 0.2s;
    }

    .help-link:hover {
      color: #064e3b;
      text-decoration: underline solid #10b981;
    }

    /* Success/Error card styles */
    .success-card {
      background: rgba(16, 185, 129, 0.1);
      border: 1px solid rgba(16, 185, 129, 0.3);
      border-radius: 1.2rem;
    }

    .error-card {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.3);
      border-radius: 1.2rem;
    }

    /* Toast notification */
    #toast {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(16, 185, 129, 0.2);
      color: #064e3b;
    }

    /* Particle canvas */
    #particleCanvas {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 0;
    }

    /* Ensure content is above canvas */
    .content-wrapper {
      position: relative;
      z-index: 10;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
  </style>
</head>

<body class="relative">

  <!-- Particle Canvas Background -->
  <canvas id="particleCanvas"></canvas>

  <!-- Main Content Wrapper -->
  <div class="content-wrapper">