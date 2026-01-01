# Руководство по валидации

Валидация входных данных обеспечивает целостность и безопасность данных.

## Основная валидация

В контроллере:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);

    User::create($validated);
}
```

## Правила валидации

### Основные правила

```php
'field' => 'required'              // Обязательное
'field' => 'string'                // Строка
'field' => 'integer'               // Целое число
'field' => 'numeric'               // Числовое
'field' => 'boolean'               // Булево
'field' => 'array'                 // Массив
```

### Правила для строк

```php
'password' => 'min:8'              // Минимум 8 символов
'title' => 'max:255'               // Максимум 255 символов
'username' => 'alpha'              // Только буквы
'code' => 'alpha_num'              // Буквы и цифры
'slug' => 'alpha_dash'             // Буквы, цифры, дефис, подчеркивание
'email' => 'email'                 // Валидный email
'url' => 'url'                     // Валидный URL
'pattern' => 'regex:/^[A-Z]+$/'    // Регулярное выражение
```

### Правила сравнения

```php
'password_confirm' => 'confirmed'  // Совпадает с полем password
'email' => 'unique:users'          // Уникально в таблице
'email' => 'exists:users'          // Существует в таблице
'age' => 'same:min_age'            // То же, что и поле
'age' => 'different:other_age'     // Отличается от поля
```

#### Детально: правило `unique`

Правило `unique` проверяет уникальность значения в базе данных. Это критически важно для предотвращения дублирования email, username и других уникальных полей.

**Синтаксис:**
```php
// Базовое использование
'email' => 'unique:users'              // Проверка в таблице users по имени поля
'email' => 'unique:users,email'        // Явное указание колонки

// При обновлении записи (игнорировать текущую запись)
'email' => 'unique:users,email,5'      // Игнорировать запись с id=5
'email' => 'unique:users,email,5,id'   // Явное указание колонки для исключения
```

**Примеры использования:**

```php
// Регистрация нового пользователя
public function register(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email',
        'username' => 'required|min:3|unique:users,username'
    ]);
    
    User::create($validated);
}

// Обновление профиля (исключаем текущего пользователя)
public function update(Request $request, int $userId)
{
    $validated = $request->validate([
        'email' => "required|email|unique:users,email,{$userId}",
        'username' => "required|unique:users,username,{$userId}"
    ]);
    
    User::find($userId)->update($validated);
}

// С нестандартной колонкой ID
public function updateByUuid(Request $request, string $uuid)
{
    $validated = $request->validate([
        'email' => "required|email|unique:users,email,{$uuid},uuid"
    ]);
    
    User::where('uuid', $uuid)->update($validated);
}
```

**Пользовательские сообщения об ошибках:**

```php
$request->validate([
    'email' => 'required|email|unique:users,email',
], [
    'email.unique' => 'Этот email уже зарегистрирован в системе'
]);
```

**Особенности реализации:**
- ✅ Использует подготовленные SQL-запросы (защита от SQL-инъекций)
- ✅ Graceful degradation — при ошибке БД пропускает проверку
- ✅ Логирует ошибки через `logger()` если доступен
- ✅ Поддерживает исключение записей при обновлении

### Правила выбора

```php
'role' => 'in:admin,user,guest'    // Одно из значений
'status' => 'not_in:deleted,banned' // Не одно из значений
```

### Условные правила

```php
// Обязательно если
'company' => 'required_if:account_type,business'

// Обязательно если не
'free_account' => 'required_unless:paid,true'

// Обязательно с
'phone' => 'required_with:address'

// Обязательно без
'email' => 'required_without:phone'
```

## Пользовательские сообщения об ошибках

```php
$request->validate([
    'name' => 'required|string',
    'email' => 'required|email|unique:users',
], [
    'name.required' => 'Пожалуйста, введите ваше имя',
    'email.required' => 'Email обязателен',
    'email.unique' => 'Email уже зарегистрирован',
]);
```

## Отображение ошибок в представлениях

```html
<!-- Проверить наличие ошибки в поле -->
<?php if ($errors->has('email')): ?>
    <span class="error"><?php echo $errors->first('email'); ?></span>
<?php endif; ?>

<!-- Показать все ошибки для поля -->
<?php if ($errors->has('password')): ?>
    <ul>
        <?php foreach ($errors->get('password') as $error): ?>
            <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<!-- Восстановить значение поля формы -->
<input 
    type="text" 
    name="name"
    value="<?php echo old('name'); ?>"
/>
```

## Валидация массивов

```php
$validated = $request->validate([
    'users.*.name' => 'required|string',
    'users.*.email' => 'required|email',
]);
```

## Правила для файлов

Файловые поля валидируются специальными правилами:

```php
$validated = $request->validate([
    'avatar' => 'required|file|mimes:jpg,png|max:2048',
]);

$file = $request->file('avatar');
// Публичная загрузка (доступно из веба):
$path = $file->moveToPublicUploads('avatar_' . time() . '.jpg');
// Либо приватное хранилище:
// $path = $file->moveToStorageUploads('avatar_' . time() . '.jpg');
```

- `file` — значение должно быть корректным загруженным файлом (`UploadedFile`).
- `mimes:...` — разрешённые расширения (проверяется по исходному имени файла).
- `max:N` — максимальный размер файла в килобайтах; `max:2048` ≈ 2 МБ.
- Дополнительную проверку реального MIME-типa можно сделать через `$file->getMimeType()`.

См. раздел безопасности для рекомендаций по путям хранения: `public/uploads` для публичных файлов и `storage/uploads` для приватных.

## Полный пример формы

```php
// В контроллере
public function create()
{
    return view('users/create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ]);

    User::create($validated);
    
    return redirect('/users');
}
```

```html
<!-- В представлении -->
<form method="POST" action="/users">
    <?php echo csrf_field(); ?>
    
    <div>
        <label for="name">Имя</label>
        <input 
            type="text"
            id="name"
            name="name"
            value="<?php echo old('name'); ?>"
            required
        />
        <?php if ($errors->has('name')): ?>
            <span class="error"><?php echo $errors->first('name'); ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="email">Email</label>
        <input 
            type="email"
            id="email"
            name="email"
            value="<?php echo old('email'); ?>"
            required
        />
        <?php if ($errors->has('email')): ?>
            <span class="error"><?php echo $errors->first('email'); ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="password">Пароль</label>
        <input 
            type="password"
            id="password"
            name="password"
            required
        />
        <?php if ($errors->has('password')): ?>
            <span class="error"><?php echo $errors->first('password'); ?></span>
        <?php endif; ?>
    </div>
    
    <div>
        <label for="password_confirmation">Подтверждение пароля</label>
        <input 
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            required
        />
    </div>
    
    <button type="submit">Создать</button>
</form>
```

---

[← Назад к документации](./README.md)
