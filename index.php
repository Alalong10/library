/* ==========================
   LANDING CONTAINER
========================== */
.landing-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow-x: hidden;
}

.landing-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="90" cy="30" r="0.5" fill="rgba(255,255,255,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

/* ==========================
   LANDING HEADER
========================== */
.landing-header {
    padding: 30px 20px;
    position: relative;
    z-index: 10;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
}

.logo-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 25px;
    flex-wrap: wrap;
}

.logo-image {
    width: 120px;
    height: 120px;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.brand-text {
    text-align: center;
}

.brand-title {
    font-size: 3.2rem;
    font-weight: 800;
    margin-bottom: 12px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    letter-spacing: -0.02em;
    line-height: 1.1;
}

.brand-main {
    display: block;
    font-size: 2.8rem;
    font-weight: 900;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 3px;
    background: linear-gradient(45deg, #fff, #f0f0f0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.brand-sub {
    display: block;
    font-size: 1.6rem;
    font-weight: 600;
    color: #e8e8e8;
    margin-top: 8px;
    letter-spacing: 1px;
}

.brand-tagline {
    font-size: 1.2rem;
    font-weight: 400;
    opacity: 0.9;
    margin-top: 15px;
    line-height: 1.4;
}

.tagline-text {
    color: #f8f8f8;
}

.tagline-accent {
    color: #ffd700;
    font-weight: 600;
}

/* ==========================
   LANDING MAIN
========================== */
.landing-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 60px 20px;
    position: relative;
    z-index: 10;
}

/* ==========================
   HERO SECTION
========================== */
.hero-section {
    text-align: center;
    max-width: 900px;
    margin-bottom: 80px;
}

.hero-content {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 50px 40px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 25px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    line-height: 1.2;
}

.hero-description {
    font-size: 1.3rem;
    color: #f8f8f8;
    line-height: 1.7;
    margin: 0;
    font-weight: 400;
    opacity: 0.95;
}

/* ==========================
   LOGIN SECTION
========================== */
.login-section {
    width: 100%;
    max-width: 1000px;
}

.login-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 40px;
    width: 100%;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 40px 35px;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.login-card:hover::before {
    transform: scaleX(1);
}

.login-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
}

.card-header {
    margin-bottom: 25px;
}

.card-icon {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2.2rem;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    position: relative;
}

.admin-card .card-icon {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
}

.student-card .card-icon {
    background: linear-gradient(135deg, #059669, #047857);
}

.card-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: #1e293b;
    letter-spacing: -0.01em;
}

.card-description {
    color: #64748b;
    margin-bottom: 30px;
    font-size: 1.05rem;
    line-height: 1.6;
    font-weight: 400;
}

.login-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 32px;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-color-dark));
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(30, 64, 175, 0.3);
    border: none;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.login-btn:hover::before {
    left: 100%;
}

.login-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(30, 64, 175, 0.4);
}

.admin-btn {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
}

.student-btn {
    background: linear-gradient(135deg, #059669, #047857);
}

.login-btn span {
    position: relative;
    z-index: 1;
}

.login-btn i {
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
}

.login-btn:hover i {
    transform: translateX(3px);
}

/* ==========================
   LANDING FOOTER
========================== */
.landing-footer {
    position: relative;
    z-index: 10;
    padding: 30px 20px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: auto;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.footer-text {
    margin-bottom: 20px;
}

.copyright {
    font-size: 0.95rem;
    color: #e8e8e8;
    margin-bottom: 8px;
    font-weight: 500;
}

.footer-tagline {
    font-size: 0.85rem;
    color: #d1d5db;
    font-weight: 400;
    opacity: 0.8;
}

.footer-decoration {
    display: flex;
    justify-content: center;
}

.decoration-line {
    width: 60px;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    border-radius: 1px;
}

/* ==========================
   RESPONSIVE DESIGN
========================== */
@media (max-width: 768px) {
    .landing-header {
        padding: 20px 15px;
    }

    .brand-title {
        font-size: 2.8rem;
    }

    .brand-main {
        font-size: 2.4rem;
    }

    .brand-sub {
        font-size: 1.4rem;
    }

    .brand-tagline {
        font-size: 1.1rem;
    }

    .logo-image {
        width: 80px;
        height: 80px;
    }

    .landing-main {
        padding: 40px 15px;
    }

    .hero-section {
        margin-bottom: 60px;
    }

    .hero-content {
        padding: 40px 30px;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-description {
        font-size: 1.2rem;
    }

    .login-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .login-card {
        padding: 35px 25px;
    }

    .card-icon {
        width: 75px;
        height: 75px;
        font-size: 1.8rem;
    }

    .card-title {
        font-size: 1.6rem;
    }

    .card-description {
        font-size: 1rem;
    }

    .login-btn {
        padding: 14px 28px;
        font-size: 0.95rem;
    }

    .landing-footer {
        padding: 25px 15px;
    }
}

@media (max-width: 480px) {
    .landing-header {
        padding: 15px 10px;
    }

    .logo-section {
        gap: 15px;
    }

    .logo-image {
        width: 45px;
        height: 45px;
    }

    .brand-title {
        font-size: 2.2rem;
    }

    .brand-main {
        font-size: 2rem;
    }

    .brand-sub {
        font-size: 1.2rem;
    }

    .brand-tagline {
        font-size: 1rem;
    }

    .landing-main {
        padding: 30px 10px;
    }

    .hero-section {
        margin-bottom: 50px;
    }

    .hero-content {
        padding: 30px 20px;
    }

    .hero-title {
        font-size: 2rem;
    }

    .hero-description {
        font-size: 1.1rem;
    }

    .login-card {
        padding: 30px 20px;
    }

    .card-icon {
        width: 65px;
        height: 65px;
        font-size: 1.5rem;
    }

    .card-title {
        font-size: 1.4rem;
    }

    .card-description {
        font-size: 0.95rem;
    }

    .login-btn {
        padding: 12px 24px;
        font-size: 0.9rem;
    }

    .landing-footer {
        padding: 20px 10px;
    }

    .copyright {
        font-size: 0.9rem;
    }

    .footer-tagline {
        font-size: 0.8rem;
    }
}
