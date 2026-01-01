# Руководство по безопасности

Создавайте безопасные приложения с FF Framework.

## Хеширование паролей

Всегда хешируйте пароли:

```php
use FF\Security\Hash;

// Хешировать
$hashed = Hash::make('password123');

// Проверить
if (Hash::check('password123', $hashed)) {
    // Пароль верный
}

// Создать пользователя
User::create([
    'name' => 'Иван',
    'email' => 'ivan@example.com',
    'password' => Hash::make($request->input('password')),
]);
```

## Шифрование данных

Шифрование чувствительных данных:

```php
use FF\Security\Encrypt;

// Использование сервиса из контейнера (рекомендованный способ)
$encryptor = app('encrypt');
$encrypted = $encryptor->encrypt('номер-кредитной-карты');
$decrypted = $encryptor->decrypt($encrypted);

// Сохранить зашифрованное
User::create([
    'ssn' => $encryptor->encrypt('123-45-6789'),
]);

// Альтернатива: использование статических методов (без dependency injection)
$encrypted = Encrypt::hash('номер-кредитной-карты');
$decrypted = Encrypt::reveal($encrypted);
```

## Защита от CSRF

Защита форм от атак:

### Использование вспомогательных функций (рекомендованный способ)

```php
<form method="POST" action="/users">
    <?php echo csrf_field(); ?>
    <!-- поля формы -->
</form>
```

Функция `csrf_field()` сгенерирует поле с правильным именем и значением токена.

### Ручное создание поля

```html
<form method="POST">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>
    <!-- поля формы -->
</form>
```

### Использование в AJAX-запросах

```javascript
fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
    },
    body: JSON.stringify(data)
});
```

### Проверка CSRF в middleware

Фреймворк автоматически проверяет CSRF-токены для POST, PUT, PATCH и DELETE запросов через `CsrfMiddleware`. Для исключения маршрутов используйте:

```php
// В файле routes.php
$router->post('/api/webhook', 'WebhookController@handle')
    ->middleware(['no_csrf']); // Исключение из CSRF-защиты
```

## Валидация входных данных

Всегда валидируйте пользовательский ввод:

```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email',
]);

// Теперь безопасно использовать
User::create($validated);
```

## Экранирование вывода

Всегда экранируйте вывод:

```html
<!-- С помощью помощника -->
<p><?php echo h($user->name); ?></p>

<!-- Или htmlspecialchars -->
<p><?php echo htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8'); ?></p>
```

## Санитайзинг HTML-контента

При рендеринге HTML, сгенерированного пользователем (например, тело поста или комментарий), обязательно санитизируйте контент и разрешайте только белый список тегов/атрибутов. Экранирование само по себе недостаточно для форматированного текста; комбинируйте санитизацию с явным рендерингом.

```php
// Пример: санитизировать HTML с помощью библиотеки или внутреннего санитайзера
$raw = $request->input('content');
$clean = Sanitizer::cleanHtml($raw); // или используйте HTML Purifier и т.п.

// Рендерить доверенный HTML явно
echo raw_html($clean);
```

Рекомендации:

- Используйте `raw_html()` только для доверенного, санитизированного HTML.
- Никогда не выводите сырой пользовательский ввод напрямую.
- Предпочитайте `h()` для заголовков, простого текста и атрибутов.
- Поддерживайте белый список разрешенных тегов (например, `p`, `strong`, `em`, `ul`, `li`, `a`) и безопасных атрибутов (`href`, `title`) с корректной валидацией.


## Предотвращение SQL-инъекций

Используйте QueryBuilder (безопасно):

```php
// ✅ Безопасно - использует подготовленные выражения
$user = User::where('email', $email)->first();

// ❌ Небезопасно - конкатенация строк
$user = DB::query("SELECT * FROM users WHERE email = '" . $email . "'");
```

## Ограничение частоты запросов

Предотвращение брутфорс-атак:

