<?php
namespace App\Email;

trait Send {

    private static $Encoding = 'UTF-8';

    /**
     * Отправка письма на почту
     * @param string $email
     * @param string $subject
     * @param array $message - писсив с частями сообщения
     * @param array $from
     * @param array $setting - доп. настройки
     * @return array
     */
    public static function sendMailSMTP($email,$subject,array $message, array $from = ['email' => '','pass' => '','host' => '','port' => 0],$setting = ['encoding' => 'UTF-8']) {
        //Установка кодировки если задана в настройках
        if (!empty($setting['encoding'])) self::$Encoding = $setting['encoding'];
        $from['email'] = !empty($from['email']) ? $from['email'] : \App\Config::SMTP['email'];
        //Формирование заголовка письма
        $subject = '=?'.self::$Encoding.'?B?'.base64_encode(self::$Encoding == 'UTF-8' ? $subject : mb_convert_encoding($subject,self::$Encoding,'UTF-8')).'?='."\r\n";
        //Заголовок возврата письма
        $header = "Return-Path: {$from['email']}\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        //Отправка письма с несколькими частями
        $boundary = "--".md5(uniqid(time()));
        //Общий заголовок письма
        $header .= "Content-Type: multipart/related; charset=\"".strtolower(self::$Encoding)."\"; boundary=\"$boundary\"\r\n";
        $result = '';
        //Собираем письмо воедино
        foreach ($message as $item) {
            //отделяем часть письм
            $result .= "--$boundary\r\n";
            $contentType = empty($item['alternative']) ? (!empty($item['file']) && empty($item['content-type']) ? 'application/octet-stream' : $item['content-type']) : 'multipart/alternative';
            //Проверяем есть ли альтернативное содержание для HTML
            $result .= "Content-Type: ".$contentType."; charset=\"".strtolower(self::$Encoding)."\"".(empty($item['alternative']) ? "" : "; boundary=\"{$boundary}-2\"")."\r\n";
            //Если вложение с файлом
            if (!empty($item['file'])) {
                //Получаем название файла
                $name = explode("/",$item['file']);
                $name = $name[count($name)-1];
                //Заголовок вложения
                $result .= "Content-Disposition: attachment; filename=\"".(self::$Encoding == 'UTF-8' ? $name : mb_convert_encoding($name,self::$Encoding,'UTF-8'))."\"\r\n";
                //считываем вложение
                $item['body'] = file_get_contents($item['file']);
            }
            $result .= "Content-Transfer-Encoding: base64\r\n\r\n";
            //Если есть альтернативное содержание
            if (!empty($item['alternative'])) {
                //Указываем альтернативное значени блока
                $result .= "--$boundary-2\r\n";
                $result .= "Content-Type: text/plain; charset=\"".strtolower(self::$Encoding)."\"\r\n";
                $result .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $result .= chunk_split(base64_encode($item['alternative']))."\r\n";
                $result .= "--$boundary-2\r\n";
                $result .= "Content-Type: ".$item['content-type']."; charset=\"".strtolower(self::$Encoding)."\"\r\n";
                $result .= "Content-Transfer-Encoding: base64\r\n\r\n";
            }
            $item['content'] = !empty($item['content']) ? $item['content'] : [];
            //Если указан тип html то ищем в нем вставленные картинки
            if ($item['content-type'] == 'text/html') $item['body'] = self::checkContentInHtml($item['body'],$item['content']);
            //Добавление текста
            $result .= chunk_split(base64_encode($item['body'])).(!empty($item['alternative']) ? "--$boundary-2--\r\n" : "")."\r\n";
            //если нет вложенный контент
            if (empty($item['content'])) continue;
            //Добавляем контент
            //Например нужен если в письме вставлены картинки через идентификатор
            //Пример: https://people.dsv.su.se/~jpalme/ietf/mhtml-test/mhtml.html (№1)
            foreach ($item['content'] as $content) {
                //Получаем название файла
                $name = explode("/",$content['file']);
                $name = $name[count($name)-1];
                //добавляем часть с вложением
                $result .= "--$boundary\r\n";
                $result .= "Content-Type: ".$content['content-type']."\r\n";
                $result .= "Content-ID: <".$name."@logic.loc>\r\n";
                $result .= "Content-Disposition: inline; filename=\"".(self::$Encoding == 'UTF-8' ? $name : mb_convert_encoding($name,self::$Encoding,'UTF-8'))."\"\r\n";
                $result .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $result .= chunk_split(
                    base64_encode(
                        file_get_contents(
                            $content['file']
                        )
                    )
                )."\r\n";
            }
        }
        //Результирующее сообщение
        $message = $result;
        $header .="From: =?".self::$Encoding."?B?".base64_encode('LOGIC.LOC')."?= <{$from['email']}>\r\n";
        $header .="To: =?".self::$Encoding."?B?".base64_encode(self::$Encoding == 'UTF-8' ? $email : mb_convert_encoding($email,self::$Encoding,'UTF-8'))."?= <{$email}>\r\n";
        $header .="List-Subscribe: <http://logic.loc>\r\n";
        $header .= "List-Unsubscribe: <https://logic.loc>\r\n\r\n";
        //Результат отправки
        $result = (
            new \App\Email\SMTP(
                $from['email'],
                empty($from['pass']) ? \App\Config::SMTP['pass'] : $from['pass'],
                empty($from['host']) ? \App\Config::SMTP['host'] : $from['host'],
                $from['email'],
                empty($from['port']) ? \App\Config::SMTP['port'] : $from['port'],
                self::$Encoding
            )
        )->send(
            $email,
            $subject,
            $message,
            $header
        );
        return is_string($result) ? ['send' => false,'error' => $result] : ['send' => true];
    }

    /**
     * Проверка наличия контента HTML который необходимо вставить в письмо через cid
     * Что бы найти контент необходимо в src задать его полный путь и предпоследний тип должен быть его тип
     * Например: картинка лежит по пути "/var/www/images/image.jpg"
     * То src надо задать: src="/var/www/images/image/image.jpg"
     * Тогда я смогу определить что content-type: image/jpg и картинка лежит в /var/www/images/
     * @param $html
     * @param $content
     * @return string
     */
    private static function checkContentInHtml($html,array &$content) {
        preg_match_all("/src=(\"|')(\/[^\"']+)(\"|')/",$html,$result);
        //Если нет результатов
        if (empty($result[2])) return $html;
        //Берем уникальные значения
        $result = array_unique($result[2]);
        //Обрабатываем параметры
        foreach ($result as $item) {
            //Название файла
            $file = basename($item);
            //Путь к файлу
            $path = explode("/",dirname($item));
            //Кол-во записей
            $total = count($path)-1;
            //Тип контента
            $contentType = $path[$total].'/'.preg_replace("/^.+\./",'',$file);
            //Удаляем лишний параметр из пути
            unset($path[$total]);
            //Собираем заново путь
            $path = implode('/',$path).'/'.$file;
            //Если нет такого файла
            if (!file_exists($path)) continue;
            //Экранируем путь
            $item = preg_quote($item,'/');
            //Заменяем значение по тексту
            $html = preg_replace("/src=(\"|')$item(\"|')/","src=$1cid:$file@logic.loc$2",$html);
            //Записываем в контент значение
            $content[] = [
                'content-type' => $contentType,
                'file' => $path

            ];
        }
        $content = array_values(array_unique($content));
        return $html;
    }
}
