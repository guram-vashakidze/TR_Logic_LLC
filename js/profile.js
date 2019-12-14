var Profile = (function (Lib) {
    function Profile(event,data) {
        //Вызываем необходимое событие
        this[event](data);
    }

    /**
     * Инициализация формы авторизации
     */
    Profile.prototype.initAuthForm = function () {
        //Словарь формы
        let dict = _Template.getDictionary();
        //Созздание формы
        Lib.c(
            'form',
            {
                html: '<div class="form-group">' +
                    '<label for="email">'+dict.form.inputs.email.name+'</label>' +
                    '<input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="'+dict.form.inputs.email.placeholder+'">' +
                    '<small id="emailHelp" class="form-text text-muted">'+dict.form.inputs.email.label+'</small>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<label for="password">'+dict.form.inputs.password.name+'</label> <small class="btn btn-link btn-sm auth-forgot">'+dict.form.buttons.authForgot+'</small>' +
                    '<input type="password" class="form-control" id="password" placeholder="'+dict.form.inputs.password.placeholder+'">' +
                    '</div>' +
                    '<button type="button" class="btn btn-primary auth-in">'+dict.form.buttons.authIn+'</button> ' +
                    '<button type="button" class="btn btn-outline-primary auth-reg">'+dict.form.buttons.authReg+'</button>',
                element: Lib.cls('content-body')
            }
        );
        //Установка событий на форму
        this.initEventAuthForm();
    };

    /**
     * Инициализация событий на форме авторизации
     */
    Profile.prototype.initEventAuthForm = function () {
        //Событие на кнопку восстановления пароля
        Lib.onClick(
            'auth-forgot',
            function (button) {
                //Перевызываем шаблон
                _Template.getHTML('forgot');
            }
        );
        //Событие на кнопку регистрации
        Lib.onClick(
            'auth-reg',
            function (button) {
                //Вызываем необходимый шаблон
                _Template.getHTML('registration');
            }
        );
        //Авторизация
        Lib.onClick(
            'auth-in',
            function () {
                //Считывание данных авторизации
                let email = Profile.getEmailFromForm(),
                    password = Lib.id('password').value;
                //Если email не валиден
                if (email === false) return;
                //Если нет пароля
                if (!password)
                    return Lib.showAlert(_Template.getDictionary().alerts.notPass,'red');
                Lib.send({
                    query: {
                        email: email,
                        password: password
                    },
                    headers: {
                        Method: 'USER_AUTH',
                        Template: _Template.getHash()
                    },
                    func: function(result) {
                        //Переход на нужную страницу
                        _Template.getHTML(result.hash);
                    }
                })
            }
        )
    };

    /**
     * Считывание e-mail с формы и валидация
     * @return {string|boolean}
     */
    Profile.getEmailFromForm = function() {
        let email = Lib.id('email').value;
        //Валидируем емаейл
        if (!email.match(/^.+@.+\..+$/)) return Lib.showAlert(_Template.getDictionary().alerts.incorrectEmail,'red');
        return email;
    };

    /**
     * Инициализация формы восстаовления пароля
     */
    Profile.prototype.initForgotForm = function () {
        //Словарь формы
        let dict = _Template.getDictionary();
        //Созздание формы
        Lib.c(
            'form',
            {
                html: '<div class="form-group">' +
                    '<label for="email"><i class="material-icons tooltips-block back-auth" data-tooltips="'+dict.form.buttons.backAuth+'" style="vertical-align: -6px">keyboard_backspace</i> '+dict.form.inputs.email.name+'</label>' +
                    '<input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="'+dict.form.inputs.email.placeholder+'">' +
                    '<small id="emailHelp" class="form-text text-muted">'+dict.form.inputs.email.label+'</small>' +
                    '</div>' +
                    '<button type="button" class="btn btn-primary forgot-pass">'+dict.form.buttons.forgotPass+'</button> ',
                element: Lib.cls('content-body')
            }
        );
        //Установка событий на форму
        this.initEventForgotForm();
    };

    /**
     * События для формы восстановления пароля
     */
    Profile.prototype.initEventForgotForm = function () {
        //Инициализируем кнопку возврата
        this.backAuth();
        //Восстановление пароля
        Lib.onClick(
            'forgot-pass',
            function () {
                Lib.loading();
                //Считывание email из формы
                let email = Profile.getEmailFromForm();
                if (email === false) return;
                Lib.send({
                    query: {email: email},
                    headers: {
                        Method: 'FORGOT_PASSWORD',
                        Template: _Template.getHash()
                    },
                    func: function (result) {
                        //Выводим сообщение
                        Lib.showAlert(result.success.msg,result.success.cls,5);
                        //выводим нужный шаблон
                        _Template.getHTML(result.hash);
                    }
                });
            }
        );
    };

    /**
     * Инициализация события на кнопке возврата к авторизации
     */
    Profile.prototype.backAuth = function() {
        Lib.onClick(
            'back-auth',
            function () {
                _Template.getHTML('auth');
            }
        );
    };

    /**
     * Открытие формы регистрации/обновления профиля пользователя
     * @param {{email: string,lastName: string,firstName: string,bDate:string,avatar: string,phone: string}}profile
     */
    Profile.prototype.initProfileForm = function (profile = null) {
        let dict = _Template.getDictionary(),
            form = '<h2>'+dict.headings.formName+'</h2>' +
            '<div class="row"><div class="col-md-8">' +
            '<form>';
        //Поле для ввода email
        if (profile) {
            form += '<div class="form-group">' +
                '<label for="email">'+dict.form.inputs.email.name+'</label>' +
                '<input type="email" readonly class="form-control" id="email" aria-describedby="emailHelp" placeholder="'+dict.form.inputs.email.placeholder+'" value="'+profile.email+'">' +
                '</div>';
        } else {
            form +=
                '<div class="form-group">' +
                '<label for="email"><i class="material-icons tooltips-block back-auth" data-tooltips="'+dict.form.buttons.backAuth+'" style="vertical-align: -6px">keyboard_backspace</i> '+dict.form.inputs.email.name+' *</label>' +
                '<input type="email" class="form-control" id="email" aria-describedby="emailHelp" placeholder="'+dict.form.inputs.email.placeholder+'">' +
                '<small id="emailHelp" class="form-text text-muted">'+dict.form.inputs.email.label+'</small>' +
                '</div>';
        }
        form += '<div class="form-group">' +
            '<label for="lastName">'+dict.form.inputs.lastName.name+' *</label>' +
            '<input type="text" class="form-control" id="lastName" placeholder="'+dict.form.inputs.lastName.placeholder+'" value="'+(profile ? profile.lastName : '')+'">' +
            '</div>'+
            '<div class="form-group">' +
            '<label for="firstName">'+dict.form.inputs.firstName.name+' *</label>' +
            '<input type="text" class="form-control" id="firstName" placeholder="'+dict.form.inputs.firstName.placeholder+'" value="'+(profile ? profile.firstName : '')+'">' +
            '</div>'+
            '<div class="form-group">' +
            '<label for="phone">'+dict.form.inputs.phone.name+'</label>' +
            '<input type="tel" class="form-control" id="phone" placeholder="+380435678907" value="'+(profile ? profile.phone : '')+'">' +
            '</div>'+
            '<div class="form-group">' +
            '<label for="bDate">'+dict.form.inputs.bDate.name+'</label>' +
            '<input type="date" class="form-control" id="bDate" placeholder="01.01.2000" value="'+(profile ? profile.bDate : '')+'">' +
            '</div>';
        if (profile) {
            form += '<h5>'+dict.headings.passForm+'</h5>'+
            '<div class="form-group">' +
            '<label for="password">'+dict.form.inputs.password.name+'</label>' +
            '<input type="password" class="form-control" id="password" placeholder="'+dict.form.inputs.password.placeholder+'">' +
            '</div>'+
            '<div class="form-group">' +
            '<label for="password1">'+dict.form.inputs.password1.name+'</label>' +
            '<input type="password" class="form-control" id="password1" placeholder="'+dict.form.inputs.password1.placeholder+'">' +
            '</div>'+
            '<div class="form-group">' +
            '<label for="password2">'+dict.form.inputs.password2.name+'</label>' +
            '<input type="password" class="form-control" id="password2" placeholder="'+dict.form.inputs.password2.placeholder+'">' +
            '</div>'
        }
        form += '<small>* - '+dict.headings.requiredFields+'</small><br>' +
                '<button type="button" class="btn btn-primary save-profile">'+dict.form.buttons.saveProfile+'</button></form></div>' +
                '<div class="col-md-4">' +
                    '<img class="avatar" data-avatar="'+(profile ? profile.avatar : '0')+'" src="/images/avatar?'+Lib.random()+'"/>' +
                    '<div class="avatar-block tooltips-block" data-tooltips="'+dict.form.buttons.addAvatar+' (.jpg,.gif,.png)">' +
                        '<i class="material-icons" style="font-size: 100px; color: white; z-index: 9; opacity: 1">add_a_photo</i>' +
                    '</div>' +
                '<br>' +
                '<div class="avatar-buttons">' +
                    '<i class="material-icons tooltips-block undo-avatar" data-tooltips="'+dict.form.buttons.undoAvatar+'">undo</i>' +
                    '<i class="material-icons tooltips-block redo-avatar" data-tooltips="'+dict.form.buttons.redoAvatar+'">redo</i>' +
                    '<i class="material-icons tooltips-block delete-avatar" data-tooltips="'+dict.form.buttons.deleteAvatar+'">delete</i>' +
                '</div>' +
                '<input type="file" class="avatar-input" data-delete="0" style="display: none" accept="image/gif, image/jpeg, image/png">' +
            '</div>';
        Lib.c(
            'div',
            {
                html: form,
                element: Lib.cls('content-body')
            }
        );
        //События на форму
        this.initEventProfileForm(dict);
    };

    /**
     * Инициализация событий на форме регистрации/обновления профиля
     */
    Profile.prototype.initEventProfileForm = function(dict) {
        //Кнопка сохранения данных
        Lib.onClick(
            'save-profile',
            function () {
                Lib.loading();
                let email = Profile.getEmailFromForm(),
                    lastName = Lib.id('lastName').value,
                    firstName = Lib.id('firstName').value,
                    phone = Lib.id('phone').value,
                    bDate = Lib.id('bDate').value,
                    avatar = Lib.cls('avatar-input'),
                    formData = new FormData();
                //Если регистрация
                if (_Template.getHash() === 'registration') {
                    //Валидируем емаейл
                    if (email === false) return;
                    //Добавляем данные для отправки
                    formData.append('email',email);
                } else  {
                    //Иначе проверяем есть ли пароли
                    let password = {
                        old: Lib.id('password').value,
                        new: [
                            Lib.id('password1').value,
                            Lib.id('password2').value,
                        ]
                    };
                    //Если пользователь решил изменить пароль
                    if (password.old) {
                        if (!password.new[0])
                            return Lib.showAlert(dict.alerts.nonNewPass,'red');
                        if (password.new[0] !== password.new[1])
                            return Lib.showAlert(dict.alerts.passNotEqual,'red');
                        if (password.old === password.new[0])
                            return Lib.showAlert(dict.alerts.nonOldPass,'red');
                    }
                    //Добавляем данные для отправки
                    formData.append('password',JSON.stringify(password));

                }
                //Валидация имени
                if (!lastName.match(/^[a-zа-яё]+$/gim))
                    return Lib.showAlert(dict.alerts.checkLastName,'red');
                if (!firstName.match(/^[a-zа-яё]+$/gim))
                    return Lib.showAlert(dict.alerts.checkFirstName,'red');
                //Если указан номер телефона
                if (phone && !phone.match(/^\+?[0-9]{10,}$/))
                    return Lib.showAlert(dict.alerts.checkPhone,'red');
                //Если указана дата рождения
                if (bDate && !bDate.match(/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/))
                    return Lib.showAlert(dict.alerts.checkBDate,'red');
                formData.append('firstName',firstName);
                formData.append('lastName',lastName);
                formData.append('phone',phone);
                formData.append('bDate',bDate);
                //Проверка наличия аватарки
                if (avatar.dataset.upload === '1' && avatar.files && avatar.files[0]) {
                    //Добавляем аватарку
                    formData.append('avatar',avatar.files[0]);
                    //Если надо удалить аватарку
                } else if (avatar.dataset.delete === '1') {
                    formData.append('avatar','delete');
                }
                //Отправляем запрос
                Lib.send({
                    formData: formData,
                    headers: {
                        Method: 'SAVE_PROFILE',
                        Template: _Template.getHash()
                    },
                    func: function (result) {
                        //Выводим сообщение
                        Lib.showAlert(result.success.msg,result.success.cls,5);
                        //выводим нужный шаблон
                        _Template.getHTML(result.hash);
                    }
                })
            }
        );
        //Событие на навидение мышки на блок аватаруи
        Lib.addListener(
            'avatar',
            'mousemove',
            function () {
                Lib.cls('avatar-block').style.visibility = 'visible';
            }
        );
        //Событие на навидение мышки на блок аватаруи
        Lib.addListener(
            'avatar-block',
            'mousemove',
            function (e,block) {
                block.style.visibility = 'visible';
            }
        );
        //Инициализация кнопок управления аватарками
        this.initAvatarButtons();
        //Инициализируем кнопку возврата
        this.backAuth();
    };

    /**
     * Инициализация кнопок управления аватарками
     */
    Profile.prototype.initAvatarButtons = function() {
        //Кнопка удаления аватара
        let deleteAvatar = Lib.cls('delete-avatar'),
            //кнопка вернуть аватар после удаления
            redoAvatar = Lib.cls('redo-avatar'),
            //Кнопку отменить добавление аватарки
            undoAvatar = Lib.cls('undo-avatar'),
            //IMG элемент с аватаром
            avatar = Lib.cls('avatar'),
            //Инпут загрузки аватарки
            avatarInput = Lib.cls('avatar-input');
        //Если установлен аватар в профиле, то показываем кнопку удаления
        if (avatar.dataset.avatar === '1') deleteAvatar.style.display = 'block';
        //Событие на удаление аватарки
        Lib.onClick(
            deleteAvatar,
            function (e,button) {
                //Вешаем отметку что удаляем аватарку
                avatarInput.dataset.delete = '1';
                //отмечаем что ничего не загружено
                avatarInput.dataset.upload = '0';
                //Меням изображение
                avatar.src = '/images/avatar?delete='+Lib.random();
                //Скрываем текущую кнопку
                button.style.display = 'none';
                //Показываем кнопку вернуть аватар
                redoAvatar.style.display = 'block';
            }
        );
        //Событие на возврат аватарки
        Lib.onClick(
            redoAvatar,
            function (e, button) {
                //Вешаем отметку что вернули аватарку
                avatarInput.dataset.delete = '0';
                //Меням изображение
                avatar.src = '/images/avatar?'+Lib.random();
                //Скрываем текущую кнопку
                button.style.display = 'none';
                //Показываем кнопку удалить аватар
                deleteAvatar.style.display = 'block';
            }
        );
        //Событие на возврат аватарки (после зхагрузки новой)
        Lib.onClick(
            undoAvatar,
            function (e, button) {
                //Возвращаем аватарку
                avatar.src = '/images/avatar?'+Lib.random();
                //Снимаем отметку что аватарка удалялась
                avatarInput.dataset.delete = '0';
                //Снимаем отметку что аватарка загружаласт
                avatarInput.dataset.upload = '0';
                //скрываем текущую кнопку
                button.style.display = 'none';
                //Показываем кнопку удалить если нужно
                if (avatar.dataset.avatar === '1') deleteAvatar.style.display = 'block';
            }
        );
        //Событие на увод мышки с аватарки
        Lib.addListener(
            'avatar-block',
            'mouseout',
            function (e,block) {
                block.style.visibility = 'hidden';
            }
        );
        //Клик по загрузке автарки
        Lib.onClick(
            'avatar-block',
            function () {
                //Вызываем событие добавления изобращения
                avatarInput.click();
            }
        );
        //Изменение инпута файла
        Lib.addListener(
            'avatar-input',
            'change',
            function (e,button) {
                //Блок аватарки
                let file = button.files[0],
                    size = file.size / 1024;
                //Отметка что аватарка не загружена
                avatar.dataset.upload = 'false';
                //Если изображение много "весит"
                if (size > 500) return Profile.errorAvatar(dict.alerts.checkSizeAvatar,button,avatar);
                //Проверяем изображение
                let reader  = new FileReader();
                reader.onloadend = function () {
                    avatar.src = reader.result.toString();
                    //отмечаем что аватарка загружена
                    avatarInput.dataset.upload = '1';
                    //скрываем удаление
                    avatarInput.dataset.delete = '0';
                    //Показываем кнопку возврата
                    undoAvatar.style.display = 'block';
                    //Скрываем остальные кнопку
                    redoAvatar.style.display = deleteAvatar.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        );
    };

    /**
     * Вывод ошибке по загрузке аватарки
     * @param error
     * @param input
     * @param avatar
     */
    Profile.errorAvatar = function (error, input,avatar) {
        //Удаляем изображение из инпута
        input.value = input.defaultValue;
        delete input.files;
        //Возвращаем изобращение по-умолчанию
        avatar.src = '/images/avatar';
        return Lib.showAlert(error,'red');
    };

    return Profile;
}(Lib));
//Вызываем нужное событие
new Profile(Template.event,Template.data);