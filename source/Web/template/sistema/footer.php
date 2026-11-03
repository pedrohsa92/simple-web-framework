<?php
  $empresaFooter = $contexto->empresa ?? $usuarioLogado->empresa ?? (object) [];
  $nomeFooter = $empresaFooter->emp_nomefantasia ?? $empresaFooter->emp_razaosocial ?? $contexto->organizacao->org_nomefantasia ?? $contexto->organizacao->org_razaosocial ?? 'Organização';
?>

<footer id="simpleFooter" class="simpleFooter">
  <div class="simpleFooterIcon" aria-hidden="true"><img src="<?= Helpers::storage($projeto->prj_favicon ?? null, 'storage/global/favicon.png') ?>" alt=""></div>
  <div class="simpleFooterText">© <?= date('Y') ?> <?= htmlspecialchars((string) $nomeFooter, ENT_QUOTES, 'UTF-8') ?>. Todos os direitos reservados.</div>
  <div class="simpleFooterText">Desenvolvido por <a href="https://phsa.com.br/" target="_blank" rel="noopener noreferrer">PHSA Tecnologia</a></div>
</footer>
