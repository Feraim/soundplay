<?php

class Transaccion {
    private $db;
    protected $id_pago;
    protected $id_usuario_emisor;
    protected $id_artista_receptor;
    protected $importe_total;
    protected $iva_applied;
    protected $fecha_pago;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
}