<?php
namespace Controller;

/**
 * Class Template
 * Класс основной обработки шаблона
 * @package Controller
 */
class Template {

    /**Шаблон по-умолчанию для авторизированного пользователя*/
    const default_auth_template = 'profile';

    /**Шаблон по-умолчанию для не авторизированного пользователя*/
    const default_template = 'auth';

    const templates = [
        'auth' => [
            'auth' => false,
            'controller' => 'Profile',
            'jsEvent' => 'initAuthForm'
        ],
        'registration' => [
            'auth' => false,
            'controller' => 'Profile',
            'jsEvent' => 'initProfileForm'
        ],
        'forgot' => [
            'auth' => false,
            'controller' => 'Profile',
            'jsEvent' => 'initForgotForm'
        ],
        'profile' => [
            'auth' => true,
            'controller' => 'Profile',
            'jsEvent' => 'initProfileForm'
        ]
    ];

    /**@var string $method - запрашиваемый метод*/
    private static $method = null;

    /**@var string $template - Шаблон на котором находится пользователь*/
    private static $template = null;

    /**
     * Вызов основного шаблона
     */
    public static function init() {
        //Получаем данные шаблона
        $headers = \App\Config::getHeaderData();
        //Установка языка
        \App\Language::setLanguage($headers['language']);
        //Если запрос не AJAХ
        if (empty($headers['method'])) return;
        //Устанавливаем шаблон
        self::set($headers);
        //Устанавливаем метод
        self::setMethod($headers['method']);
        //Необходимый контреллер
        /**@var \Controller\Profile $controller*/
        $controller = '\Controller\\'.self::templates[self::$template]['controller'];
        //Получаем данные по конкретному шаблону
        new $controller(\App\Config::getPhpInputData());
    }

    /**
     * Проверка запрашиваемого шаблона
     * @param array $headers - заголовки запрашиваемого шаблона
     * @return void
     */
    private static function set(array $headers) {
        //Проверка авторизации пользователя
        $auth = Profile::checkAuthUser();
        //Проверяем запрашиваемый шаблон
        if (!array_key_exists($headers['template'],self::templates)) {
            //Устанавливаем шаблон по умолчанию
            self:: $template = $auth !== null ? self::default_auth_template : self::default_template;
            //Если для запрашиваемого шаблона нужна авторизация
            //А пользователь не авторизован
        } elseif (self::templates[$headers['template']]['auth'] === true && $auth === null) {
            //Установливаем шаблон по умолчанию для не авторизированного пользователя
            self::$template = self::default_template;
            //Если пользователь авторизирован
            //То закрываем доступ к шаблонам авторизации и регистрации
        } elseif (self::templates[$headers['template']]['auth'] !== true && $auth !== null) {
            self::$template = self::default_auth_template;
        } else {
            //Записываем выбранный шаблон
            self::$template = $headers['template'];
        }
    }

    /**
     * Вывод запрашиваемого шаблона
     * @param boolean $ucFirst  - первая буква большая
     * @return string
     */
    public static function get($ucFirst = false) {
        return $ucFirst === false ? self::$template : ucfirst(self::$template);
    }

    /**
     * Вызываемоое события для шаблона на стороне JS
     */
    public static function getEvent() {
        return self::templates[self::$template]['jsEvent'];
    }

    /**
     * Устанавливаем метод
     * @param string $method- запрашиваемый метод
     * @return void
     */
    private static function setMethod($method) {
        //Если нет метода
        if ($method === null) return;
        //составляем название функции
        $method = explode('_', mb_strtolower($method));
        //составляем название вызываемой функции
        self::$method = $method[0];
        //составляем название мотода
        for ($i = 1, $max = count($method); $i < $max; $i++) self::$method .= ucfirst($method[$i]);
    }

    /**
     * Получение запрашиваемого метода
     */
    public static function getMethod() {
        return self::$method;
    }
}