





$(document).ready(function() {
  $('.celular, .whatsapp').mask('(99) 9 9999-9999');
  $('.telefone').mask('(99) 9999-9999');
  $('.contato').mask('(99) 999999999');
  $('.apenasNumero').mask('99999999999999');
  $('.cep').mask('99999-999');
  $('.time').mask('99:99');
  $('.timefull').mask('99:99:99');
  $('.dinheiro').mask("#.##0,00", {reverse: true});
  $('.cpf').mask('999.999.999-99');
  $('.cnpj').mask('99.999.999/9999-99');
  $('.quantidade').mask("9999,99");

  setTimeout(function(){
    $('[data-bs-toggle="tooltip"]').tooltip({ html: true });
  }, 777);

  setTimeout(function(){
    $('.tox-statusbar__branding').remove();
  }, 1500);

  $('#end_cep').blur(function () {
    const cep = $(this).val().replace(/\D/g, '');
    if (cep.length === 8) {
      $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function (data) {
        if (!data.erro) {
          $('#end_logradouro').val(data.logradouro);
          $('#end_bairro').val(data.bairro);
          $('#end_cidade').val(data.localidade);
          $('#end_uf').val(data.uf);
          $('#end_estado').val(data.estado);
        } else {
          swToastError('CEP não encontrado');
        }
      }).fail(function () {
        swToastError('Erro ao buscar o CEP. Tente novamente');
      });
    }
  });

  $('input.controleDeSenha').each(function () {
    const $input = $(this);

    // Evita duplicação
    if ($input.next('.toggleSenha').length === 0) {
      const $iconToggle = $(`
        <span id="toggleSenha" class="toggleSenha position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer;">
          <i class="fas fa-eye-slash"></i>
        </span>
      `);

      // Posiciona dentro do mesmo container
      $input.after($iconToggle);
      $input.closest('.position-relative').css('position', 'relative'); // Garante posição relativa se não tiver

      $iconToggle.on('click', function () {
        const isPassword = $input.attr('type') === 'password';
        $input.attr('type', isPassword ? 'text' : 'password');
        if ( isPassword ) {
          $(this).html(`<i class="fas fa-eye"></i>`);
        } else {
          $(this).html(`<i class="fas fa-eye-slash"></i>`);
        }
      });

      // Ajusta padding para não sobrepor texto digitado
      const existingPadding = parseInt($input.css('padding-right')) || 0;
      $input.css('padding-right', `${existingPadding + 32}px`);
    }
  });

  const scrollToTopButton = $("#scrollToTop");

  $(window).scroll(function() {
    if ($(this).scrollTop() > 300) {
      scrollToTopButton.fadeIn();
    } else {
      scrollToTopButton.fadeOut();
    }
  });

  scrollToTopButton.click(function(e) {
    e.preventDefault();
    $("html, body").animate({ scrollTop: 0 }, "smooth");
  });
});

/* preloader */
  $(window).on("load", function () {
    $("#preloader").fadeOut("slow");
  });

  setTimeout(function () {
    if ($("#preloader").is(":visible")) {
      $("#preloader").fadeOut("slow");
    }
  }, 5000);

/* BOOTSTRAP */
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });

