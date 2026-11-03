<?php
  $imagensProjeto = [
    'prj_favicon' => [
      'titulo' => 'Favicon',
      'descricao' => 'Ícone usado nas abas, atalhos e identificações do navegador.',
      'fallback' => 'storage/global/favicon.png',
      'classe' => 'imgProjetoFavicon',
      'fundo' => '#f8f9fa'
    ],
    'prj_logo' => [
      'titulo' => 'Logo principal',
      'descricao' => 'Aplicada em fundos brancos e claros.',
      'fallback' => 'storage/global/logo.png',
      'classe' => 'imgProjetoLogo',
      'fundo' => '#ffffff'
    ],
    'prj_logonegativa' => [
      'titulo' => 'Logo negativa',
      'descricao' => 'Aplicada em fundos pretos e escuros.',
      'fallback' => 'storage/global/logonegativa.png',
      'classe' => 'imgProjetoLogoNegativa',
      'fundo' => '#151515'
    ]
  ];
?>

<section class="p-3">
  <div class="text-phsa mb-4">
    <h5 class="m-0">Imagens do projeto</h5>
    <div class="small text-muted">As imagens personalizadas são armazenadas em <code>storage/projeto/<?= (int) ($projeto->prj_id ?? 1) ?>/</code>.</div>
  </div>

  <div class="row g-4">
    <?php foreach ($imagensProjeto as $campo => $imagem): ?>
      <div class="col-12 col-lg-4">
        <div class="card h-100 border-phsa-light box-phsa-shadow-light">
          <div class="card-body d-flex flex-column">
            <h6 class="text-phsa mb-1"><?= $imagem['titulo'] ?></h6>
            <div class="small text-muted mb-3"><?= $imagem['descricao'] ?></div>

            <div class="d-flex align-items-center justify-content-center rounded border mb-3" style="height: 150px; background: <?= $imagem['fundo'] ?>;">
              <img
                id="<?= $imagem['classe'] ?>"
                class="<?= $imagem['classe'] ?>"
                src="<?= Helpers::storage($projeto->{$campo} ?? null, $imagem['fallback']) ?>"
                alt="<?= $imagem['titulo'] ?>"
                style="max-width: 85%; max-height: 115px; object-fit: contain;"
              >
            </div>

            <div class="small text-muted text-truncate mb-3" title="<?= htmlspecialchars($projeto->{$campo} ?? $imagem['fallback'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($projeto->{$campo} ?? $imagem['fallback'], ENT_QUOTES, 'UTF-8') ?>
            </div>

            <?php if (Helpers::pode('projeto.atualizar')): ?>
              <div class="mt-auto d-flex justify-content-end gap-2">
                <button type="button" class="bto bto-phsa-light btnSelecionarImagemProjeto" data-campo="<?= $campo ?>" data-preview="<?= $imagem['classe'] ?>">
                  <i class="fa-solid fa-upload"></i>&nbsp;Selecionar
                </button>
                <button type="button" class="bto bto-phsa btnSalvarImagemProjeto" data-campo="<?= $campo ?>" style="display: none;">
                  <i class="fa-solid fa-floppy-disk"></i>&nbsp;Salvar
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php $this->push('script'); ?>
<script>
  $(function () {
    const imagensSelecionadasProjeto = {};

    $('.btnSelecionarImagemProjeto').on('click', function () {
      const botao = $(this);
      const campo = botao.data('campo');
      const preview = botao.data('preview');

      fgbSelecionarImagem({
        onFileSelect: function (response) {
          if (!response.status) {
            swResponse(response);
            return;
          }

          imagensSelecionadasProjeto[campo] = response.data;
          $('#' + preview).attr('src', response.data);
          botao.hide();
          botao.siblings('.btnSalvarImagemProjeto').show();
        }
      });
    });

    $('.btnSalvarImagemProjeto').on('click', function () {
      const botao = $(this);
      const campo = botao.data('campo');

      if (!imagensSelecionadasProjeto[campo]) {
        swToastError('Nenhuma imagem foi selecionada.');
        return;
      }

      swAguarde({
        title: 'Salvando imagem...',
        text: 'Aguarde enquanto processamos o arquivo.',
        timer: null
      });

      const dados = {prj_id: <?= (int) ($projeto->prj_id ?? 1) ?>};
      dados[campo] = imagensSelecionadasProjeto[campo];

      $.ajax({
        url: '<?= Helpers::url('projeto/salvarImagens') ?>',
        type: 'POST',
        dataType: 'json',
        data: dados,
        success: function (response) {
          swResponse(response);

          if (response.status) {
            delete imagensSelecionadasProjeto[campo];
            botao.hide();
            botao.siblings('.btnSelecionarImagemProjeto').show();
          }
        },
        error: function () {
          swError('Não foi possível salvar a imagem.');
        }
      });
    });
  });
</script>
<?php $this->end(); ?>
