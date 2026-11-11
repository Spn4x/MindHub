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
            background-color: #f8fafc;
            display: block;
            min-height: 100vh;
            padding: 0;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-y: auto;
        }

        .landing-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .landing-nav .brand {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .landing-nav .brand span {
            color: #809671;
        }

        .landing-nav .nav-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-nav-login {
            color: #1e293b;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-nav-login:hover {
            background-color: #f1f5f9;
        }

        .btn-nav-register {
            background-color: #809671;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 9px 18px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-nav-register:hover {
            background-color: #809671;
        }

        .hero-section {
            max-width: 1050px;
            margin: 60px auto 40px;
            padding: 0 20px;
            text-align: center;
        }

        .hero-badge {
            display: inline-block;
            background: #eff6ff;
            color: #809671;
            border: 1px solid #dbeafe;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-section h1 {
            font-size: 44px;
            line-height: 1.2;
            color: #0f172a;
            font-weight: 800;
            margin-bottom: 18px;
            letter-spacing: -0.8px;
        }

        .hero-section p {
            font-size: 17px;
            color: #64748b;
            max-width: 680px;
            margin: 0 auto 30px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background-color: #809671;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            padding: 14px 28px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-hero-primary:hover {
            background-color: #809671;
            transform: translateY(-1px);
        }

        .btn-hero-secondary {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            padding: 14px 28px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-hero-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
        }

        .pillars-section {
            max-width: 1100px;
            margin: 60px auto 80px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .pillar-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 28px 24px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            text-align: left;
        }

        .pillar-icon {
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: #eff6ff;
            color: #809671;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .pillar-card h3 {
            font-size: 18px;
            color: #0f172a;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .pillar-card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }

        .landing-footer {
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 24px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>
<body class="landing-page">

    <nav class="landing-nav">
        <div class="brand">Mind<span>Hub</span></div>
        <div class="nav-actions">
            <a href="login.php" class="btn-nav-login">Log In</a>
            <a href="register.php" class="btn-nav-register">Create Account</a>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-badge">Student Guidance & Mental Wellness Support</div>
        <h1>Mind your studies, we'll mind your wellness</h1>
        <p>
            MindHub replaces manual walk-in logbooks with a private digital portal. 
            Schedule consultations, connect with certified counselors, and access self-help resources without stigma or queue lines.
        </p>

        <div class="hero-buttons">
            <a href="login.php" class="btn-hero-primary">Log In to Portal →</a>
            <a href="register.php" class="btn-hero-secondary">Student Registration</a>
        </div>
    </div>


    <footer class="landing-footer">
        MindHub - Student Counseling & Online Appointment System &bull; Dedicated to Student Wellness
    </footer>

</body>
</html>