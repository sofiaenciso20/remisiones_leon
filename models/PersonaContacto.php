<?php
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

    // Crear una nueva persona de contacto
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

        if ($stmt->execute()) {
            $this->id_persona = $this->conn->lastInsertId();
            return true;
        }

        // Agregar información de error para depuración
        error_log("Error en la consulta: " . print_r($stmt->errorInfo(), true));
        return false;
    }

    // Leer todas las personas de contacto de un cliente
    public function leerPorCliente($id_cliente) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_cliente = ? 
                  ORDER BY nombre_persona";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_cliente);
        $stmt->execute();

        return $stmt;
    }

    // Método adicional para obtener personas por cliente (para compatibilidad)
    public function obtenerPorCliente($id_cliente) {
        return $this->leerPorCliente($id_cliente);
    }
}