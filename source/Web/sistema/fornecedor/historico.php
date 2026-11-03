<?php
  $historicoRegistro = $fornecedor;
  $historicoPrefixo = 'for_';
  $historicoId = (int) ($fornecedor->for_id ?? 0);
  $historicoStatus = (string) ($fornecedor->for_status ?? '');
  $historicoStatusClass = (string) ($fornecedor->for_statusclass ?? 'secondary');
  $historicoChave = (string) ($fornecedor->for_chave ?? '');
  $historicoLogs = json_decode((string) ($fornecedor->for_log ?? '[]'), true);
  if (!is_array($historicoLogs)) $historicoLogs = [];
  include __DIR__ . '/../historicoPadrao.php';
?>
