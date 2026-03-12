<?php
require_once 'config/db.php';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$stmt = $pdo->prepare("
    SELECT posts.*, users.username FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC 
    LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
$totalStmt = $pdo->query("SELECT COUNT(*) FROM posts");
$totalPosts = $totalStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);
$pageTitle = 'Главная';
require_once 'templates/header.php';
?>

<div class="posts-list">
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p>Постов пока нет</p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <h3>
                    <a href="post.php?id=<?= $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h3>
                <div class="post-meta">
                    <span>Автор: <?= htmlspecialchars($post['username']) ?></span>
                    <span><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <?php if (!empty($post['image_path'])): ?>
                    <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" class="post-image">
                <?php endif; ?>
                <p class="post-excerpt">
                    <?= htmlspecialchars(substr($post['content'], 0, 200)) ?>...
                </p>
                <a href="post.php?id=<?= $post['id'] ?>" class="btn-read-more">
                    Читать далее →
                </a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">← Назад</a>
        <?php endif; ?>
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        for ($i = $startPage; $i <= $endPage; $i++):
        ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Вперёд →</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php require_once 'templates/footer.php'; ?>