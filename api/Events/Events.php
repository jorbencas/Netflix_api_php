<?php
function Events($api) {
    $GET = $api->getGET();
    switch ($api->getAction()) {
        case 'deleteAll': $result = deleteAll($api); break;
        case 'createEvent': $result = createevents($api); break;
        case 'removeEvent': $result = removeevents($api); break;
        case 'subcribeevent': $result = subcribeevent($api); break;
        case 'unsubcribeevent': $result = unsubcribeevent($api); break;
        case 'apione': $result = getOnevent($api); break;
        case 'apislide': $result = getMyevents($api); break;
        case 'apiby': $result = getAllevents($api); break;
        default: $result = null; break;
    }
    return $result;
}

function getAllevents($api) {
    $db = $api->getDb();
    $sql = "SELECT e.* FROM events as e WHERE e.usuario is null";
    return $db->listar($sql);
}

function getMyevents($api) {
    $db = $api->getDb();
    $sql = "SELECT e.*,a.name, a.idbook, a.foto  FROM authors as a RIGHT JOIN events as e ON(e.idauthor = a.id) WHERE usuario = '$arrArgument'";
    return $db->listar($sql);
}

function createevents($api) {
    $db = $api->getDb();
    var_dump($arrArgument);

    $name = $arrArgument['title'];
    $type = $arrArgument['startTime'];
    $price = $arrArgument['endTime'];
    $status = $arrArgument['desc'];
    $latitude = $arrArgument['allDay'];

    $sql = "INSERT INTO events (titulo, description, timestart, timeend, AllDay,"
        . " idauthor"
        . " ) VALUES ('$name', '$type', '$price', '$status', '$latitude')";


    return $db->ejecutar($sql);
}

function removeevents($api) {
    $db = $api->getDb();
    $id = $arrArgument['idevent'];
    $user = $arrArgument['username'];
    $sql = "DELETE FROM events WHERE id = $id";
    return $db->ejecutar($sql);
}

function subcribeevent($api) {
    $db = $api->getDb();
    $usuario = $arrArgument['usuario'];
    $id = $arrArgument['id'];

    $sql = "UPDATE events SET usuario = '$usuario'  WHERE id = '$id'";
    $db->ejecutar($sql);

    $sql_prueba = "SELECT * FROM  events WHERE usuario = '$usuario' AND id = '$id'";

    return $db->listar($db->ejecutar($sql_prueba));
}


function unsubcribeevent($api) {
    $db = $api->getDb();
    $usuario = $arrArgument['usuario'];
    $id = $arrArgument['id'];

    $sql = "UPDATE events SET usuario = NULL  WHERE id = '$id' AND usuario = '$usuario'";
    $db->ejecutar($sql);

    $sql_prueba = "SELECT * FROM  events WHERE id = '$id'";

    return $db->listar($db->ejecutar($sql_prueba));
}
