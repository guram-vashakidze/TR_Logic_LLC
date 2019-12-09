var Lib = (function () {
    function Lib() {
    }

    /**
     * Создание объекта для асинхронного запроса
     * @return {*}
     */
    Lib.prototype.getXHR = function () {
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
     * @param {{method?: string,query?: string,headers?: {headerName: string} url?: string,func: ()}} Request - параметры запроса
     */
    Lib.prototype.send = function (Request) {
        //Метод AJAX запроса
        let method = typeof Request.method !== "undefined" ? (Request.method !== 'GET' ? 'GET' : 'POST') : 'POST',
            //Параметры запроса
            query = typeof Request.query !== "undefined" ? encodeURIComponent(JSON.stringify(Request.query)): null,
            //URL отправки AJAX запроса
            url = typeof Request.url !== "undefined" ? Request.url : location.href,
            //Объект для выполнения AJAX запроса
            xhr = this.getXHR();
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
                    return Request.func(response);
                }
            }
        }

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
                /**TODO SHOW ALERT*/
                return false;
            }
            return response;
        } catch (e) {
            return false;
        }
    };
    return Lib;
}());
new Lib();