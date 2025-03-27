<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PESO Los Baños</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8b5e3c;
            --primary-dark: #714d30;
            --primary-light: #c9b18f;
            --accent-color: #4CAF50;
            --accent-dark: #388E3C;
            --text-color: #333;
            --light-text: #777;
            --bg-light: #f9f7f3;
            --bg-white: #ffffff;
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header & Navigation */
        header {
            background-color: var(--bg-white);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            margin-right: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .navbar {
            display: flex;
        }

        .navbar a {
            color: var(--text-color);
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 5px;
        }

        .navbar a:hover {
            background-color: var(--primary-light);
            color: var(--bg-white);
        }

        .btn-login {
            background-color: var(--primary-color);
            color: white !important;
        }

        .btn-login:hover {
            background-color: var(--primary-dark);
        }

        .btn-register {
            background-color: var(--accent-color);
            color: white !important;
        }

        .btn-register:hover {
            background-color: var(--accent-dark);
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary-color);
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            padding: 160px 20px 100px;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/api/placeholder/1200/600') no-repeat center;
            background-size: cover;
            position: relative;
            color: white;
        }

        .hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .tagline {
            font-size: 1.4em;
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .hero-btn {
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1em;
        }

        .primary-btn {
            background-color: var(--primary-color);
            color: white;
        }

        .primary-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .secondary-btn {
            background-color: var(--accent-color);
            color: white;
        }

        .secondary-btn:hover {
            background-color: var(--accent-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Service Highlights */
        .highlight-section {
            padding: 80px 0;
            background-color: var(--bg-white);
        }

        .highlight-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .highlight-header h2 {
            color: var(--primary-color);
            font-size: 2.2em;
            margin-bottom: 15px;
        }

        .section-subtitle {
            color: var(--light-text);
            font-size: 1.1em;
            max-width: 700px;
            margin: 0 auto;
        }

        .highlight-cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .highlight-card {
            background-color: var(--bg-white);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            width: 320px;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .highlight-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: var(--primary-color);
        }

        .highlight-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .highlight-icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .highlight-card h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .highlight-card p {
            margin-bottom: 25px;
            color: var(--light-text);
        }

        .highlight-btn {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .highlight-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* About Section */
        .about-section {
            background-color: var(--bg-light);
            padding: 80px 0;
        }

        .about-container {
            display: flex;
            align-items: center;
            gap: 50px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .about-image {
            flex: 0 0 45%;
        }

        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .about-content {
            flex: 0 0 55%;
        }

        .about-content h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 2.2em;
        }

        .about-content p {
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        /* Video Section */
        .video-section {
            padding: 80px 0;
            background-color: var(--bg-white);
            text-align: center;
        }

        .video-container {
            max-width: 900px;
            margin: 50px auto 0;
        }

        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .video-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .video-placeholder i {
            font-size: 100px;
            color: white;
            opacity: 0.9;
            transition: all 0.3s ease;
        }

        .video-placeholder:hover i {
            transform: scale(1.1);
            opacity: 1;
        }

        /* Stats Section */
        .stats-section {
            background-color: var(--primary-color);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-item {
            padding: 20px;
            width: 250px;
        }

        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1.1em;
            opacity: 0.9;
        }

        /* Services Section */
        .services-section {
            padding: 80px 0;
            background-color: var(--bg-light);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .service-card {
            background-color: var(--bg-white);
            border-radius: 10px;
            padding: 30px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-icon {
            font-size: 30px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .service-card h3 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/api/placeholder/1200/400') no-repeat center;
            background-size: cover;
            color: white;
            padding: 100px 20px;
            text-align: center;
        }

        .cta-section h2 {
            margin-bottom: 20px;
            font-size: 2.2em;
        }

        .cta-section p {
            max-width: 700px;
            margin: 0 auto 30px;
            font-size: 1.1em;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .cta-btn {
            background-color: white;
            color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1em;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Footer */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-col {
            flex: 1;
            min-width: 250px;
        }

        .footer-col h3 {
            color: var(--primary-light);
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .footer-col p {
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #fff;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .footer-links a:hover {
            opacity: 1;
        }

        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .contact-info i {
            margin-right: 10px;
            color: var(--primary-light);
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-icons a {
            color: #fff;
            font-size: 18px;
            transition: transform 0.3s;
        }

        .social-icons a:hover {
            transform: translateY(-3px);
            color: var(--primary-light);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .about-container {
                flex-direction: column;
            }

            .about-image, .about-content {
                flex: 0 0 100%;
            }

            .highlight-card {
                width: 100%;
                max-width: 400px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .logo {
                margin-bottom: 15px;
            }

            .navbar {
                flex-direction: column;
                width: 100%;
            }

            .navbar a {
                margin: 5px 0;
                width: 100%;
                text-align: center;
            }

            .hero h1 {
                font-size: 2.3em;
            }

            .tagline {
                font-size: 1.2em;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 15px;
            }

            .about-container {
                padding: 0 20px;
            }

            .stat-item {
                width: 100%;
                max-width: 250px;
            }

            .cta-buttons {
                flex-direction: column;
                gap: 15px;
                max-width: 250px;
                margin: 30px auto 0;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .stagger-1 {
            animation-delay: 0.1s;
        }

        .stagger-2 {
            animation-delay: 0.2s;
        }

        .stagger-3 {
            animation-delay: 0.3s;
        }
    </style>

</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="/api/placeholder/100/50" alt="PESO Logo">
                    <a href="index.php" class="logo-text">PESO Los Baños</a>
                </div>
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <nav class="navbar" id="navbar">
                    <a href="login.php">Home</a>
                    <a href="#about">About Us</a>
                    <a href="#services">Services</a>
                    <a href="#video">Success Stories</a>
                    <a href="#contact">Contact</a>
                    <a href="login_worker.php" class="btn-login">Login Worker</a>
                    <a href="login_client.php" class="btn-register">Login Client</a>
                </nav>
            </div>
        </div>
    </header>
    
    <div class="hero">
        <div class="container">
            <h1 class="fade-in">Welcome to PESO Los Baños</h1>
            <p class="tagline fade-in stagger-1">Connecting skills with opportunities in Los Baños, Laguna - Your gateway to employment and workforce development</p>
            <div class="hero-buttons fade-in stagger-2">
                <a href="register_worker.php" class="hero-btn primary-btn">Job Seekers</a>
                <a href="register_client.php" class="hero-btn secondary-btn">Employers</a>
            </div>
        </div>
    </div>
    
    <section class="highlight-section">
        <div class="container">
            <div class="highlight-header">
                <h2>How We Can Help You</h2>
                <p class="section-subtitle">PESO Los Baños offers comprehensive employment services to both job seekers and employers in our community.</p>
            </div>
            <div class="highlight-cards">
                <div class="highlight-card fade-in">
                    <div class="highlight-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3>Find Your Dream Job</h3>
                    <p>Access hundreds of local job opportunities tailored to your skills and experience. Start your career journey today!</p>
                    <a href="register_worker.php" class="highlight-btn">Join as Worker</a>
                </div>
                
                <div class="highlight-card fade-in stagger-1">
                    <div class="highlight-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Hire Skilled Professionals</h3>
                    <p>Connect with qualified, pre-screened workers for your business needs. Find the perfect match for your requirements.</p>
                    <a href="register_client.php" class="highlight-btn">Join as Client</a>
                </div>
                
                <div class="highlight-card fade-in stagger-2">
                    <div class="highlight-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Skills Development</h3>
                    <p>Enhance your employability with our training programs and workshops designed to boost your career prospects.</p>
                    <a href="login_worker.php" class="highlight-btn">Explore Trainings</a>
                </div>
            </div>
        </div>
    </section>
    
    <section class="about-section" id="about">
        <div class="about-container">
            <div class="about-image fade-in">
                <img src="uploads/julius_imresizer.jpg" alt="PESO Los Baños Office">
            </div>
            <div class="about-content fade-in stagger-1">
                <h2>Empowering Los Baños Through Employment</h2>
                <p>
                    The Public Employment Service Office (PESO) Los Baños is a government-mandated facility dedicated to providing 
                    efficient and effective employment facilitation services to the community. As a non-fee charging service, 
                    we bridge the gap between job seekers and employers, creating opportunities for economic growth and development.
                </p>
                <p>
                    Our mission is to reduce unemployment and underemployment in Los Baños by offering comprehensive employment 
                    assistance, career guidance, and skills matching services. We work closely with local businesses, educational 
                    institutions, and government agencies to create a thriving employment ecosystem.
                </p>
                <p>
                    Established under Republic Act No. 8759 or the PESO Act of 1999, PESO Los Baños operates under the Department of Labor and Employment (DOLE) 
                    and the Local Government Unit (LGU) of Los Baños. We serve as the primary employment resource center for the municipality, 
                    catering to both job seekers and employers in our community.
                </p>
            </div>
        </div>
    </section>

    <?php
include('db.php');

// Fetch available workers
$workers_sql = "SELECT w.*, AVG(r.rating) AS avg_rating, COUNT(r.id) AS total_reviews 
                FROM workers w 
                LEFT JOIN ratings r ON w.id = r.worker_id 
                GROUP BY w.id 
                LIMIT 3"; // Limit to 3 workers
$workers_result = $conn->query($workers_sql);
?>

    
<style>
    .workers-section {
        background-color: #f4f7f6;
        padding: 60px 0;
    }

    .section-title {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 40px;
        font-size: 2.5rem;
        font-weight: 700;
    }

    .workers-grid {
        display: flex;
        justify-content: center;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .worker-card {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 350px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e0e0e0;
    }

    .worker-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .worker-card-header {
        position: relative;
        height: 250px;
        background-color: #e9ecef;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
    }

    .worker-card-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .worker-card:hover .worker-card-header img {
        transform: scale(1.1);
    }

    .worker-card-content {
        padding: 25px;
        text-align: center;
    }

    .worker-card-content h3 {
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 1.4rem;
        font-weight: 700;
    }

    .worker-rating {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 15px;
    }

    .worker-rating .stars {
        margin-right: 10px;
    }

    .worker-rating .stars i {
        color: #f39c12;
        font-size: 1.2rem;
    }

    .worker-details {
        background-color: #f9f9f9;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .worker-details p {
        margin: 8px 0;
        color: #34495e;
        font-size: 0.95rem;
    }

    .btn-primary {
        display: inline-block;
        background-color: #3498db;
        color: white;
        padding: 12px 25px;
        border-radius: 30px;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.2s ease;
        font-weight: 600;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: scale(1.05);
    }

    @media (max-width: 1024px) {
        .workers-grid {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<section class="workers-section">
    <div class="container">
        <h2 class="section-title">Meet Our Skilled Workers</h2>
        <div class="workers-grid">
            <?php while ($worker = $workers_result->fetch_assoc()): ?>
                <div class="worker-card">
                    <div class="worker-card-header">
                        <?php if (!empty($worker['']) && file_exists('uploads/' . $worker[''])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($worker['']); ?>" 
                                 alt="<?php echo htmlspecialchars($worker['full_name'] ?? 'Unknown'); ?>">
                        <?php else: ?>
                            <img src="download.png" 
                                 alt="">
                        <?php endif; ?>
                    </div>
                    <div class="worker-card-content">
                        <h3><?php echo htmlspecialchars($worker['full_name']); ?></h3>
                        
                        <div class="worker-rating">
                            <div class="stars">
                                <?php
                                    $avg_rating = $worker['avg_rating'] ?? 0;
                                    $full_stars = floor($avg_rating);
                                    $half_star = $avg_rating - $full_stars > 0.3;
                                    
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $full_stars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($half_star && $i == $full_stars + 1) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                            $half_star = false;
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                ?>
                            </div>
                            <span>(<?php echo $worker['total_reviews'] ?? 0; ?> reviews)</span>
                        </div>
                        
                        <div class="worker-details">
                            <p><strong>Skills:</strong> <?php echo htmlspecialchars($worker['skills'] ?? 'No skills listed'); ?></p>
                            <p><strong>Service Fee:</strong> ₱<?php echo number_format($worker['service_fee'] ?? 0, 2); ?></p>
                            <p><strong>Barangay:</strong> <?php echo htmlspecialchars($worker['begy'] ?? 'Not specified'); ?></p>
                        </div>
                        
                    
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
    
    <section class="stats-section">
        <div class="stats-container">
            <div class="stat-item fade-in">
                <div class="stat-number">5,000+</div>
                <div class="stat-label">Jobs Filled</div>
            </div>
            <div class="stat-item fade-in stagger-1">
                <div class="stat-number">1,200+</div>
                <div class="stat-label">Partner Employers</div>
            </div>
            <div class="stat-item fade-in stagger-2">
                <div class="stat-number">800+</div>
                <div class="stat-label">Training Programs</div>
            </div>
            <div class="stat-item fade-in stagger-3">
                <div class="stat-number">95%</div>
                <div class="stat-label">Satisfaction Rate</div>
            </div>
        </div>
    </section>
    
    <section class="services-section" id="services">
        <div class="container">
            <div class="highlight-header">
                <h2>Our Services</h2>
                <p class="section-subtitle">PESO Los Baños offers a wide range of employment services to cater to the needs of different stakeholders.</p>
            </div>
            <div class="services-grid">
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Job Matching</h3>
                    <p>We connect job seekers with suitable employment opportunities based on their skills, experience, and career goals.</p>
                </div>
                
                <div class="service-card fade-in stagger-1">
                    <div class="service-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Career Counseling</h3>
                    <p>Our professional counselors provide guidance on career paths, resume writing, and interview preparation.</p>
                </div>
                
                <div class="service-card fade-in stagger-2">
                    <div class="service-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Recruitment Assistance</h3>
                    <p>We help employers find the right talent by facilitating job fairs, pre-screening candidates, and providing referrals.</p>
                </div>
                
                <div class="service-card fade-in">
                    <div class="service-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Livelihood Programs</h3>
                    <p>We offer livelihood training and entrepreneurship development programs to promote self-employment opportunities.</p>
                </div>
                
                <div class="service-card fade-in stagger-1">
                    <div class="service-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3>Skills Training</h3>
                    <p>We provide various skills training programs to enhance employability and meet industry demands.</p>
                </div>
                
                <div class="service-card fade-in stagger-2">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Labor Market Information</h3>
                    <p>We maintain updated information on employment trends, in-demand skills, and industry developments.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta-section">
        <div class="container">
            <h2 class="fade-in">Ready to Take the Next Step in Your Career?</h2>
            <p class="fade-in stagger-1">Join thousands of successful job seekers and employers who have benefited from our services. Registration is quick, easy, and completely free!</p>
            <div class="cta-buttons fade-in stagger-2">
                <a href="register_worker.php" class="cta-btn">Register as Worker</a>
                <a href="register_client.php" class="cta-btn">Register as Client</a>
            </div>
        </div>
    </section>
    
    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>About PESO Los Baños</h3>
                    <p>The Public Employment Service Office (PESO) of Los Baños is committed to providing efficient employment facilitation services to the community.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="register_worker.php">Register as Worker</a></li>
                        <li><a href="register_client.php">Register as Client</a></li>
                        <li><a href="login_worker.php">Login as Worker</a></li>
                        <li><a href="login_client.php">Login as Client</a></li>
                        <li><a href="login_admin.php">Admin Portal</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <div class="contact-info">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>Municipal Building, Brgy. Batong Malake, Los Baños, Laguna</p>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-phone"></i>
                        <p>(049) 536-2719</p>
                    </div>
                    <div class="contact-info">
                        <i class="fas fa-envelope"></i>
                        <p>peso_losbanos@laguna.gov.ph</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>© 2025 PESO Los Baños | Public Employment Service Office | Los Baños, Laguna</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Navigation Toggle
        document.getElementById('mobileToggle').addEventListener('click', function() {
            const navbar = document.getElementById('navbar');
            navbar.style.display = navbar.style.display === 'flex' ? 'none' : 'flex';
        });

        // Video Placeholder Click
        document.getElementById('videoPlaceholder').addEventListener('click', function() {
            const videoWrapper = document.querySelector('.video-wrapper');
            const placeholderDiv = document.getElementById('videoPlaceholder');
            
            // Create a video element
            const video = document.createElement('video');
            video.controls = true;
            video.autoplay = true;
            video.style.width = '100%';
            video.style.height = '100%';
            video.style.position = 'absolute';
            video.style.top = '0';
            video.style.left = '0';
            
          // Replace with your actual video URL
source.src = "https://your-video-url.mp4";
source.type = "video/mp4";

// Append the source to the video
video.appendChild(source);

// Replace the placeholder with the video
videoWrapper.replaceChild(video, placeholderDiv);
});

// Initialize date in footer
document.addEventListener('DOMContentLoaded', function() {
    const footerYear = document.querySelector('.footer-bottom p');
    const currentYear = new Date().getFullYear();
    footerYear.innerHTML = footerYear.innerHTML.replace('2025', currentYear);
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if(targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if(targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});
