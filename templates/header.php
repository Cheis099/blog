<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Blog';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="/" class="logo">
                <div class="logo-icon">B</div>
                <span class="logo-text">Blog<span>.</span></span>
            </a>
            <nav>
                <ul>
                    <li><a href="/" class="<?= $_SERVER['PHP_SELF'] === '/' || $_SERVER['PHP_SELF'] === '/index.php' ? 'active' : '' ?>">Главная</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/logout.php">Выйти</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li><a href="/admin/index.php" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>">Админка</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="/login.php" class="<?= strpos($_SERVER['PHP_SELF'], '/login.php') !== false ? 'active' : '' ?>">Войти</a></li>
                        <li><a href="/register.php" class="<?= strpos($_SERVER['PHP_SELF'], '/register.php') !== false ? 'active' : '' ?>">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
                <div class="burger">
                    <div class="line1"></div>
                    <div class="line2"></div>
                    <div class="line3"></div>
                </div>
            </nav>
        </div>
    </header>
    <main>
        <div class="container">