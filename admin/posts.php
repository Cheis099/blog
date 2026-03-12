<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT image_path FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        if ($post && !empty($post['image_path']) && file_exists('../' . $post['image_path'])) {
            unlink('../' . $post['image_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $message = 'Пост удалён.';
    }

    if ($action === 'add' || $action === 'edit') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $imagePath = '';
        if (empty($title) || empty($content)) {
            $error = 'Заголовок и текст обязательны.';
        } else {
            if (!empty($_FILES['image']['name'])) {
                $targetDir = "../uploads/";
                $fileName = basename($_FILES["image"]["name"]);
                $targetFilePath = $targetDir . $fileName;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
                $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
                if (in_array($fileType, $allowTypes)) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                        $imagePath = "uploads/" . $fileName;
                    } else {
                        $error = 'Ошибка загрузки файла.';
                    }
                } else {
                    $error = 'Разрешены только файлы: JPG, JPEG, PNG, GIF.';
                }
            }

            if (empty($error)) {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, image_path) VALUES (:user_id, :title, :content, :image_path)");
                    $stmt->execute([
                        'user_id' => $_SESSION['user_id'],
                        'title' => $title,
                        'content' => $content,
                        'image_path' => $imagePath
                    ]);
                    $message = 'Пост добавлен.';
                } elseif ($action === 'edit') {
                    $id = (int)$_POST['id'];
                    if (empty($imagePath)) {
                        $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content WHERE id = :id");
                        $stmt->execute(['title' => $title, 'content' => $content, 'id' => $id]);
                    } else {
                        $stmt = $pdo->prepare("SELECT image_path FROM posts WHERE id = :id");
                        $stmt->execute(['id' => $id]);
                        $oldPost = $stmt->fetch();
                        if ($oldPost && !empty($oldPost['image_path']) && file_exists('../' . $oldPost['image_path'])) {
                            unlink('../' . $oldPost['image_path']);
                        }
                        $stmt = $pdo->prepare("UPDATE posts SET title = :title, content = :content, image_path = :image_path WHERE id = :id");
                        $stmt->execute(['title' => $title, 'content' => $content, 'image_path' => $imagePath, 'id' => $id]);
                    }
                    $message = 'Пост обновлён.';
                }
            }
        }
    }
}

$limit = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = [];
$params = [];

if ($filterDateFrom) {
    $where[] = "posts.created_at >= :date_from";
    $params['date_from'] = $filterDateFrom . ' 00:00:00';
}

if ($filterDateTo) {
    $where[] = "posts.created_at <= :date_to";
    $params['date_to'] = $filterDateTo . ' 23:59:59';
}

if ($searchQuery) {
    $where[] = "(posts.title LIKE :search OR posts.content LIKE :search)";
    $params['search'] = '%' . $searchQuery . '%';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts $whereClause");
$countStmt->execute($params);
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);
$stmt = $pdo->prepare("SELECT posts.*, users.username 
                       FROM posts 
                       JOIN users ON posts.user_id = users.id 
                       $whereClause
                       ORDER BY posts.created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}

$stmt->execute();
$posts = $stmt->fetchAll();
$editPost = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $editPost = $stmt->fetch();
}

$pageTitle = 'Управление постами';
require_once '../templates/header.php';
?>

<div class="mb-4">
    <h2>Управление постами</h2>
    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="admin-filters">
        <form method="GET" action="posts.php" class="filters-form">
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="По заголовку или тексту" value="<?= htmlspecialchars($searchQuery) ?>">
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
                <a href="posts.php" class="btn btn-secondary">Сбросить</a>
            </div>
        </form>
    </div>

    <div class="auth-card auth-card-full mt-4">
        <h3><?= $editPost ? 'Редактировать пост' : 'Добавить пост' ?></h3>
        <form method="post" enctype="multipart/form-data" action="posts.php">
            <input type="hidden" name="action" value="<?= $editPost ? 'edit' : 'add' ?>">
            <?php if ($editPost): ?>
                <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Заголовок</label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editPost['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Текст</label>
                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($editPost['content'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Картинка</label>
                <?php if ($editPost && !empty($editPost['image_path'])): ?>
                    <img src="../<?= htmlspecialchars($editPost['image_path']) ?>" class="post-image-preview"><br>
                <?php endif; ?>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary"><?= $editPost ? 'Сохранить' : 'Добавить' ?></button>
            <?php if ($editPost): ?>
                <a href="posts.php" class="btn btn-secondary">Отмена</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container mt-4">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Автор</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Постов не найдено.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td data-label="ID"><?= $post['id'] ?></td>
                            <td data-label="Заголовок"><?= htmlspecialchars($post['title']) ?></td>
                            <td data-label="Автор"><?= htmlspecialchars($post['username']) ?></td>
                            <td data-label="Дата"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                            <td data-label="Действия">
                                <div class="actions">
                                    <a href="../post.php?id=<?= $post['id'] ?>" class="btn btn-secondary btn-sm" target="_blank" title="Просмотреть">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                            <polyline points="15 3 21 3 21 9"/>
                                            <line x1="10" y1="14" x2="21" y2="3"/>
                                        </svg>
                                    </a>
                                    <a href="posts.php?edit=<?= $post['id'] ?>" class="btn btn-secondary btn-sm" title="Редактировать">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <form method="post" action="posts.php" class="form-inline" onsubmit="return confirm('Удалить пост?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
        Показано <?= count($posts) ?> из <?= $totalPosts ?> постов
    </p>
</div>
<?php require_once '../templates/footer.php'; ?>