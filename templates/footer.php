        </div>
    </main>
    <footer>
        <div class="container">
            <p>&copy; 2026 Blog. Все права защищены.</p>
            <div class="footer-links">
                <a href="/">Главная</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/logout.php">Выйти</a>
                <?php else: ?>
                    <a href="/login.php">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    <script src="/assets/js/script.js"></script>
</body>
</html>