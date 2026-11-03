<?php
  $historicoRegistro = $cliente;
  $historicoPrefixo = 'cli_';
  $historicoId = (int) ($cliente->cli_id ?? 0);
  $historicoStatus = '';
  $historicoStatusClass = 'secondary';
  $historicoChave = '';
  $historicoLogs = json_decode((string) ($cliente->cli_log ?? '[]'), true);
  if (!is_array($historicoLogs)) $historicoLogs = [];
  include __DIR__ . '/../historicoPadrao.php';
?>
