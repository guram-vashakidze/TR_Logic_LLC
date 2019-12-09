<?php
namespace App;

class Config {

    /**Данные подключения к MySQL*/
    const MYSQL = [
        'host' => '',
        'user' => 'root',
        'password' => '',
        'db' => '',
        'port' => ''
    ];

    /**Константа для указания всех доступных путей в приложении*/
    const PATH = [
        //Полный путь к файлам проекта
        'include' => '/var/www/logic.loc/'
    ];

    /**
     * Установка первоначальных настроек проекта
     * @return void
     */
    public static function setDefaultSetting() {
        //меняем значение переменной include_path
        set_include_path(self::PATH['include']);
        //Настройка автолоудера
        spl_autoload_register(
            function ($class) {
                $path = str_replace('\\',DIRECTORY_SEPARATOR,$class);
                require_once $path.'.php';
            }
        );
    }
}