<?php
namespace App\DB;

trait MySQL {

    /**@var \mysqli $MySQL*/
    private static $MySQL = null;

    /**
     * @return \mysqli|array
     */
    private static function connectMySQL() {
        if (!empty(self::$MySQL)) return self::$MySQL;
        //Меняем обработчик ошибок Warning
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \Exception($errstr);
        },E_WARNING);
        //Данные для подключения к MySQL
        $connect = \App\Config::MYSQL;
        //Пытаемся подключится
        try {
            self::$MySQL = mysqli_connect($connect['host'], $connect['user'], $connect['password'], $connect['db'], $connect['port']);
        } catch (\Exception $e) {
            //Неудачное подключение
            return self::returnErrorMySQL(
                'Неудалось подключиться к MySQL' . "\n" .$e->getMessage()
            );
        }
        //Неудачное подключение
        if (empty(self::$MySQL)) return self::returnErrorMySQL(
            'Неудалось подключиться к MySQL'
        );
        //Выставляем кодировку
        if (mysqli_query(self::$MySQL, "SET NAMES utf8") === false) return self::returnErrorMySQL('Failed UTF-8: '. mysqli_error(self::$MySQL));
        if (mysqli_query(self::$MySQL, "SET time_zone = '".\App\Config::TIMEZONE['mysql']."'") === false) return self::returnErrorMySQL('Failed TIME_ZONE: '. mysqli_error(self::$MySQL));
        return self::$MySQL;
    }

    private static function disconnectMySQL() {
        if (empty(self::$MySQL)) return;
        mysqli_close(self::$MySQL);
        self::$MySQL = null;
    }

    /**
     * Тип подключение к MySQL
     * @param boolean $type
     */
    public static function setMySQLConnection($type) {
        self::$MySQLConnection = $type;
    }

    /**
     * @param $query
     * @param null $id
     * @param \Closure $function
     * @return array|bool|int|\mysqli_result|null|string
     */
    public static function queryMySQL($query,$id = NULL,$function = NULL) {
        //Подключаемся к mySQL
        $connect = self::connectMySQL();
        //Если неудалось подключится к mySQL
        if (is_array($connect) && !empty($connect['error'])) return $connect;
        //Выполняем запрос к MySQL
        $response = mysqli_query($connect, $query);
        if ($response === false) return self::returnErrorMySQL('Ошибка выполнения запроса: '.mysqli_error($connect),$query);
        if ($id) {
            //id последнего вставленного элемента
            $id = mysqli_insert_id($connect);
            //Если надо - отсоеденяемся от сервера
            self::disconnectMySQL();
            return $id;
        }
        //Если запрос не на выборку
        if (!preg_match("/^[^A-Z]*(SELECT|SHOW)/i",$query)) return $response;
        //Кол-во полей
        $rowCount = mysqli_num_rows($response);
        for ($i = 1; $i <= $rowCount; $i++) {
            $result[$i-1] = mysqli_fetch_assoc($response);
        }
        self::disconnectMySQL();
        //Если нет результат
        if (empty($result)) $result = [];
        if (!empty($function)) $result = $function($result);
        return $result;
    }

    /**
     * Вывод сообщений об ошибке
     * @param $msg
     * @param string $query
     * @return string|array
     */
    private static function returnErrorMySQL($msg,$query = '') {
        //Трасировка ошибки
        $trace = debug_backtrace();
        $message = [];
        //Номер трасировки
        $j = 1;
        //Формирование сообщения
        for ($i = count($trace) - 1; $i >= 0; $i--) {
            $message[] = $j . '. ' . $trace[$i]['file'] . ' (' . $trace[$i]['line'] . ') ' .
                (!empty($trace[$i]['class']) ? $trace[$i]['class'] . $trace[$i]['type'] : '') .
                $trace[$i]['function'] . '(' . json_encode($trace[$i]['args'],JSON_UNESCAPED_UNICODE) . ')';
            $j++;
        }
        $message = "\n".implode("\n", $message);
        $result = [
            'error' => [
                'message' => $msg.$message,
                'query' => $query
            ]
        ];
        //Если включен режим разработчика
        if (\App\Config::DEV) \App\Helper::debug($result);
        return $result;
    }

    private static $MySQLTypes = [
        'TINYINT' => 'int',
        'SMALLINT' => 'int',
        'MEDIUMINT' => 'int',
        'INT' => 'int',
        'BIGINT' => 'int',
        'UNSIGNED' => 'float',
        'FLOAT' => 'float',
        'DOUBLE' => 'float',
        'REAL' => 'float',
        'DECIMAL' => 'float',
        'NUMERIC' => 'float',
        'ENUM' => 'string',
        'VARCHAR' => 'string',
        'TINYTEXT' => 'string',
        'TEXT' => 'string',
        'MEDIUMTEXT' => 'string',
        'LONGTEXT' => 'string',
        'DATE' => 'string',
        'TIME' => 'string',
        'DATETIME' => 'string',
        'TIMESTAMP' => 'string',
    ];

    /**
     * Получение типов полей
     * @param string $table - Таблица
     * @param string $db - БД
     * @param bool $check - пост. обработка
     * @return array
     */
    public static function getFieldsTypeMySql($table,$db = 'r7k12',$check = true) {
        $columns = self::queryMySQL(
            "SELECT upper(DATA_TYPE) AS DATA_TYPE,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='{$db}' AND TABLE_NAME='{$table}'",
            null,
            function ($result) {
                return array_column($result,'DATA_TYPE','COLUMN_NAME');
            }
        );
        if (!$check) return $columns;
        foreach ($columns as $name => &$type) {
            $type = self::$MySQLTypes[$type];
        }
        return $columns;
    }
}
