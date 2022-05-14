<?php
//include_once dirname(__FILE__) . "/../securize.php";
const EXCLUDES = array(
    'DC', "nuevos", "INUYASHA", "D01", "Bleach", "AT", "AS", "Haikyuu",
    "Blood+", "BTB", "DB", "DBS", "DBZ", "DGM", "DN", "FMA", "FELE", "FA", "DTB", "GB", "NANA",
    "IE", "JOJOS", "LLDU", "NNT", "Noragami", "OP", "OYB", "PP", "PPP", "SG", "SD", "SS", "TF",
    "SX", "TG", "TRC"
);
require_once __DIR__ . '/../classes/api.php';
$api = new Api('script');
// Obtener todos los codigos de idioma
$sql = "SELECT code FROM langs";
$langs = $api->getDb()->listar($sql);
$langs = array_map(function ($lang) {
    return $lang->code;
}, $langs);
// FIN de Obtener todos los codigos de idioma
$basePath = __DIR__ . "/../media/animes";
$siglas = __DIR__ . "/../files/animes.json";
if (file_exists($siglas)) {
    $animes = json_decode(file_get_contents($siglas));
}
$scanedFiles = $api->scanFolders($basePath, false, true);
if (!file_exists($siglas) || (isset($animes) && sizeof($scanedFiles) > count($animes))) {
    $api->writeFile(json_encode($scanedFiles), '', $siglas);
    $animes = json_decode(file_get_contents($siglas));
}
// $animes = array("5CPS");
$animes = array("CY");
reloadBackup($animes, $basePath, $api);
exit;

foreach ($animes as $siglas) {
    if (!in_array($siglas, EXCLUDES)) {
        $anime = "$basePath/$siglas";
        $bFolder = "$anime/.backup";
        $api->mkFolders($bFolder);
        $sqlFolder = "$bFolder/sql";
        $api->mkFolders($sqlFolder);
        $noSqlFolder = "$bFolder/nosql";
        $api->mkFolders($noSqlFolder);
        if (is_dir($sqlFolder) && is_dir($noSqlFolder)) {
            error_log("Creada estructura de ficheros para backup de $siglas - check");
            dobackupWrapper($api, 'animes', $siglas, $anime, $sqlFolder);
            dobackupWrapper($api, 'seasons', $siglas, $anime, $sqlFolder);
            dobackupWrapper($api, 'openings', $siglas, $anime, $sqlFolder);
            dobackupWrapper($api, 'endings', $siglas, $anime, $sqlFolder);
            dobackupWrapper($api, 'episodes', $siglas, $anime, $sqlFolder);
            dobackupWrapper($api, "translations", $siglas, $anime, $noSqlFolder, $langs);
            dobackupWrapper($api, "media", $siglas, $anime, $noSqlFolder,  $langs);
        }
    }
}

function dobackupWrapper($api, $kindBackup, $siglas, $anime, $folder, $langs = null)
{
    $contentFile = array();
    $file = "$folder/$kindBackup.json";
    if ($kindBackup == 'animes') {
        $contentFile = inflateanimes($contentFile, $api, $anime, $siglas);
    } else if ($kindBackup == 'seasons') {
        $contentFile = inflateseasons($contentFile, $api, $anime, $siglas);
    } else if ($kindBackup == 'openings' || $kindBackup == 'endings') {
        $contentFile = inflateopeningsendings($contentFile, $api, $anime, $siglas, $kindBackup);
    } else if ($kindBackup == 'episodes') {
        $contentFile = inflateepisodes($contentFile, $api, $anime, $siglas, $kindBackup);
    } else if ($kindBackup == 'media') {
        $contentFile = inflatemedia($contentFile, $api, $anime, $siglas);
    } else if ($kindBackup == 'translations') {
        $contentFile = inflatetranslations($contentFile, $api, $anime, $siglas, $langs);
    }

    if (sizeof($contentFile) > 0) {
        $api->writeFile(json_encode($contentFile, JSON_UNESCAPED_UNICODE), '', $file);
        if (file_exists($file)) {
            $k = isset($langs) ? 'nosql' : 'sql';
            error_log("Creado fichero $k para $kindBackup de $siglas - check");
        } else {
            error_log(json_encode($contentFile));
        }
    } else {
        error_log("No hay $kindBackup en $siglas");
    }
}

