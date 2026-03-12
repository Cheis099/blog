<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $message = 'Комментарий удалён.';
}

$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$filterPost = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$postsStmt = $pdo->query("SELECT id, title FROM posts ORDER BY created_at DESC");
$allPosts = $postsStmt->fetchAll();
$where = [];
$params = [];

if ($filterPost > 0) {
    $where[] = "comments.post_id = :post_id";
    $params['post_id'] = $filterPost;
}

if ($filterDateFrom) {
    $where[] = "comments.created_at >= :date_from";
    $params['date_from'] = $filterDateFrom . ' 00:00:00';
}

if ($filterDateTo) {
    $where[] = "comments.created_at <= :date_to";
    $params['date_to'] = $filterDateTo . ' 23:59:59';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM comments $whereClause");
$countStmt->execute($params);
$totalComments = $countStmt->fetchColumn();
$totalPages = ceil($totalComments / $limit);
$stmt = $pdo->prepare("SELECT comments.*, users.username, posts.title AS post_title, posts.id AS post_id
                       FROM comments
                       JOIN users ON comments.user_id = users.id
                       JOIN posts ON comments.post_id = posts.id
                       $whereClause
                       ORDER BY comments.created_at DESC
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}

$stmt->execute();
$comments = $stmt->fetchAll();
$pageTitle = 'Управление комментариями';
require_once '../templates/header.php';
?>

<div class="mb-4">
    <h2>Управление комментариями</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="admin-filters">
        <form method="GET" action="comments.php" class="filters-form">
            <div class="filter-group">
                <label for="post_id">Пост:</label>
                <select name="post_id" id="post_id" class="form-control">
                    <option value="0">Все посты</option>
                    <?php foreach ($allPosts as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $filterPost == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="date_from">С даты:</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= htmlspecialchars($filterDateFrom) ?>">
            </div>
            <div class="filter-group">
                <label for="date_to">По дату:</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= htmlspecialchars($filterDateTo) ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Применить</button>
                <a href="comments.php" class="btn btn-secondary">Сбросить</a>
            </div>
        </form>
    </div>

    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <p>Комментариев не найдено.</p>
        </div>
    <?php else: ?>
        <div class="comments-admin-list mt-4">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-admin-card">
                    <div class="comment-admin-header">
                        <div class="comment-admin-meta">
                            <span class="comment-admin-author"><?= htmlspecialchars($comment['username']) ?></span>
                            <span class="comment-admin-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <a href="../post.php?id=<?= $comment['post_id'] ?>" class="comment-admin-post-link" target="_blank">
                            <?= htmlspecialchars($comment['post_title']) ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </a>
                    </div>
                    <div class="comment-admin-content">
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                    </div>
                    <div class="comment-admin-actions">
                        <form method="post" action="comments.php" onsubmit="return confirm('Удалить комментарий?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
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
        <p class="table-info-text">
            Показано <?= count($comments) ?> из <?= $totalComments ?> комментариев
        </p>
    <?php endif; ?>
</div>

<?php require_once '../templates/footer.php'; ?>