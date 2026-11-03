// global
  function fgbUrl(path = '') {
    if (!CONF_URL) {
      console.error('CONF_URL não definida.');
      swError('Erro de configuração: CONF_URL não definida.');
      return '';
    }

    if (!path) {
      return CONF_URL.replace(/\/+$/, '');
    }

    return `${CONF_URL.replace(/\/+$/, '')}/${String(path).replace(/^\/+/, '')}`;
  }

  function fgbPesquisaGlobal() {
    console.error('Função global fgbPesquisaGlobal não terminada em sistema.js');
  }

  function fgbVisualizarNotificacao() {
    console.error('Função global fgbVisualizarNotificacao não terminada em sistema.js');
  }
// global

// header.php
  $(document).ready(function () {
    $(document).off('click.simpleHeader');

    $(document).on('click.simpleHeader', function (e) {
      if (
        !$(e.target).closest('#navbarContent').length &&
        !$(e.target).closest('.navbar-toggler').length
      ) {
        $('#navbarContent').collapse('hide');
      }
    });
  });

  function fgbAlterarTema(usu_tema = null, salvar = true) {
    let temaAtual = document.documentElement.getAttribute('data-bs-theme') || 'light';

    if (usu_tema === null) {
      usu_tema = temaAtual === 'dark' ? 'light' : 'dark';
    }

    document.documentElement.setAttribute('data-bs-theme', usu_tema);
    usuarioLogado.usu_tema = usu_tema;

    if (!salvar) {
      return;
    }

    $.ajax({
      url: fgbUrl('usuario/setTema'),
      type: 'post',
      dataType: 'json',
      data: {
        usu_tema: usu_tema
      },
      success: function (response) {
        if (!response.status) {
          console.error(response);
          swError(response.text || 'Erro ao alterar tema.');
        }
      },
      error: function (xhr) {
        console.error(xhr.responseText);
        swError('Erro ao alterar tema.');
      }
    });
  }

  function fgbBloquearAcesso() {
    Swal.fire({
      title: 'Bloquear sessão de sistema?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-lock"></i> Sim',
      cancelButtonText: '<i class="fas fa-times"></i> Não',
      customClass: {
        confirmButton: 'bto bto-phsa',
        cancelButton: 'bto bto-phsa-light'
      },
      reverseButtons: true,
      allowOutsideClick: false
    }).then((result) => {
      if (result.isConfirmed) {
        location.href = fgbUrl('usuario/bloqueado');
      }
    });
  }

  function fgbDesconectar(destino = null) {
    if (destino === null) {
      destino = fgbUrl('login');
    }

    Swal.fire({
      title: 'Deseja sair do sistema?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-check"></i> Sim',
      cancelButtonText: '<i class="fas fa-times"></i> Não',
      customClass: {
        confirmButton: 'bto bto-phsa',
        cancelButton: 'bto bto-phsa-light'
      },
      reverseButtons: true,
      allowOutsideClick: false
    }).then((result) => {
      if (!result.isConfirmed) {
        return;
      }

      swAguarde({
        title: 'Desconectando...',
        html: '<b>Por favor, aguarde...</b>'
      });

      $.ajax({
        url: fgbUrl('usuario/logout'),
        type: 'post',
        dataType: 'json',
        success: function (response) {
          swResponse(response);

          if (response.status) {
            setTimeout(function () {
              location.replace(destino);
            }, 1000);
          }
        },
        error: function (xhr) {
          console.error(xhr.responseText);
          swError('Erro ao tentar desconectar. Por favor, tente novamente.');
        }
      });
    });
  }
// header.php

