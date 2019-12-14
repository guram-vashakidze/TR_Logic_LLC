<?php
require_once 'App/Config.php';
//Установка значений по-умолчанию
\App\Config::setDefaultSetting();
//Вывод аватрки
\Controller\Profile::geUserAvatar();