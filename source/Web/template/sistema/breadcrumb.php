





<?php if (!empty($pagina->breadcrumb)): ?>
  <div class="breadcrumb-wrapper mb-2">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <?php foreach ($pagina->breadcrumb as $item): ?>
          <?php if (!$item->active): ?>
            <li title="<?= $item->title ?>" class="breadcrumb-item">
              <a class="text-uppercase" href="<?= $item->url ?>"><?= $item->label ?></a>
            </li>
          <?php else: ?>
            <li title="<?= $item->title ?>" class="breadcrumb-item active" aria-current="page" onclick="location.reload()">
            	<a class="text-uppercase" href="#">
            		<?= $item->label ?>
            	</a>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ol>
    </nav>
  </div>
<?php endif; ?>