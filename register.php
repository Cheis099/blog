<?php
require_once 'config/db.php';
$errors = [];
$username = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = "Все поля обязательны.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Пароли не совпадают.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Пароль должен быть не менее 6 символов.";
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
        $stmt->execute(['email' => $email, 'username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Пользователь с таким email или именем уже существует.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashed_password])) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $errors[] = "Ошибка регистрации.";
            }
        }
    }
}
$pageTitle = 'Регистрация';
require_once 'templates/header.php';
?>
<div class="auth-container">
    <div class="auth-card">
        <h2>Регистрация</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="register.php">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($username) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Подтверждение пароля</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full-width">Зарегистрироваться</button>
        </form>
        <div class="auth-footer">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </div>
    </div>
</div>
<?php require_once 'templates/footer.php'; ?>