// sidebar.php
  $(document).ready(function () {
    $(document).off('click.simpleSidebar');
    $('#simpleMenuLogo').off('mouseenter.simpleSidebar');
    $('#simpleSidebar').off('mouseleave.simpleSidebar');

    $(document).on('click.simpleSidebar', '.menu > li > .menu-item', function (e) {
      const submenu = $(this).next('.submenu');

      if (!submenu.length) {
        return;
      }

      e.preventDefault();
      e.stopImmediatePropagation();

      const icon = $(this).find('.submenu-icon');

      $('.menu > li > .submenu').not(submenu).stop(true, true).slideUp();
      $('.menu > li > .menu-item').not(this).removeClass('aberto');
      $('.menu > li > .menu-item').not(this).find('.submenu-icon').removeClass('fa-angle-down').addClass('fa-angle-right');

      submenu.stop(true, true).slideToggle();
      $(this).toggleClass('aberto');
      icon.toggleClass('fa-angle-right fa-angle-down');
    });

    $(document).on('click.simpleSidebar', '.sidebarFixedBottom > .profile-item', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();

      const submenu = $(this).next('.profile-submenu');
      const icon = $(this).find('.submenu-icon');

      submenu.stop(true, true).slideToggle();
      $(this).toggleClass('aberto');
      icon.toggleClass('fa-angle-right fa-angle-down');
    });

    $(document).on('click.simpleSidebar', '#btnSidebarMenuPreferencias', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();

      const submenu = $(this).next('.sidebar-menu-options');
      const icon = $(this).find('.submenu-icon');

      submenu.stop(true, true).slideToggle();
      $(this).toggleClass('aberto');
      icon.toggleClass('fa-angle-right fa-angle-down');
    });

    $('#simpleMenuLogo').on('mouseenter.simpleSidebar', function () {
      if (parseInt(usuarioLogado.usu_sidebar) !== 4) {
        return;
      }

      if ($(window).width() < 992) {
        return;
      }

      $('#simpleSidebar')
        .removeClass('hidden collapsed')
        .addClass('overlay');

      $('.submenu-item span').show();
      $('.menu-item span').show();
      $('.profile-item span').show();
    });

    $('#simpleSidebar').on('mouseleave.simpleSidebar', function () {
      if (parseInt(usuarioLogado.usu_sidebar) !== 4) {
        return;
      }

      if ($(window).width() < 992) {
        return;
      }

      $('#simpleSidebar')
        .addClass('hidden')
        .removeClass('collapsed overlay');

      $('.submenu').stop(true, true).slideUp();
      $('.profile-submenu').stop(true, true).slideUp();
      $('.sidebar-menu-options').stop(true, true).slideUp();
      $('.submenu-icon').removeClass('fa-angle-down').addClass('fa-angle-right');
      $('.aberto').removeClass('aberto');
    });
  });

  function fgbAtualizarCheckSidebar(mode) {
    $('[data-sidebar-mode]').each(function () {
      const itemMode = parseInt($(this).data('sidebar-mode'));
      const check = $(this).find('.fa-check');

      if (itemMode === parseInt(mode)) {
        check.removeClass('opacity-0');
      } else {
        check.addClass('opacity-0');
      }
    });
  }

  function fgbAlternarSidebarClickLogo() {
    switch (parseInt(usuarioLogado.usu_sidebar)) {
      case 1:
      default:
        fgbAlternarSidebar(2);
      break;

      case 2:
        fgbAlternarSidebar(3);
      break;

      case 3:
      case 4:
        fgbAlternarSidebar(1);
      break;
    }
  }

  function fgbAlternarSidebar(mode = null, salvar = true) {
    const sidebar = $('#simpleSidebar');
    const submenus = $('.submenu, .profile-submenu, .sidebar-menu-options');
    const icons = $('.submenu-icon');

    submenus.stop(true, true).slideUp();
    icons.removeClass('fa-angle-down').addClass('fa-angle-right');
    $('.aberto').removeClass('aberto');

    if (mode !== null) {
      usuarioLogado.usu_sidebar = parseInt(mode);
    } else {
      switch (parseInt(usuarioLogado.usu_sidebar)) {
        case 1:
        default:
          usuarioLogado.usu_sidebar = 2;
        break;

        case 2:
          usuarioLogado.usu_sidebar = 3;
        break;

        case 3:
        case 4:
          usuarioLogado.usu_sidebar = 1;
        break;
      }
    }

    if (parseInt(usuarioLogado.usu_sidebar) === 4 && $(window).width() < 992) {
      usuarioLogado.usu_sidebar = 3;
    }

    sidebar.removeClass('collapsed hidden overlay');

    switch (parseInt(usuarioLogado.usu_sidebar)) {
      case 1:
        sidebar.removeClass('collapsed hidden overlay');
        $('.submenu-item span').show();
        $('.menu-item span').show();
        $('.profile-item span').show();
        $('.submenu').css('padding-left', '15px');
      break;

      case 2:
        sidebar.addClass('collapsed');
        $('.submenu-item span').hide();
        $('.menu-item span').hide();
        $('.profile-item span').hide();
        $('.submenu').css('padding-left', '0px');
      break;

      case 3:
        sidebar.addClass('hidden');
        $('.submenu-item span').hide();
        $('.menu-item span').hide();
        $('.profile-item span').hide();
      break;

      case 4:
        sidebar.addClass('hidden');
        $('.submenu-item span').hide();
        $('.menu-item span').hide();
        $('.profile-item span').hide();
      break;
    }

    fgbAtualizarCheckSidebar(usuarioLogado.usu_sidebar);

    if (!salvar) {
      return;
    }

    $.ajax({
      url: fgbUrl('usuario/setSideBar'),
      type: 'post',
      dataType: 'json',
      data: {
        usu_sidebar: usuarioLogado.usu_sidebar
      },
      success: function (response) {
        if (!response.status) {
          console.error(response);
          swError(response.text || 'Erro ao alterar configuração da sidebar.');
        }
      },
      error: function (xhr) {
        console.error(xhr.responseText);
        swError('Erro ao alterar configuração da sidebar.');
      }
    });
  }
