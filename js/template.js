let Template = (function (Lib) {
    function Template() {
        //Доступные варианты хэша для подгрузки шаблонов
        this.hashList = [
            'auth',
            'forgot',
            'registration',
            'profile'
        ];
        //список страниц на которых нет кнопки выход
        this.nonLogOut = [
            'auth',
            'forgot',
            'registration'
        ];
        //Указаный хэш в url
        this.checkUrlHash();
        //Поддерживаемые языки
        this.accessLanguage = [
            'RU',
            'EN'
        ];
        //Хост для установки кук
        this.host = null;
        //Установленный язык
        this.language = null;
        //Вызываем шаблон
        this.getHTML();
        //События на элементы браузера
        this.initEventBrowserButton();
    }

    /**
     * Инициализация событий на кнопках "Вперед"/"Назад" в браузере
     */
    Template.prototype.initEventBrowserButton = function() {
        //События на кнопки вперед/назад
        Lib.addListener(
            window,
            'popstate',
            function (e) {
                //Установленый ранее хэш
                let hash = _Template.getHash();
                //Перечитываем хэш
                _Template.checkUrlHash();
                //Если хэш не изменился то ничего не делаем
                if (hash === _Template.getHash()) return;
                //Иначе перезапрашиваем шаблон
                _Template.getHTML();
            }
        )
    };

    /**
     * Обработка hash из url для подгрузки нужного шаблона
     */
    Template.prototype.checkUrlHash = function () {
        //Текущий ХЭШ
        let hash = location.hash.replace(/#/,'');
        //Если нет хэша или указано недоступное значение
        if (!hash || this.hashList.indexOf(hash) === -1) this.hash = this.hashList[0];
        //Иначе выводим значение url
        else this.hash = hash;
    };

    /**
     * Языковой набор
     */
    Template.dictionary = {};

    /**
     * Получение необходимого шаблона
     * @param {string} hash - новый хэш
     */
    Template.prototype.getHTML = function (hash = null) {
        Lib.loading();
        //Если надо установить новый ХЭШ
        if (hash !== null) this.hash = hash;
        Lib.send({
            method: 'GET',
            headers: {
                Method: 'GET_TEMPLATE',
                Template: this.hash
            },
            func: function (response,header) {
                //Очищаем контейнер для вставки данных
                Lib.cls('content-body').innerHTML = '';
                //Набор фраз для шаблона
                Template.dictionary = response.dictionary.tpl;
                //Подключаем внешний клас
                Template.connectOuterClass(response.controller,response.event,(typeof response.tplData !== "undefined" ? response.tplData : null));
                //Устанавливаем хост
                _Template.host = header.default_host;
                //Язык
                _Template.language = header.user_language;
                //Устанавливаем ХЭШ
                Lib.setHash(response.hash);
                //Создаем кнопку выхода если надо
                _Template.initGlobalDictionary(response.dictionary.global,response.dictionary.tpl.title);
                //Отключаем прелоудер
                Lib.loading();
            }
        })
    };

    /**
     * Получение хоста для которого устанавливать куки
     * @return {string}
     */
    Template.prototype.getHost = function() {
        return this.host;
    };

    /**
     * Язык интерфейса
     * @return {string}
     */
    Template.prototype.getLanguage = function() {
        return this.language;
    };

    /**
     * Получение текущего хеша страницы
     * @return {string}
     */
    Template.prototype.getHash = function() {
        return this.hash;
    };

    /**
     * Подгрузка глобальных подписей в шаблон
     * @param {{header: string,footer: string,buttons:{logOut: string}}} globalDictionary
     * @param {string} title
     */
    Template.prototype.initGlobalDictionary = function(globalDictionary,title) {
        //Подпись страницы
        Lib.cls('nav-header').innerHTML = globalDictionary.header;
        //Подпись языков
        let footer = document.getElementsByTagName('footer')[0];
        footer.innerHTML = globalDictionary.footer+':';
        //Инициализация кнопок языков
        this.initLanguageButton();
        document.title = title;
        //Инициализация кнопки выхода
        this.initLogOutButton(globalDictionary.buttons.logOut);
    };

    /**
     * Если подгружен шаблон профиля, то показываем кнопку выхода
     */
    Template.prototype.initLogOutButton = function(label) {
        //Если есть кнопка выхода то удаляем ее
        let button = Lib.cls('logout');
        if (button) button.remove();
        //Если для страницы не нужна кнопка выхода
        if (this.nonLogOut.indexOf(this.hash) !== -1) return;
        //Создаем кнопку
        button = Lib.c(
            'div',
            {
                cls: 'logout',
                html: label,
                element: document.getElementsByTagName('header')[0]
            }
        );
        //Вешаем событие
        Lib.onClick(
            button,
            function () {
                Lib.loading();
                Lib.send({
                    query: {email: email},
                    headers: {
                        Method: 'USER_LOG_OUT',
                        Template: _Template.getHash()
                    },
                    func: function (result) {
                        Lib.loading(true);
                        //выводим нужный шаблон
                        _Template.getHTML(result.hash);
                    }
                });
            }
        );
    };

    /**
     * Инициализация кнопок языков
     */
    Template.prototype.initLanguageButton = function() {
        //Установленный язык
        let language = Lib.getCookie('language'),
            //Блок куда добавлять языки
            footer = document.getElementsByTagName('footer')[0];
        //Создание кнопок языков
        for (let item in this.accessLanguage) {
            let button = Lib.c(
                'span',
                {
                    cls: 'language',
                    dataset: {
                        language: this.accessLanguage[item],
                        active: this.accessLanguage[item] === language ? 'active' : 'none'
                    },
                    html: ' '+this.accessLanguage[item]+' ',
                    element: footer
                }
            );
            //Не вешаем событие на кнопку выбранного языка
            if (this.accessLanguage[item] === language) continue;
            Lib.onClick(
                button,
                function (e,button) {
                    //Язык на кнопке
                    let language = button.dataset.language;
                    //Если язык не поддерживается
                    if (_Template.accessLanguage.indexOf(language) === -1) return;
                    //Устанавливаем язык
                    Lib.setCookie('language',language,365*86400,'.'+_Template.getHost());
                    //Перевызываем шаблон
                    _Template.getHTML();
                }
            )
        }
    };

    /**
     * Получение словаря
     * @return {{}|*}
     */
    Template.prototype.getDictionary = function() {
        return Template.dictionary;
    };

    /**
     * @type {null} событие которое надо вызвать после подключения обработчика
     */
    Template.event = null;

    /**
     * Данные для обработки внешним классом
     * @type {null}
     */
    Template.data = null;

    /**
     * Подключение внешнего обработчика событий
     * @param className
     * @param event
     * @param data
     */
    Template.connectOuterClass = function (className,event,data) {
        //Если файл уже подклюен
        if (typeof window[className] !== "undefined") {
            //Вызываем необходимый метод
            new window[className](event,data);
            return;
        }
        //Записываем вызываемый event
        Template.event = event;
        //Записываем входные данные для эвента
        Template.data = data;
        //создаем файл скрипт
        let script = Lib.c('script');
        script.async = true;
        script.src = '/js/'+(className.toLowerCase())+'.js?'+Lib.random();
        document.body.appendChild(script);
    };
    return Template;
}(Lib));
let _Template = new Template();