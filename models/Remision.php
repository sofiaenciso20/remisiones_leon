<?php
// models/Remision.php
require_once __DIR__ . '/../config/database.php';

class Remision {
    private $conn;
    private $table_name = "remisiones";

    public $id_remision;
    public $numero_remision;
    public $fecha_emision;
    public $id_cliente;
    public $id_persona;
    public $id_usuario;
    public $observaciones;
    public $id_estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear() {
        // Generar número de remisión automático
        $this->numero_remision = $this->generarNumeroRemision();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  SET numero_remision=:numero_remision, fecha_emision=:fecha_emision, 
                      id_cliente=:id_cliente, id_persona=:id_persona, id_usuario=:id_usuario, 
                      observaciones=:observaciones, id_estado=:id_estado";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":numero_remision", $this->numero_remision);
        $stmt->bindParam(":fecha_emision", $this->fecha_emision);
        $stmt->bindParam(":id_cliente", $this->id_cliente);
        $stmt->bindParam(":id_persona", $this->id_persona);
        $stmt->bindParam(":id_usuario", $this->id_usuario);
        $stmt->bindParam(":observaciones", $this->observaciones);
        $stmt->bindParam(":id_estado", $this->id_estado);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function generarNumeroRemision() {
        $query = "SELECT MAX(numero_remision) as ultimo_numero FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $ultimo_numero = $row['ultimo_numero'] ? $row['ultimo_numero'] : 0;
        return $ultimo_numero + 1;
    }

    public function buscarRemisiones($termino = '', $fecha_inicio = '', $fecha_fin = '') {
        $query = "SELECT r.*, c.nombre_cliente, e.nombre_estado 
                  FROM " . $this->table_name . " r
                  LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
                  LEFT JOIN estados e ON r.id_estado = e.id_estado
                  WHERE 1=1";
        
        $params = array();
        
        if(!empty($termino)) {
            $query .= " AND (r.numero_remision LIKE :termino OR c.nombre_cliente LIKE :termino)";
            $params[':termino'] = "%{$termino}%";
        }
        
        if(!empty($fecha_inicio)) {
            $query .= " AND r.fecha_emision >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        
        if(!empty($fecha_fin)) {
            $query .= " AND r.fecha_emision <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $query .= " ORDER BY r.fecha_emision DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function obtenerPorId($id) {
        $query = "SELECT r.*, c.nombre_cliente, c.nit, c.direccion, c.telefono,
                         pc.nombre_persona, e.nombre_estado
                  FROM " . $this->table_name . " r
                  LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
                  LEFT JOIN personas_contacto pc ON r.id_persona = pc.id_persona
                  LEFT JOIN estados e ON r.id_estado = e.id_estado
                  WHERE r.id_remision = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function contarRemisiones($termino = '', $fecha_inicio = '', $fecha_fin = '') {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table_name . " r
                  LEFT JOIN clientes c ON r.id_cliente = c.id_cliente
                  LEFT JOIN estados e ON r.id_estado = e.id_estado
                  WHERE 1=1";
    
        $params = array();
    
        if(!empty($termino)) {
            $query .= " AND (r.numero_remision LIKE :termino OR c.nombre_cliente LIKE :termino)";
            $params[':termino'] = "%{$termino}%";
        }
    
        if(!empty($fecha_inicio)) {
            $query .= " AND r.fecha_emision >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
    
        if(!empty($fecha_fin)) {
            $query .= " AND r.fecha_emision <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
    
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>
