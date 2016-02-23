Orchid Framework
====
Класс `Orchid` это основа фреймворка для быстрого создания Web-приложений на PHP.
```php
App::initialize();

Router::bind("/", function(){
    return "Здравствуй Мир! :)";
});

App::run();
```

## Роутинг
Роутинг запросов происходит по HTTP методу в паре с URL-правилом.  
Каждое правило должно быть отдельно объявленно вызовом метода:
```php
Router::get("/", function() {
    return "Это GET запрос...";
});

Router::post("/", function() {
    return "Это POST запрос...";
});

Router::bind("/", function() {
    return "Это GET или POST запрос...";
});
```

Правила могут включать в себя ключи, которые в дальнейшем будут доступны как элементы массива в первом аргументе функции:
```php
Router::get("/news/:date/:id", function($params) {
    return $params["date"]."-".$params["id"];
});

Router::post("/file/*", function($params) {
    return $params[":arg"];
});

Router::bind("#/page/(about|contact)#", function($params) {
    return implode("\n", $params[":capture"]);
});
```

##### Приоритет & Условия
Приоритет определяет порядок, в котором выполняется применение правил. Правила с более высоким приоритетом выполняются первыми.  
При необходимости можно задать различные условия, например проверку `user-agent`:
```php
Router::bind("/foo", function() {
    // обработка запроса...
}, "GET", strpos($_SERVER["HTTP_USER_AGENT"], "Safari") !== false, $priority = 10);
```

## Шаблоны
Можно использовать любой шаблон:
```php
Router::bind("/", function() {
    $data = [
        "title" => "Orchid | Demo",
        "body"  => "Здравствуй Мир! :)",
    ];

    return App::render("view/layout.php", $data);
});
```
`view/layout.php`:
```php
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>
<body>
    <?= $body ?>
</body>
</html>
```

## ООП
Просто подключите класс:
```php
class Page {
    /* /page или /page/index */
    public function index() {
        return App::render("page/index.php");
    }

    /* /page/contact */
    public function contact() {
        return App::render("page/contact.php");
    }

    /* /page/welcome/foo */
    public function welcome($name) {
        return App::render("page/welcome.php", ["name" => $name]);
    }
}

Router::bindClass("Page");
```
Кроме того вы можете воспользоваться классом `Controller`.

## Хранилище данных
Используйте хранилище данных типа `ключ=значение`, просто установив ключ к объекту `$app`.
```php
App::set("config.foo", ["bar" => 123]);
```
Простой доступ к элементам массива с помощью разделителя `/`.
```php
$value = App::get("config.foo/bar"); // 123
```

## Сервисы
```php
App::addService("db", function(){
    // объект будет создан в момент первого доступа
    $obj = new PDO(...);

    return $obj;
});

App::get("db")->query(...);
```

## Пути
Используйте короткие ссылки на файлы/каталоги, чтобы получить быстрый доступ к ним:
```php
App::path("view", __DIR__."/view");

$view = App::path("view:detail.php");
$view = App::render("view:detail.php");
```
Получить URL для файла:
```php
$url  = App::pathToUrl("folder/file.php");
$url  = App::pathToUrl("view:file.php");
```

## База данных
Объект `Database` реализует подключение серверу базы данных:
```php
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "username" => "...",
        "password" => "...",
    ]
]);
```
`Database` позволяет инициализировать соединение с несколькими базами данных, например работающими в режиме репликации.

Добавив параметр `role` можно указать чем является данный сервер `master` или `slave` (по умолчанию `master`):
```php
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "username" => "...",
        "password" => "...",
        "role"     => "slave",
    ]
]);
```

Кроме того можно задать опции инициализации `PDO`:
```php
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "username" => "...",
        "password" => "...",
        "option"   => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        ],
    ]
]);
```

##### Получение соединения
После инициализации, в любой момент можно получить объект соединения:
```php
$pdo = Database::getConnection($use_master = false);
```

##### Выполнение запросов
Для выполнения запроса необходимо передать его в метод `Database::prepare`:
```php
$sth = Database::query("
    SELECT
        `name`, `colour`, `calories`
    FROM
        `fruit`
    WHERE
        `calories`  < :calories AND
        `colour`    = :colour
", [":calories" => 150, ":colour" => "red"], $use_master = false);

$array = $sth->fetchAll(PDO::FETCH_ASSOC);
```

## Кеширование
Объект `Memory` реализует соединение с внешним хранилищем типа `Key-Value`:
```php
Memory::initialize([
    [
        "host"    => "localhost",
        "port"    => 11211,
    ]
]);
```
`Memory` позволяет производить подключение к одному или
нескольким серверам с разделением на `master` и `slave`.

Добавив параметр `role` можно указать чем является данный сервер `master` или `slave` (по умолчанию `master`):
```php
Memory::initialize([
    [
        "host"    => "localhost",
        "port"    => 11211,
        "role"    => "master",
    ]
]);

Memory::get($key, $default = false);
Memory::set($key, $value, $expire = 0);
Memory::delete($key);
Memory::flush();
```

##### Префикс ключей
При необходимости можно задать префикс для всех ключей одновременно:
```php
Memory::$prefix = "orchid";
```

