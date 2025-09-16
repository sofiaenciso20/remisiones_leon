<?php
// obtener_personas_contacto.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PersonaContacto.php';

header('Content-Type: application/json');

if (isset($_POST['id_cliente'])) {
    // Obtener conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    // Crear instancia del modelo PersonaContacto
    $personaContacto = new PersonaContacto($db);
    
    // Obtener personas de contacto por cliente
    $stmt = $personaContacto->leerPorCliente($_POST['id_cliente']);
    
    // Preparar array de resultados
    $personas = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $personas[] = $row;
    }
    
    echo json_encode($personas);
} else {
    echo json_encode([]);
}