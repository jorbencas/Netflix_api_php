<?php
    class Database {
        private $servidor;
        private $puerto;
        private $usuario;
        private $password;
        private $base_datos;
        private $link;
        static $_instance;
        private $config_file;
        public function __construct($init = null) {
            $this->setConexion($init);
            $this->conectar();
        }

        private function setConexion($init) {
            $basePath = __DIR__.'/../conf';
            if (isset($init) && $init == 'init') $this->config_file = "$basePath/dbinit.ini";
            else $this->config_file = "$basePath/db.ini";
            
            if (file_exists($this->config_file)) {
                $cnfg = parse_ini_file( $this->config_file);
                $this->servidor = $cnfg['HOST'];
                $this->puerto = $cnfg['PORT'];
                $this->base_datos = $cnfg['DATABASE'];
                $this->usuario = $cnfg['USER'];
                $this->password = $cnfg['PASSWORD'];
            } else {
                error_log("No existe el fichero de configuración de la base de datos {$this->config_file}");
                die();
            }
        }
        
        private function conectar() {
            try {
                if (isset($this->servidor) && isset($this->puerto) && isset($this->base_datos)) {
                    $db_credentials = 'pgsql:host='.$this->servidor.';port='.$this->puerto.';dbname='.$this->base_datos;
                    $options = array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        // PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        PDO::ATTR_PERSISTENT => true,
                        PDO::ATTR_EMULATE_PREPARES => false
                    );
                    $this->link = new PDO($db_credentials, $this->usuario, $this->password, $options);
                } else {
                    error_log("No se ha podido establecer la conexión con la base de datos");
                }
            } catch (Exception $th) {
                error_log($th->getMessage());
            }
        }

        public function ejecutar($sql) {
            $link = $this->getLink();
            if (isset($link)) {
                $statement = $link->prepare($sql);
                if ($statement->execute()) {
                    $dbError=$statement->errorInfo();
                    if (isset($statement->errorCode) && $statement->errorCode!='00000') {
                        error_log($dbError[2] . ". [SQL: " . $statement->queryString . "] [SQLSTATE: " . $dbError[0] . "] [Error Code: " . $dbError[1] . "]");
                    } else {
                        // error_log("--------------------------");
                        // error_log($sql);
                    }
                    return $statement;
                } else {
                    error_log("--------------------------");
                    error_log("Error no se ha podido establezer la conexión");
                    return null;
                }
            } else {
                return null;
            }
        }

        public function executeFromFile($data) {
            $delimiter = ");";
            $inserts = explode($delimiter,file_get_contents($data));
            array_pop($inserts);
            foreach ($inserts as $key => $insert) {
                $this->ejecutar($insert.$delimiter);
            }
        }

        public function listar($sql) {
            $statement = $this->ejecutar($sql);
            if (isset($statement)) {
                return $statement->fetchAll(PDO::FETCH_OBJ);
                // $array = array();
                // while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
                // //while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                //     array_push($array, $row);
                // }
                // return $array;
            } else {
                return null;
            }
        }

        public function obtener_uno($sql) {
            $statement = $this->ejecutar($sql);
            if (isset($statement)) {
                return $statement->fetchObject("stdClass", array()); 
                //return $statement->fetch(PDO::FETCH_OBJ);
            } else {
                return $statement;
            }
        }

        public function obtener_una_columna($sql) {
            $statement = $this->ejecutar($sql);
            if (isset($statement)) {
                return $statement->fetchColumn(); 
            } else {
                return $statement;
            }
        }

        public function transaction($sql) {
            try {
                $statement = $this->link->prepare($sql/*"UPDATE user SET name = :name"*/);
        	    /**
                 * Inicia una transaccion. Una vez iniciada se pueden ejecutar distintas sentencias SQL
                 * que al finalizar la transaccion se ejecutaran todas como una sola unidad, pudiendo volver 
                 * atras si hay algun error en alguna transaccion.
                 * 
                 * ATENCION: Solo funciona con base de datos que soporten transacciones. 
                 * Por ejemplo, no funciona con bases de datos MYSAM en MYSQL
                 * 
                 */
                $this->link->beginTransaction();

                $statement->execute();
                //$statement->execute(["name"=>'Joe']);
                /**
                 * Finaliza la transaccion y envia las sentencias SQL para que se ejecuten.
                 */
                $this->link->commit();
            } catch (\Exception $e) {
                if ($this->link->inTransaction()) {
                    /**
                     * Si falla una transaccion puede utilizarse este metodo para que vuelva 
                     * atras todos los cambios que se hayan hecho dentro de la transaccion
                     * en la base de datos previos a la sentencia que fallo
                     */
                    $this->link->rollback();
                    // If we got here our two data updates are not in the database
                }
                throw $e;
            }
        }

        public function getLink() {
            return $this->link;
        }

        public function getBase_datos(){
            return $this->base_datos;
        }
    }
?>