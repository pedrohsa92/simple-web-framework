<section>
  <div class="row">
    <div class="col-12 text-phsa mb-3">
      <h5>Ambiente</h5>
    </div>

    <div class="col-12 mb-3">
      <div class="alert alert-light border mb-0">
        Estes dados representam o ambiente comercial e técnico da organização. As alterações são controladas por processos internos da plataforma.
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="phsaFakeInput">
        <div class="phsaFakeInputLabel">Ambiente</div>
        <div class="phsaFakeInputValue"><?= $organizacao->org_ambiente ?? '-' ?></div>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="phsaFakeInput">
        <div class="phsaFakeInputLabel">Status do ambiente</div>
        <div class="phsaFakeInputValue"><?= $organizacao->org_statusambiente ?? '-' ?></div>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="phsaFakeInput">
        <div class="phsaFakeInputLabel">Banco dedicado</div>
        <div class="phsaFakeInputValue text-break"><?= $organizacao->org_banco_dedicado ?? '-' ?></div>
      </div>
    </div>

    <div class="col-sm-6 mb-3">
      <div class="phsaFakeInput">
        <div class="phsaFakeInputLabel">URL dedicada</div>
        <div class="phsaFakeInputValue text-break">
          <?php if (!empty($organizacao->org_url_dedicada)): ?>
            <a href="<?= $organizacao->org_url_dedicada ?>" target="_blank" class="text-phsa">
              <?= $organizacao->org_url_dedicada ?>
            </a>
          <?php else: ?>
            -
          <?php endif ?>
        </div>
      </div>
    </div>

    <div class="col-sm-3 mb-3">
      <div class="phsaFakeInput">
        <div class="phsaFakeInputLabel">Data de migração</div>
        <div class="phsaFakeInputValue">
          <?= empty($organizacao->org_dtmigracao) ? '-' : date('d/m/Y H:i', strtotime($organizacao->org_dtmigracao)) ?>
        </div>
      </div>
    </div>

    <div class="col-sm-3 mb-3">
      <div class="phsaFakeInput">
        <div class="phsaFakeInputLabel">Última sincronização</div>
        <div class="phsaFakeInputValue">
          <?= empty($organizacao->org_dtultimasincronizacao) ? '-' : date('d/m/Y H:i', strtotime($organizacao->org_dtultimasincronizacao)) ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $this->push('style'); ?>
  <style>
  </style>
<?php $this->end(); ?>
