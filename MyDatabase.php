<?php

class MyDatabase
{
    private $conexion;

    public function __construct($server, $user, $pass, $database)
    {
        $this->conexion = new mysqli($server, $user, $pass, $database);
    }

    public function escape($string) {
        return $this->conexion->real_escape_string($string);
    }

    public function query($string)
    {
        $result = $this->conexion->query($string);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function execute($string)
    {
        return $this->conexion->query($string);
    }

    public function lastInsertId()
    {
        return $this->conexion->insert_id;
    }

    public function __destruct(){
        $this->conexion->close();
    }
}
