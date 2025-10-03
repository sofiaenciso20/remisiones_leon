<?php
// models/Producto.php
require_once __DIR__ . '/../config/database.php';

class Producto {
    private $conn;
    private $table_name = "productos";

    public $id_producto;
    public $nombre_producto;
    public $fecha_creacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        // Verificar si el producto ya existe
        $query_check = "SELECT id_producto FROM " . $this->table_name . " WHERE nombre_producto = :nombre_producto";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':nombre_producto', $this->nombre_producto);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            throw new Exception("El producto ya existe en la base de datos");
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                 SET nombre_producto = :nombre_producto";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre_producto", $this->nombre_producto);
        
        if ($stmt->execute()) {
            $this->id_producto = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function buscar($termino = "") {
        $query = "SELECT id_producto as id, nombre_producto as text 
                 FROM " . $this->table_name . " 
                 WHERE nombre_producto LIKE :termino 
                 ORDER BY nombre_producto 
                 LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $termino = '%' . $termino . '%';
        $stmt->bindParam(":termino", $termino);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>