/* SWAL - SWEETALERT2 */
  /* swFire */
  function swFire(prm) {
    Swal.fire({
      title: prm.title || '',
      text: prm.text || '',
      icon: prm.icon || 'info',
      showConfirmButton: prm.showConfirmButton ?? false,
      confirmButtonText: prm.confirmButtonText || 'Ok',
      allowOutsideClick: prm.allowOutsideClick ?? true,
      allowEscapeKey: prm.allowEscapeKey ?? true,
      timer: prm.timer || null,
      timerProgressBar: prm.timerProgressBar || false,
      toast: prm.toast || false,
      position: prm.position || 'center',
      showCloseButton: prm.showCloseButton || false,
      didOpen: prm.didOpen || null,
      ...prm,
    });
  }

  /* swResponse */
  function swResponse(response, params = {}) {
    return new Promise((resolve, reject) => {
      const { 
        title = response.title || 'Atenção', 
        text = response.text || '', 
        icon = response.icon || 'info', 
        timer = response.status ? 3000 : null,
        url = response.url || null,
      } = response;

      const { idElemento, ...swalParams } = params;

      const prm = { ...swalParams, title, text, icon, timer };

      swFire(prm);

      if (response.status && url) {
        setTimeout(() => resolve(url), timer);
      } else {
        if (idElemento) {
          $(idElemento).find('input, button, select, textarea').attr('disabled', false);
        }
        resolve(response);
      }
    });
  }

  /* swSuccess */
  function swSuccess(text = 'Operação realizada com sucesso!', params = {}) {
    const prm = { ...params, title: 'ATENÇÃO', text: text, icon: 'success', timer: 3000 };
    swFire(prm);
  }

  /* swError */
  function swError(text = 'Ocorreu um erro ao processar sua solicitação.', params = {}) {
    const prm = { ...params, title: 'ATENÇÃO', text: text, icon: 'error', timer: 5000 };
    swFire(prm);
  }

  /* swWarning */
  function swWarning(text = 'Atenção! Verifique os dados informados.', params = {}) {
    const prm = { ...params, title: 'ATENÇÃO', text: text, icon: 'warning', timer: 4000 };
    swFire(prm);
  }

  /* swInfo */
  function swInfo(text = 'Informação importante.', params = {}) {
    const prm = { ...params, title: 'Informação', text: text, icon: 'info', timer: 3000 };
    swFire(prm);
  }

  /* swAguarde */
  function swAguarde(params = {}) {
    const prm = {
      ...params,
      title: params.title || 'Por favor, aguarde...',
      text: params.text || 'Processando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      timer: params.timer ?? 10000,
      didOpen: () => {
        Swal.showLoading();
      },
    };
    swFire(prm);
  }

  /* swToastResponse */
  function swToastResponse(response, params = {}) {
    const { icon = response.icon || 'info', title = response.title || '', text = response.text || '' } = response;
    const prm = {
      ...params,
      icon,
      title,
      text,
      toast: true,
      position: params.position || 'top-end',
      showConfirmButton: params.showConfirmButton ?? false,
      showCloseButton: params.showCloseButton ?? true,
      timer: 3000,
      timerProgressBar: true,
    };
    swFire(prm);
  }

  /* swToastSuccess */
  function swToastSuccess(text = 'Operação realizada com sucesso!', params = {}) {
    const prm = { ...params, title: 'ATENÇÃO', text: text, icon: 'success', toast: true, position: 'top-end', timer: 1500, timerProgressBar: true };
    swFire(prm);
  }

  /* swToastError */
  function swToastError(text = 'Ocorreu um erro ao processar sua solicitação.', params = {}) {
    const prm = { ...params, title: 'ATENÇÃO', text: text, icon: 'error', toast: true, position: 'top-end', timer: 3000, timerProgressBar: true };
    swFire(prm);
  }

  /* swToastWarning */
  function swToastWarning(text = 'Atenção! Verifique os dados informados.', params = {}) {
    const prm = { ...params, title: 'ATENÇÃO', text: text, icon: 'warning', toast: true, position: 'top-end', timer: 3000, timerProgressBar: true };
    swFire(prm);
  }

  /* swToastInfo */
  function swToastInfo(text = 'Informação importante.', params = {}) {
    const prm = { ...params, title: 'Informação', text: text, icon: 'info', toast: true, position: 'top-end', timer: 1500, timerProgressBar: true };
    swFire(prm);
  }

  /* swFechar */
  function swFechar() {
    Swal.close();
  }

/* XLSL */
  function fgbConverterTabelaEmXlsx(idTabela, nomeAoBaixar = 'arquivo') {
    try {
      const tabela = document.getElementById(idTabela);
      if (!tabela) {
        throw new Error(`Tabela com ID "${idTabela}" não encontrada.`);
      }

      swAguarde({ title: 'Exportando...', html: '<b>Gerando arquivo Excel, aguarde...</b>' });

      const tabelaClone = tabela.cloneNode(true);
      tabelaClone.querySelectorAll('.hideOnExcel').forEach(elemento => elemento.remove());

      tabelaClone.querySelectorAll('td, th').forEach(celula => {
        celula.innerHTML = celula.textContent;
      });

      const now = new Date();
      const timestamp = now.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }).replace(/\//g, '') + 
                        '_' + 
                        now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }).replace(/:/g, '');
      const nomeArquivo = `${nomeAoBaixar}_${timestamp}.xlsx`;

      const wb = XLSX.utils.table_to_book(tabelaClone, { sheet: 'Sheet1' });
      XLSX.writeFile(wb, nomeArquivo);
      swSuccess(`Arquivo "${nomeArquivo}" gerado com sucesso!`);
    } catch (erro) {
      swError(`Erro ao exportar a tabela para Excel: ${erro.message}`);
    } finally {
      swFechar();
    }
  }