function inflateanimes($contentFile, $api, $anime, $siglas)
{
    $numEpisodes = $api->scanFolders("$anime/episodes");
    if (sizeof($numEpisodes) > 10) {
        $kind = 'serie';
    } else if (sizeof($numEpisodes) > 1 && sizeof($numEpisodes) < 10) {
        $kind = 'ova';
    } else {
        $kind = 'pelicula';
    }
    error_log("\n \t Creando estructura animes");
    array_push($contentFile, array(
        "siglas" => $siglas,
        "generes" => '',
        "idiomas" => '',
        "date_publication"  >= '',
        "date_finalization"  >= '',
        "state"  >= 'Finalizado',
        "kind" => $kind,
        "temporada"  >= ''
    ));
    return $contentFile;
}

function inflateseasons($contentFile, $api, $anime, $siglas)
{
    $scanedFiles = $api->scanFolders("$anime/episodes", false, true);
    if (sizeof($scanedFiles) > 1) {
        //regex [0-9]{2,3}\.(mp4)
        error_log("\n");
        foreach ($scanedFiles as $file) {
            if (preg_match("/^[0-9]{1,2}/i", $file)) {
                error_log("\t Creando estructura seassions para $file");
                array_push($contentFile, array(
                    "id" => $siglas . $file
                ));
            }
        }
    }
    return $contentFile;
}

function inflateopeningsendings($contentFile, $api, $anime, $siglas, $kindBackup)
{
    if (is_dir("$anime/$kindBackup")) {
        $scanedFiles = $api->scanFolders("$anime/$kindBackup", false, true);
        if (sizeof($scanedFiles) > 1) {
            error_log("\n");
            foreach ($scanedFiles as $file) {
                error_log("\t Creando estructura $kindBackup para $file");
                $elements = explode("/", $file);
                $fileName = explode('.', end($elements));
                $num = preg_replace("/^0{1}/i", "", $fileName[0]);
                array_push($contentFile, array(
                    "id" => $siglas . $num,
                    "nombre" => '',
                    "descripcion" => '',
                    "anime" => $siglas,
                    "season" => null,
                    "num" => $num
                ));
            }
        }
    }
    return $contentFile;
}

function inflateepisodes($contentFile, $api, $anime, $siglas)
{
    $scanedFiles = $api->scanFolders("$anime/episodes");
    if (sizeof($scanedFiles) > 1) {
        error_log("\n");
        $idExternal = $siglas;
        foreach ($scanedFiles as $file) {
            $info = pathinfo($file);
            $f = explode("/", $info['dirname']);
            if (preg_match("/^[0-9]{1,2}/i", end($f))) {
                $idExternal .= preg_replace("/^0{1}/i", "", end($f));
            } else {
                $idExternal = null;
            }
            $elements = explode("/", $file);
            $fileName = explode('.', end($elements));
            $num = preg_replace("/^0{1}/i", "", $fileName[0]);
            error_log("\t Creando estructura episodes para " . end($elements));
            array_push($contentFile, array(
                "id" => $siglas . $num,
                "anime" => $siglas,
                "season" => $idExternal,
                "num" => $num
            ));
        }
    }
    return $contentFile;
}
function inflatetranslations($contentFile, $api, $anime, $siglas, $langs)
{
    $kinds = array('titulo', 'sinopsis');
    foreach ($kinds as $kind) {
        error_log("\n");
        $text = $kind;
        $contentFile = inflateTranslation($contentFile, $langs, $kind, $siglas, $text);
    }
    error_log("\n");
    $scanedFiles = $api->scanFolders("$anime/episodes", false, true);
    if (sizeof($scanedFiles) > 0) {
        foreach ($scanedFiles as $file) {
            error_log("\n");
            if (preg_match("/^[0-9]{1,2}/i", $file)) {
                $text = "sessions $file";
                $contentFile = inflateTranslation($contentFile, $langs, $kind, $file, $text);
            }
        }
    }
    error_log("\n");
    $scanedFiles = $api->scanFolders("$anime/episodes");
    if (sizeof($scanedFiles) > 0) {
        foreach ($scanedFiles as $file) {
            error_log("\n");
            $idExternal = $siglas;
            $elements = explode("/", $file);
            $fileName = explode('.', end($elements));
            $idExternal .= preg_replace("/^0{1}/i", "", $fileName[0]);
            $text = "episodes " . end($elements);
            $contentFile = inflateTranslation($contentFile, $langs, 'epititulo', $idExternal, $text);
        }
    }
    return $contentFile;
}

