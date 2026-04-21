# 📷 照片展示墙

一个简洁美观的照片展示网站，支持瀑布流展示、鼠标特效、管理面板。
![示例图片1](https://github.com/PurpleSU-zyun/-picturebuild/blob/main/picture/1%20(1).png)
![示例图片2](https://github.com/PurpleSU-zyun/-picturebuild/blob/main/picture/2.png)
##  功能

-  **瀑布流布局** — 自适应列数，照片错落有致
-  **图片 URL 添加** — 管理面板填入图片链接即可
-  **照片管理** — 添加、删除照片
-  **12 种背景预设** — 纯色 + 渐变色，一键切换
-  **自定义背景图片** — 输入任意图片 URL 作为背景
-  **鼠标特效** — 光标跟随、尾迹粒子、点击涟漪
-  **管理面板** — 修改标题、副标题、背景、管理密码
-  **适配手机、平板、桌面

## 部署步骤

### 1. 创建数据库（宝塔面板）

1. 宝塔面板 → 数据库 → 添加数据库（utf8mb4 编码）
2. 导入 `database.sql`

### 2. 配置数据库连接

编辑 `config.php`，修改以下信息：

```php
define('DB_HOST', 'localhost');       // 数据库地址
define('DB_NAME', 'gallery');          // 数据库名
define('DB_USER', 'root');            // 数据库用户名
define('DB_PASS', 'your_password');   // 数据库密码
define('ADMIN_PASSWORD', 'admin123'); // 管理面板密码（建议修改）
```

### 3. 绑定网站目录

宝塔面板 → 网站 → 添加站点 → 网站目录指向 `gallery` 文件夹

### 4. 访问

- **主页** → `http://你的域名/index.php`
- **管理面板** → `http://你的域名/admin.php`

> 默认管理密码：`admin123`

##  数据库表结构

### photos（照片表）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT | 主键，自增 |
| url | VARCHAR(500) | 图片地址 |

### settings（设置表）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT | 主键，自增 |
| key | VARCHAR(100) | 配置项名称 |
| value | TEXT | 配置值 |

初始配置项：

| key | 默认值 | 说明 |
|-----|--------|------|
| site_title | 我的照片墙 | 主标题 |
| site_subtitle | 记录美好瞬间 | 副标题 |
| site_background | #0a0a1a | 背景颜色 |

## 常见问题

**Q: 管理密码忘记了？**  
A: 编辑 `config.php`，修改 `ADMIN_PASSWORD` 的值，然后重启页面即可。

**Q: 如何获取图片 URL？**  
A: 可以使用图床服务（如 imgurl.org、sm.ms）或将自己的图片上传到服务器。