/* PESQUISA RÁPIDA */
  function fgbPesquisaRapida(modulo, moduloName=null) {
    moduloName = moduloName==null?modulo:moduloName;
    Swal.fire({
      title: `Pesquisa rápida de ${moduloName}`,
      text: `Digite o ID ou parte da informação procurada`,
      input: 'text',
      inputPlaceholder: 'Digite aqui...',
      inputAttributes: {
        autocomplete: 'off'
      },
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-search"></i> Pesquisar',
      cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
      customClass: {
        confirmButton: 'bto bto-phsa',
        cancelButton: 'bto bto-phsa-light'
      },
      reverseButtons: true,
      allowOutsideClick: false,
      preConfirm: (value) => {
        if (!value) {
          Swal.showValidationMessage(`Digite para iniciar a pesquisa em ${modulo}`);
        }
        return value;
      }
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `${CONF_URL}/${modulo}/pesquisarRapida`,
          type: 'post',
          dataType: 'json',
          data: {
            conteudo: encodeURIComponent(result.value)
          },
          success: function (response) {
            if (Number(response.total || 0) > 1) {
              response.location = `${CONF_URL}/${modulo}/list?lbLik=${encodeURIComponent(result.value)}`;
            }

            swResponse(response);

            if (response.location) {
              setTimeout(function(){
                location.replace(response.location);
              }, 2000);
            }
          }
        });
      }
    });
  }

/* GERAR IMPRESSÃO */
  function fgbGerarImpressao(idElement, title = 'Impressão', isHtml = false, removeClasse = []) {
    const arrElements = idElement.split(',');
    document.querySelectorAll('.hideOnPrint').forEach(el => el.style.display = 'none');

    arrElements.forEach(id => {
      const $el = $('#' + id.trim());
      removeClasse.forEach(className => {
        $el.removeClass(className);
        $el.find('.' + className).removeClass(className);
      });
    });

    const newWin = window.open('', 'Print-Window');
    newWin.document.open();
    newWin.document.write(`
      <html>
        <head>
          <title>${title}</title>
          <link rel="stylesheet" href="${CONF_URL}/framework/bootstrap/css/bootstrap.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/fontawesome/css/all.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/phsa/phsaImageTool/css/phsaImageTool.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/phsa/phsaNavbar/css/phsaNavbar.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/animate/animate.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/aos/aos.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/bootstrap-multiselect/css/bootstrap-multiselect.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/boxicons/css/boxicons.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/datatables/datatables.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/glightbox/css/glightbox.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/icofont/icofont.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/lightbox/css/lightbox.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/owlcarousel/css/owl.carousel.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/slick/slick.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/superfish/css/superfish.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/sweetalert2/sweetalert2.min.css">
          <link rel="stylesheet" href="${CONF_URL}/framework/swiper/swiper-bundle.min.css">

          <link rel="stylesheet" href="${CONF_URL}/source/Web/assets/css/global.css">
          <link rel="stylesheet" href="${CONF_URL}/source/Web/assets/css/phsa.css">
          <link rel="stylesheet" href="${CONF_URL}/source/Web/assets/css/site.css">
          <link rel="stylesheet" href="${CONF_URL}/source/Web/assets/css/sistema.css">
        </head>
        <body onload="window.print(); window.close();">
          <div class="container-fluid pt-5 pb-5">
            `);

            if (isHtml) {
              newWin.document.write(idElement); // Recebe conteúdo HTML direto
            } else {
              arrElements.forEach(elementId => {
                const element = document.getElementById(elementId);
                if (element) {
                  newWin.document.write(element.innerHTML);
                }
              });
            }
            newWin.document.write(`
          </div>
          <script src="${CONF_URL}/framework/popper/popper.min.js"></script>
          <script src="${CONF_URL}/framework/bootstrap/js/bootstrap.min.js"></script>
          <script src="${CONF_URL}/framework/jquery/jquery-3.7.1.js"></script>
          <script src="${CONF_URL}/framework/fontawesome/js/all.min.js"></script>
        </body>
      </html>
    `);
    newWin.document.close();

    document.querySelectorAll('.hideOnPrint').forEach(el => el.style.display = '');
  }

