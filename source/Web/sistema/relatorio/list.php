<?php $this->layout("sistema::sistema"); ?>

<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 mb-3 text-uppercase">
        <h4 class="m-0">
          Lista de relatório
        </h4>
      </div>
    </div>

    <div id="listRelatorio" class="row">
      <div class="col-md mb-3 table-responsive">
        <table class="table table-hover table-striped">
          <thead>
            <tr>
              <th>Nome</th>
              <th class="text-end">Ação</th>
            </tr>
          </thead>
          <tbody id="tbodyRelatorio">
            <?php if ($getRelatorio->status && $getRelatorio->total > 0): ?>
              <?php foreach ($getRelatorio->data as $relatorio): ?>
                <tr>
                  <td>
                    <a href="<?= Helpers::url('relatorio/form/' . urlencode($relatorio->pmr_dir)) ?>" class="text-phsa">
                      <?= htmlspecialchars($relatorio->pmr_nome) ?>
                    </a>
                  </td>
                  <td class="text-end">
                    <a href="<?= Helpers::url('relatorio/form/' . urlencode($relatorio->pmr_dir)) ?>" class="bto btn-sm bto-phsa-light">
                      <i class="fa fa-file-alt"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">
                  <i class="fa fa-exclamation-circle"></i>&nbsp;Nenhum relatório encontrado para sua permissão
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $this->push('script'); ?>
	<script type="text/javascript">
	  $(document).ready(function () {
	  });
	</script>
<?php $this->end(); ?>