```php
use FF\Security\RateLimiter;
use FF\Http\Request;
use FF\Http\Response;

class AuthController
{
    protected RateLimiter $rateLimiter;
    
    public function __construct(RateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }
    
    public function login(Request $request): Response
    {
        // Ограничение: максимум 5 попыток входа в течение 15 минут с одного IP
        $key = 'login:' . $request->ip();
        
        if ($this->rateLimiter->isLimited($key, 5, 15)) {
            return response()->json(['error' => 'Слишком много попыток входа'], 429);
        }
        
        $this->rateLimiter->recordAttempt($key, 15); // 15 минутное окно
        
        // Аутентификация...
    }
}
```

Использование RateLimiter через контейнер зависимостей:

```php
// Получение экземпляра через dependency injection
$limiter = app('rateLimiter');

// Проверка лимита по IP
if ($limiter->limitByIp($request->ip(), 10, 60)) {
    return response('Слишком много запросов', 429);
}

// Проверка лимита по ID пользователя
if ($limiter->limitByUser($userId, 100, 1)) {
    return response('Превышен лимит запросов для пользователя', 429);
}

// Проверка лимита по конечной точке
if ($limiter->limitByEndpoint('api/users', 50, 1)) {
    return response('Превышен лимит API', 429);
}
```

## Заголовки безопасности

Фреймворк автоматически добавляет заголовки безопасности через `SecurityHeadersMiddleware`:

```php
// Автоматически добавляемые заголовки:
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: no-referrer-when-downgrade
Permissions-Policy: geolocation=(), microphone=(), camera=()
X-Permitted-Cross-Domain-Policies: none
X-XSS-Protection: 0
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'
```

Для HTTPS соединений автоматически добавляется заголовок HSTS:
```
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
```

## Аутентификация

Используйте систему аутентификации фреймворка:

```php
use FF\Security\Auth;

// Получить экземпляр аутентификации
$auth = app('auth');

// Проверить, аутентифицирован ли пользователь
if ($auth->check()) {
    // Пользователь вошел в систему
    $user = $auth->user();
    $userId = $auth->id();
}

// Выйти из системы
$auth->logout();

// В middleware для защиты маршрутов
use FF\Http\Middleware\AuthMiddleware;

$router->get('/dashboard', 'DashboardController@index')
    ->middleware([AuthMiddleware::class]);
```

## CORS (Cross-Origin Resource Sharing)

Настройте CORS через `CorsMiddleware`:

```php
use FF\Http\Middleware\CorsMiddleware;

// Настройка CORS в конфигурации
$config = [
    'allowed_origins' => ['https://example.com', 'https://app.example.com'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-CSRF-TOKEN'],
    'exposed_headers' => ['X-Total-Count'],
    'max_age' => 3600,
    'credentials' => true,
];

// Применение к маршрутам
$router->group(['middleware' => [CorsMiddleware::class]], function($router) {
    $router->get('/api/users', 'ApiController@users');
    $router->post('/api/users', 'ApiController@createUser');
});
```

## Безопасность сессий

Фреймворк обеспечивает безопасность сессий:

```php
// Регенерация ID сессии при входе/выходе (автоматически)
$auth->login($user);
$auth->logout();

// Проверка безопасности сессии
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Настройки безопасности сессии в php.ini или конфигурации
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

## Middleware безопасности

Применяйте security middleware к вашим маршрутам:

```php
use FF\Http\Middleware\SecurityHeadersMiddleware;
use FF\Http\Middleware\CsrfMiddleware;
use FF\Http\Middleware\RateLimitMiddleware;

// Глобальные middleware (применяются ко всем запросам)
$kernel->pushMiddleware(SecurityHeadersMiddleware::class);