/* GERAR DATATABLE */
  var dataTableInstances = {};

  function fgbDatatable(tableId) {
    try {
      const button = event.currentTarget;
      const tableElement = document.getElementById(tableId);

      if (!tableElement) {
        throw new Error(`Tabela com ID "${tableId}" não encontrada.`);
      }

      if (dataTableInstances[tableId]) {
        swToastInfo({ title: 'Desmontando Tabela', html: '<b>Retornando tabela ao formato original...</b>' });
        dataTableInstances[tableId].destroy();
        delete dataTableInstances[tableId];
        button.innerHTML = '<i class="fa-solid fa-diagram-successor"></i>&nbsp;Automatizar tabela';
        tableElement.classList.remove('dataTable');
        swToastSuccess('Tabela normalizada com sucesso!');
      } else {
        swToastInfo({ title: 'Configurando DataTable', html: '<b>Inicializando tabela com recursos avançados...</b>' });
        dataTableInstances[tableId] = $(tableElement).DataTable({
          dom: 'Bfrtip',
          buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
          responsive: true,
          language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json' },
          pageLength: 10,
        });
        button.innerHTML = '<i class="fa-solid fa-diagram-predecessor"></i>&nbsp;Normalizar tabela';
        tableElement.classList.add('dataTable');
        swToastSuccess('Tabela automatizada com sucesso!');
      }
    } catch (erro) {
      swToastError(`Erro ao processar a tabela: ${erro.message}`);
    }
  }

/* COPIAR CONTEUDO */
  function fgbCopiarParaAreaDeTransferencia(texto, campo = 'Texto') {
    if (!texto || texto.trim() === '') {
      swToastError(`${campo} está vazio. Nada para copiar!`);
      return;
    }

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(texto)
        .then(() => swToastSuccess(`${campo} copiado com sucesso!`))
        .catch(err => {
          console.error(err);
          swToastError(`Erro ao copiar ${campo}`);
        });
    } else {
      const tempInput = document.createElement('textarea');
      tempInput.value = texto;
      tempInput.setAttribute('readonly', '');
      tempInput.style.position = 'absolute';
      tempInput.style.left = '-9999px';
      document.body.appendChild(tempInput);
      tempInput.select();
      try {
        document.execCommand('copy');
        swToastSuccess(`${campo} copiado com sucesso!`);
      } catch (err) {
        console.error(err);
        swToastError(`Erro ao copiar ${campo}`);
      }
      document.body.removeChild(tempInput);
    }
  }

