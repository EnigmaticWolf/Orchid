Orchid Ядро
====
Класс `Orchid` это микро фреймворк для быстрого создания Web-приложений на PHP.
```php
$app = new Engine\Orchid();

$app->bind("/", function(){
	return "Здравствуй Мир! :)";
});

$app->run();
```

## Роутинг
Роутинг запросов происходит по HTTP методу в паре с URL-правилом.  
Каждое правило должно быть отдельно объявленно вызовом метода:
```php
$app->get("/", function() {
    return "Это GET запрос...";
});

$app->post("/", function() {
    return "Это POST запрос...";
});

$app->bind("/", function() {
    return "Это GET или POST запрос...";
});
```
Правила могут включать в себя переменные, которые в дальнейшем будут доступны как свойства первого аргумента функции:
```php
$app->get("/news/:date/:id", function($params) {
    return $params["date"]."-".$params["id"];
});

$app->post("/file/*", function($params) {
    return $params[":arg"];
});

$app->bind("#/page/(about|contact)#", function($params) {
    return implode("\n", $params[":capture"]);
});
```

#### Приоритет & Условия
Приоритет определяет порядок, в котором выполняется применение правил. Правила с более высоким приоритетом выполняются первыми.  
При необходимости можно задать различные условия, например проверку `user-agent`:
```php
$app->bind("/foo", function() {
    // AJAX запрос...
}, "AJAX", strpos($_SERVER["HTTP_USER_AGENT"], "Safari") !== false, $priority = 10);

$app->bind("/foo", function() {
    // GET запрос...
}, "GET", strpos($_SERVER["HTTP_USER_AGENT"], "Safari") !== false);
```

## Шаблоны
Можно использовать любой шаблон:
```php
$app->bind("/", function() {
        $data = [
            "title" => "Orchid | Demo",
            "body"  => "Здравствуй Мир! :)",
        ];
        return $this->render("view/layout.php", $data);
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
    protected $app;
    public function __construct($app){
        $this->app = $app;
    }

    /* /page или /page/index */
    public function index() {
        return $this->app->render("page/index.php");
    }

    /* /page/contact */
    public function contact() {
        return $this->app->render("page/contact.php");
    }

    /* /page/welcome/foo */
    public function welcome($name) {
        return $this->app->render("page/welcome.php", array("name"=>$name));
    }
}

$app->bindClass("Page");
```
Кроме того вы можете восспользоваться классом `Controller`.

## Хранилище данных
Воспользуйтесь хранилищем данных типа `ключ=значение`, просто установив ключ к объекту `$app`.
```php
$app["config.foo"] = array("bar" => 123);
```
Простой доступ к элементам массива с помощью разделителя `/`.
```php
$value = $app["config.foo/bar"]; // вернёт 123
```

## Пути
Используйте короткие ссылки на файлы/каталоги, чтобы получить быстрый доступ к ним:
```php
$app->path("view", __DIR__."/view");

$view = $app->path("view:detail.php");
$view = $app->render("view:detail.php");
```
Получить URL для файла:
```php
$url  = $app->pathToUrl("folder/file.php");
$url  = $app->pathToUrl("view:file.php");
```

## Задачи
```php
// добавление задачи
$app->task("custometask", function(){
    // код выполняемый здесь
}, $priority = 0);

// вызов задачи
$app->trigger("custometask", $params=array());
```
Кроме того, можно использовать три системных имени задач:
 + `before` - выполняется до Роутинга;
 + `after`  - выполняется после Роутинга;
 + `shutdown` - выполняется перед завершением работы;
```php
$app->task("after", function() {
    switch($this->response->status){
        case "404":
            $this->response->body = $this->render("view/404.php");
            break;
        case "500":
            $this->response->body = $this->render("view/500.php");
            break;
    }
});
```

## Сервисы
```php
$app->service("db", function(){
    // объект будет создан в момент первого доступа к $app["db"]
    $obj = new PDO(...);

    return $obj;
});

$app["db"]->query(...);
```

## Расширения
При необходимости можно расширить функционал `Orchid` расширениями:
```php
class Foo extends Engine\Extension {
    public function bar(){
        echo "Hello!";
    }
}

$app("Foo")->bar();
```

#### Расширения в поставке
**Cache**
```php
$app("Cache")->write($key, $value, $duration = -1);
$app("Cache")->read($key, $default=null);
$app("Cache")->delete($key);
$app("Cache")->clear();
```
**Crypta**
```php
$app("Crypta")->encrypt($input);
$app("Crypta")->decrypt($input);
$app("Crypta")->hash($string);
$app("Crypta")->check($string, $hashString);
```
**Session**
```php
$app("Session")->create($sessionName=null);
$app("Session")->write($key, $value);
$app("Session")->read($key, $default=null);
$app("Session")->delete($key);
$app("Session")->destroy();
```
**String**
```php
$app("String")->start($needle, $haystack);
$app("String")->end($needle, $haystack);
$app("String")->truncate($string, $length, $append="...");
$app("String")->eos($count, $single, $double, $triple);
$app("String")->escape($input);
$app("String")->unEscape($input);
$app("String")->translate($input, $back = false);
```

## Модули
Модули - это основной функционал `Orchid`, их методы глобально доступны, кроме того, они могут добавлять: правила роутинга, внешние сервисы, задачи.
```php
class ModulePage extends Engine\Module { 
    public function initialize() {
        // зададим правило обработки запросов
		$this->app->bindClass("Page", "*");
	}

    public function foo(){
        echo "bar";
    }
}

$app("ModulePage")->foo(); // "bar"
```

#### Модули в поставке
 + `Main` - демонстрационный модуль

## Модели
```php
class Animal extends Engine\Entity\Model {
	public function read() {
	    // выборка модели из внешнего хранилища
	}
	
	public function save() {
	    // вставка/обновление модели во внешнее хранилище
	}
}
```

## Коллекции
```php
class Animals extends Engine\Entity\Collection {
	protected static $model = "Animal";
	
	public static function fetch(array $data = []) {
	    // выборка из внешнего хранилища
	}
}

$myAnimal = Animals::fetch();
foreach($myAnimal as $key => $val) {
    $val->set(...)->save();
}
```

## Валидатор
```php
class ValidData extends Engine\Entity\Validator {
    // подключение стандартных проверяющих функций
	use Engine\Entity\Validate\Base,
		Engine\Entity\Validate\Type;
		
	// при необходимости можно расширить функционал
	public function isSupportedCity() {
		return function ($field) {
			return in_array($field, ["Moscow", "Lviv"]);
		};
	}
}

// массив данных для примера
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
	  ->addRule($valid->min(6), "Поле не может быть меньше 5 символов длинной.")
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