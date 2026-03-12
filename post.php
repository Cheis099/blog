<?php
require_once 'config/db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = :id");
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    $pageTitle = 'Пост не найден';
    require_once 'templates/header.php';
    ?>
    <div class="empty-state">
        <h2>Пост не найден</h2>
        <p><a href="index.php">← Вернуться на главную</a></p>
    </div>
    <?php
    require_once 'templates/footer.php';
    exit;
}

$commentsStmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = :id ORDER BY created_at DESC");
$commentsStmt->execute(['id' => $id]);
$comments = $commentsStmt->fetchAll();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :id");
$stmt->execute(['id' => $id]);
$likesCount = $stmt->fetchColumn();
$userLiked = false;
$isAuthorized = isset($_SESSION['user_id']);

if ($isAuthorized) {
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id = :user_id AND post_id = :id");
    $stmt->execute(['user_id' => $_SESSION['user_id'], 'id' => $id]);
    $userLiked = (bool)$stmt->fetch();
}

$pageTitle = htmlspecialchars($post['title']);
require_once 'templates/header.php';
?>

<article class="post-full">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <div class="post-meta">
        <span>Автор: <?= htmlspecialchars($post['username']) ?></span>
        <span><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
    </div>
    <?php if (!empty($post['image_path'])): ?>
        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" class="post-image">
    <?php endif; ?>
    <div class="post-content">
        <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>
    <div class="like-section">
        <button
            id="like-btn"
            class="like-btn <?= $userLiked ? 'liked' : '' ?>"
            data-post-id="<?= $id ?>"
        >
            <span id="like-icon"><?= $userLiked ? '❤️' : '🤍' ?></span>
            <span id="likes-count"><?= $likesCount ?></span>
        </button>
    </div>
</article>

<div class="comments-section">
    <h3>Комментарии</h3>
    <div class="comments-list">
        <?php if (empty($comments)): ?>
            <p class="no-comments-text">Комментариев пока нет. Будьте первым!</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-author">
                        <?= htmlspecialchars($comment['username']) ?>
                        <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <div class="comment-content">
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php if ($isAuthorized): ?>
        <div class="comment-form">
            <h4>Оставить комментарий</h4>
            <form id="comment-form">
                <input type="hidden" name="post_id" value="<?= $id ?>">
                <div class="form-group">
                    <textarea name="content" class="form-control" required placeholder="Ваш комментарий..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
        </div>
    <?php else: ?>
        <p class="authorized-only-text">
            Чтобы оставить комментарий, <a href="login.php">войдите</a>.
        </p>
    <?php endif; ?>
</div>

<?php require_once 'templates/footer.php'; ?>