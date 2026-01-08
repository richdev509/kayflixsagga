<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Kayflix - La révolution du streaming</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            :root {
                --primary: #E50914;
                --secondary: #dc030c;
                --blue-accent: #1a4d8f;
                --dark: #0a0a0a;
                --card-bg: rgba(20, 20, 20, 0.85);
                --glow-primary: rgba(229, 9, 20, 0.4);
                --glow-secondary: rgba(220, 3, 12, 0.3);
            }

            body {
                font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #000000 0%, #1a0a0a 50%, #000000 100%);
                color: #ffffff;
                line-height: 1.6;
                overflow-x: hidden;
                position: relative;
            }

            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background:
                    radial-gradient(circle at 20% 50%, rgba(229, 9, 20, 0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(26, 77, 143, 0.05) 0%, transparent 50%);
                pointer-events: none;
                z-index: 0;
            }

            .navbar {
                position: fixed;
                top: 0;
                width: 100%;
                padding: 25px 60px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                z-index: 1000;
                background: rgba(0, 0, 0, 0.9);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(229, 9, 20, 0.2);
            }

            .logo {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                position: relative;
            }

            .logo img {
                height: 45px;
                width: auto;
                filter: drop-shadow(0 0 15px var(--glow-primary));
                transition: transform 0.3s ease;
            }

            .logo:hover img {
                transform: scale(1.05);
            }

            .logo-text {
                font-size: 36px;
                font-weight: 700;
                color: var(--primary);
                letter-spacing: 2px;
                filter: drop-shadow(0 0 20px var(--glow-primary));
            }

            .nav-buttons {
                display: flex;
                gap: 15px;
            }

            .btn {
                padding: 12px 30px;
                border: none;
                border-radius: 50px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                display: inline-block;
                position: relative;
                overflow: hidden;
            }

            .btn-primary {
                background: var(--primary);
                color: white;
                box-shadow: 0 0 30px var(--glow-primary);
            }

            .btn-primary::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }

            .btn-primary:hover::before {
                width: 300px;
                height: 300px;
            }

            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: 0 0 50px var(--glow-primary);
                background: var(--secondary);
            }

            .btn-secondary {
                background: transparent;
                color: #ffffff;
                border: 2px solid rgba(255, 255, 255, 0.3);
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
            }

            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
                color: #ffffff;
                border-color: var(--primary);
                box-shadow: 0 0 30px var(--glow-primary);
                transform: translateY(-3px);
            }

            .hero {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 120px 20px 60px;
                position: relative;
                background:
                    linear-gradient(to bottom,
                        rgba(0, 0, 0, 0.3) 0%,
                        rgba(0, 0, 0, 0.5) 40%,
                        rgba(0, 0, 0, 0.85) 80%,
                        #000000 100%
                    ),
                    url('{{ asset('images/banner-welcome-images/movies_image.png') }}');
                background-size: cover;
                background-position: center top;
                background-repeat: no-repeat;
                background-attachment: fixed;
            }

            .hero::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg,
                    rgba(229, 9, 20, 0.15) 0%,
                    transparent 50%,
                    rgba(26, 77, 143, 0.08) 100%
                );
                pointer-events: none;
                z-index: 0;
            }

            .hero::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 200px;
                background: linear-gradient(to bottom, transparent, #000000);
                pointer-events: none;
                z-index: 1;
            }

            .hero-content {
                max-width: 900px;
                margin: 0 auto;
                position: relative;
                z-index: 2;
            }

            .hero h1 {
                font-size: 72px;
                font-weight: 700;
                margin-bottom: 30px;
                line-height: 1.1;
                background: linear-gradient(135deg, #fff 0%, var(--primary) 70%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: glow 3s ease-in-out infinite;
            }

            @keyframes glow {
                0%, 100% { filter: drop-shadow(0 0 15px var(--glow-primary)); }
                50% { filter: drop-shadow(0 0 30px var(--glow-primary)); }
            }

            .hero p {
                font-size: 28px;
                margin-bottom: 40px;
                color: #b8b8d1;
                font-weight: 300;
            }

            .section {
                padding: 100px 20px;
                position: relative;
                z-index: 1;
            }

            .container {
                max-width: 1300px;
                margin: 0 auto;
            }

            .section-title {
                font-size: 48px;
                font-weight: 700;
                text-align: center;
                margin-bottom: 70px;
                color: var(--primary);
                filter: drop-shadow(0 0 20px var(--glow-primary));
            }

            .features-container {
                position: relative;
                margin-top: 50px;
                overflow: hidden;
                padding: 0 60px;
                width: 100%;
            }

            .features-grid {
                display: flex;
                gap: 40px;
                transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                will-change: transform;
                width: 100%;
            }

            .features-grid .feature-card {
                min-width: calc(25% - 30px);
                max-width: calc(25% - 30px);
                flex-shrink: 0;
                box-sizing: border-box;
            }

            .carousel-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(229, 9, 20, 0.9);
                color: white;
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                font-size: 24px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                z-index: 10;
                box-shadow: 0 0 20px var(--glow-primary);
            }

            .carousel-nav:hover {
                background: var(--secondary);
                transform: translateY(-50%) scale(1.1);
                box-shadow: 0 0 30px var(--glow-primary);
            }

            .carousel-nav.prev {
                left: 0;
            }

            .carousel-nav.next {
                right: 0;
            }

            .carousel-nav:disabled {
                opacity: 0.3;
                cursor: not-allowed;
                background: rgba(100, 100, 100, 0.5);
            }

            .feature-card {
                background: var(--card-bg);
                padding: 40px;
                border-radius: 20px;
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                border: 1px solid rgba(229, 9, 20, 0.15);
                backdrop-filter: blur(10px);
                overflow: hidden;
                word-wrap: break-word;
            }

            .feature-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                border-radius: 20px;
                padding: 2px;
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                opacity: 0;
                transition: opacity 0.5s;
            }

            .feature-card:hover::before {
                opacity: 1;
            }

            .feature-card:hover {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 20px 60px var(--glow-primary);
            }

            .feature-icon {
                font-size: 56px;
                margin-bottom: 25px;
                display: inline-block;
                filter: drop-shadow(0 0 10px var(--primary));
            }

            .feature-icon img {
                width: 70px;
                height: 70px;
                object-fit: contain;
                filter: drop-shadow(0 0 10px var(--glow-primary));
            }

            .feature-card h3 {
                font-size: 26px;
                margin-bottom: 18px;
                color: var(--primary);
            }

            .feature-card p {
                color: #b8b8d1;
                line-height: 1.8;
                font-size: 16px;
                word-wrap: break-word;
                overflow-wrap: break-word;
                hyphens: auto;
            }

            .pricing-section {
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(20px);
            }

            .plans-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 40px;
                margin-top: 50px;
            }

            .plan-card {
                background: var(--card-bg);
                padding: 50px 35px;
                border-radius: 25px;
                text-align: center;
                position: relative;
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                border: 2px solid transparent;
                backdrop-filter: blur(10px);
            }

            .plan-card::before {
                content: '';
                position: absolute;
                inset: -2px;
                border-radius: 25px;
                padding: 2px;
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
                -webkit-mask-composite: xor;
                mask-composite: exclude;
                opacity: 0;
                transition: opacity 0.5s;
            }

            .plan-card:hover::before {
                opacity: 1;
            }

            .plan-card.featured {
                transform: scale(1.05);
                border-color: var(--primary);
                box-shadow: 0 0 60px var(--glow-primary);
            }

            .plan-card.featured::before {
                opacity: 1;
            }

            .plan-card:hover {
                transform: translateY(-15px) scale(1.08);
                box-shadow: 0 25px 70px var(--glow-secondary);
            }

            .plan-card.coming-soon {
                opacity: 0.6;
                pointer-events: none;
            }

            .plan-card.coming-soon h3 {
                color: #888;
            }

            .plan-card.coming-soon li::before {
                color: #666;
            }

            .badge {
                position: absolute;
                top: -18px;
                left: 50%;
                transform: translateX(-50%);
                background: var(--primary);
                color: white;
                padding: 8px 25px;
                border-radius: 30px;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 1px;
                box-shadow: 0 0 30px var(--glow-primary);
            }

            .badge.coming-soon {
                background: rgba(100, 100, 100, 0.8);
                color: #ddd;
                box-shadow: 0 0 20px rgba(100, 100, 100, 0.3);
            }

            .plan-card h3 {
                font-size: 32px;
                margin-bottom: 20px;
                color: var(--primary);
            }

            .plan-card ul {
                list-style: none;
                margin: 35px 0;
                text-align: left;
            }

            .plan-card li {
                padding: 15px 0;
                border-bottom: 1px solid rgba(229, 9, 20, 0.15);
                color: #b8b8d1;
                font-size: 15px;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .plan-card li::before {
                content: '⚡';
                color: var(--primary);
                font-size: 18px;
                flex-shrink: 0;
            }

            .plan-card li:last-child {
                border-bottom: none;
            }

            footer {
                background: rgba(0, 0, 0, 0.95);
                padding: 50px 20px;
                text-align: center;
                color: #b8b8d1;
                border-top: 1px solid rgba(229, 9, 20, 0.2);
                position: relative;
                z-index: 1;
            }

            footer p {
                margin: 12px 0;
                font-size: 15px;
            }

            @media (max-width: 1200px) {
                .features-grid .feature-card {
                    min-width: calc(33.333% - 27px);
                    max-width: calc(33.333% - 27px);
                }
            }

            @media (max-width: 900px) {
                .features-grid .feature-card {
                    min-width: calc(50% - 20px);
                    max-width: calc(50% - 20px);
                }

                .features-container {
                    padding: 0 50px;
                }
            }

            @media (max-width: 768px) {
                .hero h1 {
                    font-size: 42px;
                }

                .hero p {
                    font-size: 20px;
                }

                .navbar {
                    padding: 15px 20px;
                    flex-wrap: wrap;
                }

                .logo img {
                    height: 35px;
                }

                .nav-buttons {
                    gap: 10px;
                }

                .nav-buttons .btn {
                    padding: 10px 20px;
                    font-size: 14px;
                }

                .section-title {
                    font-size: 36px;
                }

                .features-grid .feature-card {
                    min-width: 100%;
                    max-width: 100%;
                }

                .features-container {
                    padding: 0 50px;
                }

                .carousel-nav {
                    width: 40px;
                    height: 40px;
                    font-size: 20px;
                }

                .plans-grid {
                    grid-template-columns: 1fr;
                }

                .plan-card.featured {
                    transform: scale(1);
                }
            }

            @media (max-width: 480px) {
                .navbar {
                    padding: 12px 15px;
                }

                .logo img {
                    height: 30px;
                }

                .nav-buttons .btn {
                    padding: 8px 16px;
                    font-size: 13px;
                }

                .hero h1 {
                    font-size: 32px;
                }

                .hero p {
                    font-size: 16px;
                }

                .features-container {
                    padding: 0 50px;
                }

                .carousel-nav {
                    width: 35px;
                    height: 35px;
                    font-size: 18px;
                }
            }

            /* Modal Popup */
            .modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.92);
                z-index: 9999;
                justify-content: center;
                align-items: center;
                backdrop-filter: blur(10px);
                animation: fadeIn 0.3s ease-out;
            }

            .modal-overlay.active {
                display: flex;
            }

            .modal-content {
                background: linear-gradient(135deg, rgba(20, 20, 20, 0.95) 0%, rgba(10, 10, 10, 0.98) 100%);
                border-radius: 25px;
                padding: 50px;
                max-width: 500px;
                width: 90%;
                text-align: center;
                position: relative;
                border: 2px solid rgba(229, 9, 20, 0.3);
                box-shadow: 0 0 60px var(--glow-primary);
                animation: slideUp 0.4s ease-out;
            }

            .modal-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: transparent;
                border: none;
                color: #ffffff;
                font-size: 32px;
                cursor: pointer;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                border-radius: 50%;
            }

            .modal-close:hover {
                background: rgba(229, 9, 20, 0.2);
                color: var(--primary);
                transform: rotate(90deg);
            }

            .modal-image {
                width: 100%;
                max-width: 300px;
                height: auto;
                margin: 0 auto 30px;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(229, 9, 20, 0.3);
            }

            .modal-title {
                font-size: 32px;
                font-weight: 700;
                color: var(--primary);
                margin-bottom: 20px;
                filter: drop-shadow(0 0 20px var(--glow-primary));
            }

            .modal-message {
                font-size: 18px;
                color: #b8b8d1;
                line-height: 1.6;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(50px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Modal Responsive */
            @media (max-width: 768px) {
                .modal-content {
                    padding: 35px 25px;
                    max-width: 85%;
                    width: 85%;
                }

                .modal-close {
                    top: 15px;
                    right: 15px;
                    font-size: 28px;
                    width: 35px;
                    height: 35px;
                }

                .modal-image {
                    max-width: 250px;
                    margin-bottom: 25px;
                }

                .modal-title {
                    font-size: 26px;
                    margin-bottom: 15px;
                }

                .modal-message {
                    font-size: 16px;
                    line-height: 1.5;
                }
            }

            @media (max-width: 480px) {
                .modal-content {
                    padding: 30px 20px;
                    max-width: 92%;
                    width: 92%;
                    border-radius: 20px;
                }

                .modal-close {
                    top: 12px;
                    right: 12px;
                    font-size: 26px;
                    width: 32px;
                    height: 32px;
                }

                .modal-image {
                    max-width: 200px;
                    margin-bottom: 20px;
                    border-radius: 12px;
                }

                .modal-title {
                    font-size: 22px;
                    margin-bottom: 12px;
                }

                .modal-message {
                    font-size: 15px;
                    line-height: 1.5;
                }

                .nav-buttons .btn {
                    padding: 10px 18px;
                    font-size: 14px;
                    white-space: nowrap;
                }
            }

            @media (max-width: 360px) {
                .modal-content {
                    padding: 25px 15px;
                    max-width: 95%;
                    width: 95%;
                }

                .modal-image {
                    max-width: 180px;
                }

                .modal-title {
                    font-size: 20px;
                }

                .modal-message {
                    font-size: 14px;
                }

                .nav-buttons .btn {
                    padding: 8px 14px;
                    font-size: 12px;
                }
            }

            /* Animations d'entrée */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(40px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fade-in-up {
                animation: fadeInUp 1s ease-out forwards;
                opacity: 0;
            }

            .delay-1 { animation-delay: 0.2s; }
            .delay-2 { animation-delay: 0.4s; }
            .delay-3 { animation-delay: 0.6s; }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar">
            <a href="/" class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Kayflix Logo">
            </a>
            <div class="nav-buttons">
                <button onclick="openModal()" class="btn btn-primary">Télécharger Application</button>
            </div>
        </nav>

        <!-- Modal Popup -->
        <div class="modal-overlay" id="appModal" onclick="closeModalOnOverlay(event)">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal()">&times;</button>
                <img src="{{ asset('images/app-mobile-screenshot.png') }}" alt="Application Mobile Kayflix" class="modal-image">
                <h2 class="modal-title">Application Bientôt Disponible</h2>
                <p class="modal-message">Notre application mobile est en cours de développement. Restez connectés pour être les premiers informés de son lancement !</p>
            </div>
        </div>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1 class="fade-in-up">L'avenir du streaming est ici</h1>
                <p class="fade-in-up delay-1">Explorez un univers de contenus premium en qualité exceptionnelle</p>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-primary fade-in-up delay-2" style="font-size: 22px; padding: 18px 45px;">Découvrir maintenant</a>
                @endif
            </div>
        </section>

        <!-- Features Section -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Expérience de streaming ultime</h2>
                <div class="features-container">
                    <button class="carousel-nav prev" onclick="slideFeatures(-1)" id="prevBtn">‹</button>
                    <div class="features-grid" id="featuresGrid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <img src="{{ asset('images/logo-icon.png') }}" alt="Kayflix Icon">
                            </div>
                            <h3>Contenu Illimité</h3>
                            <p>Accédez à une bibliothèque infinie de films et séries en streaming haute qualité, disponibles 24/7.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">⚡</div>
                            <h3>Multi-appareils</h3>
                            <p>Regardez sur TV, ordinateur, tablette ou smartphone avec synchronisation instantanée entre tous vos appareils.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <img src="{{ asset('images/logo-icon.png') }}" alt="Kayflix Icon">
                            </div>
                            <h3>Qualité 4K Ultra HD</h3>
                            <p>Profitez d'une qualité d'image exceptionnelle avec nos contenus disponibles en HD, 4K et HDR.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">⟳</div>
                            <h3>Sans Engagement</h3>
                            <p>Annulez ou modifiez votre abonnement à tout moment, sans frais ni contrainte de durée.</p>
                        </div>
                    </div>
                    <button class="carousel-nav next" onclick="slideFeatures(1)" id="nextBtn">›</button>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="section pricing-section">
            <div class="container">
                <h2 class="section-title">Choisissez votre formule</h2>
                <div class="plans-grid">
                    <div class="plan-card featured">
                        <span class="badge">DISPONIBLE</span>
                        <h3>Premium</h3>
                        <ul>
                            <li>Streaming illimité</li>
                            <li>1 écran simultané</li>
                            <li>Qualité HD & 4K</li>
                            <li>Accès complet à la bibliothèque</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 25px; width: 100%;">Commencer</a>
                        @endif
                    </div>
                    <div class="plan-card coming-soon">
                        <span class="badge coming-soon">À VENIR</span>
                        <h3>Supra Premium</h3>
                        <ul>
                            <li>Streaming illimité</li>
                            <li>2 écrans simultanés</li>
                            <li>Qualité 4K Ultra HD</li>
                            <li>Contenu exclusif premium</li>
                        </ul>
                        <button class="btn btn-secondary" style="margin-top: 25px; width: 100%;" disabled>Bientôt disponible</button>
                    </div>
                    <div class="plan-card coming-soon">
                        <span class="badge coming-soon">À VENIR</span>
                        <h3>Ultra Premium</h3>
                        <ul>
                            <li>Streaming illimité</li>
                            <li>4 écrans simultanés</li>
                            <li>Qualité 4K Ultra HD</li>
                            <li>Contenu exclusif & prioritaire</li>
                        </ul>
                        <button class="btn btn-secondary" style="margin-top: 25px; width: 100%;" disabled>Bientôt disponible</button>
                    </div>
                </div>
            </div>
        </section>


        <!-- Footer -->
        <footer>
            <div class="container">
                <p>&copy; {{ date('Y') }} Kayflix. Tous droits réservés.</p>
                <p>La nouvelle ère du streaming premium</p>
            </div>
        </footer>

        <script>
            let currentSlide = 0;
            const featuresGrid = document.getElementById('featuresGrid');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            function getCardsPerView() {
                const width = window.innerWidth;
                if (width <= 768) return 1;
                if (width <= 900) return 2;
                if (width <= 1200) return 3;
                return 4;
            }

            function getTotalSlides() {
                const cardsPerView = getCardsPerView();
                const totalCards = featuresGrid.children.length;
                return Math.max(0, totalCards - cardsPerView);
            }

            function updateButtons() {
                const totalSlides = getTotalSlides();
                prevBtn.disabled = currentSlide === 0;
                nextBtn.disabled = currentSlide >= totalSlides;
            }

            function slideFeatures(direction) {
                const totalSlides = getTotalSlides();
                currentSlide += direction;

                if (currentSlide < 0) currentSlide = 0;
                if (currentSlide > totalSlides) currentSlide = totalSlides;

                const cardWidth = featuresGrid.children[0].offsetWidth;
                const gap = 40;
                const offset = -(currentSlide * (cardWidth + gap));

                featuresGrid.style.transform = `translateX(${offset}px)`;
                updateButtons();
            }

            // Reset carousel on window resize
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    currentSlide = 0;
                    featuresGrid.style.transform = 'translateX(0)';
                    updateButtons();
                }, 250);
            });

            // Initialize
            updateButtons();

            // Modal Functions
            function openModal() {
                document.getElementById('appModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                document.getElementById('appModal').classList.remove('active');
                document.body.style.overflow = 'auto';
            }

            function closeModalOnOverlay(event) {
                if (event.target.id === 'appModal') {
                    closeModal();
                }
            }

            // Close modal with Escape key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });
        </script>
    </body>
</html>
