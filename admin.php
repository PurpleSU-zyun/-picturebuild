<?php
/**
 * 管理面板
 */
require_once __DIR__ . '/config.php';
session_start();

$msg = '';

// ===== 登录验证 =====
if (!isset($_SESSION['admin_logged'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        if (hash_equals(ADMIN_PASSWORD, $_POST['password'] ?? '')) {
            $_SESSION['admin_logged'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $msg = '<div class="alert error">密码错误，请重试</div>';
        }
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理登录</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: #08080f;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Noto Sans SC', 'Inter', sans-serif;
        }
        .login-box {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 48px 40px; width: 380px;
            text-align: center; backdrop-filter: blur(20px);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; } }
        .login-box h1 { font-size: 1.4rem; color: #e0e0f0; margin-bottom: 8px; font-weight: 700; }
        .login-box p { color: rgba(255,255,255,0.35); font-size: 0.85rem; margin-bottom: 32px; }
        .input-wrap { position: relative; margin-bottom: 24px; }
        .input-wrap input {
            width: 100%; padding: 14px 18px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; color: #e0e0f0;
            font-size: 1rem; transition: border-color 0.3s, box-shadow 0.3s;
            text-align: center; letter-spacing: 4px;
        }
        .input-wrap input:focus {
            outline: none; border-color: rgba(139,92,246,0.6);
            box-shadow: 0 0 0 3px rgba(139,92,246,0.15);
        }
        .input-wrap input::placeholder { letter-spacing: 1px; color: rgba(255,255,255,0.2); }
        .btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            border: none; border-radius: 12px;
            color: #fff; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: all 0.3s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(124,58,237,0.4); }
        .alert { padding: 10px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.85rem; animation: fadeIn 0.3s; }
        .alert.error { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .back-link { display: inline-block; margin-top: 20px; color: rgba(255,255,255,0.3); text-decoration: none; font-size: 0.8rem; transition: color 0.3s; }
        .back-link:hover { color: #a78bfa; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🔐 管理登录</h1>
        <p>请输入管理员密码</p>
        <?= $msg ?>
        <form method="POST">
            <div class="input-wrap">
                <input type="password" name="password" placeholder="输入密码" required autofocus>
            </div>
            <button type="submit" name="login" class="btn">登 录</button>
        </form>
        <a href="index.php" class="back-link">← 返回主页</a>
    </div>
</body>
</html>
<?php
    exit;
}

// ===== 已登录：处理操作 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'logout') {
        session_destroy();
        header('Location: admin.php');
        exit;
    }

    if ($action === 'update_settings') {
        $title = trim($_POST['site_title'] ?? '');
        $subtitle = trim($_POST['site_subtitle'] ?? '');
        $bg = trim($_POST['site_background'] ?? '');

        setSetting('site_title', $title);
        setSetting('site_subtitle', $subtitle);
        setSetting('site_background', $bg);

        $msg = '<div class="alert success">✅ 设置已保存</div>';
    }

    if ($action === 'add_photo') {
        $url = trim($_POST['photo_url'] ?? '');
        if ($url !== '') {
            if (addPhoto($url)) {
                $msg = '<div class="alert success">✅ 照片添加成功</div>';
            } else {
                $msg = '<div class="alert error">❌ 添加失败，请检查数据库连接</div>';
            }
        } else {
            $msg = '<div class="alert error">⚠️ 请输入有效的图片 URL</div>';
        }
    }

    if ($action === 'delete_photo') {
        $id = intval($_POST['photo_id'] ?? 0);
        if ($id > 0) {
            deletePhoto($id);
            $msg = '<div class="alert success">🗑️ 照片已删除</div>';
        }
    }

    if ($action === 'change_password') {
        $newPass = trim($_POST['new_password'] ?? '');
        if ($newPass !== '') {
            $configPath = __DIR__ . '/config.php';
            $content = file_get_contents($configPath);
            $content = preg_replace(
                "/define\s*\(\s*['\"]ADMIN_PASSWORD['\"]\s*,\s*['\"][^'\"]*['\"]\s*\)/",
                "define('ADMIN_PASSWORD', '" . addslashes($newPass) . "')",
                $content
            );
            file_put_contents($configPath, $content);
            session_destroy();
            header('Location: admin.php');
            exit;
        }
    }
}

$settings = getSettings();
$photos = getPhotos();

// 当前背景值（用于回显和选中预设判断）
$currentBg = $settings['site_background'] ?? '#0d0d1a';

// 背景预设列表
$bgPresets = [
    ['紫罗兰', '#0d0d1a'],
    ['深海蓝', '#0a1628'],
    ['极光绿', '#0a1a14'],
    ['极光', 'linear-gradient(135deg, #0f0c29, #302b63, #24243e)'],
    ['极光蓝紫', 'linear-gradient(135deg, #667eea, #764ba2)'],
    ['日落余晖', 'linear-gradient(135deg, #ff9a9e, #fecfef, #fecfef)'],
    ['森林晨雾', 'linear-gradient(135deg, #134e5e, #71b280)'],
    ['星际紫', 'linear-gradient(135deg, #1a1a2e, #16213e, #0f3460)'],
    ['极光绿光', 'linear-gradient(135deg, #0f2027, #203a43, #2c5364)'],
    ['粉紫星空', 'linear-gradient(135deg, #2b1055, #7597de, #d66d75)'],
    ['暖阳橙', 'linear-gradient(135deg, #f83600, #f9d423)'],
    ['冰川蓝', 'linear-gradient(135deg, #2193b0, #6dd5ed)'],
];

// 判断是否是图片URL背景：以 http/https 开头且不是渐变
function isImageUrl(string $val): bool {
    return (strpos($val, 'http') === 0 || strpos($val, '//') === 0)
        && !preg_match('/^(linear|radial)-gradient/i', $val);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理面板</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #08080f; color: #e0e0f0;
            font-family: 'Noto Sans SC', 'Inter', sans-serif;
            min-height: 100vh; padding: 40px 20px 80px;
        }
        .container { max-width: 960px; margin: 0 auto; }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 44px; flex-wrap: wrap; gap: 16px;
        }
        .topbar h1 { font-size: 1.6rem; font-weight: 700; color: #fff; }
        .topbar .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .topbar .actions a, .topbar .actions button {
            padding: 8px 18px; border-radius: 10px; font-size: 0.85rem;
            font-weight: 500; text-decoration: none; transition: all 0.3s; cursor: pointer; font-family: inherit;
        }
        .topbar .actions .btn-home { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); }
        .topbar .actions .btn-home:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .topbar .actions .btn-logout { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }
        .topbar .actions .btn-logout:hover { background: rgba(239,68,68,0.2); }

        .alert { padding: 12px 18px; border-radius: 12px; margin-bottom: 24px; font-size: 0.88rem; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; } }
        .alert.success { background: rgba(16,185,129,0.1); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .alert.error { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }

        .card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px; padding: 32px; margin-bottom: 24px;
            backdrop-filter: blur(16px); animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; } }
        .card-title {
            font-size: 1rem; font-weight: 600; color: #fff;
            margin-bottom: 24px; padding-bottom: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex; align-items: center; gap: 8px;
        }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field label { font-size: 0.78rem; color: rgba(255,255,255,0.4); font-weight: 500; }
        .field input[type="text"] {
            padding: 11px 16px; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; color: #e0e0f0; font-size: 0.95rem;
            font-family: inherit; transition: border-color 0.3s, box-shadow 0.3s;
        }
        .field input[type="text"]:focus {
            outline: none; border-color: rgba(139,92,246,0.5);
            box-shadow: 0 0 0 3px rgba(139,92,246,0.1);
        }

        .btn-primary {
            padding: 12px 28px; background: linear-gradient(135deg, #7c3aed, #5b21b6);
            border: none; border-radius: 12px; color: #fff;
            font-size: 0.95rem; font-weight: 600; cursor: pointer;
            transition: all 0.3s; font-family: inherit;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(124,58,237,0.35); }

        /* 背景预设 */
        .bg-presets { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-bottom: 20px; }
        .bg-preset {
            border-radius: 12px; overflow: hidden; cursor: pointer;
            position: relative; aspect-ratio: 16/9;
            border: 2px solid transparent; transition: all 0.3s;
        }
        .bg-preset:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        .bg-preset.active { border-color: #a78bfa; box-shadow: 0 0 0 3px rgba(167,139,250,0.25); }
        .bg-preset .swatch { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .bg-preset .name {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 5px 8px; background: rgba(0,0,0,0.6);
            font-size: 0.68rem; color: rgba(255,255,255,0.85); text-align: center;
        }
        .bg-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0 16px; color: rgba(255,255,255,0.25); font-size: 0.8rem; }
        .bg-divider::before, .bg-divider::after { content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.08); }

        /* 图片预览 */
        .bg-img-preview {
            border-radius: 12px; overflow: hidden;
            height: 100px; margin-bottom: 12px;
            border: 1px solid rgba(255,255,255,0.08);
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center;
            position: relative; transition: all 0.3s;
        }
        .bg-img-preview .hint { font-size: 0.75rem; color: rgba(255,255,255,0.4); background: rgba(0,0,0,0.5); padding: 4px 10px; border-radius: 6px; }
        .bg-img-preview .clear-btn {
            position: absolute; top: 6px; right: 6px;
            background: rgba(239,68,68,0.85); border: none;
            color: #fff; width: 24px; height: 24px; border-radius: 6px;
            cursor: pointer; font-size: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.3s;
        }
        .bg-img-preview .clear-btn:hover { background: #ef4444; }

        /* 照片网格 */
        .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
        .photo-item {
            position: relative; border-radius: 12px; overflow: hidden;
            aspect-ratio: 4/3; transition: transform 0.3s, box-shadow 0.3s;
        }
        .photo-item:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .photo-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .photo-item .del {
            position: absolute; top: 6px; right: 6px;
            width: 28px; height: 28px; background: rgba(239,68,68,0.85);
            border: none; border-radius: 8px; color: #fff;
            cursor: pointer; font-size: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s;
        }
        .photo-item:hover .del { opacity: 1; }
        .photo-item .del:hover { background: #ef4444; }
        .empty-photos { text-align: center; padding: 48px; color: rgba(255,255,255,0.25); }
        .empty-photos .icon { font-size: 2.5rem; margin-bottom: 10px; }

        /* 密码修改 */
        .pwd-section { margin-top: 24px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.06); }
        .pwd-section h3 { font-size: 0.9rem; color: rgba(255,255,255,0.5); margin-bottom: 14px; }
        .pwd-row { display: flex; gap: 12px; align-items: flex-end; }
        .pwd-row .field { flex: 1; }
        .btn-danger {
            padding: 11px 20px; background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2); border-radius: 10px;
            color: #f87171; font-size: 0.88rem; cursor: pointer;
            font-family: inherit; transition: all 0.3s; white-space: nowrap;
        }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
    </style>
</head>
<body>
<div class="container">

    <div class="topbar">
        <h1>⚙️ 管理面板</h1>
        <div class="actions">
            <a href="index.php" class="btn-home">← 主页</a>
            <form method="POST" style="display:inline">
                <button type="submit" name="action" value="logout" class="btn-logout">退出登录</button>
            </form>
        </div>
    </div>

    <?= $msg ?>

    <!-- 网站设置 -->
    <div class="card">
        <div class="card-title">🌐 网站设置</div>
        <form method="POST" id="settingsForm">
            <input type="hidden" name="action" value="update_settings">

            <div class="form-row">
                <div class="field">
                    <label>主标题</label>
                    <input type="text" name="site_title" value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" placeholder="我的照片墙">
                </div>
                <div class="field">
                    <label>副标题</label>
                    <input type="text" name="site_subtitle" value="<?= htmlspecialchars($settings['site_subtitle'] ?? '') ?>" placeholder="记录美好瞬间">
                </div>
            </div>

            <label style="font-size:0.78rem;color:rgba(255,255,255,0.4);font-weight:500;display:block;margin-bottom:10px;">背景颜色 / 渐变预设</label>
            <div class="bg-presets" id="bgPresets">
                <?php foreach ($bgPresets as $i => $preset):
                    // 判断当前选中
                    $isActive = ($currentBg === $preset[1]);
                ?>
                <div class="bg-preset<?= $isActive ? ' active' : '' ?>"
                     data-value="<?= htmlspecialchars($preset[1], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="swatch" style="background:<?= htmlspecialchars($preset[1], ENT_QUOTES, 'UTF-8') ?>"></div>
                    <div class="name"><?= htmlspecialchars($preset[0]) ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-divider">或使用自定义背景图片 URL</div>

            <!-- 图片背景预览（仅在当前值是图片URL时显示） -->
            <?php if (isImageUrl($currentBg)): ?>
            <div class="bg-img-preview" id="bgPreview" style="display:flex;background-image:url('<?= htmlspecialchars($currentBg, ENT_QUOTES, 'UTF-8') ?>')">
                <span class="hint">图片背景已启用</span>
                <button type="button" class="clear-btn" onclick="clearBgImage()">✕</button>
            </div>
            <?php else: ?>
            <div class="bg-img-preview" id="bgPreview" style="display:none">
                <span class="hint">图片预览</span>
                <button type="button" class="clear-btn" onclick="clearBgImage()">✕</button>
            </div>
            <?php endif; ?>

            <div class="field" style="margin-bottom:20px">
                <label>背景图片 URL（填入后将覆盖上方预设）</label>
                <input type="text" id="bgImageUrl" name="site_background"
                       value="<?= htmlspecialchars($currentBg, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="https://example.com/bg.jpg">
            </div>

            <button type="submit" class="btn-primary">💾 保存设置</button>
        </form>

        <div class="pwd-section">
            <h3>🔑 修改管理密码</h3>
            <form method="POST" onsubmit="return confirm('确定要修改密码吗？修改后需重新登录。')">
                <input type="hidden" name="action" value="change_password">
                <div class="pwd-row">
                    <div class="field">
                        <label>新密码</label>
                        <input type="text" name="new_password" placeholder="输入新密码" required>
                    </div>
                    <button type="submit" class="btn-danger">确认修改</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 添加照片 -->
    <div class="card">
        <div class="card-title">📷 添加照片</div>
        <form method="POST">
            <input type="hidden" name="action" value="add_photo">
            <div class="field" style="margin-bottom:16px">
                <label>图片 URL</label>
                <input type="text" name="photo_url" placeholder="https://example.com/image.jpg" required>
            </div>
            <button type="submit" class="btn-primary">➕ 添加照片</button>
        </form>
    </div>

    <!-- 照片管理 -->
    <div class="card">
        <div class="card-title">🗂️ 照片管理 <small style="font-weight:400;color:rgba(255,255,255,0.3);font-size:0.8rem">(悬停显示删除)</small></div>
        <?php if (empty($photos)): ?>
        <div class="empty-photos"><div class="icon">📂</div><p>还没有照片</p></div>
        <?php else: ?>
        <div class="photo-grid">
            <?php foreach ($photos as $photo): ?>
            <div class="photo-item">
                <img src="<?= htmlspecialchars($photo['url'], ENT_QUOTES, 'UTF-8') ?>" alt="photo" loading="lazy">
                <form method="POST" id="del-form-<?= (int)$photo['id'] ?>">
                    <input type="hidden" name="action" value="delete_photo">
                    <input type="hidden" name="photo_id" value="<?= (int)$photo['id'] ?>">
                </form>
                <button type="button" class="del" onclick="if(confirm('确定删除这张照片?')) document.getElementById('del-form-<?= (int)$photo['id'] ?>').submit()">✕</button>
            </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:14px;font-size:0.78rem;color:rgba(255,255,255,0.25);">共 <?= count($photos) ?> 张照片</p>
        <?php endif; ?>
    </div>

</div>

<script>
    // 背景预设选择
    function selectBg(el) {
        document.querySelectorAll('.bg-preset').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        var val = el.dataset.value;
        document.getElementById('bgImageUrl').value = val;
        updatePreview(val);
    }

    // 更新图片预览
    function updatePreview(value) {
        var preview = document.getElementById('bgPreview');
        var isImg = (value.indexOf('http') === 0 || value.indexOf('//') === 0)
                    && !/^(linear|radial)-gradient/i.test(value);
        if (isImg && value.trim() !== '') {
            preview.style.display = 'flex';
            preview.style.backgroundImage = "url('" + value.replace(/'/g, "\\'") + "')";
        } else {
            preview.style.display = 'none';
        }
    }

    // 清空图片背景
    function clearBgImage() {
        document.getElementById('bgImageUrl').value = '';
        document.getElementById('bgPreview').style.display = 'none';
        document.querySelectorAll('.bg-preset').forEach(p => p.classList.remove('active'));
    }

    // 监听输入
    document.getElementById('bgImageUrl').addEventListener('input', function() {
        document.querySelectorAll('.bg-preset').forEach(p => p.classList.remove('active'));
        updatePreview(this.value);
    });

    // 预设点击
    document.querySelectorAll('.bg-preset').forEach(function(el) {
        el.addEventListener('click', function() { selectBg(this); });
    });

    // 初始化
    updatePreview(document.getElementById('bgImageUrl').value);
</script>

</body>
</html>
