<?php
function changeTextEpisodes()
{
    // $frase = readline("Introduce una frase (en minisculas): ");
    // $frase = strtoupper($frase);
    // print_r($frase);
    $anime = 'KNY';
    $scanedFiles = glob(__DIR__ . "/../media/dudas/$anime/episodes/(\[AnimeOnlineNinja]\_Kimetsu_no_Yaiba_BD_Castellano_)*");;
    if (sizeof($scanedFiles) > 0) {
        foreach ($scanedFiles as $file) {
            error_log("/FLU $file");
            // $exploes = explode("/",$file);
            // $name = end($exploes);
            // $newname = str_replace("", "", $name);
            // $newname = str_replace(" en Catalan", "", $newname);
            // $newname = str_replace("(360p_H.264-AAC)", "", $newname);
            // $newname = str_replace(" Final", "", $newname);
            // if(preg_match('/^0{1}[0-9]{2}/i',$newname)){
            //     $newname = preg_replace("/^0{1}/i", "" ,$newname);
            //}// $f = explode("/",$file);
            //array_pop($exploes);
            //$path = implode("/",$exploes);
            //rename("$path/$name","$path/$newname");
        }
    }
}

function deleteTables()
{
    // include_once dirname(__FILE__) . "/../securize.php";
    require_once __DIR__ . '/../classes/database.php';
    $db = new Database();
    $sql = "SELECT tablename FROM pg_catalog.pg_tables
        WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'";
    $tables = $db->listar($sql);
    foreach ($tables as $table) {
        $sql = "DELETE FROM $table->tablename";
        $deleted = $db->ejecutar($sql);
        if ($deleted) {
            error_log("Deleted table {$table->tablename}");
        }
    }
    //reiniciamos el id sequences de todas las tablas
    $sql = "SELECT c.relname AS seq FROM pg_class c WHERE c.relkind = 'S';";
    $sequences = $db->listar($sql);
    foreach ($sequences as $sequence) {
        $db->ejecutar("ALTER SEQUENCE $sequence->seq RESTART;");
    }
}

function dropTables()
{
    include_once dirname(__FILE__) . "/../securize.php";
    require_once __DIR__ . '/../classes/database.php';
    $db = new Database();
    $sql = "SELECT tablename FROM pg_catalog.pg_tables
        WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'";
    $tables = $db->listar($sql);
    foreach ($tables as $table) {
        error_log("Table: $table->tablename");
        error_log("//////////////////////////");
        $sql = "DROP TABLE IF EXISTS  $table->tablename";
        $deleted = $db->ejecutar($sql);
        if ($deleted) {
            error_log("DROPED table " . PHP_EOL);
        }
    }
    // $sql = "SELECT count(*) FROM (SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema') AS tables;";
    // $tables = $db->obtener_una_columna($sql);
    // error_log("Number of tables $tables");
}

function getSizeDB()
{
    include_once dirname(__FILE__) . "/../securize.php";
    require_once __DIR__ . '/../classes/database.php';
    $db = new Database();
    $database = $db->getBase_datos();
    $sql = "select pg_size_pretty(pg_database_size('$database')); ";
    $tables = $db->obtener_una_columna($sql);
    error_log("\nTamaño de la base da datos: $tables");
}

function ManageFiles()
{
    //download youtube-dl -f mp4 $url
    //convert
    $basePath = __DIR__ . "/../media/animes";
    $path = "$basePath/NNT/episodes/*.avi";
    require_once __DIR__ . '/../classes/api.php';
    $api = new Api();
    $scanedFiles = $api->scanFolders($path);
    if (sizeof($scanedFiles) > 0) {
        foreach ($scanedFiles as $file) {
            if (is_file($file) && strstr($file, 'Arc the Lad ')) {
                $exploes = explode("/", $file);
                $fileName = end($exploes);
                $arrFile = explode('.', $fileName);
                $name = $arrFile[0];
                $extension = $arrFile[1];
                if (!preg_match("/\.(mp4)/i", $fileName)) {
                    shell_exec("ffmpeg -i uploads/" . $name . $extension . " uploads/" . $name . ".mp4");
                    // This condition is only if you wish to allow uploading of specific file types    
                    //unlink($fileTmpLoc); // Remove the uploaded file from the PHP temp folder
                    //exit();
                }

                if (strstr($name, ".mp4")) {
                    //system("ffmpeg -i $file '${$file%.*}'.mp4 && rm '$file'");
                    //$str = "ffmpeg -i ".$video_path." ".$p_path."frames_%05d.png";
                    //system($str);
                    //don't want to convert mp4 files
                }
            }
        }
    }
    #convertir mkb a mp4 selecionan pista de audio a convertir
    # ffmpeg -i 01.mkv -c:v libx264 -c:a ac3 -crf 20 -map 0:v:0 -map 0:a:1 01.mp4
    # https://ostechnix.com/20-ffmpeg-commands-beginners/
    #youtube-dl -f mp4 https://www.youtube.com/playlist?list=PL1rvSvnnyoDfq6mlcZPJ3fUqlOwAQnMKx
}

function downloadAssets()
{
    exec("wget https://gitlab.com/jorbencas/Anime/-/blob/0cdfd71bb142f7da85ceda094a9f131e0acd13d5/tema/libs/jquery-3.4.1.min.js -O /var/www/Anime/public/libs/jquery.js -o logs/down_lib_assets.log");
    exec("wget https://use.fontawesome.com/releases/v5.15.1/fontawesome-free-5.15.1-web.zip -O /var/www/Anime/public/libs/fontawesome.zip");
    exec("unzip /var/www/Anime/public/libs/fontawesome.zip -d /var/www/Anime/public/libs/fotawesome");
    exec("mv /var/www/Anime/public/libs/fontawesome/fontawesome* /var/www/Anime/public/libs/fontawesome");
    exec("rm /var/www/Anime/public/libs/fontawesome*.zip");
}

print_r("\n Scripts de mantenimiento: 
    1 getSizeDB
    2 ManageFiles
    3 downloadAssets
    4 dropTables
    5 changeTextEpisodes
    Seleciona una opción: ");
$tipoEjercicios = (int)trim(fgets(STDIN), "\n");
switch ($tipoEjercicios) {
    case 1:
        getSizeDB();
        break;
    case 2:
        ManageFiles();
        break;
    case 3:
        downloadAssets();
        break;
    case 4:
        deleteTables();
        break;
    case 5:
        changeTextEpisodes();
        break;
    default:
        print_r("No existe ninguna funcion $tipoEjercicios");
        break;
}
