<?php
header('Content-Type: application/json');
require_once __DIR__ . "/conexao.php";

$query = "SELECT IdMarcas, nome, imagem FROM MARCAS";
$result = $conn->query($query);

$marcas = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $marcas[] = [
            'id' => $row['IdMarcas'],
            'nome' => $row['nome'],
            'imagem' => "data:image/jpeg;base64," . base64_encode($row['imagem'])
        ];
    }
}

echo json_encode($marcas);
