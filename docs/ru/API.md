# Справочник API

Полная документация API для FF Framework.

## Основные функции

### Приложение

```php
app()                              // Получить экземпляр приложения
app(\YourService::class)          // Получить из контейнера
app()->make(\Class::class)         // Создать экземпляр
app()->bind('key', $concrete)      // Зарегистрировать связывание
app()->singleton('key', $concrete) // Зарегистрировать синглтон
```

### Запрос и ответ

```php
request()                           // Получить текущий запрос
request()->input('name')            // Получить входные данные
request()->all()                    // Все входные данные
request()->validate([...])          // Валидировать входные данные
request()->url()                    // Текущий URL

response($content, 200)             // Создать ответ
response()->json($data)             // JSON ответ
redirect('/url')                    // Перенаправление
route('name', ['id' => 1])         // URL маршрута
url('/path')                        // URL
```

### Представления

```php
view('name')                        // Рендерить представление
view('name', ['var' => $value])    // С переменными
h($string)                          // Экранирование HTML
old('field')                        // Старое значение формы
```

### Сессии

```php
session()                           // Получить сессию
session('key')                      // Получить значение сессии
session()->put('key', $value)       // Сохранить
session()->flash('key', $message)   // Flash-сообщение
```

### База данных

```php
// Модели
User::all()                         // Получить все
User::find(1)                       // Получить по ID
User::findOrFail(1)                 // Получить или ошибка
User::where('status', 'active')    // Запрос
User::create($data)                 // Создать
$user->update($data)                // Обновить
$user->delete()                     // Удалить

// QueryBuilder
User::where('...')
    ->orderBy('...')
    ->limit(10)
    ->get()
```

### Безопасность

```php
Hash::make('password')              // Хешировать пароль
Hash::check('password', $hash)      // Проверить хеш
encrypt('data')                     // Зашифровать данные
decrypt($encrypted)                 // Расшифровать
csrf_token()                        // Получить CSRF токен
csrf_field()                        // Поле CSRF в форме
```

### Валидация

```php
$request->validate([...])           // Валидировать
$errors->has('field')               // Проверить ошибку
$errors->first('field')             // Получить первую ошибку
$errors->all()                      // Все ошибки
```

### Логирование

```php
logger()                            // Получить логгер
logger()->info('message')           // Залогировать
logger()->error('error')            // Залогировать ошибку
logger()->debug('debug')            // Залогировать отладку
```

### Кеширование

```php
cache()                             // Получить кеш
cache()->put('key', $value, 3600)  // Сохранить
cache()->get('key')                 // Получить
cache()->has('key')                 // Проверить
cache()->forget('key')              // Удалить
cache()->flush()                    // Очистить все
```

### Утилиты

```php
now()                               // Текущее время DateTime
dump($var)                          // Вывести переменную
dd($var)                            // Вывести и завершить
env('KEY')                          // Переменная окружения
config('app.name')                  // Конфигурация
abort(404)                          // Прервать с кодом статуса
```

## Справочник классов

### Запрос

```php
$request->input($key, $default)
$request->all()
$request->only(['key1', 'key2'])
$request->except(['key1'])
$request->file('name')
$request->get('name')
$request->post('name')
$request->header('name')
$request->method()
$request->url()
$request->fullUrl()
$request->path()
$request->ip()
$request->isMethod('post')
$request->isPost()
$request->isGet()
$request->isAjax()
$request->validate([...])
```

### Ответ

```php
response($content, 200)
response()->json($data)
response()->download($path)
response()->header($key, $value)
response()->withHeaders([...])
response()->setStatusCode(200)
```

### Модель

```php
Model::all()
Model::find($id)
Model::findOrFail($id)
Model::where($column, $value)
Model::create($data)
$model->update($data)
$model->save()
$model->delete()
$model->toArray()
$model->toJson()
```

### Сессия

```php
session()->put($key, $value)
session()->get($key, $default)
session()->has($key)
session()->forget($key)
session()->flush()
session()->flash($key, $value)
session()->regenerate()
```

---

[← Назад к документации](./README.md)
