<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pageTitle = 'Админ-панель';
require_once '../templates/header.php';
?>

<div class="mb-4">
    <h2>Админ-панель</h2>
    <div class="posts-list mt-4">
        <div class="post-card">
            <h3><a href="posts.php">Управление постами</a></h3>
            <p class="post-excerpt">Добавление, редактирование и удаление постов</p>
        </div>
        <div class="post-card">
            <h3><a href="comments.php">Управление комментариями</a></h3>
            <p class="post-excerpt">Просмотр и удаление комментариев</p>
        </div>
    </div>
</div>

<?php require_once '../templates/footer.php'; ?>