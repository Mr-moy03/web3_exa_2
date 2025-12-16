<?php

require 'conexion.php';

// Importante: Decirle al cliente que siempre responderemos JSON
//header("Content-Type: application/json");

$conexion = new Conexion();
$pdo = $conexion->obtenerConexion();

// --- METODO GET (Listar o Buscar) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM alumno";
    $parametros = [];

    if (isset($_GET['ci'])) {
        $sql .= " WHERE ci = :ci";
        $parametros[":ci"] = $_GET['ci'];
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        header("HTTP/1.1 200 OK");
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(["error" => "Error al consultar datos"]);
    }
    exit;
}

// --- METODO POST (Insertar) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar que lleguen los datos necesarios
    if (!isset($_POST['ci']) || !isset($_POST['nombre']) || !isset($_POST['paterno'])) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(["error" => "Faltan datos (ci, nombre, paterno)"]);
        exit;
    }

    // Usar nombres de columnas explícitos es más seguro
    $sql = "INSERT INTO alumno (ci, nombre, paterno) VALUES (:ci, :nombre, :paterno)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':ci', $_POST['ci']);
        $stmt->bindValue(':nombre', $_POST['nombre']);
        $stmt->bindValue(':paterno', $_POST['paterno']);
        $stmt->execute();

        // 201 significa "Created" (Creado)
        header("HTTP/1.1 201 Created");
        echo json_encode(["mensaje" => "Alumno creado", "id" => $_POST['ci']]);

    } catch (PDOException $e) {
        // Capturamos si el CI ya existe (Error de duplicado)
        header("HTTP/1.1 409 Conflict");
        echo json_encode(["error" => "Error al insertar: " . $e->getMessage()]);
    }
    exit;
}
?>