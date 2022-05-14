<?php
require_once __DIR__ . '/validators.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
class Api extends Config
{
    use Validators;
    private $lang;
    private $GET;
    private $POST;
    private $headers;
    private $db;
    private $am;
    private $action;
    private $headersCurl;

    /**
     * A constructor function.
     * 
     * @param params The parameters to pass to the API.
     */
    public function __construct($params = null)
    {
        parent::__construct();
        $this->db = new Database();
        if ($params != 'script') {
            $this->GET = $this->getrequest();
            $GET = $this->getGET();
            $this->headers = apache_request_headers();
            $this->am = isset($GET['am']) ? $GET['am'] : null;
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->POST = $this->postrequest();
                $POST = $this->getPOST();
                if (isset($POST['action']) && !empty($POST['action'])) $this->action = $POST['action'];
            } else {
                if (isset($GET['aq']) && !empty($GET['aq'])) $this->action = 'query';
                elseif (isset($GET['ap']) && !empty($GET['ap'])) $this->action = 'apione';
                elseif (isset($GET['as']) && !empty($GET['as'])) $this->action = 'apislide';
                elseif (isset($GET['aa']) && !empty($GET['aa'])) $this->action = 'apiby';
                else $this->action = null;
            }
            $this->lang = $this->urlLang($GET);
        }
        $headers = $this->getHeaders();
        $apiToken = isset($headers['api_token']) ? 'api_token:' . $headers['api_token'] : 'api_token:' . $this->getApiToken();
        $accesToken = isset($headers['acces_token']) ? 'acces_token:' . $headers['acces_token'] : null;
        $adminToken = isset($headers['admin_token']) ? 'admin_token:' . $headers['admin_token'] : null;
        // if (isset($adminToken)) {
        //     $adminToken = str_replace(' ','X', str_pad('u'.$adminToken, 32));//remplazar espacios por X
        // }
        $this->headersCurl = [
            'Content-Type: application/json',
            'charset: utf-8',
            $apiToken,
            $accesToken,
            $adminToken
        ];
    }

    public function getAm()
    {
        return $this->am;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getGET()
    {
        return $this->GET;
    }

    public function getPOST()
    {
        return $this->POST;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function getHeadersCurl()
    {
        return $this->headersCurl;
    }

    /**
     * It checks if the header is set, if not, it sets it to the value of the session variable
     * 
     * @param name the name of the header to be added
     */
    public function falseHeaders($name)
    {
        $headers = $this->getHeaders();
        if (!isset($headers[$name])) {
            switch ($name) {
                case 'api_token':
                    $headers[$name] = $this->getApiToken();
                    break;
                case 'acces_token':
                case 'admin_token':
                    $headers[$name] = $_SESSION['auth'][$name];
                    break;
            }
            $this->setHeaders($headers);
        }
    }

    private function getrequest()
    {
        if (isset($_GET) && isset($_GET['r'])) {
            $nuevoGET = array();
            foreach ($_GET as $k => $valor) {
                $valor = str_replace('SELECT', '', $valor);
                $valor = str_replace('CREATE', '', $valor);
                $valor = str_replace('UPDATE', '', $valor);
                $valor = str_replace('INSERT', '', $valor);
                $valor = str_replace('DELETE', '', $valor);
                $valor = str_replace('FROM', '', $valor);
                $valor = str_replace('WHERE', '', $valor);
                $valor = str_replace('SET', '', $valor);
                $valor = str_replace('ALTER', '', $valor);
                $valor = str_replace('EXECUTE', '', $valor);
                $valor = str_replace('SLEEP', '', $valor);
                $valor = str_replace('COPY', '', $valor);
                $valor = str_replace('SELECT', '', $valor);
                $valor = str_replace("DUMP", "", $valor);
                $valor = str_replace(" OR ", "", $valor);
                $valor = str_replace("%", "",    $valor);
                $valor = str_replace("LIKE", "", $valor);
                $valor = str_replace("--", " ",     $valor);
                $valor = str_replace("^", "",       $valor);
                $valor = str_replace("[", "",       $valor);
                $valor = str_replace("]", "",       $valor);
                $valor = str_replace("(", "",       $valor);
                $valor = str_replace(")", "",       $valor);
                $valor = str_replace("\\", "",      $valor);
                $valor = str_replace("!", "",       $valor);
                $valor = str_replace("¡", "",       $valor);
                //$valor = str_replace("?","",       $valor);
                //$valor = str_replace("=","",       $valor);
                $valor = str_replace("\"", " ",     $valor);
                $valor = str_replace("'", " ",      $valor);
                $valor = str_replace('\'', " ",      $valor);
                $valor = str_replace('%20', "_",     $valor);
                // $valor = str_replace("&","",       $valor);
                $nuevoGET[$k] = trim($valor);
            }
            return $nuevoGET;
        } else {
            return $_GET;
        }
    }

    private function postrequest()
    {
        if (isset($_POST) && count($_POST) > 0) {
            return $_POST;
        } else {
            return json_decode(file_get_contents('php://input'), true);
        }
    }

    /**
     * If the first part of the URL is a valid language code, then use that language code. Otherwise, use
     * the default language code
     * 
     * @param GET The  array
     * 
     * @return The default language is returned.
     */
    private function urlLang($GET)
    {
        $lang = $this->getDefaultLang();
        if (isset($GET['r']) && !empty($GET['r'])) {
            $getpath = explode('/', $GET['r']);
            $urlang = reset($getpath);
            if (in_array($urlang, $this->getValidModules()) && isset($getpath[1])) {
                $urlang = $getpath[1];
            }
            $code = $this->getIdLang($urlang);
            if (empty($code)) {
                $lang = $urlang;
            }
        }
        return $lang;
    }

    /**
     * It returns the path of a media file, if it exists, or a default image if it doesn't
     * 
     * @param tipo the type of media you want to handle.
     * @param name The name of the file.
     * @param ext The file extension of the media.
     * @param id_anime The id of the anime.
     * @param id_element This is the id of the element that you want to get the image from.
     * 
     * @return The path to the media file.
     */
    public function handleMedia($tipo, $name, $ext, $id_anime = null, $id_element = null)
    {
        $nomedia = $this->getNomediaImg();
        $mediapath = $nomedia;
        switch ($tipo) {
            case 'banner':
            case 'portada':
            case 'openings':
            case 'endings':
            case 'episodes':
                $mediaSrc = "media/animes/$id_anime/$tipo/{$name}.$ext";
                break;
            case 'personages':
                $mediaSrc = "media/animes/$id_anime/$tipo/$name/{$name}.$ext";
                break;
            case 'profiles':
            case 'new_post':
            case 'chat':
                $mediaSrc = "media/$tipo/$id_anime/{$name}.$ext";
                break;
            default:
                $mediaSrc = $nomedia;
                break;
        }

        if (isset($id_element)) {
            $mediaSrc = "media/animes/$id_anime/$tipo/$id_element/{$name}.$ext";
        }
        if (file_exists($mediaSrc)) {
            $mediapath = $mediaSrc;
        }

        return "http://{$this->getDomain()}/$mediapath";
    }

    /**
     * It takes a module name and a string, and returns the translated string
     * 
     * @param mod The name of the module you want to translate.
     * @param string The string to be translated.
     * 
     * @return The translation of the string.
     */
    public function tt($mod, $string)
    {
        $file = "api/$mod/$mod.json";
        if (file_exists($file)) {
            $langsJson = json_decode(file_get_contents($file));
            $trans = isset($langsJson->$string) ? $langsJson->$string : $string;
        } else {
            $trans = $string;
        }
        return $trans;
    }

    public function apiReq($peticion, $params = null)
    {
        $date = new DateTime();
        $peticion .= "&rand={$date->getTimestamp()}";
        return $this->curl("{$this->getUrlApi()}{$this->getLang()}&am=$peticion", $params);
    }

    /**
     * It makes a request to the node.
     * 
     * @param peticion The request you want to make.
     * @param params 
     * 
     * @return The response from the API.
     */
    public function apiReqNode($peticion, $params = null)
    {
        return $this->curl($this->getUrlNode() . $peticion, $params);
    }

    /**
     * It makes a curl request to the url and returns the response.
     * 
     * @param url The URL to send the request to.
     * @param params The parameters to be sent to the API.
     * 
     * @return The result of the curl request.
     */
    private function curl($url, $params)
    {
        $ch = curl_init($url);
        if (isset($params)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeadersCurl());
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $res = curl_exec($ch);
        $curl_error = curl_error($ch);
        if ($curl_error) {
            $result = $this->response('error', 500);
            $result['status']['http'] = array(
                'url_error' => $curl_error,
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE)
            );
        } else {
            $result = json_decode($res, true);
        }
        curl_close($ch);
        return $result;
    }

    /**
     * It takes a message, a code, and optionally a data array, an alert array, and a notifications
     * array, and returns a response array
     * 
     * @param message The message to be displayed to the user.
     * @param code The HTTP status code
     * @param data The data to be returned.
     * @param alert This is an array with two keys: code and text. The code is the type of alert, and
     * the text is the message to be displayed.
     * @param notifications an array of notifications to be played.
     * 
     * @return An array with the following structure:
     * ```
     * array(
     *     'data' => ,
     *     'status' => array(
     *         'code' => ,
     *         'text' => ,
     *         'message' => ->tt(->getAm(), )
     *     )
     * )
     * ```
     */
    public function response($message, $code, $data = null, $alert = null, $notifications = null)
    {
        switch ($code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
        }

        $resp = array(
            'data' => $data,
            'status' => array(
                'code' => $code,
                'text' => $text,
                'message' => $this->tt($this->getAm(), $message)
            )
        );

        if (isset($alert) || isset($notifications)) {
            $resp['specials'] = array();
            if (isset($alert)) {
                $resp['specials']['alert'] = array(
                    'c' => $alert['code'],
                    't' => $this->tt($this->getAm(), $alert['text'])
                );
            }
            if (isset($notifications)) {
                $resp['specials']['alert'] = $this->handleMedia('notifications', $notifications, 'mp3');
            }
        }
        return $resp;
    }

    public function writeFile($content, $kind = '', $path = null)
    {
        $permission = 0755;
        $flag = (in_array($kind, array('backup', 'error', 'log'))) ? FILE_APPEND : 0;
        if ($kind == 'log') {
            $content = "User SO: " . get_current_user() . " - " . date("d-m-Y H:i:s");
            $content .= " acción: {$content['kind']} - {$content['message']} PHP_EOL";
            $path = "logs/api.log";
        }
        if (
            !file_exists($path) ||
            (file_exists($path) &&
                substr_compare($content, file_get_contents($path), 0) !== 0)
        ) {
            //$this->mkFolders($path); TODO reparar  para que cree la carpeta, no una carpeta con el nombre del fichero
            file_put_contents($path, $content, $flag);
            //$permissionFile = substr(sprintf('%o', fileperms($path)), -4);
            //if($permissionFile == $permission) chmod($path,$permission);
            chown($path, get_current_user());
            chmod($path, $permission);
        }
    }

    public function mkFolders($path)
    {
        if (isset($path) && !is_dir($path)) {
            $p = explode("/", $path);
            //if (strstr(end($p),".")) array_pop($p);
            if (count($p) > 0) {
                $new = implode("/", $p);
                if (!is_dir($new)) {
                    mkdir($new, 0755, true);
                    chown($new, get_current_user());
                }
            }
        }
    }

    public function isValidAccesToken()
    {
        $headers = $this->getHeaders();
        $sql = "SELECT acess_token FROM users WHERE acess_token = '{$headers['acess_token']}'";
        $valor = $this->getDb()->obtener_una_columna($sql);
        return isset($valor) ? true : false;
    }

    public function isValidAdminToken()
    {
        $headers = $this->getHeaders();
        $sql = "SELECT admin_token FROM users WHERE admin_token = '{$headers['admin_token']}'";
        $valor = $this->getDb()->obtener_una_columna($sql);
        return isset($valor) ? true : false;
    }

    /**
     * It scans a folder and returns an array of all the files and folders in it
     * 
     * @param path The path to the folder you want to scan.
     * @param recursive true/false - whether to scan subfolders or not
     * @param endelement If true, the function will return the last element of the path.
     * 
     * @return An array of all the files in the folder.
     */
    public function scanFolders($path, $recursive = true, $endelement = false)
    {
        $scan = array();
        if (file_exists($path)) {
            $folders = array_diff(scandir($path), array("..", "."));
            if (sizeof($folders) > 0) {
                foreach ($folders as $value) {
                    $folder = "$path/$value";
                    if ($recursive && is_dir($folder)) {
                        $scan = array_merge($scan, $this->scanFolders($folder, $recursive, $endelement));
                    } else {
                        if ($endelement) {
                            $t = explode("/", $folder);
                            $folder = end($t);
                        }
                        array_push($scan, $folder);
                    }
                }
            }
        }
        return $scan;
    }

    /**
     * It returns the id of the language with the given code
     * 
     * @param code The language code, e.g. 'en'
     * 
     * @return The id of the language.
     */
    private function getIdLang($code = null)
    {
        if (!$code && !$this->getLang()) {
            $code = $this->getDefaultLang();
        } elseif (!$code) {
            $code = $this->getLang();
        }
        $sql = "SELECT id FROM langs WHERE code = '$code'";
        return $this->getDb()->obtener_una_columna($sql);
    }

    /**
     * It returns a translation
     * 
     * @param params the array of translations you want to get.
     * 
     * @return An array of translations.
     */
    public function gettranslations($params)
    {
        $translation = $this->apiReqNode("translation", array(
            "code" => $this->getIdLang(),
            "translations" => $params
        ));
        if (count($translation) == 1) {
            $translation = current($translation);
        }
        return $translation;
    }

    /**
     * It gets the media from the API.
     * 
     * @param params 
     * 
     * @return An array of media objects.
     */
    public function getMedias($params)
    {
        return $this->apiReqNode("media", array(
            "media" => $params
        ));
    }
}