##### Отключение чтения
Отключить чтение из внешнего хранилища можно изменив флаг:
```php
Memory::$disabled = true;
```
*Данный флаг не влияет на запись и удаление ключей.*

## Задачи
```php
// добавление задачи
Task::add("custometask", function(){
    // код выполняемый здесь
}, $priority = 0);

// вызов задачи
Task::trigger("custometask", $params=array());
```
Кроме того, можно использовать три системных имени задач:
 + `before` - выполняется до Роутинга;
 + `after`  - выполняется после Роутинга;
 + `shutdown` - выполняется перед завершением работы;
 
```php
Task::add("after", function() {
    switch(Response::$status){
        case "404":
            Response::$body = App::render("view/404.php");
            break;
        case "500":
            Response::$body = App::render("view/500.php");
            break;
    }
});
```

## Расширения
При необходимости можно расширить функционал `Orchid` расширениями:
```php
class Foo extends Orchid\Classes\Extension {
    public static function bar(){
        return "Hello!";
    }
}

Foo::bar(); // Hello!
```

#### Расширения в поставке
**Cache**
```php
Cache::write($key, $value, $duration=-1);
Cache::read($key, $default=null);
Cache::delete($key);
Cache::clear();
```
**Crypta**
```php
Crypta::encrypt($input);
Crypta::decrypt($input);
Crypta::hash($string);
Crypta::check($string, $hashString);
```
**Session**
```php
Session::create($sessionName=null);
Session::write($key, $value);
Session::read($key, $default=null);
Session::delete($key);
Session::destroy();
```
**String**
```php
Str::start($needle, $haystack);
Str::end($needle, $haystack);
Str::truncate($string, $length, $append="...");
Str::eos($count, $single, $double, $triple);
Str::escape($input);
Str::unEscape($input);
Str::translate($input, $back = false);
```

## Модули
Модули - это основной функционал `Orchid`, их методы глобально доступны, кроме того, они могут добавлять: правила роутинга, внешние сервисы, задачи.
```php
class ModulePage extends Orchid\Classes\Module { 
    public static function initialize() {
        // зададим правило обработки запросов
        Router::bindClass("Page", "*");
    }

    public static function foo(){
        echo "bar";
    }
}

ModulePage::foo(); // "bar"
```

#### Модули в поставке
 + `Main` - демонстрационный модуль

## Модели
```php
class Car extends Orchid\Classes\Model {
    protected static $default = [
        "brand" => "",
        "model" => "",
        "color" => "",
    ];

    public function read(array $data = []) {
        // выборка модели из внешнего хранилища
    }

    public function save() {
        // вставка/обновление модели во внешнее хранилище
    }

    /**
     * @return String марка и модель машины
     */
    public function getMark() {
        return $this->data["brand"] . " " . $this->data["model"];
    }
}

$auto = Car::read(["id" => 14]);

if ($auto->isEmpty()) {
    $auto->setAll([
        "brand" => "BMW",
        "model" => "X1",
        "color" => "Orange",
    ]);

    $auto->save();
}

echo $auto->getMark();
```

## Коллекции
```php
class Cars extends Orchid\Classes\Collection {
    protected static $model = "Car"; // указывает какую модель создать при вызове метода get

    public static function fetch(array $data = []) {
        // выборка из внешнего хранилища
    }
}

$myCars = Cars::fetch()->find('brand', 'bmw');
foreach($myCars as $key => $val) {
    echo $val->getMark();
}
```

## Валидатор
```php
class ValidData extends Orchid\Classes\Validator {
    // подключение стандартных проверяющих функций
    use Orchid\Classes\Validate\Base,
        Orchid\Classes\Validate\Type,
        Orchid\Classes\Validate\String;
		
    // при необходимости можно расширить функционал
    public function isSupportedCity() {
        return function ($field) {
            return in_array($field, ["Moscow", "Lviv"]);
        };
    }
}

// для примера
$data = [
    "username" => "Aleksey",
    "email"    => "Aleksey@example.com",
    "city"     => "Moscow",
];

// создаём объект валидатора
$valid = new ValidData($data);

// правила проверки полей
$valid->attr("username")
      ->addRule($valid->isNotEmpty(), "Поле не может быть пустым.")
      ->addRule($valid->min(5), "Поле не может быть меньше 5 символов длинной.")
      ->addRule($valid->max(16), "Поле не может быть больше 16 символов длинной.");
$valid->attr("email")
      ->addRule($valid->isEmail(), "Введённое значение не является валидным E-Mail адресом.");
$valid->option("city")
      ->addRule($valid->isSupportedCity(), "Простите, данный город не поодерживается.");

// проверяем
$result = $valid->validate();   // в случае успеха результат будет true
                                // в противном случае будет возвращен массив
                                // где ключ = поле, значение = причина
```

## Демоны
Позволяют выполнять некую работу в фоне без прямого взаимодействия с пользователем.
```php
// подключаем файл начальной загрузки
require_once(__DIR__ . "/../bootstrap.php");

use Orchid\Classes\Daemon;

for($i = 0; $i < 10; $i++){
    echo $i . " \n\r";
}
```
Затем выполнять по `cron` или запустить фоном: `../orchid/daemon/#php Demo.php`