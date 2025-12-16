<?php
require 'conexion.php';

// Cabeceras para que funcione como API JSON
header("Content-Type: application/json");

$conexion = new Conexion();
$pdo = $conexion->obtenerConexion();

// Detectamos qué método está usando el cliente (GET, POST, PUT, DELETE)
$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {

    // --- 1. LEER (READ) ---
    case 'GET':
        $sql = "SELECT * FROM alumno";
        $parametros = [];

        if (isset($_GET['ci'])) {
            $sql .= " WHERE ci = :ci";
            $parametros[':ci'] = $_GET['ci'];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($resultados);
        break;

    // --- 2. CREAR (CREATE) ---
    case 'POST':
        if (isset($_POST['ci'], $_POST['nombre'], $_POST['paterno'])) {
            $sql = "INSERT INTO alumno (ci, nombre, paterno) VALUES (:ci, :nombre, :paterno)";
            $stmt = $pdo->prepare($sql);

            try {
                $stmt->execute([
                    ':ci' => $_POST['ci'],
                    ':nombre' => $_POST['nombre'],
                    ':paterno' => $_POST['paterno']
                ]);
                header("HTTP/1.1 201 Created");
                echo json_encode(["mensaje" => "Alumno creado exitosamente"]);
            } catch (PDOException $e) {
                header("HTTP/1.1 409 Conflict");
                echo json_encode(["error" => "Error, tal vez el CI ya existe"]);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["error" => "Faltan datos"]);
        }
        break;

    // --- 3. ACTUALIZAR (UPDATE) ---
    case 'PUT':
        // PHP no tiene $_PUT nativo, así que leemos la entrada "cruda"
        // Esto convierte los datos recibidos (form-urlencoded) en un array $_PUT simulado
        parse_str(file_get_contents("php://input"), $_PUT);

        if (isset($_PUT['ci'], $_PUT['nombre'], $_PUT['paterno'])) {
            $sql = "UPDATE alumno SET nombre = :nombre, paterno = :paterno WHERE ci = :ci";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nombre' => $_PUT['nombre'],
                ':paterno' => $_PUT['paterno'],
                ':ci' => $_PUT['ci']
            ]);

            // rowCount nos dice si se modificó alguna fila
            if ($stmt->rowCount() > 0) {
                echo json_encode(["mensaje" => "Alumno actualizado"]);
            } else {
                echo json_encode(["mensaje" => "No se encontró el alumno o no hubo cambios"]);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["error" => "Faltan datos para actualizar"]);
        }
        break;

    // --- 4. ELIMINAR (DELETE) ---
    case 'DELETE':
        // Para borrar, generalmente enviamos el CI en la URL: microser.php?ci=123
        if (isset($_GET['ci'])) {
            $sql = "DELETE FROM alumno WHERE ci = :ci";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':ci' => $_GET['ci']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["mensaje" => "Alumno eliminado"]);
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(["mensaje" => "No se encontró el alumno para eliminar"]);
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo json_encode(["error" => "Falta el CI a eliminar"]);
        }
        break;

    default:
        header("HTTP/1.1 405 Method Not Allowed");
        echo json_encode(["error" => "Metodo no permitido"]);
        break;
}
?>