/* SELECT FILE */
  function fgbAbrirExplorer(params) {
    const { accept = '', onFileSelect, multiple = false } = params;
    const input = document.createElement('input');
    input.type = 'file';
    input.style.display = 'none';
    input.accept = accept;

    if (multiple) {
      input.multiple = true;
    }

    input.addEventListener('change', async () => {
      const files = input.files;

      if (!files || files.length === 0) {
        swToastInfo('Operação cancelada');
      } else {
        const arquivosProcessados = [];
        for (const file of files) {
          const objArquivo = await gerarObjetoArquivo(file);
          arquivosProcessados.push(objArquivo);
        }

        if (onFileSelect && typeof onFileSelect === 'function') {
          onFileSelect(multiple ? arquivosProcessados : arquivosProcessados[0]);
        }
      }
    });

    document.body.appendChild(input);
    input.click();
    input.addEventListener('blur', () => input.remove());
  }

  function fgbSelecionarArquivo(params = {}) {
    const defaultAccept = '.pdf,.txt,.doc,.docx,.xls,.xlsx,.json,.xml';
    const { customAccept, replaceDefault = false, onFileSelect, multiple = false } = params;
    const accept = replaceDefault
      ? customAccept || defaultAccept
      : `${defaultAccept}${customAccept ? ',' + customAccept : ''}`;
    fgbAbrirExplorer({ accept, onFileSelect, multiple });
  }

  function fgbSelecionarImagem(params = {}) {
    const defaultAccept = 'image/*';
    const { customAccept, replaceDefault = false, onFileSelect, multiple = false } = params;
    const accept = replaceDefault
      ? customAccept || defaultAccept
      : `${defaultAccept}${customAccept ? ',' + customAccept : ''}`;
    fgbAbrirExplorer({ accept, onFileSelect, multiple });
  }

  function fgbSelecionarMultiplosArquivos(params = {}) {
    fgbSelecionarArquivo({ ...params, multiple: true });
  }

  function fgbSelecionarMultiplasImagens(params = {}) {
    fgbSelecionarImagem({ ...params, multiple: true });
  }

  function gerarObjetoArquivo(file) {
    return new Promise((resolve, reject) => {
      const objExemplo = {
        status: true,
        text: `${file.name} carregado com sucesso`,
        icon: 'success',
        class: 'success',
        total: 1,
        data: null,
        isImage: file.type.startsWith('image/'),
        size: file.size,
        name: file.name,
        lastModified: file.lastModified,
        lastModifiedDate: file.lastModifiedDate,
        type: file.type,
        tamanho: null,
        medida: null,
        largura: null,
        altura: null,
        duracao: null,
      };

      const { tamanho, medida } = formatarTamanho(file.size);
      objExemplo.tamanho = tamanho;
      objExemplo.medida = medida;

      const reader = new FileReader();

      if (objExemplo.isImage) {
        const img = new Image();

        reader.onload = function (e) {
          img.onload = function () {
            objExemplo.largura = img.width;
            objExemplo.altura = img.height;
            objExemplo.data = e.target.result;
            resolve(objExemplo);
          };
          img.onerror = function () {
            objExemplo.status = false;
            objExemplo.text = 'Erro ao carregar imagem';
            objExemplo.icon = 'error';
            objExemplo.class = 'danger';
            resolve(objExemplo);
          };
          img.src = e.target.result;
        };

        reader.onerror = function () {
          objExemplo.status = false;
          objExemplo.text = 'Erro ao ler o arquivo';
          objExemplo.icon = 'error';
          objExemplo.class = 'danger';
          resolve(objExemplo);
        };

        reader.readAsDataURL(file);
      } else if (file.type.startsWith('video/')) {
        const video = document.createElement('video');

        reader.onload = function (e) {
          video.onloadedmetadata = function () {
            objExemplo.largura = video.videoWidth;
            objExemplo.altura = video.videoHeight;
            objExemplo.duracao = video.duration;
            objExemplo.data = e.target.result;
            resolve(objExemplo);
          };
          video.onerror = function () {
            objExemplo.status = false;
            objExemplo.text = 'Erro ao carregar vídeo';
            objExemplo.icon = 'error';
            objExemplo.class = 'danger';
            resolve(objExemplo);
          };
          video.src = e.target.result;
        };

        reader.onerror = function () {
          objExemplo.status = false;
          objExemplo.text = 'Erro ao ler o arquivo';
          objExemplo.icon = 'error';
          objExemplo.class = 'danger';
          resolve(objExemplo);
        };

        reader.readAsDataURL(file);
      } else {
        reader.onload = function (e) {
          objExemplo.data = e.target.result;
          resolve(objExemplo);
        };

        reader.onerror = function () {
          objExemplo.status = false;
          objExemplo.text = 'Erro ao ler o arquivo';
          objExemplo.icon = 'error';
          objExemplo.class = 'danger';
          resolve(objExemplo);
        };

        reader.readAsDataURL(file);
      }
    });
  }

  function formatarTamanho(bytes) {
    const unidades = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return { tamanho: '0', medida: 'Bytes' };
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return { tamanho: (bytes / Math.pow(1024, i)).toFixed(2), medida: unidades[i] };
  }

