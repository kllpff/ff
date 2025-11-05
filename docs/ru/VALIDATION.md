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
    {{ csrf_field() }}
    
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
            <span class="error">{{ $errors->first('name') }}</span>
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
            <span class="error">{{ $errors->first('email') }}</span>
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
            <span class="error">{{ $errors->first('password') }}</span>
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
