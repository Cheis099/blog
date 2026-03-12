<?php
require_once 'config/db.php';
$error = '';
$success = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Введите логин и пароль.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :login OR email = :email");
        $stmt->execute(['login' => $login, 'email' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Неверный логин или пароль.";
        }
    }
}

$pageTitle = 'Вход';
require_once 'templates/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Вход</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">Регистрация успешна! Войдите.</div>
        <?php endif; ?>
        
        <form method="post" action="login.php">
            <div class="form-group">
                <label for="login">Логин или Email</label>
                <input type="text" class="form-control" id="login" name="login" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full-width">Войти</button>
        </form>
        <div class="auth-footer">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>
</div>
<?php require_once 'templates/footer.php'; ?>