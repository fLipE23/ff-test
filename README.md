### Запуск приложения

При старте создается очередь operations и запускаются 10 воркеров для обработки событий.
Команда `php artisan rmq:publish` создаст 5 событий (для предсказуемости порядка выполнения).

```bash
# Создание .env файла
cp .env.example .env

# Запуск приложения:
docker-compose up

# Создание событий в очереди operations 
php artisan rmq:publish

```

### Другие полезные команды

```bash
docker-compose exec app tail -f /var/log/queue.log

docker-compose exec app cat /var/log/queue.log

docker-compose exec app cat /var/www/html/storage/logs/laravel.log
```

### Перечень используемых инструментов

Framework: Laravel 11

Database: Postgres 16 (latest)

Queue: RabbitMQ 3.13.1

