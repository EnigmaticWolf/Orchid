<?php

namespace Orchid\Extension {

    use Orchid\App;
    use Orchid\Entity\Exception\FileNotFoundException;

    class i18n
    {
        /**
         * Buffer storage of the language file
         *
         * @var array
         */
        public static $locale = [];
        /**
         * @var App
         */
        protected $app;

        /**
         * i18n constructor
         *
         * @param App    $app
         * @param string $default
         * @param string $force
         */
        public function __construct(App $app, $default = "ru", $force = null)
        {
            $this->app = $app;

            if ($force) {
                $locale = $force;
            } else {
                $locale = $app->request()->getLanguage($default);
            }

            if (!in_array($locale, $app->get("locale", []))) {
                $locale = $default;
            }

            static::$locale = $this->load($locale);
        }

        /**
         * Load language file for specified local
         *
         * @param $locale
         *
         * @return array|mixed
         * @throws FileNotFoundException
         */
        protected function load($locale)
        {
            // default path
            $path = $this->app->getBaseDir() . "/storage/i18n/" . trim($locale) . ".php";

            // check for dynamic path
            if (($file = $this->app->path("lang:" . $locale . ".php")) !== false) {
                $path = $file;
            }

            if (file_exists($path)) {
                $ext = pathinfo($path);

                switch ($ext["extension"]) {
                    case "ini":
                        return parse_ini_file($path, true);
                    case "php":
                        return require_once $path;
                }
            }

            throw new FileNotFoundException("Could not find a language file");
        }
    }
}

namespace {

    use Orchid\Extension\i18n;

    class L
    {
        /**
         * Returns internationalized text for the specified key
         *
         * @param $key
         *
         * @return mixed
         */
        public static function get($key)
        {
            if (array_key_exists($key, i18n::$locale)) {
                return i18n::$locale[$key];
            }

            return null;
        }
    }
}
