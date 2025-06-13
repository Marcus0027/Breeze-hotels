<?php
session_start();

// Forçar cabeçalho JSON
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Método não permitido']));
}

// Receber dados
$data = json_decode(file_get_contents('php://input'), true);

// Validar dados
if (empty($data['currency']) || empty($data['symbol']) || !isset($data['rate'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Dados inválidos']));
}

// Atualizar sessão
$_SESSION['moeda_selecionada'] = $data['currency'];
$_SESSION['simbolo_moeda'] = $data['symbol'];
$_SESSION['taxa_conversao'] = (float)$data['rate'];

echo json_encode(['success' => true]);