<?php

$mysqli = new mysqli("localhost", "root", "", "parkvision");

function reservar_vaga($numero_vaga, $observacao) {
    // Verifica se a vaga está livre
    $sql = "SELECT status_vaga FROM vagas WHERE numero_vaga = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $numero_vaga);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['status_vaga'] == 'free') {
        // Atualiza o status da vaga
        $sql = "UPDATE vagas SET status_vaga = 'reserved', observacao_vaga = ? WHERE numero_vaga = ?";
        // ...
        return ['status' => 'success'];
    } else {
        return ['status' => 'error', 'message' => 'Vaga já ocupada'];
    }
}

// Processa a requisição AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $numero_vaga = $data['numero_vaga'];
    $observacao = $data['observacao'];

    $resultado = reservar_vaga($numero_vaga, $observacao);
    echo json_encode($resultado);
}