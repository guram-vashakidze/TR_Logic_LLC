let Lib = (function () {
    function Lib() {
        this.tooltips();
    }

    /**
     * Создание подсказок у элементов
     */
    Lib.prototype.tooltips = function() {
        document.addEventListener(
            'mousemove',
            function (e) {
                //Если нет элемента
                if (typeof e.target === "undefined") return Lib.removeAllTooltips();
                //записываем элемент
                let element = e.target;
                //Если нет нужного имени
                element = Lib.searchTooltipElement(element);
                if (!element) return;
                //Если уже есть тултип
                if (element.dataset.tooltipId && Lib.id(element.dataset.tooltipId)) return;
                //Удаляем все тултипы
                Lib.removeAllTooltips();
                //вешаем указатель на элемент
                element.style.cursor = 'pointer';
                //Текст
                let text = element.dataset.tooltips;
                //Если нет текста
                if (!text) return;
                let id = 'tooltips'+Math.random().toString().replace(/^0\./,''),
                    tooltip = Lib.c(
                    'div',
                    {
                        cls: 'tooltips',
                        html: text,
                        id: id,
                        element: document.body
                    }
                );
                //Запоминаем идентификатор
                element.dataset.tooltipId = id;
                //Определяем позицию тултипа
                let positionNewTooltip = Lib.getTooltipPosition(element, tooltip);
                // размещаем тултип на странице
                tooltip.style.top = positionNewTooltip.top.toString() + 'px';
                tooltip.style.left = positionNewTooltip.left.toString() + 'px';
            }
        );
    };

    /**
     * Поиск элемента с классом tooltip
     * @param element
     * @return {HTMLElement|null}
     */
    Lib.searchTooltipElement = function(element) {
        if (!element) return Lib.removeAllTooltips();
        //Ищем элементы тултипов "под" указателем мышки
        if (typeof element.className === "undefined" || element.className.indexOf('tooltips-block') === -1) {
            //Если дошли до BODY то выходим
            if (element.tagName === 'BODY') return Lib.removeAllTooltips();
            //Если есть родительский элемент
            if (typeof element.parentNode !== "undefined") return Lib.searchTooltipElement(element.parentNode);
            return Lib.removeAllTooltips();
        }
        return element;
    };

    /**
     * Удаление всех подсказок
     */
    Lib.removeAllTooltips = function() {
        //Получаем все элементы подсказок
        let tooltips = Lib.cls('tooltips',true);
        //если нет элементов
        if (!tooltips) return;
        //Удаляем
        for (let i = 0, max = tooltips.length; i < max; i++) {
            tooltips[i].remove();
        }
        return null;
    };

    /**
     * Получение данных позиционирования тултипа относительно элемента
     */
    Lib.getTooltipPosition = function(element,tooltip) {
        //координаты элемента
        let position = element.getBoundingClientRect(),
            //отступ сверху до элемента с тултипом
            top = position.top,
            //отступ слева до элемента с тултипом
            left = position.left,
            //Высота тултипа
            tooltipH = tooltip.offsetHeight,
            //Ширина тултипа
            tooltipW = tooltip.offsetWidth,
            //Отступ сверху
            tooltipTop = top - tooltipH - 7,
            //Отступ слева
            tooltipLeft = left + (element.offsetWidth / 2) - (tooltipW / 2);
        return {
            left: tooltipLeft,
            top: tooltipTop
        };
    };

    /**
     * Создание элемента HTML
     * @param {string} name - название элемента
     * @param {{cls?: string,id?:string,html?:string,value?: string,css?:{cssParam: string},dataset?:{dataName: string},element?:HTMLElement}|{null}} setting - доп. настройки создания элемента
     * @return {HTMLElement}
     */
    Lib.c = function (name, setting = null) {
        //Создаем элемент
        let element = document.createElement(name);
        //Если нет доп. настроек
        if (!setting) return element;
        //Указание класса
        if (setting.cls) element.className = setting.cls;
        //указание id
        if (setting.id) element.id = setting.id;
        //Указание содержимого HTML
        if (setting.html) element.innerHTML = setting.html;
        //Указание value
        if (setting.value && typeof (element).value !== 'undefined') (element).value = setting.value;
        //Если надо установить data атрибуты
        if (setting.dataset) {
            for (let dname in setting.dataset) {
                element.dataset[dname] = setting.dataset[dname];
            }
        }
        if (setting.css) {
            for (let css in setting.css) {
                    element.style[css] = setting.css[css];
            }
        }
        if (!setting.element) return element;
        setting.element.appendChild(element);
        return element;
    };

    /**
     * Поиск элемента по ID
     * @param id id элемента
     */
    Lib.id = function(id) {
        return document.getElementById(id);
    };

    Lib.cls = function(className,numberElement = null) {
        //Получаем элемент из модели DOM
        let elements = document.getElementsByClassName(className);
        //Если такого элемента нет
        if (typeof elements[0] === "undefined") return null;
        //Если надо вернуть всю коллекцию
        if (numberElement === true) return elements;
        //Если не задан элемент который надо вернуть
        if (numberElement === null) numberElement = 0;
        //Если номер элемента больше 0
        if (numberElement >= 0) return typeof elements[numberElement] !== "undefined" ? elements[numberElement] : null;
        //Возбращаем элемент с конца
        numberElement = elements.length+numberElement;
        return numberElement >= 0 && typeof elements[numberElement] !== "undefined" ? elements[numberElement] : null;
    };

    /**
     * Создание объекта для асинхронного запроса
     * @return {*}
     */
    Lib.getXHR = function () {
        if (typeof XMLHttpRequest !== "undefined") {
            try {
                return new XMLHttpRequest();
            } catch (e) {
            }
        } else if (typeof ActiveXObject !== "undefined") {
            try {
                return new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
            }
            try {
                return new ActiveXObject('Microsoft.XMLHTTP');
            } catch (e) {
            }
        }
        return null;
    };

    /**
     * Отправка AJAX запроса
     * @param {{method?: string,query?: string,formData: {},headers?: {headerName: string} url?: string,func: ()}} Request - параметры запроса
     */
    Lib.send = function (Request) {
        //Метод AJAX запроса
        let method = typeof Request.method !== "undefined" ? (Request.method === 'GET' ? 'GET' : 'POST') : 'POST',
            //Параметры запроса
            query = typeof Request.query !== "undefined" ? encodeURIComponent(JSON.stringify(Request.query)): null,
            //URL отправки AJAX запроса
            url = typeof Request.url !== "undefined" ? Request.url : location.href,
            //Объект для выполнения AJAX запроса
            xhr = Lib.getXHR();
        //Если есть данные формы
        if (typeof Request.formData !== "undefined")  query = Request.formData;
        //Открываем соединение
        xhr.open(method,url,true);
        //Если в запросе есть заголовки
        if (typeof Request.headers !== "undefined") {
            //Добавляем заголовки в запрос
            for (let header in Request.headers) {
                xhr.setRequestHeader(header, Request.headers[header]);
            }
        }
        //Если определен метод вызова после возврата ответа на AJAX запрос
        if (typeof Request.func !== "undefined") {
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    //Обрабатываем ответ от сервера
                    let response = Lib.checkResponseXhr(xhr);
                    //Если была ошибка то далее пользовательский метод обработки ответа не запускаем
                    if (response === false) return null;
                    //Запуск пользовтельского метода обработки ответа
                    return Request.func(response,Lib.checkXhrHeader(xhr));
                }
            }
        }
        xhr.send(query);

    };

    /**
     * Ошибки обработчика исключений ответа AJAX
     * @type {{RU: string, EN: string}}
     */
    Lib.tryXhrError = {
        EN: 'Failed to complete the action. Try later',
        RU: 'Неудалось выполнить действие. Попробуйте ещё раз'
    };

    /**
     * Обработка ответа от сервера на AJAX запрос
     * @param {XMLHttpRequest} xhr - объект AJAX
     */
    Lib.checkResponseXhr = function (xhr) {
        try {
            let response = JSON.parse(xhr.responseText);
            //Если в ответе есть сообщение об ошибке
            if (typeof response.error !== "undefined") {
                return Lib.showAlert(response.error.msg,response.error.cls);
            }
            return response;
        } catch (e) {
            return Lib.showAlert(Lib.tryXhrError[_Template.getLanguage()],'red');
        }
    };

    /**
     * Обработка заголовкаов ответа
     * @param xhr
     */
    Lib.checkXhrHeader = function (xhr) {
        try {
            let header = xhr.getAllResponseHeaders().split("\r\n"),
                result = {};
            for (let i = 0, max = header.length; i < max; i++) {
                //Разбиваем заголовки
                header[i] = header[i].split(': ');
                if (!header[i][1]) continue;
                //записываем результат
                result[header[i][0].replace('-','_')] = header[i][1].trim().toString();
            }
            return result;
        } catch (e) {
            return {}
        }
    };

    /**
     * Включение/выключение прелоудера
     * @param {boolean} close
     */
    Lib.loading = function (close = false) {
        //рассчет позиционирование прелоудера
        let width = window.outerWidth/2-25,
            height = window.outerHeight/2-25,
            preloader = Lib.cls('md-preloader'),
            blockWindow = Lib.cls('block-window'),
            loading = Lib.cls('loading');
        //Если прелодер включен
        if (preloader !== null) {
            //Включаем его
            preloader.remove();
            blockWindow.style.zIndex = -12;
            blockWindow.style.visibility = 'hidden';
            loading.style.display = 'none';
            return;
        }
        if (close === true) return;
        //Создаем прелоудаер
        Lib.c(
            'div',
            {
                html: '<div class="md-preloader md-preloader-show" style="margin-top:'+height+'px;margin-left: '+width+'px">' +
                    '            <div class="md-spinner blue" style="width: 50px; height: 50px;">' +
                    '                <div class="md-spinner-container">' +
                    '                    <div class="md-spinner-rotator">' +
                    '                        <div class="md-spinner-left">' +
                    '                            <div class="md-spinner-circle" style="border-width: 2px"></div>' +
                    '                        </div>' +
                    '                        <div class="md-spinner-right">' +
                    '                            <div class="md-spinner-circle"></div>' +
                    '                        </div>' +
                    '                    </div>' +
                    '                </div>' +
                    '            </div>' +
                    '        </div>',
                element: document.body,
            }
        );
        //Блокируем все элементы
        blockWindow.style.zIndex = 100;
        blockWindow.style.visibility = 'visible';
        loading.style.display = 'block';
    };

    /**
     * Указание ХЭШ url
     * @param {string} hash
     */
    Lib.setHash = function(hash = '') {
        hash = hash ? '#'+hash : '';
        history.pushState(null, null, location.href.replace(/#?.+/, hash));
    };

    /**
     * Установка события onClick наэлемнеты
     * @param {string|HTMLElement} element
     * @param {(button) => {null}}method
     */
    Lib.onClick = function (element, method) {
        //Вешаем событие onclick
        Lib.addListener(element,'click',method);
    };

    /**
     *
     * @param {string|HTMLElement} element
     * @param {string} listener
     * @param {(button)}method
     */
    Lib.addListener = function(element,listener,method) {
        //Если необходимо получить нужный элемент
        element = typeof element === "string" ? Lib.cls(element) : element;
        if (!element) return;
        if (typeof element.dataset !== "undefined" ) {
            //Проверяем есть ли уже событие
            if (element.dataset[listener + 'EventDone'] === '1') return;
            //Флаг события
            element.dataset[listener + 'EventDone'] = '1';
        }
        element.addEventListener(
            listener,
            function (e) {
                method(e,this);
            }
        )
    };

    /**
     * Показ алерта
     * @param msg
     * @param cls
     * @param timeout
     */
    Lib.showAlert = function (msg,cls,timeout = 3) {
        //Классы алерта
        let alertClassName = {
            red: 'danger',
            green: 'success',
            default: 'info'
        },
            alert = Lib.cls('show-alert');
        //Устанавливаем нужный клас
        alert.className = "alert show-alert alert-"+alertClassName[cls];
        //Устанавливаем сообщение
        alert.innerHTML = msg;
        //Показываем алерт
        alert.style.bottom = '25px';
        //Отключаем прелоудер
        Lib.loading(true);
        //Таймаут на закрытие
        setTimeout(
            function () {
                alert.style.bottom = '-100px';
            }, timeout*1000
        );
        return false;
    };

    /**
     * Генерация целого числа
     * @return {number}
     */
    Lib.random = function () {
        let result = Math.random().toString().replace(/^0\./,'');
        return parseFloat(result);
    };

    /**
     * Считывание кук
     * @param {string} name
     * @return {string|boolean}
     */
    Lib.getCookie = function(name) {
        let cookie = document.cookie.match(new RegExp(name + '=([^;$]*)'));
        return cookie ? cookie[1] : false;
    };

    /**
     * Установка куки
     * @param {string} name - название куки
     * @param {string} value - содержание куки
     * @param {number} expires - срок действия
     * @param {string} host - хост для которого устанавливается кука
     */
    Lib.setCookie = function (name,value,expires,host) {
        let cookie = name + '=' + encodeURIComponent(value),
            date = new Date();
        //Время до которого действует кука
        date.setTime(date.getTime() + expires*1000);
        document.cookie = cookie + "; path=/; expires="+date.toUTCString()+"; domain="+host;
    };

    return Lib;
}());
new Lib();