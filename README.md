Простое API для управления пользователями и переводами средств.

## Как запустить проект (через Docker)

1. **Подготовь окружение**:
   ```powershell
   copy .env.example .env
   ```

2. **Запусти контейнеры**:
   ```bash
   ./vendor/bin/sail up -d
   ```

3. **Установи всё необходимое**:
   ```bash
   ./vendor/bin/sail composer install
   ./vendor/bin/sail php artisan key:generate
   ./vendor/bin/sail php artisan migrate --seed
   ```
   *Команда `--seed` создаст 10 тестовых пользователей с ID от 1 до 10.*

---

## Как запустить тесты
Проверь, что всё работает правильно (14 тестов):
```bash
./vendor/bin/sail php artisan test
```

---

## Основные эндпоинты (v1)

Все финансовые операции требуют **Bearer Token** в заголовке `Authorization`.

### 1. Вход и Регистрация
*   `POST` `/api/v1/register` — Регистрация (имя, email, возраст, пароль).
*   `POST` `/api/v1/login` — Вход (возвращает токен).
*   `POST` `/api/v1/logout` — Выход из системы (аннулирует токен).

### 2. Пользователь
*   `PATCH` `/api/v1/users/{id}` — Обновить своё имя, почту или возраст.

### 3. Деньги
*   `POST` `/api/v1/users/{id}/deposit` — Пополнить свой счет.
*   `POST` `/api/v1/transfers` — Перевести деньги другому (нужен `receiver_id` и `amount`).