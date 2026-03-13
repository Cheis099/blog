# Блог на PHP

## Установка

### Требования

- PHP 7.4+
- MySQL/MariaDB
- Веб-сервер (OpenServer, XAMPP или другой)

Если у вас ничего не установлено — скачайте [OpenServer](https://ospanel.io/) (Windows), там уже есть всё необходимое.

### Шаги установки

1. Клонируйте репозиторий:
```bash
git clone https://github.com/Cheis099/blog.git
cd pr
```

2. Создайте БД MySQL и импортируйте `database.sql`:

**Вариант 1 — через консоль MySQL:**
```bash
mysql -u root -p -e "CREATE DATABASE blog"
mysql -u root -p blog < database.sql
```

**Вариант 2 — через phpMyAdmin:**
- Откройте phpMyAdmin в браузере
- Создайте новую базу данных (например, `blog`)
- Выберите её и перейдите на вкладку «Импорт»
- Загрузите файл `database.sql`

3. Настройте `.env` по примеру из `config/db.php`:
```
DB_HOST=localhost
DB_NAME=blog
DB_USER=root
DB_PASS=ваш_пароль
```

4. Создайте папку `uploads/` в корне проекта и убедитесь, что она доступна для записи

### Запуск проекта

1. Запустите проект на любом PHP-сервере:
   - **VS Code**: расширение "PHP Server" (кликни правой кнопкой на `index.php` → `PHP Server: Serve project`)
   - **OpenServer/XAMPP**: поместите проект в папку сервера
   - **Встроенный PHP**: `php -S localhost:8000` в терминале
   - **Replit/онлайн-песочницы**: загрузите файлы туда

2. Откройте сайт в браузере

3. Вход в админ-панель (для проверки):
   - Логин: `admin`
   - Пароль: `admin123`