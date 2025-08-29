<?php
// models/PersonaContacto.php
require_once __DIR__ . '/../config/database.php';

class PersonaContacto {
    private $conn;
    private $table_name = "personas_contacto";

    public $id_persona;
    public $nombre_persona;
    public $cargo;
    public $telefono;
    public $correo;
    public $id_cliente;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre_persona=:nombre_persona, cargo=:cargo, telefono=:telefono, 
                      correo=:correo, id_cliente=:id_cliente";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nombre_persona", $this->nombre_persona);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function obtenerPorCliente($id_cliente) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_cliente = :id_cliente 
                  ORDER BY nombre_persona ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cliente", $id_cliente);
        $stmt->execute();
        
        return $stmt;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_persona = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id_persona = $row['id_persona'];
            $this->nombre_persona = $row['nombre_persona'];
            $this->cargo = $row['cargo'];
            $this->telefono = $row['telefono'];
            $this->correo = $row['correo'];
            $this->id_cliente = $row['id_cliente'];
            return true;
        }
        return false;
    }
}
?>
