





<form id="formPwaConfig" class="needs-validation" novalidate>
  <input type="hidden" name="pwa_id" value="<?= $pwaEmpresa->pwa_id ?? '' ?>">
  <input type="hidden" name="pwa_idempresa" value="<?= $empresa->emp_id ?>">

  <div class="row mt-2">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select 
          id="pwa_status" 
          name="pwa_status" 
          class="form-control input-phsa-focus input-phsa-hover"
          required
        >
          <option value="1" <?= isset($pwaEmpresa->pwa_status) && $pwaEmpresa->pwa_status == '1' ? 'selected' : '' ?>>Ativo</option>
          <option value="0" <?= isset($pwaEmpresa->pwa_status) && $pwaEmpresa->pwa_status == '0' ? 'selected' : '' ?>>Inativo</option>
        </select>
        <label for="pwa_status">Status do PWA</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input 
          id="pwa_name" 
          name="pwa_name" 
          value="<?= $pwaEmpresa->pwa_name ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Nome do PWA" 
          required
        >
        <label for="pwa_name">Nome do PWA</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input 
          id="pwa_short_name" 
          name="pwa_short_name" 
          value="<?= $pwaEmpresa->pwa_short_name ?? '' ?>"
          class="form-control input-phsa-focus input-phsa-hover" 
          placeholder="Nome Curto" 
          required
        >
        <label for="pwa_short_name">Nome Curto</label>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select 
          id="pwa_start_url" 
          name="pwa_start_url" 
          class="form-control input-phsa-focus input-phsa-hover"
          required
        >
          <option value="dashboard" <?= empty($pwaEmpresa->pwa_start_url) || $pwaEmpresa->pwa_start_url == 'dashboard' ? 'selected' : '' ?>>Dashboard</option>
          <option value="login" <?= $pwaEmpresa->pwa_start_url == 'login' ? 'selected' : '' ?>>Login</option>
          <option value="" <?= $pwaEmpresa->pwa_start_url == '' ? 'selected' : '' ?>>Site</option>
        </select>
        <label for="pwa_start_url">Página Inicial do PWA</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input 
          type="color"
          id="pwa_theme_color" 
          name="pwa_theme_color" 
          value="<?= $pwaEmpresa->pwa_theme_color ?? '#007bff' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
        >
        <label for="pwa_theme_color">Cor do Tema</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <input 
          type="color"
          id="pwa_background_color" 
          name="pwa_background_color" 
          value="<?= $pwaEmpresa->pwa_background_color ?? '#ffffff' ?>"
          class="form-control input-phsa-focus input-phsa-hover"
        >
        <label for="pwa_background_color">Cor de Fundo</label>
      </div>
    </div>
  </div>

  <div class="row">
		<div class="col-sm-6 mb-3">
		  <div class="form-floating">
		    <select 
		      id="pwa_display" 
		      name="pwa_display" 
		      class="form-control input-phsa-focus input-phsa-hover"
		    >
		      <option value="fullscreen" <?= $pwaEmpresa->pwa_display == 'fullscreen' ? 'selected' : '' ?>>Tela Cheia (fullscreen)</option>
		      <option value="standalone" <?= empty($pwaEmpresa->pwa_display) || $pwaEmpresa->pwa_display == 'standalone' ? 'selected' : '' ?>>Independente (standalone)</option>
		      <option value="minimal-ui" <?= $pwaEmpresa->pwa_display == 'minimal-ui' ? 'selected' : '' ?>>UI Mínima (minimal-ui)</option>
		      <option value="browser" <?= $pwaEmpresa->pwa_display == 'browser' ? 'selected' : '' ?>>Navegador (browser)</option>
		    </select>
		    <label for="pwa_display">Modo de Exibição Padrão</label>
		  </div>
		  <div class="w-100 small text-secondary">
		    Define como o aplicativo será exibido no dispositivo. "Standalone" faz o app parecer um aplicativo independente.
		  </div>
		</div>

		<div class="col-sm-6 mb-3">
		  <div class="form-floating">
		    <select 
		      id="pwa_display_override" 
		      name="pwa_display_override" 
		      class="form-select input-phsa-focus input-phsa-hover"
		    >
		      <option value="fullscreen" <?= $pwaEmpresa->pwa_display_override == 'fullscreen' ? 'selected' : '' ?>>Tela Cheia (fullscreen)</option>
		      <option value="standalone" <?= empty($pwaEmpresa->pwa_display_override) || $pwaEmpresa->pwa_display_override == 'standalone' ? 'selected' : '' ?>>Aplicativo Independente (standalone)</option>
		      <option value="minimal-ui" <?= $pwaEmpresa->pwa_display_override == 'minimal-ui' ? 'selected' : '' ?>>Mínima UI (minimal-ui)</option>
		      <option value="browser" <?= $pwaEmpresa->pwa_display_override == 'browser' ? 'selected' : '' ?>>Navegador (browser)</option>
		      <option value="window-controls-overlay" <?= ($pwaEmpresa->pwa_display_override ?? '') == 'window-controls-overlay' ? 'selected' : '' ?>>Com Controles do Sistema (window-controls-overlay)</option>
		    </select>
		    <label for="pwa_display_override">Preferência do Sistema</label>
		  </div>
		  <div class="w-100 small text-secondary">
		    Alguns sistemas podem ignorar o modo padrão e usar um diferente. Aqui você pode definir uma preferência alternativa.
		  </div>
		</div>
  </div>

  <div class="row">
    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select 
          id="pwa_orientation" 
          name="pwa_orientation" 
          class="form-control input-phsa-focus input-phsa-hover"
        >
          <option value="any" <?= $pwaEmpresa->pwa_orientation == 'any' ? 'selected' : '' ?>>Qualquer</option>
          <option value="portrait" <?= empty($pwaEmpresa->pwa_orientation) || $pwaEmpresa->pwa_orientation == 'portrait' ? 'selected' : '' ?>>Retrato</option>
          <option value="landscape" <?= $pwaEmpresa->pwa_orientation == 'landscape' ? 'selected' : '' ?>>Paisagem</option>
        </select>
        <label for="pwa_orientation">Orientação</label>
      </div>
    </div>

    <div class="col-sm-4 mb-3">
      <div class="form-floating">
        <select 
          id="pwa_lang" 
          name="pwa_lang" 
          class="form-control input-phsa-focus input-phsa-hover"
        >
          <option value="pt-BR" <?= isset($pwaEmpresa->pwa_lang) && $pwaEmpresa->pwa_lang == 'pt-BR' ? 'selected' : '' ?>>Português</option>
          <option value="en" <?= isset($pwaEmpresa->pwa_lang) && $pwaEmpresa->pwa_lang == 'en' ? 'selected' : '' ?>>Inglês</option>
        </select>
        <label for="pwa_lang">Idioma</label>
      </div>
    </div>

	  <div class="col-sm-4 mb-3">
	    <div class="form-floating">
	      <select 
	        id="pwa_version_name" 
	        name="pwa_version_name" 
	        class="form-control input-phsa-focus input-phsa-hover"
	        required
	      >
	        <option value="Beta" <?= isset($pwaEmpresa->pwa_version_name) && $pwaEmpresa->pwa_version_name == "Beta" ? 'selected' : '' ?>>Beta</option>
	        <option value="Estável" <?= isset($pwaEmpresa->pwa_version_name) && $pwaEmpresa->pwa_version_name == "Estável" ? 'selected' : '' ?>>Estável</option>
	        <option value="Versão de Teste" <?= isset($pwaEmpresa->pwa_version_name) && $pwaEmpresa->pwa_version_name == "Versão de Teste" ? 'selected' : '' ?>>Versão de Teste</option>
	        <option value="Produção" <?= isset($pwaEmpresa->pwa_version_name) && $pwaEmpresa->pwa_version_name == "Produção" ? 'selected' : '' ?>>Produção</option>
	        <option value="Experimental" <?= isset($pwaEmpresa->pwa_version_name) && $pwaEmpresa->pwa_version_name == "Experimental" ? 'selected' : '' ?>>Experimental</option>
	      </select>
	      <label for="pwa_version_name">Versão do PWA</label>
	    </div>
	  </div>
	</div>

	<div class="row">
	  <div class="col-sm-12 mb-3">
	    <div class="form-floating">
	      <textarea 
	        id="pwa_description" 
	        name="pwa_description" 
	        class="form-control input-phsa-focus input-phsa-hover" 
	        placeholder="Descrição do PWA" 
	        style="height: 100px;" 
	        maxlength="255"
	      ><?= $pwaEmpresa->pwa_description ?? 'Aplicativo Web Progressivo' ?></textarea>
	      <label for="pwa_description">Descrição</label>
	    </div>
	  </div>
	</div>

  <div class="row">
    <div class="col-sm-12 mb-3 text-end">
      <button class="bto bto-phsa" type="submit">
        <i class="fas fa-save"></i>&nbsp;Salvar Configuração
      </button>
    </div>
  </div>
</form>

<?php $this->push('script'); ?>
  <script type="text/javascript">
  	$(document).ready(function () {
      SimpleForm.bindForm('#formPwaConfig', {
        urlDestino: '<?= Helpers::url("empresa/salvarPwa") ?>'
      });
    });
  </script>
<?php $this->end(); ?>