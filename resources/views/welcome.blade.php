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
                --primary: #00f0ff;
                --secondary: #ff00ff;
                --dark: #0a0a0f;
                --card-bg: rgba(20, 20, 35, 0.8);
                --glow-primary: rgba(0, 240, 255, 0.5);
                --glow-secondary: rgba(255, 0, 255, 0.5);
            }

            body {
                font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #0a0a0f 0%, #1a0a2e 50%, #0a0a0f 100%);
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
                    radial-gradient(circle at 20% 50%, rgba(0, 240, 255, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(255, 0, 255, 0.1) 0%, transparent 50%);
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
                background: rgba(10, 10, 15, 0.85);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(0, 240, 255, 0.1);
            }

            .logo {
                font-size: 36px;
                font-weight: 700;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                text-decoration: none;
                letter-spacing: 2px;
                position: relative;
                text-shadow: 0 0 30px var(--glow-primary);
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
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                color: #0a0a0f;
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
            }

            .btn-secondary {
                background: transparent;
                color: var(--primary);
                border: 2px solid var(--primary);
                box-shadow: 0 0 20px rgba(0, 240, 255, 0.2);
            }

            .btn-secondary:hover {
                background: var(--primary);
                color: #0a0a0f;
                box-shadow: 0 0 40px var(--glow-primary);
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
            }

            .hero-content {
                max-width: 900px;
                margin: 0 auto;
                position: relative;
                z-index: 1;
            }

            .hero h1 {
                font-size: 72px;
                font-weight: 700;
                margin-bottom: 30px;
                line-height: 1.1;
                background: linear-gradient(135deg, #fff 0%, var(--primary) 50%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: glow 3s ease-in-out infinite;
            }

            @keyframes glow {
                0%, 100% { filter: drop-shadow(0 0 20px var(--glow-primary)); }
                50% { filter: drop-shadow(0 0 40px var(--glow-secondary)); }
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
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 40px;
                margin-top: 50px;
            }

            .feature-card {
                background: var(--card-bg);
                padding: 40px;
                border-radius: 20px;
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                border: 1px solid rgba(0, 240, 255, 0.1);
                backdrop-filter: blur(10px);
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

            .feature-card h3 {
                font-size: 26px;
                margin-bottom: 18px;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .feature-card p {
                color: #b8b8d1;
                line-height: 1.8;
                font-size: 16px;
            }

            .pricing-section {
                background: rgba(10, 10, 30, 0.5);
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

            .badge {
                position: absolute;
                top: -18px;
                left: 50%;
                transform: translateX(-50%);
                background: linear-gradient(135deg, var(--primary), var(--secondary));
                color: #0a0a0f;
                padding: 8px 25px;
                border-radius: 30px;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 1px;
                box-shadow: 0 0 30px var(--glow-primary);
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
                border-bottom: 1px solid rgba(0, 240, 255, 0.1);
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
                background: rgba(10, 10, 15, 0.95);
                padding: 50px 20px;
                text-align: center;
                color: #b8b8d1;
                border-top: 1px solid rgba(0, 240, 255, 0.1);
                position: relative;
                z-index: 1;
            }

            footer p {
                margin: 12px 0;
                font-size: 15px;
            }

            @media (max-width: 768px) {
                .hero h1 {
                    font-size: 42px;
                }

                .hero p {
                    font-size: 20px;
                }

                .navbar {
                    padding: 20px 25px;
                }

                .logo {
                    font-size: 28px;
                }

                .section-title {
                    font-size: 36px;
                }

                .features-grid,
                .plans-grid {
                    grid-template-columns: 1fr;
                }

                .plan-card.featured {
                    transform: scale(1);
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
            <a href="/" class="logo">KAYFLIX</a>
            <div class="nav-buttons">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-secondary">Connexion</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary">S'inscrire</a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>

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
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">∞</div>
                        <h3>Contenu Illimité</h3>
                        <p>Accédez à une bibliothèque infinie de films et séries en streaming haute qualité, disponibles 24/7.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Multi-appareils</h3>
                        <p>Regardez sur TV, ordinateur, tablette ou smartphone avec synchronisation instantanée entre tous vos appareils.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">◈</div>
                        <h3>Qualité 4K Ultra HD</h3>
                        <p>Profitez d'une qualité d'image exceptionnelle avec nos contenus disponibles en HD, 4K et HDR.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⟳</div>
                        <h3>Sans Engagement</h3>
                        <p>Annulez ou modifiez votre abonnement à tout moment, sans frais ni contrainte de durée.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="section pricing-section">
            <div class="container">
                <h2 class="section-title">Choisissez votre formule</h2>
                <div class="plans-grid">
                    <div class="plan-card">
                        <h3>Basique</h3>
                        <ul>
                            <li>Streaming illimité</li>
                            <li>1 écran simultané</li>
                            <li>Qualité HD</li>
                            <li>1 profil utilisateur</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-secondary" style="margin-top: 25px; width: 100%;">Choisir</a>
                        @endif
                    </div>
                    <div class="plan-card featured">
                        <span class="badge">POPULAIRE</span>
                        <h3>Premium</h3>
                        <ul>
                            <li>Streaming illimité</li>
                            <li>2 écrans simultanés</li>
                            <li>Qualité HD & 4K</li>
                            <li>3 profils utilisateurs</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 25px; width: 100%;">Choisir</a>
                        @endif
                    </div>
                    <div class="plan-card">
                        <h3>VIP</h3>
                        <ul>
                            <li>Streaming illimité</li>
                            <li>4 écrans simultanés</li>
                            <li>Qualité 4K Ultra HD</li>
                            <li>5 profils utilisateurs</li>
                        </ul>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-secondary" style="margin-top: 25px; width: 100%;">Choisir</a>
                        @endif
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
    </body>
</html>