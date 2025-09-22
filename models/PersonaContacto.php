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

    // Obtener todas las personas de contacto
    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre_persona";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear nueva persona de contacto
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre_persona=:nombre_persona, cargo=:cargo, 
                      telefono=:telefono, correo=:correo, id_cliente=:id_cliente";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre_persona = htmlspecialchars(strip_tags($this->nombre_persona));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->id_cliente = htmlspecialchars(strip_tags($this->id_cliente));
        
        // Vincular valores
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

    // Obtener personas de contacto por cliente (para usar en remisiones)
    public function obtenerPorCliente($id_cliente) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_cliente = :id_cliente 
                  ORDER BY nombre_persona ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_cliente", $id_cliente);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener persona de contacto por ID y cargar en el objeto
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

    // Obtener persona de contacto por ID y retornar como array
    public function obtenerPorIdArray($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_persona = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método adicional: actualizar persona de contacto
    public function actualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre_persona = :nombre_persona, cargo = :cargo, 
                      telefono = :telefono, correo = :correo, id_cliente = :id_cliente
                  WHERE id_persona = :id_persona";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre_persona = htmlspecialchars(strip_tags($this->nombre_persona));
        $this->cargo = htmlspecialchars(strip_tags($this->cargo));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->id_cliente = htmlspecialchars(strip_tags($this->id_cliente));
        $this->id_persona = htmlspecialchars(strip_tags($this->id_persona));
        
        // Vincular valores
        $stmt->bindParam(":nombre_persona", $this->nombre_persona);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->bindParam(":id_persona", $this->id_persona);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Método adicional: eliminar persona de contacto
    public function eliminar() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_persona = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_persona);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>