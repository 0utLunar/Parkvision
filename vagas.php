<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parkvision";

// Cria uma conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Consulta para buscar os dados das vagas
$sql = "SELECT numero_vaga, status_vaga FROM vagas";
$result = $conn->query($sql);

$vagas = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vagas[] = $row;
    }
}

// Retorna os dados no formato JSON
echo json_encode($vagas);

// Fecha a conexão
$conn->close();
?>