// sidebar.php

// phsa
  function fgbAlterarPaletaDeCores(paleta = null, abrirModal = false, config = {}) {
    const paletaPadrao = {
      softBg: '#e7eeec',
      softText: '#2d8a68',
      softHoverBg: '#f2f7f5',
      softHoverText: '#1b543f',

      primaryBg: '#2d8a68',
      primaryText: '#ffffff',
      primaryHoverBg: '#27775a',
      primaryHoverText: '#ffffff',

      strongBg: '#20624a',
      strongText: '#ffffff',
      strongHoverBg: '#37be8c',
      strongHoverText: '#ffffff',

      sidebarText: '#868e96',
      sidebarHoverBg: '#2d8a68',
      sidebarHoverText: '#ffffff',
      sidebarSubmenuHoverBg: '#37be8c',
      sidebarSubmenuHoverText: '#ffffff',

      sidebarActiveBg: '#27775a',
      sidebarActiveText: '#ffffff',
      sidebarSubmenuActiveBg: '#37be8c',
      sidebarSubmenuActiveText: '#ffffff'
    };

    const mapaCss = {
      softBg: '--phsa-soft-bg',
      softText: '--phsa-soft-text',
      softHoverBg: '--phsa-soft-hover-bg',
      softHoverText: '--phsa-soft-hover-text',

      primaryBg: '--phsa-primary-bg',
      primaryText: '--phsa-primary-text',
      primaryHoverBg: '--phsa-primary-hover-bg',
      primaryHoverText: '--phsa-primary-hover-text',

      strongBg: '--phsa-strong-bg',
      strongText: '--phsa-strong-text',
      strongHoverBg: '--phsa-strong-hover-bg',
      strongHoverText: '--phsa-strong-hover-text',

      sidebarText: '--phsa-sidebar-text',
      sidebarHoverBg: '--phsa-sidebar-hover-bg',
      sidebarHoverText: '--phsa-sidebar-hover-text',
      sidebarSubmenuHoverBg: '--phsa-sidebar-submenu-hover-bg',
      sidebarSubmenuHoverText: '--phsa-sidebar-submenu-hover-text',

      sidebarActiveBg: '--phsa-sidebar-active-bg',
      sidebarActiveText: '--phsa-sidebar-active-text',
      sidebarSubmenuActiveBg: '--phsa-sidebar-submenu-active-bg',
      sidebarSubmenuActiveText: '--phsa-sidebar-submenu-active-text'
    };

    const cfg = {
      modulo: config.modulo || 'usuario',
      titulo: config.titulo || '<i class="fa-solid fa-palette"></i> Paleta de cores',
      input: config.input || null,
      salvarAutomatico: typeof config.salvarAutomatico === 'boolean' ? config.salvarAutomatico : (config.modulo || 'usuario') === 'usuario',
      url: config.url || 'usuario/setPaletaCores',
      campo: config.campo || 'usu_paletacores',
      dataExtra: config.dataExtra || {},
      onSalvar: typeof config.onSalvar === 'function' ? config.onSalvar : null
    };

    function normalizarPaleta(valor) {
      if (!valor) {
        return {...paletaPadrao};
      }

      if (typeof valor === 'string') {
        try {
          valor = JSON.parse(valor);
        } catch (e) {
          return {...paletaPadrao};
        }
      }

      const paletaNormalizada = {
        ...paletaPadrao,
        ...valor
      };

      Object.keys(paletaPadrao).forEach(function (chave) {
        const cor = String(paletaNormalizada[chave] || '').trim();

        if (!/^#[0-9A-Fa-f]{6}$/.test(cor)) {
          paletaNormalizada[chave] = paletaPadrao[chave];
        }
      });

      return paletaNormalizada;
    }

    function hexParaRgb(hex) {
      const valor = String(hex || '').replace('#', '').trim();

      if (!/^[0-9A-Fa-f]{6}$/.test(valor)) {
        return {r: 45, g: 138, b: 104};
      }

      return {
        r: parseInt(valor.substring(0, 2), 16),
        g: parseInt(valor.substring(2, 4), 16),
        b: parseInt(valor.substring(4, 6), 16)
      };
    }

    function rgbParaHex(rgb) {
      return '#' + [rgb.r, rgb.g, rgb.b]
        .map(function (canal) {
          return Math.max(0, Math.min(255, Math.round(canal))).toString(16).padStart(2, '0');
        })
        .join('');
    }

    function rgbParaHsl(rgb) {
      const r = rgb.r / 255;
      const g = rgb.g / 255;
      const b = rgb.b / 255;
      const max = Math.max(r, g, b);
      const min = Math.min(r, g, b);
      const delta = max - min;
      let h = 0;
      let s = 0;
      const l = (max + min) / 2;

      if (delta !== 0) {
        s = delta / (1 - Math.abs((2 * l) - 1));
        if (max === r) h = 60 * (((g - b) / delta) % 6);
        else if (max === g) h = 60 * (((b - r) / delta) + 2);
        else h = 60 * (((r - g) / delta) + 4);
      }

      if (h < 0) h += 360;
      return {h: h, s: s, l: l};
    }

    function hslParaHex(hsl) {
      const h = ((hsl.h % 360) + 360) % 360;
      const s = Math.max(0, Math.min(1, hsl.s));
      const l = Math.max(0, Math.min(1, hsl.l));
      const c = (1 - Math.abs((2 * l) - 1)) * s;
      const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
      const m = l - (c / 2);
      let rgb = {r: 0, g: 0, b: 0};

      if (h < 60) rgb = {r: c, g: x, b: 0};
      else if (h < 120) rgb = {r: x, g: c, b: 0};
      else if (h < 180) rgb = {r: 0, g: c, b: x};
      else if (h < 240) rgb = {r: 0, g: x, b: c};
      else if (h < 300) rgb = {r: x, g: 0, b: c};
      else rgb = {r: c, g: 0, b: x};

      return rgbParaHex({r: (rgb.r + m) * 255, g: (rgb.g + m) * 255, b: (rgb.b + m) * 255});
    }

    function ajustarCor(hex, ajuste) {
      const hsl = rgbParaHsl(hexParaRgb(hex));
      hsl.l = Math.max(0, Math.min(1, hsl.l + ajuste));
      return hslParaHex(hsl);
    }

    function corDeTexto(hex) {
      const rgb = hexParaRgb(hex);
      const canais = [rgb.r, rgb.g, rgb.b].map(function (canal) {
        const valor = canal / 255;
        return valor <= 0.03928 ? valor / 12.92 : Math.pow((valor + 0.055) / 1.055, 2.4);
      });
      const luminancia = (0.2126 * canais[0]) + (0.7152 * canais[1]) + (0.0722 * canais[2]);
      return luminancia > 0.45 ? '#212529' : '#ffffff';
    }

    function gerarPaleta(corPrincipal) {
      const principal = /^#[0-9A-Fa-f]{6}$/.test(String(corPrincipal || '').trim())
        ? String(corPrincipal).trim().toLowerCase()
        : paletaPadrao.primaryBg;
      const principalHover = ajustarCor(principal, -0.05);
      const forte = ajustarCor(principal, -0.105);
      const hslPrincipal = rgbParaHsl(hexParaRgb(principal));
      const forteHoverHsl = {...hslPrincipal};
      const suaveHsl = {...hslPrincipal};
      const suaveHoverHsl = {...hslPrincipal};

      forteHoverHsl.l = Math.min(0.48, Math.max(0.32, forteHoverHsl.l + 0.15));
      forteHoverHsl.s = Math.max(0.55, forteHoverHsl.s * 0.65);
      suaveHsl.l = 0.92;
      suaveHsl.s = Math.min(0.45, Math.max(0.18, suaveHsl.s * 0.35));
      suaveHoverHsl.l = 0.96;
      suaveHoverHsl.s = Math.min(0.55, Math.max(0.24, suaveHoverHsl.s * 0.5));

      return normalizarPaleta({
        softBg: hslParaHex(suaveHsl), softText: principal,
        softHoverBg: hslParaHex(suaveHoverHsl), softHoverText: ajustarCor(principal, -0.14),
        primaryBg: principal, primaryText: corDeTexto(principal),
        primaryHoverBg: principalHover, primaryHoverText: corDeTexto(principalHover),
        strongBg: forte, strongText: corDeTexto(forte),
        strongHoverBg: hslParaHex(forteHoverHsl), strongHoverText: corDeTexto(hslParaHex(forteHoverHsl)),
        sidebarText: '#868e96', sidebarHoverBg: principal, sidebarHoverText: '#ffffff',
        sidebarSubmenuHoverBg: hslParaHex(forteHoverHsl), sidebarSubmenuHoverText: '#ffffff',
        sidebarActiveBg: principalHover, sidebarActiveText: '#ffffff',
        sidebarSubmenuActiveBg: hslParaHex(forteHoverHsl), sidebarSubmenuActiveText: '#ffffff'
      });
    }

    function aplicarPaleta(valor) {
      const paletaAplicar = normalizarPaleta(valor);

      Object.keys(mapaCss).forEach(function (chave) {
        document.documentElement.style.setProperty(mapaCss[chave], paletaAplicar[chave]);
      });

      return paletaAplicar;
    }

    function getPaletaCssAtual() {
      const paletaAtual = {};

      Object.keys(mapaCss).forEach(function (chave) {
        const valor = getComputedStyle(document.documentElement).getPropertyValue(mapaCss[chave]).trim();

        paletaAtual[chave] = /^#[0-9A-Fa-f]{6}$/.test(valor) ? valor : paletaPadrao[chave];
      });

      return normalizarPaleta(paletaAtual);
    }

    function getValorInicial() {
      if (paleta !== null && paleta !== undefined && paleta !== '') {
        return paleta;
      }

      if (cfg.input && $(cfg.input).length && $(cfg.input).val().trim() !== '') {
        return $(cfg.input).val();
      }

      if (
        cfg.modulo === 'usuario' &&
        typeof usuarioLogado !== 'undefined' &&
        usuarioLogado.usu_paletacores
      ) {
        return usuarioLogado.usu_paletacores;
      }

      return null;
    }

    function getPaletaModal() {
      const paleta = {};

      Object.keys(paletaPadrao).forEach(function (chave) {
        paleta[chave] = $('#paleta_' + chave).val();
      });

      return normalizarPaleta(paleta);
    }

    function setPaletaModal(valor) {
      const paletaSetar = normalizarPaleta(valor);

      Object.keys(paletaPadrao).forEach(function (chave) {
        $('#paleta_' + chave).val(paletaSetar[chave]);
      });

      if ($('#paletaCorPrincipal').length) {
        $('#paletaCorPrincipal').val(paletaSetar.primaryBg);
        $('#paletaCorPrincipalHex').text(paletaSetar.primaryBg.toUpperCase());
      }

      aplicarPaleta(paletaSetar);
    }

    function inputPaleta(id, label, value) {
      return `
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label small mb-1" for="paleta_${id}">${label}</label>
          <input type="color" id="paleta_${id}" class="form-control form-control-color w-100 js-paleta-color" value="${value}">
        </div>
      `;
    }

    if (!abrirModal) {
      aplicarPaleta(getValorInicial());
      return;
    }

    const paletaOriginalVisual = getPaletaCssAtual();
    const valorInicial = getValorInicial();
    const paletaAtual = valorInicial ? normalizarPaleta(valorInicial) : gerarPaleta(paletaPadrao.primaryBg);
    let paletaSalva = false;

    const modalId = SimpleModal.mostrar({
      titulo: cfg.titulo,
      tamanho: 'modal-xl modal-dialog-scrollable modal-fullscreen-sm-down',
      corpo: `
        <div id="paletaCoresBody">
          <div class="row">
            <div class="col-12 col-xl-8">
              <div id="paletaCoresGerador">
                <h6 class="text-uppercase text-muted mb-3">Cor principal</h6>

                <div class="border rounded p-3 mb-3 bg-light-subtle">
                  <label class="form-label small mb-2" for="paletaCorPrincipal">Escolha uma cor para a organização</label>
                  <div class="d-flex align-items-center gap-3">
                    <input type="color" id="paletaCorPrincipal" class="form-control form-control-color" value="${paletaAtual.primaryBg}" title="Escolher cor principal">
                    <div>
                      <div id="paletaCorPrincipalHex" class="fw-semibold text-uppercase">${paletaAtual.primaryBg}</div>
                      <div class="small text-muted">As demais cores serão calculadas automaticamente.</div>
                    </div>
                  </div>
                </div>

                <div class="d-grid gap-2">
                  <button type="button" id="btnDefinirCorPrincipal" class="bto bto-phsa w-100">
                    <i class="fa-solid fa-palette"></i>&nbsp;Definir como cor principal
                  </button>
                  <button type="button" id="btnDefinirCorManualmente" class="bto bto-phsa-light w-100">
                    <i class="fa-solid fa-sliders"></i>&nbsp;Definir cor manualmente
                  </button>
                </div>
              </div>

              <div id="paletaCoresManual" class="d-none">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h6 class="text-uppercase text-muted mb-0">Definição manual</h6>
                  <button type="button" id="btnVoltarGeradorPaleta" class="bto bto-sm bto-phsa-light">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;Usar gerador
                  </button>
                </div>
              <div class="row mb-3">
                <div class="col-12">
                  <h6 class="text-uppercase text-muted mb-3">Suave</h6>
                </div>

                ${inputPaleta('softBg', 'Plano de fundo', paletaAtual.softBg)}
                ${inputPaleta('softText', 'Texto', paletaAtual.softText)}
                ${inputPaleta('softHoverBg', 'Plano de fundo hover', paletaAtual.softHoverBg)}
                ${inputPaleta('softHoverText', 'Texto hover', paletaAtual.softHoverText)}
              </div>

              <hr>

              <div class="row mb-3">
                <div class="col-12">
                  <h6 class="text-uppercase text-muted mb-3">Principal</h6>
                </div>

                ${inputPaleta('primaryBg', 'Plano de fundo', paletaAtual.primaryBg)}
                ${inputPaleta('primaryText', 'Texto', paletaAtual.primaryText)}
                ${inputPaleta('primaryHoverBg', 'Plano de fundo hover', paletaAtual.primaryHoverBg)}
                ${inputPaleta('primaryHoverText', 'Texto hover', paletaAtual.primaryHoverText)}
              </div>

              <hr>

              <div class="row mb-3">
                <div class="col-12">
                  <h6 class="text-uppercase text-muted mb-3">Forte</h6>
                </div>

                ${inputPaleta('strongBg', 'Plano de fundo', paletaAtual.strongBg)}
                ${inputPaleta('strongText', 'Texto', paletaAtual.strongText)}
                ${inputPaleta('strongHoverBg', 'Plano de fundo hover', paletaAtual.strongHoverBg)}
                ${inputPaleta('strongHoverText', 'Texto hover', paletaAtual.strongHoverText)}
              </div>

              <hr>

              <div class="row mb-3">
                <div class="col-12">
                  <h6 class="text-uppercase text-muted mb-3">Menu lateral</h6>
                </div>

                ${inputPaleta('sidebarText', 'Texto padrão', paletaAtual.sidebarText)}
                ${inputPaleta('sidebarActiveBg', 'Plano de fundo pai ativo', paletaAtual.sidebarActiveBg)}
                ${inputPaleta('sidebarActiveText', 'Texto pai ativo', paletaAtual.sidebarActiveText)}
                ${inputPaleta('sidebarHoverBg', 'Plano de fundo pai hover', paletaAtual.sidebarHoverBg)}
                ${inputPaleta('sidebarHoverText', 'Texto pai hover', paletaAtual.sidebarHoverText)}
                ${inputPaleta('sidebarSubmenuActiveBg', 'Plano de fundo filho ativo', paletaAtual.sidebarSubmenuActiveBg)}
                ${inputPaleta('sidebarSubmenuActiveText', 'Texto filho ativo', paletaAtual.sidebarSubmenuActiveText)}
                ${inputPaleta('sidebarSubmenuHoverBg', 'Plano de fundo filho hover', paletaAtual.sidebarSubmenuHoverBg)}
                ${inputPaleta('sidebarSubmenuHoverText', 'Texto filho hover', paletaAtual.sidebarSubmenuHoverText)}
              </div>
              </div>
            </div>

            <div class="col-12 col-xl-4">
              <div class="position-sticky" style="top: 15px;">
                <h6 class="text-uppercase text-muted mb-3">Pré-visualização</h6>

                <div class="mb-3">
                  <button type="button" class="bto bto-phsa-light w-100 mb-2">
                    <div class="fw-bold">Suave</div>
                    <div class="small">Plano de fundo e texto</div>
                  </button>

                  <button type="button" class="bto bto-phsa w-100 mb-2">
                    <div class="fw-bold">Principal</div>
                    <div class="small">Plano de fundo e texto</div>
                  </button>

                  <button type="button" class="bto bto-phsa-dark w-100">
                    <div class="fw-bold">Forte</div>
                    <div class="small">Plano de fundo e texto</div>
                  </button>
                </div>

                <h6 class="text-uppercase text-muted mb-3">Menu lateral</h6>

                <div class="rounded overflow-hidden" style="background: #212529;">
                  <div class="p-2" style="color: var(--phsa-sidebar-text);">
                    <i class="fa-solid fa-database me-1"></i> Item normal
                  </div>

                  <div class="p-2" style="color: var(--phsa-sidebar-hover-text); background: var(--phsa-sidebar-hover-bg);">
                    <i class="fa-solid fa-folder me-1"></i> Item selecionado
                  </div>

                  <div class="p-2" style="color: var(--phsa-sidebar-submenu-hover-text); background: var(--phsa-sidebar-submenu-hover-bg);">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Item filho
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      `,
      rodape: `
        <button type="button" id="btnCancelarPaletaCores" class="bto bto-phsa-light" data-bs-dismiss="modal">
          <i class="fas fa-times"></i>&nbsp;Cancelar
        </button>

        <button type="button" id="btnRestaurarPaletaCores" class="bto bto-phsa-light">
          <i class="fa-solid fa-rotate-left"></i>&nbsp;Restaurar padrões
        </button>

        <button type="button" id="btnSalvarPaletaCores" class="bto bto-phsa">
          <i class="fas fa-save"></i>&nbsp;Salvar
        </button>
      `
    });

    aplicarPaleta(paletaAtual);

    $(document).off('input.paletaGerador').on('input.paletaGerador', '#paletaCorPrincipal', function () {
      const corPrincipal = String($(this).val() || paletaPadrao.primaryBg).toLowerCase();
      $('#paletaCorPrincipalHex').text(corPrincipal.toUpperCase());
      setPaletaModal(gerarPaleta(corPrincipal));
      $('#btnRestaurarPaletaCores').prop('disabled', false);
    });

    $(document).off('click.paletaModoManual').on('click.paletaModoManual', '#btnDefinirCorManualmente', function () {
      $('#paletaCoresGerador').addClass('d-none');
      $('#paletaCoresManual').removeClass('d-none');
    });

    $(document).off('click.paletaModoGerador').on('click.paletaModoGerador', '#btnVoltarGeradorPaleta', function () {
      $('#paletaCoresManual').addClass('d-none');
      $('#paletaCoresGerador').removeClass('d-none');
    });

    $(document).off('click.paletaDefinirPrincipal').on('click.paletaDefinirPrincipal', '#btnDefinirCorPrincipal', function () {
      $('#btnSalvarPaletaCores').trigger('click');
    });

    $(document).off('input.paletaCores').on('input.paletaCores', '.js-paleta-color', function () {
      aplicarPaleta(getPaletaModal());
      if ($(this).attr('id') === 'paleta_primaryBg') {
        $('#paletaCorPrincipal').val($(this).val());
        $('#paletaCorPrincipalHex').text(String($(this).val()).toUpperCase());
      }
      $('#btnRestaurarPaletaCores').prop('disabled', false);
    });

    $(document).off('click.paletaRestaurar').on('click.paletaRestaurar', '#btnRestaurarPaletaCores', function () {
      setPaletaModal(paletaPadrao);
      $(this).prop('disabled', true);
    });

    $(document).off('click.paletaSalvar').on('click.paletaSalvar', '#btnSalvarPaletaCores', function () {
      console.log(cfg);
      /*
        {
            "modulo": "empresa",
            "titulo": "<i class='fa-solid fa-palette'></i> Paleta de cores da empresa",
            "input": "#emp_paletacores",
            "salvarAutomatico": false,
            "url": "usuario/setPaletaCores",
            "campo": "usu_paletacores",
            "onSalvar": null
        }
      */

      const novaPaleta = getPaletaModal();

      if (!cfg.salvarAutomatico) {
        if (cfg.input && $(cfg.input).length) {
          $(cfg.input).val(JSON.stringify(novaPaleta));
        }

        if (cfg.onSalvar) {
          cfg.onSalvar(novaPaleta);
        }

        paletaSalva = true;
        aplicarPaleta(novaPaleta);
        SimpleModal.fechar(modalId);
        return;
      }

      $('#btnSalvarPaletaCores')
        .prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin"></i>&nbsp;Salvando');

        const dataAjax = {
          ...cfg.dataExtra,
          [cfg.campo]: JSON.stringify(novaPaleta)
        };

        $.ajax({
          url: fgbUrl(cfg.url),
          type: 'post',
          dataType: 'json',
          data: dataAjax,
          success: function (response) {
            swResponse(response);

            if (!response.status) {
              aplicarPaleta(paletaOriginalVisual);

              $('#btnSalvarPaletaCores')
                .prop('disabled', false)
                .html('<i class="fas fa-save"></i>&nbsp;Salvar');

              return;
            }

            paletaSalva = true;

            if (cfg.modulo === 'usuario' && typeof usuarioLogado !== 'undefined') {
              usuarioLogado.usu_paletacores = JSON.stringify(novaPaleta);
            }

            if (cfg.input && $(cfg.input).length) {
              $(cfg.input).val(JSON.stringify(novaPaleta));
            }

            if (cfg.onSalvar) {
              cfg.onSalvar(novaPaleta, response);
            }

            aplicarPaleta(novaPaleta);
            SimpleModal.fechar(modalId);
          },
          error: function (xhr) {
            console.error(xhr.responseText);
            aplicarPaleta(paletaOriginalVisual);
            swError('Erro ao salvar paleta de cores.');

            $('#btnSalvarPaletaCores')
              .prop('disabled', false)
              .html('<i class="fas fa-save"></i>&nbsp;Salvar');
          }
        });
    });

    $('#' + modalId).on('hidden.bs.modal', function () {
      if (!paletaSalva) {
        aplicarPaleta(paletaOriginalVisual);
      }

      $(document).off('input.paletaCores');
      $(document).off('input.paletaGerador');
      $(document).off('click.paletaModoManual');
      $(document).off('click.paletaModoGerador');
      $(document).off('click.paletaDefinirPrincipal');
      $(document).off('click.paletaRestaurar');
      $(document).off('click.paletaSalvar');
    });
  }
// phsa
