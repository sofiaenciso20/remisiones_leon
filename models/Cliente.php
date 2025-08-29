<?php
// models/Cliente.php
require_once __DIR__ . '/../config/database.php';

class Cliente {
    private $conn;
    private $table_name = "clientes";

    public $id_cliente;
    public $nombre_cliente;
    public $tipo_cliente;
    public $nit;
    public $direccion;
    public $telefono;
    public $correo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre_cliente=:nombre_cliente, tipo_cliente=:tipo_cliente, nit=:nit, 
                      direccion=:direccion, telefono=:telefono, correo=:correo";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nombre_cliente", $this->nombre_cliente);
        $stmt->bindParam(":tipo_cliente", $this->tipo_cliente);
        $stmt->bindParam(":nit", $this->nit);
        $stmt->bindParam(":direccion", $this->direccion);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":correo", $this->correo);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function buscar($termino) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (nombre_cliente LIKE :termino OR nit LIKE :termino) 
                  ORDER BY nombre_cliente ASC";
        
        $stmt = $this->conn->prepare($query);
        $termino = "%{$termino}%";
        $stmt->bindParam(":termino", $termino);
        $stmt->execute();
        
        return $stmt;
    }

    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY nombre_cliente ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cliente = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id_cliente = $row['id_cliente'];
            $this->nombre_cliente = $row['nombre_cliente'];
            $this->tipo_cliente = $row['tipo_cliente'];
            $this->nit = $row['nit'];
            $this->direccion = $row['direccion'];
            $this->telefono = $row['telefono'];
            $this->correo = $row['correo'];
            return true;
        }
        return false;
    }

    public function contarTotal() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
