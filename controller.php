<?php
require_once 'App/Config.php';
//Установка значений по-умолчанию
\App\Config::setDefaultSetting();
//Вызываем обработчик запроса
\Controller\Template::init();