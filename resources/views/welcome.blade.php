<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'HRMS Portal') }} - Welcome</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap" rel="stylesheet" />

    <style>
        /* ─── LOCK SCREEN TO 100VH ─── */
        html, body {
            height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Nunito', sans-serif;
            background: #1E3A8A;
            overflow: hidden; /* Prevents all scrolling */
            box-sizing: border-box;
        }

        /* ─── BACKGROUND DECORATIONS ─── */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-layer::after {
            content: '';
            position: absolute;
            width: 70vh;
            height: 70vh;
            border-radius: 50%;
            border: 8vh solid rgba(245, 158, 11, 0.08);
            bottom: -25vh;
            right: -15vh;
        }

        .bg-layer::before {
            content: '';
            position: absolute;
            width: 35vh;
            height: 35vh;
            border-radius: 50%;
            border: 4vh solid rgba(245, 158, 11, 0.12);
            top: -10vh;
            left: -10vh;
        }

        /* ─── MAIN CONTAINER ─── */
        .main-wrapper {
            position: relative;
            z-index: 1;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2vh 20px;
            /* Added smooth transition for page exit */
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ─── BRANDING / LOGO ─── */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 3.5vh;
            animation: fadeInDown 0.8s ease-out;
        }

        .shield {
            width: 48px;
            height: 54px;
            background: #F59E0B;
            clip-path: polygon(0 0, 100% 0, 100% 68%, 50% 100%, 0 68%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
        }

        .brand-text .title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(20px, 3vh, 26px);
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            margin: 0;
        }

        .brand-text .subtitle {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #F59E0B;
            margin-top: 4px;
        }

        /* ─── HERO TYPOGRAPHY ─── */
        .hero-text {
            text-align: center;
            max-width: 600px;
            margin-bottom: 4vh;
            animation: fadeInDown 1s ease-out 0.2s both;
        }

        .hero-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(28px, 5vh, 44px);
            font-weight: 900;
            color: #fff;
            margin-bottom: 1.5vh;
            line-height: 1.15;
        }

        .hero-text h1 em {
            font-style: italic;
            color: #F59E0B;
        }

        .hero-text p {
            font-size: clamp(14px, 2vh, 16px);
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.5;
            margin-bottom: 0;
        }

        /* ─── PORTAL CARDS ─── */
        .portal-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 20px;
            width: 100%;
            max-width: 760px;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .portal-card {
            background: #fff;
            border-radius: 16px;
            padding: 3vh 24px;
            text-align: center;
            text-decoration: none;
            color: #0F172A;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: #1E3A8A;
            transition: background 0.3s ease;
        }

        .portal-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .portal-card:hover::before {
            background: #F59E0B;
        }

        .icon-wrapper {
            width: 60px;
            height: 60px;
            background: #F8FAFC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #1E3A8A;
            margin-bottom: 2vh;
            transition: all 0.3s ease;
        }

        .portal-card:hover .icon-wrapper {
            background: #1E3A8A;
            color: #fff;
            transform: scale(1.1);
        }

        .portal-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(20px, 3vh, 24px);
            font-weight: 800;
            margin-bottom: 1vh;
            color: #1E3A8A;
        }

        .portal-card p {
            font-size: clamp(13px, 1.8vh, 14px);
            color: #64748B;
            line-height: 1.5;
            margin-bottom: 2.5vh;
            flex-grow: 1;
        }

        .btn-portal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background: #F8FAFC;
            color: #1E3A8A;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 800;
            transition: all 0.3s ease;
        }

        .portal-card:hover .btn-portal {
            background: #1E3A8A;
            color: #fff;
            border-color: #1E3A8A;
            box-shadow: 0 6px 15px rgba(30, 58, 138, 0.2);
        }

        /* ─── ANIMATIONS ─── */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ─── FOOTER ─── */
        .footer-text {
            margin-top: 4vh;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.4);
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        /* ─── RESPONSIVE SAFEGUARD FOR EXTREMELY TINY SCREENS ─── */
        @media (max-width: 768px) {
            .portal-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .hero-text h1 { font-size: 28px; }
            .bg-layer::after { display: none; }
        }

        /* Re-enable scrolling ONLY if the screen height is shorter than 600px to prevent UI breaking */
        @media (max-height: 600px) {
            html, body { overflow-y: auto; height: auto; }
            .main-wrapper { min-height: 100vh; height: auto; padding: 40px 20px; }
        }
    </style>
</head>

<body>

    <!-- Decorative Background -->
    <div class="bg-layer"></div>

    <div class="main-wrapper" id="mainWrapper">
        
        <!-- Brand Header -->
        <div class="brand-header">
            <div class="shield">
                <i class="bi bi-buildings-fill" style="margin-top: -4px;"></i>
            </div>
            <div class="brand-text">
                <h2 class="title">Enterprise HRMS</h2>
                <div class="subtitle">Unified Workforce Platform</div>
            </div>
        </div>

        <!-- Hero Content -->
        <div class="hero-text">
            <h1>Empowering your <em>business</em> <br>one click away</h1>
            <p>Select your dedicated portal below to manage attendance, payroll, leaves, and organizational performance securely.</p>
        </div>

        <!-- Portal Selection Grid -->
        <div class="portal-grid">
            
            <!-- Client Portal Card -->
            <a href="{{ route('client.login') }}" class="portal-card js-transition-link">
                <div class="icon-wrapper">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <h3>Client Portal</h3>
                <p>Designed for HR Administrators and Management to oversee operations, process payroll, and manage company records.</p>
                <div class="btn-portal">
                    Client Login <i class="bi bi-arrow-right"></i>
                </div>
            </a>

            <!-- Employee Portal Card -->
            <a href="{{ route('employee.login') }}" class="portal-card js-transition-link">
                <div class="icon-wrapper">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <h3>Employee Portal</h3>
                <p>Designed for Staff Members to apply for leaves, download payslips, and track daily attendance dynamically.</p>
                <div class="btn-portal">
                    Employee Login <i class="bi bi-arrow-right"></i>
                </div>
            </a>

        </div>

        <!-- Footer -->
        <div class="footer-text">
            &copy; {{ date('Y') }} HRMS Portal. All rights reserved.
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Page Transition Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const links = document.querySelectorAll('.js-transition-link');
            const wrapper = document.getElementById('mainWrapper');

            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Stop immediate navigation
                    const targetUrl = this.getAttribute('href');
                    
                    // 1. Give visual feedback on the clicked button
                    const btn = this.querySelector('.btn-portal');
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Connecting...`;
                    btn.style.backgroundColor = '#1E3A8A';
                    btn.style.color = '#fff';

                    // 2. Trigger the smooth fade-out and slight scale-down animation
                    wrapper.style.opacity = '0';
                    wrapper.style.transform = 'scale(0.96) translateY(-10px)';

                    // 3. Navigate to the actual route after the animation completes (400ms)
                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 400);
                });
            });

            // Prevent Safari/Chrome 'back button' cache issue where page remains invisible
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        });
    </script>
</body>

</html>