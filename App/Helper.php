<?php
namespace App;

/**
 * Class Helper
 * Вспомогательные методы
 * @package App
 */
class Helper {

    /**
     * Построение HTML шаблона
     * @param string $path путь к файлу
     * @param array|null $data
     * @return string
     */
    public static function requireInBuffer ($path,array $data = null) {
        if ($data) {
            foreach ($data as $key => $item) {
                $$key = $item;
            }
        }
        ob_start();
        require_once $path.'.php';
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    /**
     * Вывод сообщения на запрос AJAX
     * @param string $status - статус сообщения
     * @param string $msg - текст сообщения
     * @param string $cls - класс алерта
     * @param array $data - доп. данные
     */
    public static function alert($status,$msg,$cls = 'default',array $data = []) {
        die(
            json_encode(
                array_merge(
                    [
                        $status => [
                            'msg' => $msg,
                            'cls' => $cls
                        ]
                    ],
                    $data
                )
            )
        );
    }

    public static function showDefaultAlert() {
        self::alert('error',\App\Language::dictionary('global','alerts')['default'],'red');
    }

    /**
     * Вывод результат через AJAX
     * @param array $data - результат
     */
    public static function result(array $data) {
        die(
            json_encode(
                $data
            )
        );
    }

    /**
     * @param $data
     */
    public static function debug($data) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }
}