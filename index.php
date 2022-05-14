<?php
/*
TODO empezar ha migrar a nodejs
ya que se hace tedioso el mantenimiento, solamente lo utilizaremos para scripts
y mas tarde se deberian de migrar estos mismos a python o zx
*/

require_once __DIR__ . '/classes/api.php';
$api = new Api();
$mod = $api->getAm();
if (isset($mod)) {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, acces_token, api_token, admin_token");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Allow: GET, POST, PUT, DELETE");
    //falseo para poder realizar peticiones desde el navegador
    if ($api->isLocalHost() && !$api->isAjax()) {
        $api->falseHeaders("api_token");
    }
    $headers = $api->getHeaders();
    // if ($api->isAjax()) {
    //     error_log($api->getApiToken());
    //     error_log("/////////////////////");
    //     error_log($headers['api_token']);
    // }
    //error_log("/////////////////////");
    if (isset($headers['api_token']) && substr_compare($api->getApiToken(), $headers['api_token'], 0) == 0) {
        $controller = "api/$mod/{$mod}.php";
        if (file_exists($controller)) {
            include_once $controller;
            if (function_exists($mod)) {
                $process = call_user_func_array($mod, array('api' => $api));
            } else {
                $process = $api->response("Modulo no existe $mod o no permitido", 404);
            }
        } else {
            $process = $api->response("Modulo no existe $mod", 404);
        }
    } else {
        $process = $api->response("No estas autorizado para utilizar la api de {$api->getDomain()}", 401);
    }
} else {
    $mensage = 'Ha habido un error, comprueba la petición realizada a la api.';
    $process = $api->response($mensage, 404);
}
echo json_encode($process, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
