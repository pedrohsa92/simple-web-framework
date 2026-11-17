<?php
  $logsPwa = json_decode((string) ($pwaConfig->pwa_log ?? '[]'), true);
  $logsPwa = is_array($logsPwa) ? $logsPwa : [];
?>

<div>
  <?php
    $historicoRegistro = $pwaConfig ?? (object) [];
    $historicoPrefixo = 'pwa_';
    $historicoId = (int) ($pwaConfig->pwa_id ?? 0);
    $historicoStatusBruto = (string) ($pwaConfig->pwa_status ?? '');
    $historicoStatus = $historicoStatusBruto === '1' ? 'Ativo' : ($historicoStatusBruto === '0' ? 'Inativo' : $historicoStatusBruto);
    $historicoStatusClass = $historicoStatus === 'Ativo' ? 'success' : ($historicoStatus === 'Inativo' ? 'danger' : 'secondary');
    $historicoLogs = $logsPwa;
    include __DIR__ . '/../historicoPadrao.php';
  ?>
</div>
