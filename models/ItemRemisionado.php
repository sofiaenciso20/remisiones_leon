<?php
// models/ItemRemisionado.php
require_once __DIR__ . '/../config/database.php';

class ItemRemisionado {
    private $conn;
    private $table_name = "items_remisionados";

    public $id_item;
    public $id_remision;
    public $descripcion;
    public $cantidad;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para crear un solo ítem
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_remision, descripcion, cantidad) 
                  VALUES (:id_remision, :descripcion, :cantidad)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id_remision", $this->id_remision);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":cantidad", $this->cantidad);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Método para crear múltiples ítems
    public function crearItems($id_remision, $items) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_remision, descripcion, cantidad) 
                  VALUES (:id_remision, :descripcion, :cantidad)";
        
        $stmt = $this->conn->prepare($query);

        foreach ($items as $item) {
            $stmt->bindValue(":id_remision", $id_remision, PDO::PARAM_INT);
            $stmt->bindValue(":descripcion", $item['descripcion'], PDO::PARAM_STR);
            $stmt->bindValue(":cantidad", $item['cantidad'], PDO::PARAM_INT);
            $stmt->execute();
        }
        return true;
    }

    // Método para obtener ítems de una remisión
    public function obtenerPorRemision($id_remision) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_remision = :id_remision";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_remision", $id_remision);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
