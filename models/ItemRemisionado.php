<?php
// models/ItemRemisionado.php
require_once '../config/database.php';

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

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_remision=:id_remision, descripcion=:descripcion, cantidad=:cantidad";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id_remision", $this->id_remision);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":cantidad", $this->cantidad);
        
        return $stmt->execute();
    }

    public function obtenerPorRemision($id_remision) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_remision = :id_remision 
                  ORDER BY id_item ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_remision", $id_remision);
        $stmt->execute();
        
        return $stmt;
    }

    public function eliminarPorRemision($id_remision) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_remision = :id_remision";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_remision", $id_remision);
        return $stmt->execute();
    }
}
?>
