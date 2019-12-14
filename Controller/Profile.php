<?php
namespace Controller;

use App\Email\Send;
use App\Helper;

class Profile {

    use \App\DB\MySQL;

    /**@var int $userId - идентификатор пользователя*/
    private $userId = null;

    /**@var string $sendPassword - пароль для отправки пользователю*/
    private static $sendPassword = null;

    /**
     * Проверка авторизации пользователя
     * @return bool|null
     */
    public static function checkAuthUser() {
        //Валидация куки
        $userAuth = filter_input(
            INPUT_COOKIE,
            'auth',
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'default' => null,
                    'regexp' => "/^[\w\d]+$/"
                ]
            ]
        );
        //Если нет куки пользователя
        if ($userAuth === null) return null;
        //Получаем пользователя
        $userId = self::queryMySQL(
            "SELECT userId FROM userAuth WHERE `hash`='{$userAuth}'",
            null,
            function ($result) {
                return !empty($result[0]['userId']) ? (int)$result[0]['userId'] : null;
            }
        );
        //если нет идентификатора пользователя
        if ($userId === null) return null;
        //IP адрес с которого произведено действие
        $ip = ip2long($_SERVER['REMOTE_ADDR']);
        //Обновляем данные последнего действия пользователя
        self::queryMySQL("UPDATE userAuth SET lastIp={$ip},lastAction='" . date('Y-m-d H:i:s') . "' WHERE `hash`='{$userAuth}'");
        //Записываем идентификатор пользователя
        $_SESSION['userId'] = $userId;
        //Выводим флаг авторизации пользователя
        return true;
    }


    private static function setUserHashInCookie($userHash = '') {
        //Установка/удаление куки авторизации
        setcookie('auth', $userHash, ($userHash ? time() + 14 * 86400 : time() - 86400), '/', '.'.\App\Config::HOST, false, true);
        //Если куку устанавливали
        if ($userHash) return;
        //Удаляем значения сессии
        session_unset();
    }

    /**
     * Profile constructor.
     * @param array|null $data - входные данные для метода
     */
    public function __construct($data = null) {
        //Идентификатор польззователя
        $this->userId = !empty($_SESSION['userId']) ? $_SESSION['userId'] : null;
        //Проверка существования метода
        if (!method_exists($this,Template::getMethod()))
            \App\Helper::showDefaultAlert();
        //Вызываем метод
        $this->{Template::getMethod()}($data);
    }

    /**
     * Получение HTML шаблона по определенной страниц
     * @param array|null $data - входные параметры для шаблона
     */
    private function getTemplate($data) {
        \App\Helper::result([
            'hash' => Template::get(),
            'event' => Template::getEvent(),
            'controller' => 'Profile',
            'dictionary' => \App\Language::dictionary('profile',Template::get()),
            'tplData' => !method_exists($this,'get'.Template::get(true).'Data') ? null : $this->{'get'.Template::get(true).'Data'}()
        ]);
    }

    /**
     * Данные для шаблона профиля
     * @return mixed
     */
    private function getProfileData() {
        return self::queryMySQL(
            "SELECT email,avatar,firstName,lastName,phone,bDate FROM users WHERE id={$this->userId}",
            null,
            function ($result) {
                $result = $result[0];
                //проверяем наличие аватара
                $result['avatar'] = !empty($result['avatar']) && file_exists(\App\Config::PATH['avatar'].$result['avatar']) ? '1' : '0';
                return $result;
            }
        );
    }

    /**
     * Сохранение профиля пользователя/регистрация
     * @param array $data
     */
    private function saveProfile(array $data){
        //Валидация данных
        if (empty($data['firstName']) || !preg_match("/^\w+$/ui",$data['firstName']))
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','checkLastName'),'red');
        if (empty($data['lastName']) || !preg_match("/^\w+$/ui",$data['lastName']))
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','checkFirstName'),'red');
        if (!empty($data['phone']) && !preg_match("/^\+?[0-9]{10,}$/",$data['phone']))
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','checkPhone'),'red');
        if (!empty($data['bDate']) && !preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/",$data['bDate']))
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','checkBDate'),'red');
        //Данные для вставки/обновления
        $userData = [
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'phone' => $data['phone'],
            'bDate' => $data['bDate']
        ];
        //Если регистрация
        if ($this->userId === null) self::checkNewUser($data,$userData);
        //Если пользователь хочет изменить пароль
        if ($this->userId !== null && !empty($data['password'])) $this->checkUpdatePassword($data['password'],$userData);
        //Загрузка аватарки
        $this->uploadAvatar($data,$userData);
        //Если надо отправить сообщение
        self::sendRegistrationEmail($userData);
        //Сохраняем данные
        if ($this->userId === null) {
            //Регестрируем пользователя
            self::queryMySQL("INSERT INTO users(email, pass, firstName, lastName, avatar, phone, bDate, regDate) VALUES ".
            "('{$userData['email']}','{$userData['password']}','{$userData['firstName']}','{$userData['lastName']}','{$userData['avatar']}','{$userData['phone']}','{$userData['bDate']}','{$userData['regDate']}')");
            //Выводим сообщение
            \App\Helper::alert(
                'success',
                \App\Language::getAlerts('profile','registration','success'),
                'green',
                [
                    'hash' => 'auth'
                ]
            );
        }
        //Обновляем данные пользователя
        self::queryMySQL("UPDATE users SET firstName='{$userData['firstName']}',lastName='{$userData['lastName']}'".(array_key_exists('avatar',$userData) ? ",avatar='{$userData['avatar']}'" : "").",phone='{$userData['phone']}',bDate='{$userData['bDate']}',tempPass='',tempPassTime=0".(!empty($userData['password']) ? ",pass='{$userData['password']}'" : ""). " WHERE id={$this->userId}");
        //Выводим сообщение
        \App\Helper::alert(
            'success',
            \App\Language::getAlerts('profile','profile','success'),
            'green',
            [
                'hash' => 'profile'
            ]
        );
    }

    /**
     * Валидация данных нового пользователя
     * @param array $data - передаваемые данные
     * @param array $userData - данные для вставки в БД
     */
    private static function checkNewUser(array $data,array &$userData) {
        //Проверка e-mail адреса
        $userCheck = self::checkEmail($data);
        //Если есть пользователь с таким email
        if ($userCheck['user'] !== null)
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','duplicateEmail'),'red');
        //Проверяем есть ли уже пользователь с таким email
        self::queryMySQL(
            "SELECT id FROM tr_logic.users WHERE email='{$userCheck['email']}'",
            null,
            function ($result) {
                //Если пользователя нет
                if (empty($result[0]['id'])) return;
                \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','duplicateEmail'),'red');
            }
        );
        //Записываем email
        $userData['email'] = $userCheck['email'];
        //Генерируем пароль
        $password = self::generateUserPassword();
        //Записываем пароль для отправки пользователю
        self::$sendPassword = $password['password'];
        //В БД сохраняем ХЭШ пароля
        $userData['password'] = $password['hash'];
        //Записываем дату регистрации
        $userData['regDate'] = date('Y-m-d H:i:s');
    }

    /**
     * Проверка email и получение данных пользователя по email
     * @param array $data
     * @return array
     */
    private static function checkEmail(array $data) {
        $email = filter_var_array(
            $data,
            [
                'email' => FILTER_VALIDATE_EMAIL,
                'options' => [
                    'default' => null
                ]
            ]
        )['email'];
        //Если не валидный email
        if ($email === null)
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','incorrectEmail'),'red');
        //Проверяем есть ли уже пользователь с таким email
        $user = self::queryMySQL(
            "SELECT * FROM tr_logic.users WHERE email='{$email}'",
            null,
            function ($result) {
                //Если пользователя нет
                return empty($result[0]['id']) ? null : $result[0];
            }
        );
        return [
            'email' => $email,
            'user' => $user
        ];
    }

    /**
     * Отправка письма регистрации пользователю
     * @param array $userData - данные пользователя
     */
    private static function sendRegistrationEmail(array $userData) {
        //Если не надо отправляять письмо
        if (!self::$sendPassword) return;
        //Получаем текст сообщения
        $textMail = \App\Helper::requireInBuffer(
            \App\Config::PATH['mail'].'mail_registration_'.\App\Language::get(),
            [
                'firstName' => $userData['firstName'],
                'email' => $userData['email'],
                'password' => self::$sendPassword,
                'site' => \App\Config::HOST
            ]
        );
        //Разбиваем текст на html верстку и альтернативное содержание
        $textMail = explode('ALTERNATIVE',$textMail);
        //отправляем сообщение
        \App\Email\Send::sendMailSMTP(
            $userData['email'],
            \App\Language::getAlerts('profile','registration','mailSubject'),
            [
                [
                    'content-type' => 'text/html',
                    'body' => $textMail[0],
                    'alternative' => $textMail[1]
                ]
            ]
        );
    }

    /**
     * Обновления пароля пользователем
     * @param string $password - массив с старым паролем и новым (в двух экземплярах)
     * @param array $userData - данные пользователя
     */
    private function checkUpdatePassword($password,array &$userData) {
        //Декодируем данные json
        $password = json_decode($password,true);
        //Если не указан старый пароль
        if (json_last_error() !== JSON_ERROR_NONE || empty($password['old'])) return;
        //Проверяем пароль на соответствие в БД
        $pass = md5($password['old'].\App\Config::USER_PASSWORD_HASH);
        self::queryMySQL(
            "SELECT id FROM tr_logic.users WHERE id={$this->userId} AND pass='{$pass}'",
            null,
            function ($result) {
                if (empty($result[0]['id']))
                    \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','incorrectPass'),'red');
            }
        );
        //Проверяем новые пароли
        if (empty($password['new'][0]) || empty($password['new'][1]))
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','nonNewPass'),'red');
        //Если пароли не совпадают
        if ($password['new'][0] !== $password['new'][1])
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','passNotEqual'),'red');
        //Если новый пароль не отдичается от старого
        if ($password['new'][0] === $password['old'])
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','nonOldPass'),'red');
        //Записываем новый пароль
        $userData['password'] = md5($password['new'][0].\App\Config::USER_PASSWORD_HASH);
    }

    /**
     * Загрузка аватара на сервер
     * @param array $data
     * @param array $userData
     */
    private function uploadAvatar(array $data,array &$userData) {
        //Записываем значение аватарки для нового пользователя
        if ($this->userId === null) $userData['avatar'] = '';
        //Если надо удалить аватарку
        if (array_key_exists('avatar',$data) && $data['avatar'] === 'delete') {
            //Стираем значение аватарки
            $userData['avatar'] = '';
            //Удаляем изображение
            $this->deleteAvatar();
            return;
        }
        //Если не загружают изображение
        if (empty($_FILES['avatar'])) return;
        //Проверка файла на наличие
        if (empty($_FILES['avatar']['tmp_name']) || !empty($_FILES['avatar']['error']))
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','failUploadAvatar'),'red');
        //Если размер файла привышает 500kb
        if ($_FILES['avatar']['size'] > 524288 || filesize($_FILES['avatar']['tmp_name']) > 524288) {
            //Удаляем файл
            unlink($_FILES['avatar']['tmp_name']);
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','checkSizeAvatar'),'red');
        }
        //проверяем расширение файла
        $ext = mb_strtolower(pathinfo($_FILES['avatar']['name'],PATHINFO_EXTENSION));
        //Тип файла
        $fileData = getimagesize($_FILES['avatar']['tmp_name']);
        //Если недопустимое раширение
        if (!in_array($ext,['jpg','jpeg','png','gif']) || !in_array($fileData['mime'],['image/jpeg','image/png','image/gif'])) {
            //Удаляем файл
            unlink($_FILES['avatar']['tmp_name']);
            \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','checkTypeAvatar'),'red');
        }
        //Название файла
        $fileName = md5('image'.file_get_contents($_FILES['avatar']['tmp_name'])).'.'.$ext;
        //Если такого файла то загружаем файл на сервер
        if (!file_exists(\App\Config::PATH['avatar'].$fileName)) {
            //Загружаем файл и проверяем загрузку
            if (move_uploaded_file($_FILES['avatar']['tmp_name'],\App\Config::PATH['avatar'].$fileName) === false) {
                unlink($_FILES['avatar']['tmp_name']);
                \App\Helper::alert('error',\App\Language::getAlerts('profile','profile','failUploadAvatar'),'red');
            }
            //Если был пользователь то проверяем была ли предыдщая аватарка
            if ($this->userId !== null) $this->deleteAvatar();
        } else {
            //Если файл повторно не загружали
            unlink($_FILES['avatar']['tmp_name']);
        }
        //Меняем права на автарку
        chmod(\App\Config::PATH['avatar'].$fileName,0777);
        //Записываем название аватарки
        $userData['avatar'] = $fileName;
    }

    /**
     * Удаление аватарки с сервера
     */
    private function deleteAvatar() {
        //Получаем название файла аватарки
        $avatar = self::queryMySQL(
            "SELECT avatar FROM tr_logic.users WHERE id={$this->userId}",
            null,
            function ($result) {
                //проверяем установлена ли аватарка и есть ли файл
                return !empty($result[0]['avatar']) && file_exists(\App\Config::PATH['avatar'].$result[0]['avatar']) ? $result[0]['avatar'] : null;
            }
        );
        //Нет аватарки
        if ($avatar === null) return;
        //Проверяем используется ли изображение удругих пользователей (теоретически при загрузке изображений из инете такое возможо,
        //с оченьмаленькой вероятностью)
        $total = (int)self::queryMySQL(
            "SELECT COUNT(*) AS total FROM tr_logic.users WHERE id<>{$this->userId} AND avatar='{$avatar}'"
        )[0]['total'];
        //Если изображение используется
        if ($total !== 0) return;
        //Удаляем изображение
        unlink(\App\Config::PATH['avatar'].$avatar);
    }

    /**
     * Вывод аватарки пользователю
     */
    public static function geUserAvatar() {
        //Если пользователь не авторизирован
        //Или пользователь решил удалить аватрку
        if (empty($_SESSION['userId']) || !empty($_GET['delete'])) {
            //Ставим аватарку по умолчанию
            $avatar = \App\Config::PATH['default_avatar'];
        } else {
            //Ищем аватарку пользователя
            $avatar = self::queryMySQL(
                "SELECT avatar FROM users WHERE id={$_SESSION['userId']}",
                null,
                function ($result) {
                    //Проверяем есть ли фото пользователя на сервере
                    return !empty($result[0]['avatar']) && file_exists(\App\Config::PATH['avatar'].$result[0]['avatar']) ?
                        \App\Config::PATH['avatar'].$result[0]['avatar'] :  \App\Config::PATH['default_avatar'];
                }
            );
        }
        //Считываем MIME- тип
        $fileData = getimagesize($avatar);
        //заголовки изображения
        header('Content-Length: '.filesize($avatar));
        header('Content-Type: '.$fileData['mime']);
        die(
            file_get_contents($avatar)
        );
    }

    /**
     * Восстановление пароля
     * @param array $data - входные данные
     *
     */
    private function forgotPassword(array $data) {
        //Валидация e-mail и поиск пользователя
        $userCheck = self::checkEmail($data);
        //Если нет пользователя
        if ($userCheck['user'] === null)
            \App\Helper::alert('error',\App\Language::getAlerts('profile','forgot','nonEmail'),'red');
        //Генерируем новыый временный пароль
        $password = self::generateUserPassword();
        //Записываем пароль (срок жизни - 30 минут)
        self::queryMySQL("UPDATE users SET tempPass='{$password['hash']}',tempPassTime=".(time()+1800)." WHERE id={$userCheck['user']['id']}");
        //Отправляем сообщение
        //Получаем текст сообщения
        $textMail = \App\Helper::requireInBuffer(
            \App\Config::PATH['mail'].'mail_forgot_password_'.\App\Language::get(),
            [
                'firstName' => $userCheck['user']['firstName'],
                'email' => $userCheck['email'],
                'password' => $password['password'],
                'site' => \App\Config::HOST
            ]
        );
        //Разбиваем текст на html верстку и альтернативное содержание
        $textMail = explode('ALTERNATIVE',$textMail);
        //отправляем сообщение
        \App\Email\Send::sendMailSMTP(
            $userCheck['email'],
            \App\Language::getAlerts('profile','forgot','mailSubject'),
            [
                [
                    'content-type' => 'text/html',
                    'body' => $textMail[0],
                    'alternative' => $textMail[1]
                ]
            ]
        );
        \App\Helper::alert('success',\App\Language::getAlerts('profile','forgot','success'),'default',['hash' => 'auth']);
    }

    /**
     * Генерация пароля для пользователя
     * @return array
     */
    private static function generateUserPassword() {
        //Пароль для пользоватея
        $password = mb_substr(uniqid(),3,8);
        return [
            //Пароль для пользователя
            'password' => $password,
            //Хэш для сохранения в БД
            'hash' => md5($password.\App\Config::USER_PASSWORD_HASH)
        ];
    }

    /**
     * Авторизация пользователя в системе
     * @param array $data
     */
    private function userAuth(array $data) {
        //Валидируем e-mail и ищем пользователя
        $user = self::checkEmail($data)['user'];
        //Если не нашли пользователя
        if ($user === null)
            \App\Helper::alert('error',\App\Language::getAlerts('profile','auth','incorrectAuthData'),'red');
        //преобразуем пароль
        $password = md5($data['password'].\App\Config::USER_PASSWORD_HASH);
        //Сверяем сначала временные пароли (если пользователь их восстанавливал)
        if ($password === $user['tempPass'] && $user['tempPassTime'] >= time()-1800) {
            //Записываем пароль
            $user['pass'] = $password;
        }
        //Если пароль неверный
        if ($user['pass'] !== $password)
            \App\Helper::alert('error',\App\Language::getAlerts('profile','auth','incorrectAuthData'),'red');
        //Генерируем хэш авторизации
        $hash = md5($user['id'].$user['pass'].uniqid());
        //Обновляем данные по последней авторизации
        $date = date('Y-m-d H:i:s');
        $ip = ip2long($_SERVER['REMOTE_ADDR']);
        self::queryMySQL("UPDATE users SET tempPassTime=0,tempPass='',pass='{$user['pass']}',lastAuth='{$date}' WHERE id={$user['id']}");
        //Записываем хэш
        self::queryMySQL("INSERT INTO userAuth(hash, userId, lastIp, lastAction) VALUES ('{$hash}',{$user['id']},{$ip},'{$date}')");
        //Записываем хэш в куки
        self::setUserHashInCookie($hash);
        //Перебрасываем пользователя в профиль
        \App\Helper::result(['hash' => 'profile']);
    }

    /**
     * Выход пользователя из личного кабинета
     */
    private function userLogOut() {
        //Удаляем данные авторизации из БД
        self::queryMySQL("DELETE FROM userAuth WHERE userId={$this->userId} AND hash='{$_COOKIE['auth']}'");
        //Удаляем куки/сессию
        self::setUserHashInCookie();
        //Уходим на окно авторизации
        \App\Helper::result(['hash' => 'auth']);
    }
}