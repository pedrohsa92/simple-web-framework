<?php
  $enderecoProjeto = $enderecoProjeto ?? null;
  $podeAtualizarProjeto = Helpers::pode('projeto.atualizar');
  $atributoSomenteLeitura = $podeAtualizarProjeto ? '' : 'readonly';
  $valorEndereco = static function (string $campo, string $padrao = '') use ($enderecoProjeto): string {
    return htmlspecialchars((string) ($enderecoProjeto->{$campo} ?? $padrao), ENT_QUOTES, 'UTF-8');
  };
?>

<form id="formDadosBasicosProjeto" class="needs-validation p-3" novalidate>
  <input type="hidden" name="prj_id" value="<?= (int) ($projeto->prj_id ?? 1) ?>">

  <div class="row">
    <div class="col-12 text-phsa mb-3">
      <h5 class="m-0">Dados básicos</h5>
      <div class="small text-muted">Informações públicas e endereço institucional do projeto.</div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-lg-6 mb-3">
      <div class="form-floating">
        <input id="prj_nome" name="prj_nome" value="<?= htmlspecialchars($projeto->prj_nome ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="100" required <?= $atributoSomenteLeitura ?> >
        <label for="prj_nome">Nome técnico</label>
        <div class="invalid-feedback">Informe o nome técnico.</div>
      </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
      <div class="form-floating">
        <input id="prj_titulo" name="prj_titulo" value="<?= htmlspecialchars($projeto->prj_titulo ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="150" required <?= $atributoSomenteLeitura ?> >
        <label for="prj_titulo">Título público</label>
        <div class="invalid-feedback">Informe o título público.</div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="form-floating">
        <textarea id="prj_descricao" name="prj_descricao" class="form-control input-phsa-focus input-phsa-hover" maxlength="255" style="height: 100px;" <?= $atributoSomenteLeitura ?> ><?= htmlspecialchars($projeto->prj_descricao ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        <label for="prj_descricao">Descrição</label>
      </div>
    </div>
  </div>

  <hr class="my-4">

  <div class="row">
    <div class="col-12 text-phsa mb-3">
      <h5 class="m-0">Endereço</h5>
      <div class="small text-muted">O projeto possui um endereço institucional único.</div>
    </div>
  </div>

  <input type="hidden" name="end_id" value="<?= (int) ($enderecoProjeto->end_id ?? 0) ?>">
  <input type="hidden" name="end_origem" value="projeto">
  <input type="hidden" name="end_idorigem" value="<?= (int) ($projeto->prj_id ?? 1) ?>">
  <input type="hidden" name="end_idorganizacao" value="0">
  <input type="hidden" name="end_idempresa" value="0">
  <input type="hidden" name="end_principal" value="1">
  <input type="hidden" name="end_pais" value="<?= $valorEndereco('end_pais', 'Brasil') ?>">
  <input type="hidden" name="end_estado" id="end_estado" value="<?= $valorEndereco('end_estado') ?>">

  <div class="row">
    <div class="col-12 col-lg-4 mb-3">
      <div class="form-floating">
        <input id="end_nome" name="end_nome" value="<?= $valorEndereco('end_nome') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="100" <?= $atributoSomenteLeitura ?> >
        <label for="end_nome">Nome de identificação</label>
      </div>
    </div>

    <div class="col-12 col-lg-4 mb-3">
      <div class="form-floating">
        <input id="end_cep" name="end_cep" value="<?= $valorEndereco('end_cep') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="9" required <?= $atributoSomenteLeitura ?> >
        <label for="end_cep">CEP</label>
        <div class="invalid-feedback">Informe um CEP válido.</div>
      </div>
    </div>

    <div class="col-12 col-lg-4 mb-3">
      <div class="form-floating">
        <select id="end_status" name="end_status" class="form-select input-phsa-focus input-phsa-hover" required <?= $podeAtualizarProjeto ? '' : 'disabled' ?> >
          <option value="Ativo" <?= ($enderecoProjeto->end_status ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
          <option value="Inativo" <?= ($enderecoProjeto->end_status ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <label for="end_status">Status do endereço</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-lg-9 mb-3">
      <div class="form-floating">
        <input id="end_logradouro" name="end_logradouro" value="<?= $valorEndereco('end_logradouro') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="150" required readonly>
        <label for="end_logradouro">Logradouro</label>
        <div class="invalid-feedback">Informe o CEP para localizar o logradouro.</div>
      </div>
    </div>

    <div class="col-12 col-lg-3 mb-3">
      <div class="form-floating">
        <input id="end_numero" name="end_numero" value="<?= $valorEndereco('end_numero') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="20" <?= $atributoSomenteLeitura ?> >
        <label for="end_numero">Número</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="form-floating">
        <input id="end_complemento" name="end_complemento" value="<?= $valorEndereco('end_complemento') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="100" <?= $atributoSomenteLeitura ?> >
        <label for="end_complemento">Complemento</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 col-lg-5 mb-3">
      <div class="form-floating">
        <input id="end_bairro" name="end_bairro" value="<?= $valorEndereco('end_bairro') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="100" required readonly>
        <label for="end_bairro">Bairro</label>
      </div>
    </div>

    <div class="col-12 col-lg-5 mb-3">
      <div class="form-floating">
        <input id="end_cidade" name="end_cidade" value="<?= $valorEndereco('end_cidade') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="100" required readonly>
        <label for="end_cidade">Cidade</label>
      </div>
    </div>

    <div class="col-12 col-lg-2 mb-3">
      <div class="form-floating">
        <input id="end_uf" name="end_uf" value="<?= $valorEndereco('end_uf') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="2" required readonly>
        <label for="end_uf">UF</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 mb-3">
      <div class="form-floating">
        <input id="end_referencia" name="end_referencia" value="<?= $valorEndereco('end_referencia') ?>" class="form-control input-phsa-focus input-phsa-hover" maxlength="150" <?= $atributoSomenteLeitura ?> >
        <label for="end_referencia">Referência</label>
      </div>
    </div>
  </div>

  <?php if (Helpers::pode('projeto.atualizar')): ?>
    <div class="d-flex justify-content-end mb-2">
      <button type="submit" class="bto bto-phsa">
        <i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar dados básicos
      </button>
    </div>
  <?php endif; ?>
</form>

<?php $this->push('script'); ?>
<script>
  $(function () {
    $('#end_cep').mask('99999-999');

    $('#end_cep').on('blur', function () {
      const cep = String($(this).val() || '').replace(/\D/g, '');

      if (cep.length !== 8) {
        return;
      }

      $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function (data) {
        if (data.erro) {
          swError('CEP não encontrado.');
          return;
        }

        $('#end_logradouro').val(data.logradouro || '');
        $('#end_bairro').val(data.bairro || '');
        $('#end_cidade').val(data.localidade || '');
        $('#end_uf').val(data.uf || '');
        $('#end_estado').val(data.estado || '');
      }).fail(function () {
        swError('Erro ao buscar o CEP. Tente novamente.');
      });
    });

    $('#formDadosBasicosProjeto').on('submit', function (event) {
      event.preventDefault();
      event.stopPropagation();

      const form = $(this);

      if (!this.checkValidity()) {
        form.addClass('was-validated');
        return;
      }

      swAguarde({
        title: 'Salvando projeto...',
        text: 'Aguarde enquanto processamos os dados.',
        timer: null
      });

      $.ajax({
        url: '<?= Helpers::url('projeto/salvarProjeto') ?>',
        type: 'POST',
        dataType: 'json',
        data: form.serialize(),
        success: function (response) {
          swResponse(response);
        },
        error: function () {
          swError('Não foi possível salvar os dados do projeto.');
        }
      });
    });
  });
</script>
<?php $this->end(); ?>
