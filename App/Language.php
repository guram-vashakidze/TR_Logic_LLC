<?php
namespace App;

/**
 * Trait Language
 * Языковой словарь для шаблонов
 * @package App
 */
trait Language {

    /**@var string $language - Языка шаблона*/
    public static $language = null;

    private static $dictionary = [
        'RU' => [
            'global' => [
                'header' => 'Тестовое задание | TR Logic LLC',
                'footer' => 'Язык',
                'buttons' => [
                    'logOut' => 'Выйти'
                ],
                'alerts' => [
                    'default' => 'Неудалось выполнить действие. Попробуйте позже'
                ]
            ],
            'profile' => [
                'auth' => [
                    'title' => 'Авторизация | Тестовое задание | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Укажите E-mail для входа в систему',
                                'placeholder' => 'example@email.com'
                            ],
                            'password' => [
                                'name' => 'Пароль',
                                'placeholder' => 'Пароль'
                            ]
                        ],
                        'buttons' => [
                            'authIn' => 'Войти',
                            'authReg' => 'Регистрация',
                            'authForgot' => 'Забыли?'
                        ]
                    ],
                    'alerts' => [
                        'notPass' => 'Укажите ваш пароль для входа',
                        'incorrectEmail' => 'Укажите корректный адрес электронной почты',
                        'incorrectAuthData' => 'Неверный Email или пароль'
                    ]
                ],
                'forgot' => [
                    'title' => 'Восстановление пароля | Тестовое задание | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Укажите E-mail для которого необходимо восстановить пароль',
                                'placeholder' => 'example@email.com'
                            ]
                        ],
                        'buttons' => [
                            'forgotPass' => 'Восстановить',
                            'backAuth' => 'Назад'
                        ]
                    ],
                    'alerts' => [
                        'incorrectEmail' => 'Укажите корректный адрес электронной почты',
                        'nonEmail' => 'Указанный адрес электронной почты не зарегестрирован в системе',
                        'success' => 'Дальнейшие инструкции по восстановлению пароля отправлены на указанный адрес электронной почты',
                        'mailSubject' => 'Восстановление пароля'
                    ]
                ],
                'registration' => [
                    'title' => 'Регистрация | Тестовое задание | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Укажите ваш E-mail',
                                'placeholder' => 'example@email.com'
                            ],
                            'lastName' => [
                                'name' => 'Фамилия',
                                'placeholder' => 'Иванов'
                            ],
                            'firstName' => [
                                'name' => 'Имя',
                                'placeholder' => 'Иван'
                            ],
                            'phone' => [
                                'name' => 'Телефон'
                            ],
                            'bDate' => [
                                'name' => 'Дата рождения'
                            ]
                        ],
                        'buttons' => [
                            'backAuth' => 'Назад',
                            'saveProfile' => 'Регистрация',
                            'addAvatar' => 'Загрузить аватар',
                            'deleteAvatar' => 'Удалить аватар',
                            'undoAvatar' => 'Отменить добавление аватара',
                            'redoAvatar' => 'Вернуть аватар'
                        ]
                    ],
                    'headings' => [
                        'formName' => 'Регистрация',
                        'requiredFields' => 'обязательные поля'
                    ],
                    'alerts' => [
                        'incorrectEmail' => 'Укажите корректный адрес электронной почты',
                        'checkLastName' => 'Указана некорректная фамилия',
                        'checkFirstName' => 'Указано некорректное имя',
                        'checkPhone' => 'Укажите корректный номер телефона',
                        'checkBDate' => 'Укажите корректную дату рождения',
                        'checkSizeAvatar' => 'Размер файла изображения не должен превышать 500Кb',
                        'success' => 'Вы успешно зарегистрировались. Пароль для входа в личный кабинет отправлен Вам на почту',
                        'mailSubject' => 'Завершение регистрации'
                    ]
                ],
                'profile' => [
                    'title' => 'Профиль пользователя | Тестовое задание | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Укажите ваш E-mail',
                                'placeholder' => 'example@email.com'
                            ],
                            'lastName' => [
                                'name' => 'Фамилия',
                                'placeholder' => 'Иванов'
                            ],
                            'firstName' => [
                                'name' => 'Имя',
                                'placeholder' => 'Иван'
                            ],
                            'phone' => [
                                'name' => 'Телефон'
                            ],
                            'bDate' => [
                                'name' => 'Дата рождения'
                            ],
                            'password' => [
                                'name' => 'Старый пароль',
                                'placeholder' => 'Старый пароль'
                            ],
                            'password1' => [
                                'name' => 'Новый пароль',
                                'placeholder' => 'Новый пароль'
                            ],
                            'password2' => [
                                'name' => 'Повторите новый пароль',
                                'placeholder' => 'Повторите новый пароль'
                            ]
                        ],
                        'buttons' => [
                            'backAuth' => 'Назад',
                            'saveProfile' => 'Сохранить',
                            'addAvatar' => 'Загрузить аватар',
                            'deleteAvatar' => 'Удалить аватар',
                            'undoAvatar' => 'Отменить добавление аватара',
                            'redoAvatar' => 'Вернуть аватар'
                        ]
                    ],
                    'headings' => [
                        'formName' => 'Ваш профиль',
                        'passForm' => 'Изменить пароль',
                        'requiredFields' => 'обязательные поля'
                    ],
                    'alerts' => [
                        'incorrectEmail' => 'Укажите корректный адрес электронной почты',
                        'duplicateEmail' => 'Пользователь с таким Email уже зарегистрирован',
                        'incorrectPass' => 'Указан неверный пароль',
                        'nonNewPass' => 'Укажите новый пароль',
                        'passNotEqual' => 'Пароли не совпадают',
                        'nonOldPass' => 'Новый пароль должен отличатся от уже установленного',
                        'checkLastName' => 'Указана некорректная фамилия',
                        'checkFirstName' => 'Указано некорректное имя',
                        'checkPhone' => 'Укажите корректный номер телефона',
                        'checkBDate' => 'Укажите корректную дату рождения',
                        'checkSizeAvatar' => 'Размер файла изображения не должен превышать 500Кb',
                        'failUploadAvatar' => 'Неудалось загрузить аватар',
                        'checkTypeAvatar' => 'Недопустимое расширение файла изображения товара. Допустимые типы: .jpg, .png, .gif',
                        'success' => 'Данные успешно обновлены'
                    ]
                ]
            ]
        ],
        'EN' => [
            'global' => [
                'header' => 'Test Task | TR Logic LLC',
                'footer' => 'Language',
                'buttons' => [
                    'logOut' => 'Logout'
                ],
                'alerts' => [
                    'default' => 'Failed to complete the action. Try later'
                ]
            ],
            'profile' => [
                'auth' => [
                    'title' => 'Login | Test Task | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Enter E-mail to Log In',
                                'placeholder' => 'example@email.com'
                            ],
                            'password' => [
                                'name' => 'Password',
                                'placeholder' => 'Password'
                            ]
                        ],
                        'buttons' => [
                            'authIn' => 'Log In',
                            'authReg' => 'Registration',
                            'authForgot' => 'Forgot?'
                        ]
                    ],
                    'alerts' => [
                        'notPass' => 'Enter your login password',
                        'incorrectEmail' => 'Please enter a valid email address',
                        'incorrectAuthData' => 'Invalid Email or Password'
                    ]
                ],
                'forgot' => [
                    'title' => 'Restore password | Test Task | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Enter the email address for which you want to recover the password',
                                'placeholder' => 'example@email.com'
                            ]
                        ],
                        'buttons' => [
                            'forgotPass' => 'Restore',
                            'backAuth' => 'Back'
                        ]
                    ],
                    'alerts' => [
                        'incorrectEmail' => 'Please enter a valid email address',
                        'nonEmail' => 'The specified email address is not registered in the system',
                        'success' => 'Further password recovery instructions have been sent to the specified email address.',
                        'mailSubject' => 'Restore password'
                    ]
                ],
                'registration' => [
                    'title' => 'Registration | Test Task | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Enter your Email',
                                'placeholder' => 'example@email.com'
                            ],
                            'lastName' => [
                                'name' => 'Last Name',
                                'placeholder' => 'Ivanov'
                            ],
                            'firstName' => [
                                'name' => 'First Name',
                                'placeholder' => 'Ivan'
                            ],
                            'phone' => [
                                'name' => 'Phone'
                            ],
                            'bDate' => [
                                'name' => 'Date of Birth'
                            ]
                        ],
                        'buttons' => [
                            'backAuth' => 'Back',
                            'saveProfile' => 'Registration',
                            'addAvatar' => 'Upload avatar',
                            'deleteAvatar' => 'Delete avatar',
                            'undoAvatar' => 'Cancel add avatar',
                            'redoAvatar' => 'Return avatar'
                        ]
                    ],
                    'headings' => [
                        'formName' => 'Registration',
                        'requiredFields' => 'required fields'
                    ],
                    'alerts' => [
                        'incorrectEmail' => 'Please enter a valid email address',
                        'checkLastName' => 'Invalid last name specified',
                        'checkFirstName' => 'Invalid first name specified',
                        'checkPhone' => 'Please enter a valid phone number',
                        'checkBDate' => 'Please enter a valid date of birth',
                        'checkSizeAvatar' => 'Image file size must not exceed 500Kb',
                        'success' => 'You have successfully registered. The password to enter your personal account has been sent to you by mail',
                        'mailSubject' => 'Registration Completion'
                    ]
                ],
                'profile' => [
                    'title' => 'Profile | Test Task | TR Logic LLC',
                    'form' => [
                        'inputs' => [
                            'email' => [
                                'name' => 'E-mail',
                                'label' => 'Enter your Email',
                                'placeholder' => 'example@email.com'
                            ],
                            'lastName' => [
                                'name' => 'Last Name',
                                'placeholder' => 'Ivanov'
                            ],
                            'firstName' => [
                                'name' => 'First Name',
                                'placeholder' => 'Ivan'
                            ],
                            'phone' => [
                                'name' => 'Phone'
                            ],
                            'bDate' => [
                                'name' => 'Date of Birth'
                            ],
                            'password' => [
                                'name' => 'Old password',
                                'placeholder' => 'Old password'
                            ],
                            'password1' => [
                                'name' => 'New password',
                                'placeholder' => 'New password'
                            ],
                            'password2' => [
                                'name' => 'Repeat new password',
                                'placeholder' => 'Repeat new password'
                            ]
                        ],
                        'buttons' => [
                            'backAuth' => 'Back',
                            'saveProfile' => 'Save',
                            'addAvatar' => 'Upload avatar',
                            'deleteAvatar' => 'Delete avatar',
                            'undoAvatar' => 'Cancel add avatar',
                            'redoAvatar' => 'Return avatar'
                        ]
                    ],
                    'headings' => [
                        'formName' => 'Profile',
                        'passForm' => 'Change password',
                        'requiredFields' => 'required fields'
                    ],
                    'alerts' => [
                        'incorrectEmail' => 'Please enter a valid email address',
                        'duplicateEmail' => 'A user with this Email is already registered',
                        'incorrectPass' => 'Invalid password specified',
                        'nonNewPass' => 'Enter a new password',
                        'passNotEqual' => 'Passwords do not match',
                        'nonOldPass' => 'The new password must be different from the one already set.',
                        'checkLastName' => 'Invalid last name specified',
                        'checkFirstName' => 'Invalid first name specified',
                        'checkPhone' => 'Please enter a valid phone number',
                        'checkBDate' => 'Please enter a valid date of birth',
                        'checkSizeAvatar' => 'Image file size must not exceed 500Kb',
                        'failUploadAvatar' => 'Failed to upload avatar',
                        'checkTypeAvatar' => 'Invalid product image file extension. Allowed types: .jpg, .png, .gif',
                        'success' => 'Data updated successfully'
                    ]
                ]
            ]
        ]
    ];

    /**
     * Устновка язвка
     * @param string $acceptLanguage - заголовок Accept-Language
     */
    public static function setLanguage($acceptLanguage) {
        //Если язык интерфейса не задан
        if (empty($_COOKIE['language']) || !in_array($_COOKIE['language'],['RU','EN'])) {
            //Определяем приоритет языка из браузера
            $language = preg_match("/^ru/ui",$acceptLanguage) ? 'RU' : 'EN';
        } else {
            $language = $_COOKIE['language'];
        }
        //Выкидываем заголовок установленного языка
        header('User-Language: '.$language);
        //Записываем в куку значение языка
        setcookie('language', $language,time()+365*86400, '/', '.'.\App\Config::HOST);
        self::$language = $language;
    }

    /**
     * Получение конкртного значения из словаря в зависимости от языка
     * @param string $item - запрашиваемый элемент
     * @param string $subItem - вложенный элемент
     * @return string|array
     */
    public static function dictionary($item,$subItem = null) {
        $elem = self::$dictionary[self::$language][$item];
        //Если есть под элемент
        $elem = $subItem === null ? $elem : $elem[$subItem];
        //Если запрашиваются глобальные настройки
        if ($item === 'global') return $elem;
        return [
            'tpl' => $elem,
            'global' => self::$dictionary[self::$language]['global']
        ];
    }

    /**
     * Получение Алертов по конкретному шаблону
     * @param string $item - контроллер
     * @param string $subItem - подпункт
     * @param string $alertName - вызываемый алерт
     * @return string
     */
    public static function getAlerts($item,$subItem,$alertName) {
        return self::$dictionary[self::$language][$item][$subItem]['alerts'][$alertName];
    }

    /**
     * Получение установленного языка
     * @return string
     */
    public static function get() {
        return self::$language;
    }
}