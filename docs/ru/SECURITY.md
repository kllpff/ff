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

// Использование сервиса из контейнера
$encryptor = app('encrypt');
$encrypted = $encryptor->encrypt('номер-кредитной-карты');
$decrypted = $encryptor->decrypt($encrypted);

// Или через статические методы
$encrypted = Encrypt::hash('номер-кредитной-карты');
$decrypted = Encrypt::reveal($encrypted);

// Сохранить зашифрованное
User::create([
    'ssn' => $encryptor->encrypt('123-45-6789'),
]);
```

## Защита от CSRF

Защита форм от атак:

```php
<form method="POST" action="/users">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <!-- поля формы -->
</form>
```

Или вручную:

```html
<form method="POST">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"/>
    <!-- поля формы -->
</form>
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

$limiter = app('rateLimiter');

public function login(Request $request)
{
    $identifier = "login:" . $request->ip();

    if ($limiter->isLimited($identifier, 5, 15)) {
        return response()->json(['error' => 'Слишком много попыток входа'], 429);
    }

    $limiter->recordAttempt($identifier, 15); // 15 минутное окно

    // Аутентификация...
}
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
