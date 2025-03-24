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

// Receber dados JSON
$data = json_decode(file_get_contents("php://input"), true);
$numero_vaga = $data['numero_vaga'];

// Atualiza o status da vaga para 'free'
$sql = "UPDATE vagas SET status_vaga = 'free', observacao_vaga = NULL WHERE numero_vaga = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $numero_vaga);

if ($stmt->execute()) {
    $response = array("status" => "success", "message" => "Reserva excluida com sucesso!");
} else {
    $response = array("status" => "error", "message" => "Erro ao excluir a reserva da vaga.");
}

$stmt->close();
$conn->close();

// Retorna a resposta em formato JSON
echo json_encode($response);
?>
