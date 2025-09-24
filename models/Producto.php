<?php
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