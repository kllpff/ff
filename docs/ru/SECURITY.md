# Руководство по безопасности

Создавайте безопасные приложения с FF Framework.

## Хеширование паролей

Всегда хешируйте пароли:

```php
use FF\Framework\Security\Hash;

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
// Зашифровать
$encrypted = encrypt('номер-кредитной-карты');

// Расшифровать
$decrypted = decrypt($encrypted);

// Сохранить зашифрованное
User::create([
    'ssn' => encrypt('123-45-6789'),
]);
```

## Защита от CSRF

Защита форм от атак:

```html
<form method="POST" action="/users">
    {{ csrf_field() }}
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
use FF\Framework\Security\RateLimiter;

$limiter = new RateLimiter();

public function login(Request $request)
{
    $identifier = "login:" . $request->ip();
    
    if ($limiter->tooManyAttempts($identifier, 5)) {
        return error('Слишком много попыток входа');
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
public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:jpg,png|max:2048',
    ]);
    
    $file = $request->file('file');
    
    // Проверить MIME-тип
    $allowed = ['image/jpeg', 'image/png'];
    if (!in_array($file->getMimeType(), $allowed)) {
        return error('Неверный тип файла');
    }
    
    // Сохранить в хранилище (вне public)
    $path = $file->move(storage_path('uploads'));
    
    return $path;
}
```

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
