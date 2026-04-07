
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer</title>

    <link rel="icon" type="image/png" href="assets/logo_white.png">
    <link rel="shortcut icon" href="assets/logo_white.png" type="image/png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg-1:#030712;
            --bg-2:#0b1220;
            --bg-3:#111827;
            --panel:rgba(255,255,255,0.06);
            --panel-2:rgba(255,255,255,0.09);
            --border:rgba(255,255,255,0.12);
            --text:#f3f4f6;
            --muted:#9ca3af;
            --accent:#60a5fa;
            --accent-2:#2563eb;
            --glow:rgba(96,165,250,0.35);
            --shadow:0 25px 70px rgba(0,0,0,0.45);
            --radius:26px;
        }

        *{
            box-sizing:border-box;
        }

        html{
            scroll-behavior:smooth;
        }

        body{
            margin:0;
            font-family:'Inter',sans-serif;
            color:var(--text);
            background: linear-gradient(180deg, var(--bg-1) 0%, var(--bg-2) 50%, var(--bg-3) 100%);
            overflow-x:hidden;
        }

        /* Animated flag-like background */
        .developer-bg{
            position:fixed;
            inset:0;
            z-index:-3;
            overflow:hidden;
            background:
                radial-gradient(circle at 15% 20%, rgba(37,99,235,0.20), transparent 25%),
                radial-gradient(circle at 80% 30%, rgba(96,165,250,0.16), transparent 28%),
                radial-gradient(circle at 50% 80%, rgba(255,255,255,0.05), transparent 22%),
                linear-gradient(180deg, #020617 0%, #08101d 45%, #0b1323 100%);
        }

        .flag-wave{
            position:absolute;
            width:160%;
            height:260px;
            left:-30%;
            background: linear-gradient(
                90deg,
                rgba(255,255,255,0.02) 0%,
                rgba(96,165,250,0.10) 25%,
                rgba(255,255,255,0.03) 50%,
                rgba(37,99,235,0.10) 75%,
                rgba(255,255,255,0.02) 100%
            );
            filter: blur(8px);
            transform-origin:center;
            box-shadow:
                0 30px 80px rgba(0,0,0,0.28),
                inset 0 1px 0 rgba(255,255,255,0.06),
                inset 0 -20px 40px rgba(0,0,0,0.20);
            border-radius: 45% 55% 55% 45% / 40% 35% 65% 60%;
            animation: waveFlow 10s ease-in-out infinite;
        }

        .flag-wave.wave-1{
            top:6%;
            animation-delay:0s;
            opacity:.75;
        }

        .flag-wave.wave-2{
            top:28%;
            animation-delay:2s;
            opacity:.55;
        }

        .flag-wave.wave-3{
            top:55%;
            animation-delay:4s;
            opacity:.38;
        }

        @keyframes waveFlow{
            0%,100%{
                transform: translateX(0) rotate(-2deg) skewY(-2deg);
            }
            25%{
                transform: translateX(2%) rotate(1deg) skewY(1deg);
            }
            50%{
                transform: translateX(-2%) rotate(-1deg) skewY(-1deg);
            }
            75%{
                transform: translateX(1%) rotate(1deg) skewY(2deg);
            }
        }

        .floating-light{
            position:absolute;
            border-radius:50%;
            background:radial-gradient(circle, rgba(96,165,250,0.35) 0%, transparent 70%);
            filter:blur(20px);
            animation: floatLight 12s ease-in-out infinite;
        }

        .floating-light.light-1{
            width:220px;
            height:220px;
            top:8%;
            left:6%;
            animation-delay:0s;
        }

        .floating-light.light-2{
            width:180px;
            height:180px;
            top:60%;
            right:8%;
            animation-delay:3s;
        }

        .floating-light.light-3{
            width:240px;
            height:240px;
            bottom:8%;
            left:35%;
            animation-delay:6s;
        }

        @keyframes floatLight{
            0%,100%{ transform:translateY(0) translateX(0); }
            50%{ transform:translateY(-18px) translateX(12px); }
        }

        .noise{
            position:fixed;
            inset:0;
            z-index:-2;
            pointer-events:none;
            opacity:.05;
            background-image:
                linear-gradient(rgba(255,255,255,.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.4) 1px, transparent 1px);
            background-size:4px 4px;
            mix-blend-mode:soft-light;
        }

        .developer-page{
            padding: 70px 20px 90px;
        }

        .developer-container{
            max-width: 1240px;
            margin: 0 auto;
        }

        .hero{
            position:relative;
            padding:42px;
            border-radius:32px;
            background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
            border:1px solid var(--border);
            box-shadow: var(--shadow);
            overflow:hidden;
            margin-bottom:34px;
        }

        .hero::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                linear-gradient(120deg, transparent 20%, rgba(255,255,255,0.08) 50%, transparent 80%);
            transform:translateX(-120%);
            animation: shine 6s linear infinite;
            pointer-events:none;
        }

        @keyframes shine{
            100%{ transform:translateX(120%); }
        }

        .hero-grid{
            display:grid;
            grid-template-columns: 1.2fr .8fr;
            gap:28px;
            align-items:center;
            position:relative;
            z-index:1;
        }

        .eyebrow{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding:10px 16px;
            border-radius:999px;
            background:rgba(255,255,255,0.06);
            border:1px solid var(--border);
            color:#c7d2fe;
            font-size:.92rem;
            font-weight:600;
            margin-bottom:18px;
        }

        .hero h1{
            font-size:clamp(2.2rem, 5vw, 4.6rem);
            line-height:1.02;
            margin:0 0 18px;
            font-weight:900;
            letter-spacing:-1.8px;
        }

        .hero h1 span{
            display:block;
            color:var(--accent);
            text-shadow: 0 0 24px rgba(96,165,250,.22);
        }

        .hero p{
            color:var(--muted);
            font-size:1.06rem;
            line-height:1.9;
            max-width:760px;
            margin:0 0 26px;
        }

        .hero-actions{
            display:flex;
            gap:14px;
            flex-wrap:wrap;
        }

        .btn-dev{
            display:inline-flex;
            align-items:center;
            gap:10px;
            text-decoration:none;
            color:var(--text);
            padding:14px 20px;
            border-radius:16px;
            font-weight:700;
            transition:.25s ease;
            border:1px solid var(--border);
        }

        .btn-dev.primary{
            background:linear-gradient(135deg, var(--accent-2), var(--accent));
            color:#fff;
            box-shadow:0 16px 32px rgba(37,99,235,.28);
            border:none;
        }

        .btn-dev.secondary{
            background:rgba(255,255,255,0.06);
        }

        .btn-dev:hover{
            transform:translateY(-2px);
            color:#fff;
        }

        .profile-card{
            background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.05));
            border:1px solid var(--border);
            border-radius:28px;
            padding:28px;
            box-shadow:0 22px 50px rgba(0,0,0,.32);
            position:relative;
        }

        .profile-top{
            display:flex;
            align-items:center;
            gap:18px;
            margin-bottom:22px;
        }

        .avatar{
            width:88px;
            height:88px;
            border-radius:24px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg, rgba(37,99,235,.95), rgba(96,165,250,.95));
            box-shadow: 0 16px 36px rgba(37,99,235,.35);
            font-size:2rem;
            color:white;
        }

        .profile-top h3{
            margin:0 0 4px;
            font-size:1.35rem;
            font-weight:800;
        }

        .profile-top p{
            margin:0;
            color:var(--muted);
            font-size:.95rem;
        }

        .profile-meta{
            display:grid;
            gap:12px;
        }

        .meta-item{
            display:flex;
            align-items:center;
            gap:12px;
            color:#d1d5db;
            padding:12px 14px;
            border-radius:16px;
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
        }

        .meta-item i{
            width:34px;
            height:34px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:12px;
            background:rgba(96,165,250,.14);
            color:#93c5fd;
        }

        .grid{
            display:grid;
            grid-template-columns: repeat(3, 1fr);
            gap:24px;
            margin-bottom:28px;
        }

        .card-dev{
            background:rgba(255,255,255,0.06);
            border:1px solid var(--border);
            border-radius:24px;
            padding:28px;
            box-shadow:0 16px 40px rgba(0,0,0,.24);
            transition:.25s ease;
        }

        .card-dev:hover{
            transform:translateY(-6px);
            box-shadow:0 24px 50px rgba(0,0,0,.30);
            border-color:rgba(96,165,250,.28);
        }

        .card-icon{
            width:60px;
            height:60px;
            border-radius:18px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg, rgba(96,165,250,.22), rgba(37,99,235,.18));
            color:#93c5fd;
            font-size:1.35rem;
            margin-bottom:18px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .card-dev h3{
            margin:0 0 10px;
            font-size:1.18rem;
            font-weight:800;
        }

        .card-dev p{
            margin:0;
            color:var(--muted);
            line-height:1.85;
            font-size:.98rem;
        }

        .wide-section{
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:24px;
            margin-bottom:28px;
        }

        .panel{
            background:rgba(255,255,255,0.06);
            border:1px solid var(--border);
            border-radius:28px;
            padding:32px;
            box-shadow:0 18px 44px rgba(0,0,0,.24);
        }

        .panel h2{
            margin:0 0 18px;
            font-size:1.5rem;
            font-weight:900;
        }

        .panel p{
            color:var(--muted);
            line-height:1.95;
            margin:0 0 16px;
        }

        .skills{
            display:flex;
            flex-wrap:wrap;
            gap:12px;
        }

        .skill{
            padding:12px 16px;
            border-radius:999px;
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.10);
            color:#dbeafe;
            font-weight:600;
            font-size:.95rem;
        }

        .timeline{
            display:grid;
            gap:16px;
        }

        .timeline-item{
            display:flex;
            gap:14px;
            align-items:flex-start;
            padding:16px;
            border-radius:18px;
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
        }

        .timeline-dot{
            width:16px;
            height:16px;
            border-radius:50%;
            margin-top:6px;
            background:linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow:0 0 18px var(--glow);
            flex-shrink:0;
        }

        .timeline-item h4{
            margin:0 0 6px;
            font-size:1.05rem;
            font-weight:800;
        }

        .timeline-item p{
            margin:0;
            color:var(--muted);
            line-height:1.8;
        }

        .contact-panel{
            text-align:center;
            background:
                linear-gradient(180deg, rgba(96,165,250,0.09), rgba(255,255,255,0.04));
        }

        .contact-panel p{
            max-width:760px;
            margin:0 auto 22px;
        }

        .socials{
            display:flex;
            justify-content:center;
            gap:16px;
            flex-wrap:wrap;
            margin-top:16px;
        }

        .social{
            width:62px;
            height:62px;
            display:flex;
            align-items:center;
            justify-content:center;
            text-decoration:none;
            border-radius:50%;
            color:white;
            font-size:1.35rem;
            transition:.25s ease;
            box-shadow:0 16px 34px rgba(0,0,0,.26);
        }

        .social:hover{
            transform:translateY(-4px) scale(1.06);
            color:white;
        }

        .social.whatsapp{ background:#25D366; }
        .social.instagram{ background:linear-gradient(45deg,#f58529,#dd2a7b,#8134af,#515bd4); }
        .social.facebook{ background:#1877f2; }
        .social.telegram{ background:#229ED9; }

        .footer-mini{
            text-align:center;
            padding:8px 0 0;
            color:var(--muted);
            font-size:.95rem;
        }

        @media (max-width: 1100px){
            .hero-grid{
                grid-template-columns:1fr;
            }

            .grid{
                grid-template-columns:1fr 1fr;
            }

            .wide-section{
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 700px){
            .developer-page{
                padding:40px 14px 60px;
            }

            .hero{
                padding:24px;
                border-radius:24px;
            }

            .profile-card{
                padding:22px;
            }

            .grid{
                grid-template-columns:1fr;
            }

            .panel,
            .card-dev{
                padding:22px;
                border-radius:22px;
            }

            .hero h1{
                letter-spacing:-1px;
            }

            .hero-actions{
                flex-direction:column;
            }

            .btn-dev{
                justify-content:center;
                width:100%;
            }

            .profile-top{
                align-items:flex-start;
                flex-direction:column;
            }

            .avatar{
                width:76px;
                height:76px;
                font-size:1.7rem;
            }
        }
    </style>
</head>
<body>

<div class="developer-bg">
    <div class="flag-wave wave-1"></div>
    <div class="flag-wave wave-2"></div>
    <div class="flag-wave wave-3"></div>

    <div class="floating-light light-1"></div>
    <div class="floating-light light-2"></div>
    <div class="floating-light light-3"></div>
</div>
<div class="noise"></div>

<section class="developer-page">
    <div class="developer-container">

        <div class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">
                        <i class="fas fa-terminal"></i>
                        Developer Showcase
                    </div>

                    <h1>
                        Building Digital Systems
                    </h1>

                    <p>
                        This page presents the developer behind the platform — focused on creating modern,
                        reliable, and visually refined systems. From front-end experience to admin workflows,
                        every detail is designed to feel clean, professional, and efficient.
                    </p>

                    <div class="hero-actions">
                        <a href="#contact" class="btn-dev primary">
                            <i class="fas fa-paper-plane"></i>
                            Contact Developer
                        </a>
                        <a href="#work" class="btn-dev secondary">
                            <i class="fas fa-layer-group"></i>
                            View Expertise
                        </a>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="profile-top">
                        <div class="avatar">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div>
                            <h3>Ali Al Hilfi</h3>
                       <p>Full-Stack Developer • Platform Architect</p>
                        </div>
                    </div>

                    <div class="profile-meta">
    <div class="meta-item">
        <i class="fas fa-code"></i>
        <span>Custom Applications & Business Platforms</span>
    </div>

    <div class="meta-item">
        <i class="fas fa-database"></i>
        <span>Scalable Database Architecture & Data Management</span>
    </div>

    <div class="meta-item">
        <i class="fas fa-layer-group"></i>
        <span>Enterprise Admin Dashboards & Operational Systems</span>
    </div>

    <div class="meta-item">
        <i class="fas fa-shield-halved"></i>
        <span>Secure Authentication & Role-Based Access Control</span>
    </div>
</div>
                </div>
            </div>
        </div>

        <div class="grid" id="work">
            <div class="card-dev">
                <div class="card-icon"><i class="fas fa-object-group"></i></div>
                <h3>Interface Design</h3>
                <p>
                    Creating dark, elegant, and responsive interfaces that feel modern, immersive, and easy to use across desktop and mobile devices.
                </p>
            </div>

            <div class="card-dev">
                <div class="card-icon"><i class="fas fa-gears"></i></div>
                <h3>System Logic</h3>
                <p>
                    Developing structured business logic for forms, admin tools, permissions, product management, and workflow automation.
                </p>
            </div>

            <div class="card-dev">
                <div class="card-icon"><i class="fas fa-chart-column"></i></div>
                <h3>Analytics & Reports</h3>
                <p>
                    Building practical reporting tools, visual dashboards, time-based filters, and operational insights for daily business use.
                </p>
            </div>
        </div>

        <div class="wide-section">
            <div class="panel">
                <h2>Core Skills</h2>
                <p>
                    A strong focus on practical full-stack development with an emphasis on clarity, maintainability, and polished user experience.
                </p>

                <div class="skills">
    <div class="skill">Enterprise Web Applications</div>
    <div class="skill">Full-Stack Platform Development</div>
    <div class="skill">Custom Business Systems</div>
    <div class="skill">Admin Panel Architecture</div>
    <div class="skill">Secure Authentication Flows</div>
    <div class="skill">Role & Permission Management</div>
    <div class="skill">Data-Driven System Design</div>
    <div class="skill">Order & Operations Management</div>
    <div class="skill">Analytics & Reporting Tools</div>
    <div class="skill">Responsive Experience Engineering</div>
    <div class="skill">Modern Interface Systems</div>
    <div class="skill">Scalable Product Development</div>
</div>
            </div>

            <div class="panel">
                <h2>Development Approach</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div>
                            <h4>Understand the Workflow</h4>
                            <p>Each page is built around how people actually use the system in real daily operations.</p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div>
                            <h4>Design for Clarity</h4>
                            <p>Layouts, interactions, and visual hierarchy are carefully organized for speed and simplicity.</p>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div>
                            <h4>Build for Growth</h4>
                            <p>The goal is to create a foundation that can expand with more tools, reports, and features over time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel contact-panel" id="contact">
            <h2>Let’s Build Something Strong</h2>
            <p>
                Looking for a custom platform, administrative system, reporting tools, or a complete business workflow solution? Get in touch with us using the contact methods below.
            </p>

            <div class="socials">
                <a href="https://wa.me/9647806614644" target="_blank" class="social whatsapp" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.instagram.com/a6h9" target="_blank" class="social instagram" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/Allawy.6" target="_blank" class="social facebook" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://t.me/a6h99" target="_blank" class="social telegram" aria-label="Telegram">
                    <i class="fab fa-telegram-plane"></i>
                </a>
            </div>
        </div>

        <div class="footer-mini">
            Crafted with structure, style, and focus.
        </div>

    </div>
</section>
