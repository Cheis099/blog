# Блог на PHP

## Установка

1. Клонируйте репозиторий:
```bash
git clone <URL>
cd pr
```

2. Создайте БД MySQL и импортируйте `database.sql`:
```bash
mysql -u root -p -e "CREATE DATABASE blog"
mysql -u root -p blog < database.sql
```

3. Настройте `.env` по примеру из `config/db.php`:
```
DB_HOST=localhost
DB_NAME=blog
DB_USER=root
DB_PASS=ваш_пароль
```

4. Убедитесь, что папка `uploads/` доступна для записи
