Orchid Framework
====
#### Требования
* Nginx || Apache || IIS >= 8.5
* PHP >= 5.6
* PDO
* Memcache
* GD

#### Установка
* Скачать Orchid и положить в папку в корневом каталоге вашего веб-проекта;
* Убедитесь, что /path-to-project/storage и все его вложенные папки доступны для записи;
* Вы готовы использовать Orchid ;-)

## Документация
```php
App::initialize();

Router::bind("/", function(){
    return "Здравствуй Мир! :)";
});

App::run();
```

### Роутер
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

### Шаблоны
Можно использовать любой шаблон:
```php
Router::bind("/", function() {
    $data = [
        "title" => "Orchid | Demo",
        "body"  => "Здравствуй Мир! :)",
    ];

    return View::fetch("view/layout.php", $data);
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

### ООП
Просто подключите класс:
```php
class Page {
    /* /page или /page/index */
    public function index() {
        return View::fetch("page/index.php");
    }

    /* /page/contact */
    public function contact() {
        return View::fetch("page/contact.php");
    }

    /* /page/welcome/foo */
    public function welcome($name) {
        return View::fetch("page/welcome.php", ["name" => $name]);
    }
}

Router::bindClass("Page");
```
Кроме того вы можете воспользоваться классом `Controller`.

### Пути
Используйте короткие ссылки на файлы/каталоги, чтобы получить быстрый доступ к ним:
```php
App::addPath("view", __DIR__."/view");

$view = App::getPath("view:detail.php");
```
Получить URL для файла:
```php
$url  = App::pathToUrl("folder/file.php");
$url  = App::pathToUrl("view:file.php");
```

### Расширения
При необходимости можно расширить функционал `Orchid` расширениями:
```php
namespace Orchid\Extension;

class Foo {
    public static function bar(){
        return "Hello!";
    }
}

...

Orchid\Extension\Foo::bar(); // Hello!
```

#### Расширения в дистрибутиве
**Asset**
Обработка карты ресурсов, генерация подключений ресурсов в layout, а так же с шаблонами.

**Cache**
Временное файловое хранилище данных.

**Crypta**
Кодирование и декодирование строк, создание хешей.

**FileSystem**
Работа с файловой системой, получение списка папок и файлов, создание, перемещение и удаление.

**Form**
Создание форм в шаблонах (поддерживает элементы HTML5).

**i18n**
Реализация поддержки нескольких языков.

**Session**
Создание, редактирование и удаление сессий.

**Str**
Работа со строками, безопасное усечение текста, склонение и др.

## Модули
Модули - это основной функционал `Orchid`, их методы глобально доступны, кроме того, они могут добавлять: правила роутинга, сервисы, задачи.
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

#### Модули в дистрибутиве
 + `Main` - демонстрационный модуль

### Конфигурации
Класс `Configuration` удобной является обёрткой над `ini` и `php` файлами конфигураций.
```php
$config = Configuration::fromArray([
    "debug" => true,
]);
// или
$config = Configuration::fromFile("Main:ExampleConfig.php");

$congig->get("debug"); // true
```
`ExampleConfig.php`
```php
<?
return [
    "debug" => true,
];
```

#### Итератор
Объект класса `Configuration` поддерживает итератор.
```php
$config = Configuration::fromArray([
    [
        "dsn" => "...",
        ...
        "role" => "master",
    ],
    [
        "dsn" => "...",
        ...
        "role" => "slave",
    ]
]);

foreach($config as $index => $data) {
    echo $data["role"] . PHP_EOL;
}
```
*Результат*
```
master
slave
```

### База данных
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

Добавив параметр `role` можно указать чем является данный сервер `master` или `slave` (по-умолчанию `master`):
```php
// инициализация поддерживает класс Configuration
Database::initialize(Configuration::fromArray([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "username" => "...",
        "password" => "...",
        "role"     => "slave",
    ]
]));
```

Кроме того можно задать опции инициализации `PDO`:
```php
Database::initialize([
    [
        "dsn"      => "mysql:dbname=base;host=localhost",
        "username" => "...",
        "password" => "...",
        "options"  => [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        ],
    ]
]);
```

##### Получение соединения
После инициализации, в любой момент можно получить объект соединения:
```php
$pdo = Database::getInstance($use_master = false);
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
        `calories`  = :calories AND
        `colour`    = :colour
", [":calories" => 150, ":colour" => "red"], $use_master = false);

$array = $sth->fetchAll(PDO::FETCH_ASSOC);
```

### Кеширование
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

Добавив параметр `role` можно указать чем является данный сервер `master` или `slave` (по-умолчанию `master`):
```php
// инициализация поддерживает класс Configuration
Memory::initialize(Configuration::fromArray([
    [
        "host"    => "localhost",
        "port"    => 11211,
        "role"    => "master",
    ]
]));

Memory::get($key, $default = false);
Memory::set($key, $value, $expire = 0, $tag = null);
Memory::delete($key);
Memory::flush();
Memory::getByTag($tag);
Memory::deleteByTag($tag);
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

##### Внутренний буффер
Для разгрузки внешнего хранилища можно указать некоторые ключи в массив,
при обращении к данным по этим ключам они будут браться из внутреннего буффера класса `Memory`:
```php
Memory::$cachedKeys = [
    "car:"
];
```
*При такой записи во внутренний буффер попадут ключи: `car:list`, `car:model` и другие начинающиеся с `car:`*


### Модели
```php
class Car extends Orchid\Entity\Model {
    protected static $field = [
        "brand" => "",
        "model" => "",
        "color" => "",
    ];

    public function read(array $data = []) {
        // выборка данных из внешнего хранилища
    }

    public function save() {
        // вставка/обновление данных во внешнее хранилище
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

### Коллекции
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

### Валидатор
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
      ->addRule($valid->isSupportedCity(), "Данный город не поддерживается.");

// проверяем
$result = $valid->validate();   // в случае успеха результат будет true
                                // в противном случае будет возвращен массив
                                // где ключ = поле, значение = причина
```

### Сервисы
```php
App::addService("db", function(){
    // объект будет создан в момент первого доступа
    $obj = new PDO(...);

    return $obj;
});

App::getService("db")->query(...);
```

### Задачи
```php
// добавление задачи
Task::add("custometask", function(){
    // код выполняемый здесь
}, $priority = 0);

// вызов задачи
Task::trigger("custometask", $params=array());
```
Кроме того, можно использовать три внутренних имени задач:
 + `before` - выполняется до роутинга;
 + `after`  - выполняется после составления ответа;
 + `shutdown` - выполняется после отправки ответа, перед завершением работы;

```php
Task::add("after", function() {
    switch(Response::getStatus()){
        case "404": {
            Response::setContent(View::fetch("view/404.php"));
            break;
        }
        case "500": {
            Response::setContent(View::fetch("view/500.php"));
            break;
        }
    }
});
```

### Демоны
Позволяют синхронно/асинхронно выполнять задачи в фоне без прямого взаимодействия с пользователем.
```php
use Orchid\Entity\Daemon;

// подключаем файл начальной загрузки
require_once(__DIR__ . "/../bootstrap.php");

while(true){
    echo $i++ . " \n\r";
}
```
Запустить исполняемый файл: `../orchid/daemon/# php Demo.php`

#### Отдельный процесс
Запуска фонового процесса обеспечивает вызов метода `forkProcess`.
```php
...
Daemon::forkProcess();
...
```

#### Журнал
Для включения записи логов в журнал необходим вызов метода `writeLog`.
```php
...
Daemon::writeLog();
...
```