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

Правила могут включать в себя переменные, которые в дальнейшем будут доступны как элементы массива в первом аргументе функции:
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
Кроме того вы можете восспользоваться классом `Controller`.

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
Объект `Database` реализует подключение к одному или нескольким серверам баз данных:
```
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "user"     => "...",
        "password" => "...",
    ]
]);
```
`Database` позволяет инициализировать соединение с несколькими базами данных, например работающими в режиме репликации.

Добавив параметр `role` можно указать чем является данный сервер `master` или `slave` (по умолчанию `master`):
```
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "user"     => "...",
        "password" => "...",
        "role"     => "slave",
    ]
]);
```

Кроме того можно задать опции инициализации `PDO`:
```
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "user"     => "...",
        "password" => "...",
        "option"   => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        ],
    ]
]);
```

##### Получение соединения
После инициализации, в любой момент можно получить объект соединения:
```
$pdo = Database::getConnection($use_master = false);
```

##### Выполнение запросов
Для выполнения запроса необходимо передать его в метод `Database::prepare`:
```
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
Task::task("after", function() {
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
class Foo extends Orchid\Entity\Extension {
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
String::start($needle, $haystack);
String::end($needle, $haystack);
String::truncate($string, $length, $append="...");
String::eos($count, $single, $double, $triple);
String::escape($input);
String::unEscape($input);
String::translate($input, $back = false);
```

## Модули
Модули - это основной функционал `Orchid`, их методы глобально доступны, кроме того, они могут добавлять: правила роутинга, внешние сервисы, задачи.
```php
class ModulePage extends Orchid\Entity\Module { 
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
class Car extends Orchid\Entity\Model {
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
class Cars extends Orchid\Entity\Collection {
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
class ValidData extends Orchid\Entity\Validator {
    // подключение стандартных проверяющих функций
    use Orchid\Entity\Validate\Base,
        Orchid\Entity\Validate\Type,
        Orchid\Entity\Validate\String;
		
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
class MyDaemon extends Daemon {
    public function run() {
        // рабочий код демона
    }
}
```
Затем выполнять по `cron` или запустить фоном: `php index.php [название демона]`