function inflatemedia($contentFile, $api, $anime, $siglas)
{
    $kinds = array('portada', 'banner', 'openings', 'endings', 'episodes');
    foreach ($kinds as $kind) {
        $path = "$anime/$kind";
        if (is_dir($path)) {
            error_log("\n");
            $scanedFiles = $api->scanFolders($path, true, true);
            if (sizeof($scanedFiles) > 0) {
                foreach ($scanedFiles as $fol) {
                    $fileName = explode('.', $fol);
                    $idExternal = $siglas;
                    if ($kind !== 'portada' && $kind !== 'banner') {
                        $idExternal .= preg_replace("/^0{1}/i", "", $fileName[0]);
                    }
                    error_log("\t Creando estructura media para $kind  " . $fol);
                    array_push($contentFile, array(
                        "type" => $kind,
                        "name" => $fileName[0],
                        "extension" => $fileName[1],
                        "id_external" => $idExternal
                    ));
                }
            }
        }
    }
    return $contentFile;
}

function inflateTranslation($contentFile, $langs, $kind, $idExternal, $text)
{
    foreach ($langs as $lang) {
        error_log("\t Creando estructura translations para $text con idioma $lang");
        array_push($contentFile, array(
            "translation" => '',
            "lang" => $lang,
            "kind" => $kind,
            "id_external" => $idExternal
        ));
    }
    return $contentFile;
}

function reloadBackup($animes, $basePath, $api)
{
    foreach ($animes as $siglas) {
        $sql = "";
        $bFolder = "$basePath/$siglas/.backup";
        $sqlFolder = "$bFolder/sql";
        $noSqlFolder = "$bFolder/nosql";
        $scanedFiles = $api->scanFolders("$sqlFolder", false);
        if (sizeof($scanedFiles) > 1) {
            foreach ($scanedFiles as $file) {
                $data = json_decode(file_get_contents($file));
                $pathElements = explode("/", $file);
                $table = str_replace(".json", "", end($pathElements));
                foreach ($data as $content) {
                    $keys = implode(",", array_keys(get_object_vars($content)));
                    $arr = array();
                    $val = array_values(get_object_vars($content));
                    foreach ($val as $value) {
                        $value = empty($value) || is_string($value) ? "$value" : $value;
                        array_push($arr, $val);

                        error_log(gettype($arr));
                        error_log($value);
                    }
                    $values = implode(",", $arr);
                    // $values = $arr;
                    $sql .= "INSERT INTO $table ($keys) VALUES($values); \n";
                }
            }
        }
        //error_log(json_encode($sql));
        //$api->writeFile($sql, '', __DIR__ . "/sql.sql");
        // if (!empty($sql)) {
        //     $api->getDb()->executeFromFile($sql);
        //     $translationsFile = "$noSqlFolder/translations.json";
        //     $mediaFile = "$noSqlFolder/media.json";
        //     if (file_exists($translationsFile) && file_exists($mediaFile)) {
        //         $data = json_decode(file_get_contents($translationsFile));
        //         foreach ($data as $d) {
        //             error_log(json_encode($api->apiReqNode("translation/new", $d)));
        //         }
        //         $data = json_decode(file_get_contents($mediaFile));
        //         foreach ($data as $d) {
        //             error_log(json_encode($api->apiReqNode("media/new", $d)));
        //         }
        //     }
        // }
    }
}
