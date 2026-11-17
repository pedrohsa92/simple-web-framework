<div>
  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <h5 class="text-phsa mb-2"><i class="fa-solid fa-icons me-1"></i>Ícones do aplicativo</h5>
      <p class="text-muted small">Os ícones são gerados automaticamente a partir do logo da empresa ao salvar a configuração do PWA.</p>
      <?php if (empty($pwaImagens['icones'])): ?>
        <div class="alert alert-light border mb-0">Salve a configuração do PWA para gerar os ícones automaticamente.</div>
      <?php else: ?>
        <div class="d-flex flex-wrap gap-3">
          <?php foreach ($pwaImagens['icones'] as $icone): ?>
            <div class="text-center">
              <div class="border rounded p-2 bg-white"><img src="<?= $escPwa($icone['src'] ?? '') ?>" alt="Ícone <?= $escPwa($icone['label'] ?? '') ?>" width="72" height="72" style="object-fit:contain"></div>
              <div class="small mt-1"><?= $escPwa($icone['label'] ?? '-') ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="col-12 col-lg-6">
      <h5 class="text-phsa mb-2"><i class="fa-solid fa-camera me-1"></i>Capturas de tela</h5>
      <p class="text-muted small">As capturas técnicas são geradas automaticamente para o manifesto do PWA.</p>
      <?php if (empty($pwaImagens['capturas'])): ?>
        <div class="alert alert-light border mb-0">Nenhuma captura de tela foi gerada.</div>
      <?php else: ?>
        <div class="row g-2">
          <?php foreach ($pwaImagens['capturas'] as $captura): ?>
            <div class="col-6 col-md-4">
              <a href="<?= $escPwa($captura['src'] ?? '') ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                <img src="<?= $escPwa($captura['src'] ?? '') ?>" alt="Captura <?= $escPwa($captura['label'] ?? '') ?>" class="img-fluid border rounded">
                <span class="small d-block text-center mt-1"><?= $escPwa($captura['label'] ?? '-') ?></span>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
