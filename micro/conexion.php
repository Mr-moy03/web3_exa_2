<?php
class Conexion
{
    private $pdo;

    public function __construct(){
        try{
            // Agregado ;charset=utf8mb4 al final del DSN
            $this->pdo = new PDO("mysql:host=localhost;dbname=academico;charset=utf8mb4", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // En una API, es mejor devolver un JSON de error si falla la conexión
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(["error" => "Error de conexión a la base de datos"]);
            exit;
        }
    }

    public function obtenerConexion(){
        return $this->pdo;
    }
}
?>