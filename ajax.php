<?php
ob_start();
require_once 'config/db.php';
$response = ['success' => false, 'message' => 'Произошла неизвестная ошибка.'];

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Некорректный запрос.');
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        throw new Exception('Необходимо авторизоваться.');
    }

    $formData = $_POST;
    $action = $formData['action'] ?? 'comment';
    $postId = isset($formData['post_id']) ? (int)$formData['post_id'] : 0;

    if (!$postId) {
        throw new Exception('Некорректный ID поста.');
    }

    if ($action === 'comment') {
        $content = isset($formData['content']) ? trim($formData['content']) : '';

        if (empty($content)) {
            throw new Exception('Комментарий не может быть пустым.');
        }

        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $_SESSION['user_id'],
            'content' => $content
        ]);
        $lastId = $pdo->lastInsertId();
        $newCommentStmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id");
        $newCommentStmt->execute(['id' => $lastId]);
        $newComment = $newCommentStmt->fetch();
        $response = [
            'success' => true,
            'comment' => [
                'username' => htmlspecialchars($_SESSION['username']),
                'content' => htmlspecialchars($newComment['content']),
                'created_at' => date('d.m.Y H:i', strtotime($newComment['created_at']))
            ]
        ];
    } elseif ($action === 'like') {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $stmt->execute(['user_id' => $_SESSION['user_id'], 'post_id' => $postId]);

        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->execute(['user_id' => $_SESSION['user_id'], 'post_id' => $postId]);
            $liked = false;
        } else {
            $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");
            $stmt->execute(['user_id' => $_SESSION['user_id'], 'post_id' => $postId]);
            $liked = true;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
        $stmt->execute(['post_id' => $postId]);
        $count = $stmt->fetchColumn();
        $pdo->commit();
        $response = ['success' => true, 'liked' => $liked, 'count' => $count];
    } else {
        throw new Exception('Неизвестное действие.');
    }

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['success' => false, 'message' => 'Ошибка базы данных.'];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

ob_end_clean();
echo json_encode($response);
exit;
?>