// Группы middleware
$router->group(['middleware' => [
    CsrfMiddleware::class,
    RateLimitMiddleware::class . ':60,1' // 60 запросов в минута
]], function($router) {
    $router->post('/api/users', 'UserController@store');
    $router->put('/api/users/{id}', 'UserController@update');
});
```

## Рекомендации по безопасности

### 1. Обновляйте зависимости
```bash
composer update
```

### 2. Используйте HTTPS
```php
// Принудительное перенаправление на HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}
```

### 3. Валидация файлов
```php
// Проверка типа файла
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
    throw new \Exception('Invalid file type');
}

// Проверка расширения
$extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array(strtolower($extension), $allowedExtensions)) {
    throw new \Exception('Invalid file extension');
}
```

### 4. Защита от временных атак
```php
// Используйте хеширование для сравнения чувствительных данных
if (hash_equals($expectedToken, $providedToken)) {
    // Токены совпадают
}
```

### 5. Логирование безопасности
```php
// Логируйте важные события безопасности
logger()->warning('Failed login attempt', [
    'ip' => $request->ip(),
    'email' => $request->input('email'),
    'user_agent' => $request->header('User-Agent'),
]);
```

## Аутентификация

Реализация аутентификации:

```php
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    $user = User::where('email', $credentials['email'])->first();
    
    if ($user && Hash::check($credentials['password'], $user->password)) {
        session()->put('user_id', $user->id);
        return redirect('/dashboard');
    }
    
    return redirect('/login')->with('error', 'Неверные учетные данные');
}

public function logout()
{
    session()->forget('user_id');
    session()->regenerate();
    return redirect('/');
}
```

Проверка аутентификации:

```php
<?php if (session()->has('user_id')): ?>
    <!-- Пользователь вошел -->
<?php endif; ?>
```

## Безопасные заголовки

```php
return response($content)
    ->header('X-Content-Type-Options', 'nosniff')
    ->header('X-Frame-Options', 'DENY')
    ->header('X-XSS-Protection', '1; mode=block');
```

## Безопасность загрузки файлов

```php
use FF\Http\Request;

public function upload(Request $request)
{
    // Валидация: обязательный файл, только jpg|png, размер до 2 МБ
    $request->validate([
        'file' => 'required|file|mimes:jpg,png|max:2048',
    ]);

    $file = $request->file('file');

    // Дополнительная проверка MIME по содержимому файла
    $allowed = ['image/jpeg', 'image/png'];
    if (!in_array($file->getMimeType(), $allowed, true)) {
        return error('Неверный тип файла');
    }

    // Безопасное перемещение: разрешены ТОЛЬКО public/uploads и storage/uploads
    // Публичные аватары/картинки:
    $path = $file->moveToPublicUploads('avatar_' . time() . '.jpg');

    // Приватные документы (вне публичной директории):
    // $path = $file->moveToStorageUploads('doc_' . time() . '.pdf');

    return $path;
}
```

Примечания:
- Правило `max` для файлов измеряется в килобайтах: `max:2048` ≈ 2 МБ.
- `UploadedFile::moveToPublicUploads()` и `moveToStorageUploads()` внутри проверяют путь и запретят сохранение вне `public/uploads` или `storage/uploads`.
- Для чувствительных данных используйте `storage/uploads`; для общедоступных изображений — `public/uploads`.

## Переменные окружения

Храните чувствительные данные в `.env`:

```env
APP_KEY=ваш_ключ_шифрования
DB_PASSWORD=ваш_пароль
API_SECRET=ваш_секрет
```

Доступ в коде:

```php
$secret = env('API_SECRET');
```

## Чек-лист безопасности

Перед развертыванием:

- ✅ `APP_DEBUG=false` в .env
- ✅ Сильный `APP_KEY`
- ✅ Включенный HTTPS
- ✅ Все входные данные валидированы
- ✅ Весь вывод экранирован
- ✅ Пароли хешированы с BCrypt
- ✅ CSRF токены на формах
- ✅ Безопасные настройки cookie
- ✅ Включено ограничение частоты запросов
- ✅ Обновлены зависимости

---

[← Назад к документации](./README.md)
