# Блог на PHP

## Установка

1. Установите MySQL и VS Code с расширением "PHP Server"

2. Клонируйте репозиторий:
```bash
git clone https://github.com/Cheis099/blog.git
cd blog
```

3. Создайте базу данных MySQL и импортируйте файл `database.sql`:

**Через консоль:**
```bash
mysql -u root -p -e "CREATE DATABASE blog"
mysql -u root -p blog < database.sql
```

**Или через phpMyAdmin:**
- Откройте phpMyAdmin в браузере
- Создайте новую базу данных (например, `blog`)
- Выберите её и перейдите на вкладку «Импорт»
- Загрузите файл `database.sql`

4. Настройте `.env` в папке `config`:
```
DB_HOST=localhost
DB_NAME=blog
DB_USER=root
DB_PASS=ваш_пароль
```

5. Откройте `index.php`, кликните правой кнопкой → `PHP Server: Serve project`

6. Для входа в админ-панель:
   - Логин: `admin`
   - Пароль: `admin123`