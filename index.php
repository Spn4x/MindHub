<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: students/dashboard.php");
    } else {
        header("Location: counselors/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindHub - Student Guidance & Counseling Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body.landing-page {
            background-color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .landing-center {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            max-width: 860px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }

        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .brand-header img {
            height: 48px;
            width: auto;
            object-fit: contain;
        }

        .brand-header .brand-name {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .hero-badge {
            display: inline-block;
            color: #809671;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .landing-center h1 {
            font-size: 46px;
            line-height: 1.2;
            color: #0f172a;
            font-weight: 800;
            margin: 0 0 16px;
            letter-spacing: -0.8px;
        }

        .landing-center p {
            font-size: 16px;
            color: #64748b;
            max-width: 620px;
            margin: 0 0 32px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background-color: #809671;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            padding: 12px 28px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-hero-primary:hover {
            background-color: #275f02;
        }

        .btn-hero-secondary {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            padding: 12px 28px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-hero-secondary:hover {
            background-color: #f8fafc;
        }

        .landing-footer {
            margin-top: auto;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 18px 24px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            width: 100%;
        }
    </style>
</head>
<body class="landing-page">

    <div class="landing-center">
        <div class="brand-header">
            <img src="assets/images/logo.png" alt="MindHub Logo">
            <span class="brand-name">Mind<span class="hub-accent">Hub</span></span>
        </div>

        <div class="hero-badge">Student Guidance & Mental Wellness Support</div>
        <h1>Mind your studies, we'll mind your wellness</h1>
        <p>
            MindHub replaces manual walk-in logbooks with a private digital portal. 
            Schedule consultations, connect with certified counselors, and access self-help resources without stigma or queue lines.
        </p>

        <div class="hero-buttons">
            <a href="login.php" class="btn-hero-primary">Log In to Portal &rarr;</a>
            <a href="register.php" class="btn-hero-secondary">Student Registration</a>
        </div>
    </div>

    <footer class="landing-footer">
        Mind<span class="hub-accent">Hub</span> &bull; Student Counseling & Online Appointment System
    </footer>

</body>
</html>