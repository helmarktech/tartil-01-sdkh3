{{-- ═══ Splash Screen: TartilPro → SD Khadijah 3 ═══
     Tampil sekali per sesi browser (sessionStorage).
     Layar 1: TartilPro (motion 1 detik) → transisi smooth →
     Layar 2: logo SD Khadijah 3 → fade out ke konten aplikasi. --}}
<div id="tartil-splash" class="tartil-splash" role="presentation" aria-hidden="true">
    <div class="splash-stage">
        <div class="splash-screen" data-screen="1">
            <img src="/icons/icon-192.png" alt="TartilPro" class="splash-logo">
            <h1 class="splash-title">TartilPro</h1>
            <p class="splash-subtitle">Sistem Penilaian Tartil</p>
        </div>
        <div class="splash-screen" data-screen="2">
            <img src="/images/logo-sd-khadijah-3.jpg" alt="SD Khadijah 3" class="splash-logo splash-logo-school">
            <h1 class="splash-title">SD Khadijah 3</h1>
            <p class="splash-subtitle">Sistem Penilaian Tartil</p>
        </div>
    </div>
    <div class="splash-footer">PT. Helmark Tech Digital</div>
</div>
<style>
    .tartil-splash {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        transition: opacity 0.5s ease;
    }
    .tartil-splash.splash-exit { opacity: 0; pointer-events: none; }
    .splash-stage { position: relative; flex: 1; }
    .splash-screen {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.45s ease, visibility 0.45s;
    }
    .splash-screen.active {
        opacity: 1;
        visibility: visible;
        animation: splash-in 1s cubic-bezier(0.22, 1, 0.36, 1) both;
    }
    .splash-screen.leave { opacity: 0; visibility: hidden; transform: translateY(-16px) scale(0.97); transition: opacity 0.45s ease, transform 0.45s ease, visibility 0.45s; }
    @keyframes splash-in {
        0% { opacity: 0; transform: translateY(24px) scale(0.92); }
        60% { opacity: 1; }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .splash-logo {
        width: 110px;
        height: 110px;
        border-radius: 28px;
        box-shadow: 0 12px 32px rgba(12, 138, 95, 0.18);
        object-fit: cover;
    }
    .splash-logo-school { background: #fff; }
    .splash-title {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: #1c1917;
        letter-spacing: -0.5px;
    }
    .splash-subtitle {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
        font-size: 12px;
        font-weight: 500;
        color: #78716c;
        margin-top: -6px;
    }
    .splash-footer {
        padding: 20px 0 28px;
        text-align: center;
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.4px;
        color: #a8a29e;
    }
    @media (prefers-reduced-motion: reduce) {
        .splash-screen.active { animation: none; }
        .tartil-splash, .splash-screen { transition-duration: 0.1s; }
    }
</style>
<script>
    (function () {
        var splash = document.getElementById('tartil-splash');
        if (!splash) return;

        // Tampil sekali per sesi browser
        try {
            if (sessionStorage.getItem('tartil_splash_shown')) {
                splash.remove();
                return;
            }
            sessionStorage.setItem('tartil_splash_shown', '1');
        } catch (e) { /* storage diblokir: splash tetap tampil */ }

        var layar1 = splash.querySelector('[data-screen="1"]');
        var layar2 = splash.querySelector('[data-screen="2"]');

        // Layar 1: TartilPro (motion 1 detik)
        requestAnimationFrame(function () { layar1.classList.add('active'); });

        // Transisi smooth ke layar 2: SD Khadijah 3
        setTimeout(function () {
            layar1.classList.remove('active');
            layar1.classList.add('leave');
            layar2.classList.add('active');
        }, 1700);

        // Fade out ke konten aplikasi
        setTimeout(function () {
            splash.classList.add('splash-exit');
            setTimeout(function () { splash.remove(); }, 600);
        }, 3400);
    })();
</script>
