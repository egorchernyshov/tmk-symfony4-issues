# Тестовое задание ТМК

Описание задач в файле [issues.md](issues.md)

Запуск:

```bash
# see `make help`
make start
```

- Останавливает и удаляет контейнеры сервисов, если они были запущены
- Запускает сборку докер-образов
- Запуск сервисов docker-compose
- Установка зависимостей Composer
- Создание таблиц и применение миграций
- Загрузка фикстур в базу данных
- Запуск тестов 

Остановка:

```bash
# Stop services
make stop
```