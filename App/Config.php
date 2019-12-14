<?php
namespace App;

class Config {

    const HOST = '....';

    /**Данные подключения к MySQL*/
    const MYSQL = [
        'host' => 'localhost',
        'user' => '',
        'password' => '',
        'db' => 'tr_logic',
        'port' => '8123'
    ];

    /**Константа для указания всех доступных путей в приложении*/
    const PATH = [
        //Полный путь к файлам проекта
        'include' => '....',
        //путь к аватаркам
        'avatar' => '..../images/',
        //путь к аватарке по умолчанию
        'default_avatar' => '..../images/avatar.png',
        //Путь к шаблонам e-mail сообщений
        'mail' => '..../tpl/mail/'
    ];

    /**Данные для отправки писем пользователям*/
    const SMTP = [
        'host' => 'ssl://smtp.yandex.ru',
        'port' => 465,
        'email' => 'logic.loc@yandex.ua',
        'pass' => 'sP031JQa53MnyudH'
    ];

    /**Часовой пояс приложения*/
    const TIMEZONE = [
        'php' => 'UTC',
        'mysql' => '+00:00'
    ];

    /**Дополнительный ХЭШ к паролям пользователей*/
    const USER_PASSWORD_HASH = 'F7C5279364605C7A361276E09D10BCF3';

    /**
     * Режим разработчика, если включен то будут выводится ошибки PHP
     * и ошибки БД
     */
    const DEV = true;

    /**
     * Установка первоначальных настроек проекта
     * @return void
     */
    public static function setDefaultSetting() {
        //Если включен режим разработчика
        if (self::DEV) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }
        header('Content-type: text/html; charset=UTF-8');
        header('Default-Host: '.self::HOST);
        //Включаем сессию
        session_start();
        //меняем значение переменной include_path
        set_include_path(self::PATH['include']);
        //Настройка автолоудера
        spl_autoload_register(
            function ($class) {
                $path = str_replace('\\',DIRECTORY_SEPARATOR,$class);
                require_once $path.'.php';
            }
        );
        //установка часового пояса
        date_default_timezone_set(self::TIMEZONE['php']);

    }

    /**
     * Получение данных из php://input используется при AJAX запросах (или из $_POST при использовании js класса FormData)
     * @return array
     */
    public static function getPhpInputData() {
        //Считываем данные
        $phpInput = file_get_contents('php://input');
        //Если нет данных
        if (empty($phpInput)) return empty($_POST) ? null : $_POST;
        //Декодируем данные
        $phpInput = json_decode(
            urldecode($phpInput),
            true
        );
        //Если неудалось декодировать данные
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($phpInput)) return null;
        return $phpInput;
    }

    public static function getHeaderData() {
        //заголовки запроса
        $headers = getallheaders();
        return [
            'template' => !empty($headers['Template']) ? $headers['Template'] : null,
            'method' => !empty($headers['Method']) ? $headers['Method'] : null,
            'language' => !empty($headers['Accept-Language']) ? $headers['Accept-Language'] : ''
        ];
    }
}