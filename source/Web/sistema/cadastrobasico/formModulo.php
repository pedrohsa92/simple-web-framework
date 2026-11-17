<?php
  $this->layout('sistema::sistema');
  $prefixo = $config->prefixo;
  $idCampo = $prefixo . '_id';
  $registroId = (int) ($registro->{$idCampo} ?? 0);
  $tipoPadrao = $tiposCadastro[0]->value ?? 'Empresa';
  $urlBase = Helpers::url("{$modulo}/basico/{$config->modulo}");
  $urlSalvar = $urlBase . '/salvar';
  $urlVoltar = $urlBase;
  $logs = json_decode((string) ($registro->{$prefixo . '_log'} ?? '[]'), true);
  if (!is_array($logs)) $logs = [];
  $esc = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
  $formatarData = static function ($value): string {
    if (empty($value)) return '—';
    $timestamp = strtotime((string) $value);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : (string) $value;
  };
?>

<div class="card">
  <div class="card-body">
    <div class="row align-items-center" id="cabecalhoCadastroBasico">
      <div class="col-12 col-md-7 mb-3">
        <h4 id="tituloFormularioCadastroBasico" class="m-0"><?= $registroId > 0 ? 'Editando registro' : 'Novo registro' ?></h4>
        <div id="subtituloFormularioCadastroBasico" class="text-muted small"><?= $registroId > 0 ? $esc($registro->{$prefixo . '_nome'} ?? '') : 'Informe os dados do cadastro básico.' ?></div>
      </div>
      <div class="col-12 col-md-5 mb-3">
        <div class="d-flex justify-content-md-end">
          <a href="<?= $esc($urlVoltar) ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Cancelar</a>
        </div>
      </div>
    </div>

    <section id="secaoFormularioCadastroBasico">
      <header class="phsaScrollHeader">
        <nav class="phsaScrollNav" data-menu="tabCadastroBasico"><ul class="phsaScrollList">
          <li class="phsaScrollItem active" data-target="painelCadastroBasico"><i class="fa-solid fa-id-card"></i>&nbsp;Cadastro</li>
          <?php if ($registroId > 0): ?><li class="phsaScrollItem" data-target="painelHistoricoCadastroBasico"><i class="fa-solid fa-clock-rotate-left"></i>&nbsp;Histórico</li><?php endif; ?>
        </ul></nav>
      </header>

      <div class="phsaTabContent phsaTabSubContent">
        <div class="phsaTabItem active p-3" id="painelCadastroBasico">
          <form id="formCadastroBasico" class="needs-validation" novalidate autocomplete="off">
            <input type="hidden" name="<?= $idCampo ?>" value="<?= $registroId ?>">
            <div class="row">
              <div class="col-12 col-md-4 mb-3"><div class="form-floating">
                <select id="<?= $prefixo ?>_tipocadastro" name="<?= $prefixo ?>_tipocadastro" class="form-control input-phsa-focus input-phsa-hover" <?= $registroId > 0 ? 'disabled' : '' ?>>
                  <?php foreach ($tiposCadastro as $tipo): ?><option value="<?= $esc($tipo->value) ?>" <?= ($registro->{$prefixo . '_tipocadastro'} ?? $tipoPadrao) === $tipo->value ? 'selected' : '' ?>><?= $esc($tipo->label) ?></option><?php endforeach; ?>
                </select><label for="<?= $prefixo ?>_tipocadastro">Tipo de cadastro</label>
              </div></div>
              <?php if ($registroId > 0): ?><input type="hidden" name="<?= $prefixo ?>_tipocadastro" value="<?= $esc($registro->{$prefixo . '_tipocadastro'} ?? $tipoPadrao) ?>"><?php endif; ?>

              <div class="col-12 col-md-4 mb-3"><div class="form-floating">
                <select id="<?= $prefixo ?>_status" name="<?= $prefixo ?>_status" class="form-control input-phsa-focus input-phsa-hover">
                  <option value="Ativo" <?= ($registro->{$prefixo . '_status'} ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                  <option value="Inativo" <?= ($registro->{$prefixo . '_status'} ?? 'Ativo') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                </select><label for="<?= $prefixo ?>_status">Status</label>
              </div></div>

              <div class="col-12 col-md-4 mb-3"><div class="form-floating"><input id="<?= $prefixo ?>_ordem" name="<?= $prefixo ?>_ordem" type="number" min="0" step="1" class="form-control input-phsa-focus input-phsa-hover" value="<?= (int) ($registro->{$prefixo . '_ordem'} ?? 0) ?>"><label for="<?= $prefixo ?>_ordem">Ordem</label></div></div>

              <div class="col-12 col-md-<?= $config->modulo === 'naturalidade' ? '6' : '12' ?> mb-3"><div class="form-floating">
                <input id="<?= $prefixo ?>_nome" name="<?= $prefixo ?>_nome" type="text" class="form-control input-phsa-focus input-phsa-hover" maxlength="<?= $config->modulo === 'naturalidade' ? 100 : 50 ?>" value="<?= $esc($registro->{$prefixo . '_nome'} ?? '') ?>" required>
                <label for="<?= $prefixo ?>_nome">Nome *</label><div class="invalid-feedback">Informe o nome do cadastro.</div>
              </div></div>

              <?php if ($config->modulo === 'naturalidade'): ?>
                <div class="col-12 col-md-3 mb-3"><div class="form-floating"><input id="nat_uf" name="nat_uf" type="text" class="form-control input-phsa-focus input-phsa-hover text-uppercase" maxlength="2" value="<?= $esc($registro->nat_uf ?? '') ?>"><label for="nat_uf">UF</label></div></div>
                <div class="col-12 col-md-3 mb-3"><div class="form-floating"><input id="nat_pais" name="nat_pais" type="text" class="form-control input-phsa-focus input-phsa-hover" maxlength="50" value="<?= $esc($registro->nat_pais ?? 'Brasil') ?>"><label for="nat_pais">País</label></div></div>
              <?php endif; ?>

              <div class="col-12 mb-3"><div class="form-check form-switch">
                <input id="<?= $prefixo ?>_padrao" name="<?= $prefixo ?>_padrao" class="form-check-input" type="checkbox" value="1" <?= !empty($registro->{$prefixo . '_padrao'}) ? 'checked' : '' ?>><label class="form-check-label" for="<?= $prefixo ?>_padrao">Definir registro como padrão</label>
              </div></div>
            </div>
            <div class="d-flex justify-content-end gap-2"><button id="btnSalvarCadastroBasico" type="submit" class="bto bto-phsa"><i class="fa-solid fa-check-double"></i>&nbsp;Salvar</button></div>
          </form>
        </div>

        <?php if ($registroId > 0): ?>
          <div class="phsaTabItem p-3" id="painelHistoricoCadastroBasico">
            <?php
              $historicoRegistro = $registro;
              $historicoPrefixo = $prefixo . '_';
              $historicoId = $registroId;
              $historicoStatus = (string) ($registro->{$prefixo . '_status'} ?? '');
              $historicoStatusClass = (string) ($registro->{$prefixo . '_statusclass'} ?? 'secondary');
              $historicoLogs = $logs;
              include __DIR__ . '/../historicoPadrao.php';
            ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</div>

<?php $this->push('script'); ?>
<script>
  $('#formCadastroBasico').on('submit',function(event){
    event.preventDefault(); const form=$(this),button=$('#btnSalvarCadastroBasico');
    if(!form[0].checkValidity()){form.addClass('was-validated');form.find(':invalid').first().trigger('focus');return;}
    form.addClass('was-validated');button.prop('disabled',true);
    $.ajax({url:<?= json_encode($urlSalvar) ?>,method:'POST',dataType:'json',data:form.serialize(),success:function(response){
      swResponse(response); if(response.status){const id=Number(response.data?.id||response.lastInsertId||<?= $registroId ?>);const target=<?= json_encode($urlBase) ?>+'/'+id;setTimeout(()=>window.location.replace(target),1500);return;} button.prop('disabled',false);
    },error:function(xhr){console.error(xhr.responseText);swError('Não foi possível salvar o cadastro básico.');button.prop('disabled',false);}});
  });
</script>
<?php $this->end(); ?>
