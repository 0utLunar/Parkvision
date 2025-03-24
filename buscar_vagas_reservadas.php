<?php
// Conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parkvision";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Consulta para buscar vagas reservadas
$sql = "SELECT numero_vaga, observacao_vaga FROM vagas WHERE status_vaga = 'reserved'";
$result = $conn->query($sql);

$vagas = array();

if ($result->num_rows > 0) {
    // Armazena os resultados em um array
    while($row = $result->fetch_assoc()) {
        $vagas[] = $row;
    }
}

// Retorna os dados em formato JSON
echo json_encode($vagas);

$conn->close();
?>