/* MANIPULAR DATA */
  function fgbFormatarDataUsaParaBr(date) {
    if (!date || date.split('-').length !== 3) {
      return null;
    }

    const [year, month, day] = date.split('-');
    
    if (
      isNaN(year) || isNaN(month) || isNaN(day) ||
      month < 1 || month > 12 ||
      day < 1 || day > 31
    ) {
      return null;
    }

    return `${day.padStart(2, '0')}/${month.padStart(2, '0')}/${year}`;
  }

  function fgbFormatarDataHoraUsaParaBr(dataHora) {
    if (!dataHora) return null;

    dataHora = dataHora.replace('T', ' ');

    const [data, hora] = dataHora.split(' ');
    if (!data) return null;

    const [ano, mes, dia] = data.split('-');
    if (!ano || !mes || !dia) return null;

    const horaFormatada = hora ? hora.substring(0, 5) : ''; 

    return `${dia.padStart(2, '0')}/${mes.padStart(2, '0')}/${ano}${horaFormatada ? ` ${horaFormatada}` : ''}`;
  }

  function fgbFormatarHora(hora) {
    if (!hora || typeof hora !== 'string') return null;

    const partes = hora.split(':');
    if (partes.length < 2) return null;

    const [hh, mm] = partes;
    return `${hh.padStart(2, '0')}:${mm.padStart(2, '0')}`;
  }

/* Google Agenda */
  function fgbAdicionarEventoNaMinhaAgendaDoGoogle(evento) {
    if (!evento) return;

    const pad = n => n.toString().padStart(2, '0');

    const inicio = new Date(`${evento.eve_dtinicio}T${evento.eve_hrinicio}`);
    const fim = evento.eve_dtfim && evento.eve_hrfim
      ? new Date(`${evento.eve_dtfim}T${evento.eve_hrfim}`)
      : new Date(inicio.getTime() + 60 * 60 * 1000);

    const formatGoogleDate = date => `${date.getFullYear()}${pad(date.getMonth() + 1)}${pad(date.getDate())}T${pad(date.getHours())}${pad(date.getMinutes())}00`;

    const url = new URL('https://www.google.com/calendar/render');
    url.searchParams.set('action', 'TEMPLATE');
    url.searchParams.set('text', evento.eve_nome || 'Evento');
    url.searchParams.set('dates', `${formatGoogleDate(inicio)}/${formatGoogleDate(fim)}`);
    url.searchParams.set('location', evento.eve_loc_nome || '');
    url.searchParams.set('details', evento.eve_temadamensagem || '');
    url.searchParams.set('sf', 'true');
    url.searchParams.set('output', 'xml');

    window.open(url.toString(), '_blank');
  }

  function fgbGetAgendaDoGoogle(calendarId, apiKey, callback) {
    if (!calendarId || !apiKey || typeof callback !== 'function') {
      console.error('Parâmetros inválidos para fgbGetAgendaDoGoogle');
      return;
    }

    const timeMin = new Date().toISOString();
    const timeMax = new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString();

    const url = `https://www.googleapis.com/calendar/v3/calendars/${encodeURIComponent(calendarId)}/events?key=${apiKey}&timeMin=${timeMin}&timeMax=${timeMax}&singleEvents=true&orderBy=startTime`;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data && Array.isArray(data.items)) {
          callback(data.items);
        } else {
          console.error('Erro ao buscar eventos do Google Agenda', data);
          callback([]);
        }
      })
      .catch(err => {
        console.error('Erro de rede ao buscar Google Agenda', err);
        callback([]);
      });
  }

/* Manipulador de string */
  function gerarAssinatura(nome, modo = []) {
    const partes = nome.trim().split(/\s+/);
    const total = partes.length;

    if (modo.length === 0) {
      // Default: primeiro + segundo se existir
      return partes.slice(0, Math.min(2, total)).join(' ');
    }

    if (modo.includes('abreviado')) {
      if (total === 1) return partes[0];
      const primeiro = partes[0];
      let iniciais = '';
      for (let i = 1; i < total; i++) {
        iniciais += partes[i][0]?.toUpperCase() || '';
      }
      return `${primeiro} ${iniciais}`;
    }

    const saida = [];

    if (modo.includes('primeiro')) {
      saida.push(partes[0]);
    }

    if (modo.includes('segundo') && partes[1]) {
      saida.push(partes[1]);
    }

    if (modo.includes('ultimo') && total > 1) {
      saida.push(partes[total - 1]);
    }

    return saida.length > 0 ? saida.join(' ') : nome;
  }

