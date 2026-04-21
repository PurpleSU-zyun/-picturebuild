<?php
/**
 * 照片展示墙主页
 */
require_once __DIR__ . '/config.php';

$settings = getSettings();
$photos = getPhotos();

$siteTitle   = $settings['site_title'] ?? '我的照片墙';
$siteSubtitle = $settings['site_subtitle'] ?? '记录美好瞬间';
$siteBg      = $settings['site_background'] ?? '#0a0a1a';

// 判断背景类型：以 http 开头且不是渐变 = 图片 URL
$isImageBg = (strpos($siteBg, 'http') === 0 || strpos($siteBg, '//') === 0)
             && !preg_match('/^(linear|radial)-gradient/i', $siteBg);
$cssBg = $isImageBg ? 'url("' . str_replace('"', '%22', $siteBg) . '")' : $siteBg;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            font-family: 'Noto Sans SC', 'Inter', -apple-system, sans-serif;
            background: <?php echo $cssBg; ?>;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #f0f0f5;
            overflow-x: hidden;
            cursor: none;
        }

        /* 渐变遮罩（图片背景时自动减弱，让图片清晰可见） */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(10, 10, 25, 0.55) 0%,
                rgba(20, 20, 45, 0.40) 50%,
                rgba(10, 10, 25, 0.55) 100%
            );
            z-index: 0;
            pointer-events: none;
        }

        /* ===== 浮动粒子 ===== */
        .bg-particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: floatUp linear infinite;
        }
        @keyframes floatUp {
            0%   { transform: translateY(0) rotate(0deg); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* ===== 光标 ===== */
        #cursor {
            position: fixed;
            width: 36px;
            height: 36px;
            border: 2px solid rgba(139, 92, 246, 0.8);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: width 0.25s, height 0.25s, border-color 0.3s, background 0.3s;
            mix-blend-mode: screen;
        }
        #cursor-dot {
            position: fixed;
            width: 6px;
            height: 6px;
            background: #a78bfa;
            border-radius: 50%;
            pointer-events: none;
            z-index: 10000;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 10px #a78bfa;
        }
        body:hover #cursor {
            width: 56px;
            height: 56px;
            border-color: rgba(167, 139, 250, 0.5);
            background: rgba(139, 92, 246, 0.08);
        }

        /* ===== 尾迹 ===== */
        .trail {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9998;
            opacity: 0;
            animation: trailFade 1s ease-out forwards;
        }
        @keyframes trailFade {
            0%   { opacity: 0.9; transform: translate(-50%,-50%) scale(1); }
            100% { opacity: 0; transform: translate(-50%,-50%) scale(0); }
        }

        /* ===== 全局包裹 ===== */
        .wrapper { position: relative; z-index: 1; }

        /* ===== Hero 区域 ===== */
        .hero {
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 80px 24px 40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: rgba(139, 92, 246, 0.12);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 50px;
            font-size: 0.78rem;
            color: #a78bfa;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 32px;
            animation: fadeInDown 0.8s ease;
        }
        .hero-badge .dot {
            width: 6px; height: 6px;
            background: #a78bfa;
            border-radius: 50%;
            animation: pulse 2s ease infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.7); }
        }

        .hero h1 {
            font-size: clamp(2.5rem, 8vw, 5.5rem);
            font-weight: 800;
            letter-spacing: -2px;
            line-height: 1.1;
            margin-bottom: 20px;
            background: linear-gradient(
                135deg,
                #e0c3fc 0%,
                #8ec5fc 25%,
                #a78bfa 50%,
                #c4b5fd 75%,
                #f0abfc 100%
            );
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInDown 0.8s ease 0.15s both;
        }

        .hero p {
            font-size: clamp(1rem, 2.5vw, 1.25rem);
            color: rgba(255,255,255,0.5);
            font-weight: 300;
            letter-spacing: 1px;
            max-width: 480px;
            animation: fadeInDown 0.8s ease 0.3s both;
        }

        .hero-divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #a78bfa, transparent);
            margin: 28px auto 0;
            animation: fadeInDown 0.8s ease 0.45s both;
        }

        /* ===== 照片网格 ===== */
        .gallery-wrap { max-width: 1300px; margin: 0 auto; padding: 0 24px 100px; }

        .gallery-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            animation: fadeInUp 0.6s ease 0.5s both;
        }
        .gallery-header .line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(167,139,250,0.4), transparent);
        }
        .gallery-header span {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.3);
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .gallery {
            columns: 4 240px;
            column-gap: 16px;
        }

        .photo-card {
            break-inside: avoid;
            margin-bottom: 16px;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            cursor: none;
            animation: fadeInUp 0.6s ease both;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease;
        }
        .photo-card:hover {
            transform: translateY(-8px) scale(1.015);
            box-shadow: 0 24px 60px rgba(139, 92, 246, 0.25), 0 8px 24px rgba(0,0,0,0.4);
            z-index: 2;
        }
        .photo-card img {
            width: 100%;
            display: block;
            transition: transform 0.6s ease, filter 0.4s ease;
        }
        .photo-card:hover img {
            transform: scale(1.06);
            filter: brightness(1.08) saturate(1.1);
        }
        .photo-card .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(0,0,0,0.75) 0%,
                rgba(0,0,0,0.1) 40%,
                transparent 70%
            );
            opacity: 0;
            transition: opacity 0.4s ease;
            display: flex;
            align-items: flex-end;
            padding: 16px;
        }
        .photo-card:hover .overlay { opacity: 1; }
        .photo-card .overlay-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.85rem;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* ===== 空状态 ===== */
        .empty {
            text-align: center;
            padding: 120px 24px;
            animation: fadeInUp 0.6s ease;
        }
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.4;
        }
        .empty p { color: rgba(255,255,255,0.4); font-size: 1rem; }
        .empty a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid rgba(167,139,250,0.4);
            padding-bottom: 2px;
            transition: border-color 0.3s;
        }
        .empty a:hover { border-color: #a78bfa; }

        /* ===== 涟漪 ===== */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(167, 139, 250, 0.35);
            transform: scale(0);
            animation: rippleAnim 0.7s ease-out forwards;
            pointer-events: none;
        }
        @keyframes rippleAnim { to { transform: scale(5); opacity: 0; } }

        /* ===== 全屏闪 ===== */
        .flash {
            position: fixed; inset: 0;
            background: rgba(255,255,255,0.04);
            pointer-events: none; z-index: 9997;
            animation: flashAnim 0.4s ease-out forwards;
        }
        @keyframes flashAnim { 0% { opacity: 1; } 100% { opacity: 0; } }

        /* ===== 页脚 ===== */
        .footer {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 40px 24px;
            border-top: 1px solid rgba(255,255,255,0.06);
            animation: fadeInUp 0.6s ease 0.8s both;
        }
        .footer p {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.28);
            letter-spacing: 1px;
        }
        .footer a {
            color: rgba(167,139,250,0.7);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            border-bottom: 1px solid rgba(167,139,250,0.3);
            padding-bottom: 1px;
        }
        .footer a:hover { color: #a78bfa; border-color: #a78bfa; }

        /* ===== 管理入口 ===== */
        .admin-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.4);
            font-size: 1rem;
            text-decoration: none;
            backdrop-filter: blur(12px);
            transition: all 0.3s;
            cursor: none;
        }
        .admin-btn:hover {
            background: rgba(139,92,246,0.2);
            border-color: rgba(139,92,246,0.4);
            color: #a78bfa;
            transform: rotate(30deg);
        }

        /* ===== 动画 ===== */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ===== 响应式 ===== */
        @media (max-width: 768px) {
            .gallery { columns: 2 160px; column-gap: 12px; }
            .hero { padding: 60px 20px 30px; }
        }
        @media (max-width: 480px) {
            .gallery { columns: 2 120px; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div id="cursor"></div>
    <div id="cursor-dot"></div>

    <a href="admin.php" class="admin-btn" title="管理面板">⚙</a>

    <!-- 浮动粒子 -->
    <div id="particles"></div>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-badge">
            <span class="dot"></span>
            PHOTO WALL
            <span class="dot"></span>
        </div>
        <h1><?= htmlspecialchars($siteTitle) ?></h1>
        <p><?= htmlspecialchars($siteSubtitle) ?></p>
        <div class="hero-divider"></div>
    </section>

    <!-- 照片墙 -->
    <div class="gallery-wrap">
        <div class="gallery-header">
            <div class="line"></div>
            <span><?= count($photos) ?> Photos</span>
        </div>

        <?php if (empty($photos)): ?>
        <div class="empty">
            <div class="empty-icon">🌌</div>
            <p>还没有照片，<a href="admin.php">去添加</a> 一些吧</p>
        </div>
        <?php else: ?>
        <div class="gallery">
            <?php foreach ($photos as $idx => $photo): ?>
            <div class="photo-card" data-url="<?= htmlspecialchars($photo['url'], ENT_QUOTES, 'UTF-8') ?>" style="animation-delay: <?= min($idx * 0.07, 0.5) ?>s">
                <img src="<?= htmlspecialchars($photo['url'], ENT_QUOTES, 'UTF-8') ?>" alt="photo" loading="lazy"
                     onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%231a1a2e%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%236b6b8a%22 font-size=%2220%22%3E图片加载失败%3C/text%3E%3C/svg%3E'">
                <div class="overlay">
                    <div class="overlay-icon">🔍</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- 页脚 -->
    <footer class="footer">
        <p>由 <a href="http://zyun.ink" target="_blank">ZYUN</a> 提供支持</p>
    </footer>
</div>

<script>
    // === 浮动粒子 ===
    (function(){
        const colors = ['rgba(139,92,246,0.6)','rgba(167,139,250,0.5)','rgba(236,72,153,0.4)','rgba(59,130,246,0.4)','rgba(52,211,153,0.4)'];
        const container = document.getElementById('particles');
        for (let i = 0; i < 18; i++) {
            const p = document.createElement('div');
            p.className = 'bg-particle';
            const size = Math.random() * 4 + 2;
            const color = colors[Math.floor(Math.random() * colors.length)];
            p.style.cssText = `
                width:${size}px; height:${size}px;
                background:${color};
                left:${Math.random()*100}%;
                bottom:-20px;
                animation-duration:${Math.random()*15+10}s;
                animation-delay:${Math.random()*10}s;
                box-shadow:0 0 ${size*2}px ${color};
            `;
            container.appendChild(p);
        }
    })();

    // === 光标 ===
    const cursor = document.getElementById('cursor');
    const dot = document.getElementById('cursor-dot');
    let mx=0, my=0, cx=0, cy=0;
    document.addEventListener('mousemove', e => { mx=e.clientX; my=e.clientY; dot.style.left=mx+'px'; dot.style.top=my+'px'; });
    (function animate(){ cx+=(mx-cx)*0.15; cy+=(my-cy)*0.15; cursor.style.left=cx+'px'; cursor.style.top=cy+'px'; requestAnimationFrame(animate); })();

    // === 尾迹 ===
    const trailColors = ['#8b5cf6','#a78bfa','#ec4899','#3b82f6','#34d399','#f59e0b'];
    let lastTrail = 0;
    document.addEventListener('mousemove', e => {
        const now = Date.now();
        if (now - lastTrail < 35) return;
        lastTrail = now;
        const t = document.createElement('div');
        t.className = 'trail';
        const color = trailColors[Math.floor(Math.random()*trailColors.length)];
        const sz = Math.random()*5+3;
        t.style.cssText = `left:${e.clientX}px;top:${e.clientY}px;width:${sz}px;height:${sz}px;background:${color};box-shadow:0 0 6px ${color};`;
        document.body.appendChild(t);
        setTimeout(()=>t.remove(), 1000);
    });

    // === 点击涟漪 + 全屏闪 ===
    document.addEventListener('click', e => {
        const card = e.target.closest('.photo-card');
        if (card) {
            const ripple = document.createElement('div');
            ripple.className = 'ripple';
            const r = card.getBoundingClientRect();
            ripple.style.cssText = `left:${e.clientX-r.left}px;top:${e.clientY-r.top}px;width:50px;height:50px;margin:-25px 0 0 -25px;`;
            card.appendChild(ripple);
            setTimeout(()=>ripple.remove(), 700);
        }
        const flash = document.createElement('div');
        flash.className = 'flash';
        document.body.appendChild(flash);
        setTimeout(()=>flash.remove(), 400);
    });

    // === 预览 ===
    document.addEventListener('click', e => {
        const card = e.target.closest('.photo-card');
        if (!card) return;
        const url = card.dataset.url;
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(5,5,15,0.95);display:flex;align-items:center;justify-content:center;cursor:none;animation:fadeIO 0.3s ease;backdrop-filter:blur(12px);';
        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = 'max-width:92vw;max-height:92vh;border-radius:16px;box-shadow:0 30px 80px rgba(0,0,0,0.7);object-fit:contain;animation:scaleIO 0.4s cubic-bezier(0.34,1.56,0.64,1);';
        overlay.appendChild(img);
        document.body.appendChild(overlay);
        overlay.addEventListener('click', () => {
            overlay.style.opacity='0';
            overlay.style.transition='opacity 0.25s';
            setTimeout(()=>overlay.remove(), 250);
        });
    });
</script>
</body>
</html>
