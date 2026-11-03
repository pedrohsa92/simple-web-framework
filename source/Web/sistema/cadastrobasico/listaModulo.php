<?php
  $this->layout('sistema::sistema');
  $prefixo = $config->prefixo;
  $base = Helpers::url("{$modulo}/basico/{$config->modulo}");
  $podeCriar = Helpers::pode("{$modulo}.criar");
  $podeAtualizar = Helpers::pode("{$modulo}.atualizar");
  $tiposPermitidos = array_map(static fn($tipo) => $tipo->value, $tiposCadastro);
?>

<div class="card">
  <div class="card-body">
    <div class="row align-items-center mb-3">
      <div class="col-12 col-md-7">
        <h4 class="m-0 text-uppercase"><?= htmlspecialchars($config->titulo, ENT_QUOTES, 'UTF-8') ?></h4>
        <div class="text-muted small">Cadastro básico do contexto ativo</div>
      </div>
      <div class="col-12 col-md-5 d-flex justify-content-md-end gap-2 mt-2 mt-md-0">
        <a href="<?= Helpers::url("{$modulo}/basico") ?>" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-arrow-left"></i>&nbsp;Voltar</a>
        <?php if ($podeCriar): ?><a href="<?= $base ?>/novo" class="bto bto-phsa"><i class="fa-solid fa-plus"></i>&nbsp;Novo</a><?php endif; ?>
      </div>
    </div>

    <div class="row mb-2"><div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
      <button type="button" onclick="alternarVisualizacaoCadastroBasico()" class="bto bto-phsa-light bto-phsa-hover" aria-label="Alternar modo analítico e sintético"><i class="fa-solid fa-table-columns"></i>&nbsp;<span id="labelVisualizacaoCadastroBasico">Modo analítico</span></button>
      <button type="button" onclick="fgbDatatable('tableCadastroBasico')" class="bto bto-phsa-light bto-phsa-hover"><i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar</button>
      <button type="button" onclick="fgbConverterTabelaEmXlsx('tableCadastroBasico', '<?= htmlspecialchars($config->modulo, ENT_QUOTES, 'UTF-8') ?>')" class="bto bto-phsa-light bto-phsa-hover"><i class="fas fa-file-excel"></i>&nbsp;Exportar</button>
      <button type="button" onclick="fgbGerarImpressao('resultadoCadastroBasico', '<?= htmlspecialchars($config->titulo, ENT_QUOTES, 'UTF-8') ?>')" class="bto bto-phsa-light bto-phsa-hover"><i class="fas fa-print"></i>&nbsp;Imprimir</button>
    </div></div>

    <section id="resultadoCadastroBasico"><div class="table-responsive">
      <table id="tableCadastroBasico" class="table table-hover align-middle">
        <thead><tr>
          <th>Nome</th><th>Tipo</th><th>Status</th>
          <?php if ($config->modulo === 'naturalidade'): ?><th class="colSecundariaCadastroBasico d-none">UF</th><th class="colSecundariaCadastroBasico d-none">País</th><?php endif; ?>
          <th class="colSecundariaCadastroBasico d-none text-center">Padrão</th><th class="colSecundariaCadastroBasico d-none text-center">Ordem</th>
          <th class="text-end hideOnPrint hideOnExcel">Ação</th>
        </tr></thead>
        <tbody>
          <?php if (!empty($registros->data)): foreach ($registros->data as $item):
            $id = (int) ($item->{$prefixo . '_id'} ?? 0);
            $tipo = (string) ($item->{$prefixo . '_tipocadastro'} ?? '');
            $status = (string) ($item->{$prefixo . '_status'} ?? '');
            $statusClass = (string) ($item->{$prefixo . '_statusclass'} ?? 'secondary');
            $padrao = (int) ($item->{$prefixo . '_padrao'} ?? 0) === 1;
            $tipoEditavel = in_array($tipo, $tiposPermitidos, true);
          ?>
            <tr title="ID: <?= $id ?>">
              <td><a class="text-phsa fw-semibold text-decoration-none" href="<?= $base ?>/<?= $id ?>"><?= htmlspecialchars((string) ($item->{$prefixo . '_nome'} ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><?php if ($podeAtualizar && $tipoEditavel): ?><button type="button" class="badge bg-<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?> border-0 btnStatusCadastroBasico" data-id="<?= $id ?>" data-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>" data-nome="<?= htmlspecialchars((string) ($item->{$prefixo . '_nome'} ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></button><?php else: ?><span class="badge bg-<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></td>
              <?php if ($config->modulo === 'naturalidade'): ?><td class="colSecundariaCadastroBasico d-none"><?= htmlspecialchars((string) ($item->nat_uf ?? ''), ENT_QUOTES, 'UTF-8') ?: '<span class="text-muted">-</span>' ?></td><td class="colSecundariaCadastroBasico d-none"><?= htmlspecialchars((string) ($item->nat_pais ?? ''), ENT_QUOTES, 'UTF-8') ?: '<span class="text-muted">-</span>' ?></td><?php endif; ?>
              <td class="colSecundariaCadastroBasico d-none text-center"><?php if ($padrao): ?><i class="fa-solid fa-check text-success"></i><?php elseif ($podeAtualizar && !$tipoEditavel): ?><button type="button" class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Definir como padrão do contexto" onclick="definirPadraoCadastroBasico(<?= $id ?>)"><i class="fa-solid fa-check"></i></button><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
              <td class="colSecundariaCadastroBasico d-none text-center"><?= (int) ($item->{$prefixo . '_ordem'} ?? 0) ?></td>
              <td class="text-end hideOnPrint hideOnExcel"><?php if ($podeAtualizar): ?><a class="bto btn-sm bto-phsa-light bto-phsa-hover" title="Editar registro" href="<?= $base ?>/<?= $id ?>"><i class="fa-solid fa-pen-to-square"></i></a><?php else: ?><span class="text-muted">—</span><?php endif; ?></td>
            </tr>
          <?php endforeach; else: ?><tr><td colspan="100" class="text-center text-muted py-4">Nenhum registro encontrado neste contexto.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></section>
  </div>
</div>

<?php $this->push('script'); ?>
<script>
  let cadastroBasicoModoAnalitico=false;
  const cadastroBasicoPrefixo=<?= json_encode($prefixo) ?>;
  const cadastroBasicoCodigo=<?= json_encode($config->modulo) ?>;
  const cadastroBasicoUrlStatus=<?= json_encode($base.'/status') ?>;
  const cadastroBasicoUrlSalvar=<?= json_encode($base.'/salvar') ?>;

  function alternarVisualizacaoCadastroBasico(){
    cadastroBasicoModoAnalitico=!cadastroBasicoModoAnalitico;
    document.querySelectorAll('.colSecundariaCadastroBasico').forEach(col=>col.classList.toggle('d-none',!cadastroBasicoModoAnalitico));
    document.getElementById('labelVisualizacaoCadastroBasico').textContent=cadastroBasicoModoAnalitico?'Modo sintético':'Modo analítico';
  }
  function definirPadraoCadastroBasico(id){
    Swal.fire({title:'Definir como padrão?',icon:'question',showCancelButton:true,confirmButtonText:'<i class="fas fa-check"></i> Confirmar',cancelButtonText:'<i class="fas fa-times"></i> Cancelar',customClass:{confirmButton:'bto bto-phsa',cancelButton:'bto bto-phsa-light'},reverseButtons:true,allowOutsideClick:false}).then(result=>{
      if(!result.isConfirmed)return;
      const data={cadastro:cadastroBasicoCodigo}; data[cadastroBasicoPrefixo+'_id']=id; data[cadastroBasicoPrefixo+'_padrao']=1;
      $.ajax({url:cadastroBasicoUrlSalvar,method:'POST',dataType:'json',data,success:response=>{swResponse(response);if(response.status&&response.location)setTimeout(()=>location.replace(response.location),1000);},error:()=>swError('Erro ao definir como padrão.')});
    });
  }
  function alterarStatusCadastroBasico(id,statusAtual,nome){
    const novoStatus=statusAtual==='Ativo'?'Inativo':'Ativo';
    const idModal=SimpleModal.mostrar({
      titulo:'<i class="fa-solid fa-toggle-on"></i>&nbsp;Alterar status',
      corpo:`<form id="formStatusCadastroBasico" class="needs-validation" novalidate autocomplete="off"><input type="hidden" name="id" value="${id}"><input type="hidden" name="status" value="${fgbEscapeHtml(novoStatus)}"><div class="alert alert-light border small"><strong>${fgbEscapeHtml(nome||'Registro')}</strong><br>Status atual: ${fgbEscapeHtml(statusAtual)}<br>Novo status: <span class="badge bg-${novoStatus==='Ativo'?'success':'danger'}">${fgbEscapeHtml(novoStatus)}</span></div><div class="form-floating"><textarea id="motivoStatusCadastroBasico" name="motivo" class="form-control" style="height:120px" minlength="10" ${novoStatus==='Inativo'?'required':''}></textarea><label for="motivoStatusCadastroBasico">Motivo ${novoStatus==='Inativo'?'*':'(opcional)'}</label><div class="invalid-feedback">Informe pelo menos 10 caracteres para inativar.</div></div></form>`,
      rodape:'<button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i>&nbsp;Cancelar</button><button type="button" class="bto bto-phsa" id="btnSalvarStatusCadastroBasico"><i class="fa-solid fa-check"></i>&nbsp;Confirmar</button>'
    });
    $('#btnSalvarStatusCadastroBasico').off('click').on('click',function(){const form=$('#formStatusCadastroBasico');if(!form[0].checkValidity()){form.addClass('was-validated');return;}const button=$(this).prop('disabled',true);$.ajax({url:cadastroBasicoUrlStatus,method:'POST',dataType:'json',data:form.serialize(),success:response=>{swResponse(response);if(response.status){SimpleModal.fechar(idModal);setTimeout(()=>location.reload(),700);}else button.prop('disabled',false);},error:()=>{button.prop('disabled',false);swError('Não foi possível alterar o status.');}});});
  }
  $(document).off('click.statusCadastroBasico').on('click.statusCadastroBasico','.btnStatusCadastroBasico',function(){alterarStatusCadastroBasico(Number($(this).data('id')),String($(this).data('status')),String($(this).data('nome')));});
</script>
<?php $this->end(); ?>
