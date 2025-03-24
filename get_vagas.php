<?php
// Conexão com o banco de dados
$mysqli = new mysqli("localhost", "root", "", "parkvision");

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Consulta para obter o status e a observação das vagas
$sql = "SELECT numero_vaga, status_vaga, observacao_vaga FROM vagas";
$result = $mysqli->query($sql);

$vagas = [];
while ($row = $result->fetch_assoc()) {
    $vagas[] = $row;
}

echo json_encode($vagas);

$mysqli->close();
?>
