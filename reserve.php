<?php
// Configurações de conexão ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "parkvision";

// Criando conexão com o banco
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar se há erro na conexão
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados']));
}

// Verificar se o formulário foi enviado corretamente
if (isset($_POST['spot']) && isset($_POST['obs'])) {
    $numeroVaga = intval($_POST['spot']);
    $observacao = $conn->real_escape_string($_POST['obs']);
    
    // Verifica se a vaga já está reservada ou ocupada
    $checkStatusSql = "SELECT status_vaga FROM vagas WHERE numero_vaga = $numeroVaga";
    $result = $conn->query($checkStatusSql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['status_vaga'] === 'reserved' || $row['status_vaga'] === 'occupied') {
            echo json_encode(['status' => 'error', 'message' => 'A vaga já está reservada ou ocupada.']);
            exit;
        }
    }

    // Atualiza a vaga para "reserved" no banco de dados
    $sql = "UPDATE vagas SET status_vaga = 'reserved', observacao_vaga = '$observacao' WHERE numero_vaga = $numeroVaga";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Vaga reservada com sucesso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao reservar a vaga']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Parâmetros incompletos']);
}

$conn->close();
?>