/* Trocar de CONTEXTO */
  let contextoModalId = null;
  let contextoSelecionado = null;
  let contextoTimerPesquisa = null;

  function fgbAbrirModalContexto() {
    contextoSelecionado = null;

    contextoModalId = SimpleModal.mostrar({
      titulo: '<i class="fa-solid fa-repeat"></i> Trocar organização',
      tamanho: 'modal-xl modal-dialog-top',
      corpo: `
        <div id="contextoModalBody">
          <div class="mb-3">
            <div class="form-floating">
              <input
                type="text"
                id="ctxPesquisa"
                class="form-control input-phsa-focus input-phsa-hover"
                placeholder="Pesquisar organização, empresa ou CNPJ"
                autocomplete="off">
              <label for="ctxPesquisa">Pesquisar organização, empresa ou CNPJ</label>
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="ctxPesquisarDigitando" checked>
              <label class="form-check-label small" for="ctxPesquisarDigitando">
                Pesquisar enquanto digita
              </label>
            </div>

            <div id="ctxPesquisaManual" class="mt-2 d-none text-end">
              <button type="button" class="bto bto-phsa-light btn-sm" onclick="fgbPesquisarContexto()">
                <i class="fas fa-search"></i>&nbsp;Pesquisar
              </button>
            </div>
          </div>

          <div id="ctxResultado" class="table-responsive">
            <div class="text-center py-4">
              <div class="spinner-border text-phsa mb-3" role="status"></div>
              <div>Carregando opções disponíveis...</div>
            </div>
          </div>

          <div class="small text-muted mt-2">
            A pesquisa retorna até 10 registros por vez.
          </div>
        </div>
      `,
      rodape: `
        <button type="button" class="bto bto-phsa-light" data-bs-dismiss="modal">
          <i class="fas fa-times"></i>&nbsp;Cancelar
        </button>

        <button type="button" id="btnConfirmarContexto" class="bto bto-phsa" disabled onclick="fgbConfirmarContexto()">
          <i class="fas fa-check"></i>&nbsp;Confirmar
        </button>
      `
    });

    setTimeout(function () {
      $('#ctxPesquisa').focus();
      fgbPesquisarContexto();
    }, 300);

    $(document).off('input', '#ctxPesquisa').on('input', '#ctxPesquisa', function () {
      if (!$('#ctxPesquisarDigitando').is(':checked')) {
        return;
      }

      clearTimeout(contextoTimerPesquisa);

      contextoTimerPesquisa = setTimeout(function () {
        fgbPesquisarContexto();
      }, 350);
    });

    $(document).off('change', '#ctxPesquisarDigitando').on('change', '#ctxPesquisarDigitando', function () {
      $('#ctxPesquisaManual').toggleClass('d-none', $(this).is(':checked'));
    });

    $(document).off('click', '.ctxLinhaResultado').on('click', '.ctxLinhaResultado', function () {
      $('.ctxLinhaResultado').removeClass('table-active text-phsa fw-bold');
      $('.ctxLinhaResultado .ctxIconSelecionado')
        .removeClass('fas fa-check-circle text-phsa')
        .addClass('far fa-circle text-muted');

      $(this).addClass('table-active text-phsa fw-bold');

      $(this).find('.ctxIconSelecionado')
        .removeClass('far fa-circle text-muted')
        .addClass('fas fa-check-circle text-phsa');

      contextoSelecionado = {
        org_id: $(this).data('org'),
        emp_id: $(this).data('emp')
      };

      $('#btnConfirmarContexto').prop('disabled', false);
    });
  }

  function fgbPesquisarContexto() {
    contextoSelecionado = null;
    $('#btnConfirmarContexto').prop('disabled', true);

    $('#ctxResultado').html(`
      <div class="text-center py-4">
        <div class="spinner-border text-phsa mb-3" role="status"></div>
        <div>Pesquisando...</div>
      </div>
    `);

    $.ajax({
      url: `${CONF_URL}/contexto/listar`,
      type: 'post',
      dataType: 'json',
      data: {
        conteudo: $('#ctxPesquisa').val() || ''
      },
      success: function (response) {
        if (!response.status) {
          $('#ctxResultado').html(`
            <div class="alert alert-warning mb-0">
              ${response.text || 'Não foi possível carregar os contextos disponíveis.'}
            </div>
          `);
          return;
        }

        fgbRenderizarContextos(response.data || []);
      },
      error: function () {
        $('#ctxResultado').html(`
          <div class="alert alert-danger mb-0">
            Erro ao pesquisar organizações e empresas disponíveis.
          </div>
        `);
      }
    });
  }

  function fgbRenderizarContextos(data) {
    contextoSelecionado = null;
    $('#btnConfirmarContexto').prop('disabled', true);

    if (!data.length) {
      $('#ctxResultado').html(`
        <div class="alert alert-info mb-0">
          Nenhuma organização/empresa encontrada para a pesquisa informada.
        </div>
      `);
      return;
    }

    let html = `
      <table class="table table-sm table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 42px;"></th>
            <th>Organização</th>
            <th>Empresa</th>
          </tr>
        </thead>
        <tbody>
    `;

    data.forEach((item) => {
      const orgId = item.org_id || 0;
      const empId = item.emp_id || 0;

      const orgNome = item.org_nomefantasia || item.org_razaosocial || 'Organização';
      const orgCnpj = item.org_cnpjcpf_formatado || '-';
      const orgLogo = item.org_logo_url || `${CONF_URL}/storage/global/semimagem.jpg`;

      const empNome = item.emp_nomefantasia || item.emp_razaosocial || 'Empresa';
      const empCnpj = item.emp_cnpjcpf_formatado || '-';
      const empLogo = item.emp_logo_url || `${CONF_URL}/storage/global/semimagem.jpg`;

      html += `
        <tr
          class="ctxLinhaResultado"
          data-org="${orgId}"
          data-emp="${empId}"
          title="ID: ${empId}"
          style="cursor: pointer;"
        >
          <td class="text-center">
            <i class="far fa-circle text-muted ctxIconSelecionado"></i>
          </td>

          <td>
            <div class="d-flex align-items-center gap-2">
              <img
                src="${fgbEscapeHtml(orgLogo)}"
                alt="${fgbEscapeHtml(orgNome)}"
                class="rounded border bg-white"
                style="width: 34px; height: 34px; object-fit: contain;"
              >

              <div>
                <div class="fw-bold">${fgbEscapeHtml(orgNome)}</div>
                <div class="small text-muted">${fgbEscapeHtml(orgCnpj)}</div>
              </div>
            </div>
          </td>

          <td>
            <div class="d-flex align-items-center gap-2">
              <img
                src="${fgbEscapeHtml(empLogo)}"
                alt="${fgbEscapeHtml(empNome)}"
                class="rounded border bg-white"
                style="width: 34px; height: 34px; object-fit: contain;"
              >

              <div>
                <div class="fw-bold">${fgbEscapeHtml(empNome)}</div>
                <div class="small text-muted">${fgbEscapeHtml(empCnpj)}</div>
              </div>
            </div>
          </td>
        </tr>
      `;
    });

    html += `
        </tbody>
      </table>
    `;

    $('#ctxResultado').html(html);
  }

  function fgbConfirmarContexto() {
    if (!contextoSelecionado || !contextoSelecionado.org_id || !contextoSelecionado.emp_id) {
      swToastWarning('Selecione uma organização/empresa para continuar.');
      return;
    }

    fgbTrocarContexto(contextoModalId, contextoSelecionado.org_id, contextoSelecionado.emp_id);
  }

  function fgbTrocarContexto(modalId, orgId, empId) {
    $('#btnConfirmarContexto')
      .prop('disabled', true)
      .html('<i class="fas fa-spinner fa-spin"></i>&nbsp;Confirmando');

    $.ajax({
      url: `${CONF_URL}/contexto/trocar`,
      type: 'post',
      dataType: 'json',
      data: {
        org_id: orgId,
        emp_id: empId
      },
      success: function (response) {
        swResponse(response);

        if (!response.status) {
          $('#btnConfirmarContexto')
            .prop('disabled', false)
            .html('<i class="fas fa-check"></i>&nbsp;Confirmar');

          return;
        }

        setTimeout(function () {
          window.location.reload();
        }, 700);
      },
      error: function () {
        swToastError('Erro ao trocar contexto.');

        $('#btnConfirmarContexto')
          .prop('disabled', false)
          .html('<i class="fas fa-check"></i>&nbsp;Confirmar');
      }
    });
  }

  function fgbEscapeHtml(valor) {
    return String(valor ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
