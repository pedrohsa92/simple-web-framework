(function () {
  const imagemCache = {};
  let modalId = null;
  let scrollTop = 0;

  function elemento(id) {
    return document.getElementById(id);
  }

  function escopoSelecionado() {
    return $('input[name="levhubCarteirinhaScope"]:checked').val() || 'complete';
  }

  function elementosDoEscopo(escopo) {
    if (escopo === 'cover') return [elemento('carteirinhaCapa')];
    if (escopo === 'back') return [elemento('carteirinhaDeMembro')];
    return [elemento('carteirinhaCapa'), elemento('carteirinhaDeMembro')];
  }

  function copiarEstilosComputados(origem, destino) {
    const estilos = window.getComputedStyle(origem);
    Array.from(estilos).forEach((propriedade) => {
      destino.style.setProperty(
        propriedade,
        estilos.getPropertyValue(propriedade),
        estilos.getPropertyPriority(propriedade)
      );
    });
  }

  function clonarComEstilos(origem) {
    const clone = origem.cloneNode(true);
    const origens = [origem, ...origem.querySelectorAll('*')];
    const clones = [clone, ...clone.querySelectorAll('*')];

    origens.forEach((node, indice) => {
      if (clones[indice]) copiarEstilosComputados(node, clones[indice]);
    });

    const rect = origem.getBoundingClientRect();
    clone.style.width = `${Math.ceil(rect.width)}px`;
    clone.style.height = `${Math.ceil(rect.height)}px`;
    clone.style.minWidth = `${Math.ceil(rect.width)}px`;
    clone.style.minHeight = `${Math.ceil(rect.height)}px`;
    clone.style.maxWidth = 'unset';
    clone.style.maxHeight = 'unset';
    clone.style.margin = '0';
    clone.style.transform = 'none';

    return clone;
  }

  async function renderizarCanvas(origem) {
    if (!origem) throw new Error('Elemento da carteirinha não encontrado.');

    const clone = clonarComEstilos(origem);
    const suporte = document.createElement('div');
    suporte.style.position = 'fixed';
    suporte.style.left = '-99999px';
    suporte.style.top = '0';
    suporte.style.padding = '0';
    suporte.style.margin = '0';
    suporte.style.background = '#fff';
    suporte.appendChild(clone);
    document.body.appendChild(suporte);

    try {
      const imagens = suporte.querySelectorAll('img');
      await Promise.all([...imagens].map((img) => new Promise((resolve) => {
        if (img.complete) {
          resolve();
          return;
        }
        img.crossOrigin = 'anonymous';
        img.onload = img.onerror = () => resolve();
      })));

      const rect = clone.getBoundingClientRect();
      return await html2canvas(clone, {
        backgroundColor: '#fff',
        useCORS: true,
        scale: 3,
        width: Math.ceil(rect.width),
        height: Math.ceil(rect.height)
      });
    } finally {
      suporte.remove();
    }
  }

  async function criarCanvas(escopo) {
    if (typeof html2canvas === 'undefined') {
      throw new Error('A biblioteca de imagem não foi carregada.');
    }

    if (document.fonts && document.fonts.ready) await document.fonts.ready;

    const canvases = await Promise.all(elementosDoEscopo(escopo).filter(Boolean).map(renderizarCanvas));
    if (canvases.length === 1) return canvases[0];

    const gap = Math.max(1, Math.round(canvases[0].width * (2 / 86)));
    const composito = document.createElement('canvas');
    composito.width = Math.max(...canvases.map((canvas) => canvas.width));
    composito.height = canvases.reduce((total, canvas) => total + canvas.height, 0) + gap;

    const contexto = composito.getContext('2d');
    contexto.fillStyle = '#fff';
    contexto.fillRect(0, 0, composito.width, composito.height);

    let posicaoY = 0;
    canvases.forEach((canvas, indice) => {
      contexto.drawImage(canvas, 0, posicaoY);
      posicaoY += canvas.height;
      if (indice < canvases.length - 1) posicaoY += gap;
    });

    return composito;
  }

  function nomeArquivo(extensao) {
    const card = elemento('carteirinhaDeMembro');
    const partesNome = (card?.dataset.carteirinhaNome || 'membro')
      .trim()
      .split(/\s+/)
      .filter(Boolean)
      .slice(0, 2)
      .map((parte) => parte.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9_-]/g, ''));
    const primeiroNome = partesNome[0] || 'membro';
    const segundoNome = partesNome[1] || primeiroNome;
    const membroId = (card?.dataset.carteirinhaMembroId || '0').replace(/[^a-zA-Z0-9_-]/g, '');
    const agora = new Date();
    const pad = (valor) => String(valor).padStart(2, '0');
    const dataHora = `${pad(agora.getDate())}${pad(agora.getMonth() + 1)}${agora.getFullYear()}${pad(agora.getHours())}${pad(agora.getMinutes())}${pad(agora.getSeconds())}`;
    return `carteirinha_${primeiroNome}_${segundoNome}_${membroId || '0'}_${dataHora}.${extensao}`;
  }

  async function gerarImagem(escopo) {
    if (imagemCache[escopo]) return imagemCache[escopo];

    const canvas = await criarCanvas(escopo);
    const blob = await new Promise((resolve, reject) => {
      canvas.toBlob((arquivo) => arquivo ? resolve(arquivo) : reject(new Error('Falha ao gerar a imagem da carteirinha.')), 'image/png');
    });
    const arquivo = new File([blob], nomeArquivo('png'), { type: 'image/png' });
    const asset = { canvas, blob, arquivo, dataUrl: canvas.toDataURL('image/png'), filename: nomeArquivo('png') };
    imagemCache[escopo] = asset;
    return asset;
  }

  async function executarComImagem(escopo, callback, titulo) {
    try {
      swAguarde({ title: titulo, text: 'Aguarde alguns instantes.', timer: null });
      await callback(await gerarImagem(escopo));
    } catch (erro) {
      console.error(erro);
      swToastError(erro?.message || 'Não foi possível concluir esta ação agora.');
    } finally {
      swFechar();
    }
  }

  function corpoModal() {
    return `
      <div class="newapp-carteirinha-modal-label">Selecione o que deseja gerar:</div>
      <div class="newapp-carteirinha-modal-scope" role="radiogroup" aria-label="Escolha a carteirinha">
        <input type="radio" class="btn-check" name="levhubCarteirinhaScope" id="levhubCarteirinhaScopeComplete" value="complete" checked>
        <label class="bto bto-phsa" for="levhubCarteirinhaScopeComplete"><i class="fa-solid fa-id-card"></i><span>Carteirinha completa</span></label>
        <input type="radio" class="btn-check" name="levhubCarteirinhaScope" id="levhubCarteirinhaScopeBack" value="back">
        <label class="bto border-phsa text-phsa" for="levhubCarteirinhaScopeBack"><i class="fa-solid fa-address-card"></i><span>Apenas o verso</span></label>
        <input type="radio" class="btn-check" name="levhubCarteirinhaScope" id="levhubCarteirinhaScopeCover" value="cover">
        <label class="bto border-phsa text-phsa" for="levhubCarteirinhaScopeCover"><i class="fa-solid fa-image"></i><span>Apenas a capa</span></label>
      </div>
      <div class="newapp-carteirinha-modal-actions mt-3">
        <button type="button" class="bto bto-phsa-light" id="levhubCarteirinhaPrint"><i class="fa-solid fa-file-pdf"></i><span>Imprimir</span></button>
        <button type="button" class="bto bto-phsa-light" id="levhubCarteirinhaDownload"><i class="fa-solid fa-image"></i><span>Baixar</span></button>
        <button type="button" class="bto bto-phsa-light" id="levhubCarteirinhaCopy"><i class="fa-solid fa-copy"></i><span>Copiar</span></button>
        <button type="button" class="bto bto-phsa-light" id="levhubCarteirinhaShare"><i class="fa-solid fa-share-nodes"></i><span>Compartilhar</span></button>
      </div>
    `;
  }

  function abrirModal() {
    const card = elemento('carteirinhaDeMembro');
    if (!card) return;

    if (card.dataset.carteirinhaIncomplete === '1') {
      swToastInfo(`A carteirinha está incompleta. Atualize: ${card.dataset.carteirinhaPendentes || 'os dados pendentes'}.`);
      return;
    }

    if (typeof SimpleModal === 'undefined') {
      swToastInfo('O recurso de modal não está disponível agora.');
      return;
    }

    scrollTop = window.scrollY || window.pageYOffset || 0;
    modalId = SimpleModal.mostrar({
      titulo: '<i class="fa-solid fa-id-card"></i>&nbsp;Opções da carteirinha',
      corpo: corpoModal(),
      rodape: 'hide',
      tamanho: 'modal-dialog-centered'
    });

    const modal = SimpleModal.modais[modalId];
    modal.on('hidden.bs.modal', () => window.scrollTo(0, scrollTop));
    modal.find('#levhubCarteirinhaCopy').toggleClass('is-hidden', !(navigator.clipboard && window.ClipboardItem));
    modal.find('#levhubCarteirinhaShare').toggleClass('is-hidden', !(navigator.share && navigator.canShare));
  }

  function fecharModal() {
    if (modalId && typeof SimpleModal !== 'undefined' && SimpleModal.modais[modalId]) SimpleModal.fechar(modalId);
  }

  function imprimir(escopo) {
    if (!window.jspdf || !window.jspdf.jsPDF) {
      swToastError('A biblioteca de PDF não foi carregada.');
      return;
    }

    executarComImagem(escopo, async (asset) => {
      const { jsPDF } = window.jspdf;
      const altura = escopo === 'complete' ? 110 : 54;
      const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: [86, altura], compress: true });
      pdf.addImage(asset.dataUrl, 'PNG', 0, 0, 86, altura, undefined, 'FAST');
      pdf.save(nomeArquivo('pdf'));
    }, 'Gerando PDF...');
  }

  function baixar(escopo) {
    executarComImagem(escopo, async (asset) => {
      const link = document.createElement('a');
      link.href = asset.dataUrl;
      link.download = asset.filename;
      link.click();
    }, 'Gerando imagem...');
  }

  function copiar(escopo) {
    if (!(navigator.clipboard && window.ClipboardItem)) {
      swToastInfo('Copiar imagem não é suportado neste dispositivo.');
      return;
    }

    executarComImagem(escopo, async (asset) => {
      await navigator.clipboard.write([new ClipboardItem({ 'image/png': asset.blob })]);
      swToastSuccess('Imagem copiada!');
    }, 'Copiando imagem...');
  }

  function compartilhar(escopo) {
    if (!(navigator.share && navigator.canShare)) {
      swToastInfo('Compartilhar imagem não é suportado neste dispositivo.');
      return;
    }

    executarComImagem(escopo, async (asset) => {
      const payload = { title: 'Carteirinha digital', text: 'Carteirinha digital de membro', files: [asset.arquivo] };
      if (!navigator.canShare(payload)) throw new Error('Este dispositivo não suporta compartilhar a imagem da carteirinha.');
      try {
        await navigator.share(payload);
        swToastSuccess('Compartilhado!');
      } catch (erro) {
        if (erro?.name !== 'AbortError') throw erro;
      }
    }, 'Preparando compartilhamento...');
  }

  window.limparCacheCarteirinha = () => Object.keys(imagemCache).forEach((chave) => delete imagemCache[chave]);

  $(document).on('click', '#newappCarteirinhaOpenOptions, #carteirinhaDeMembro, #carteirinhaCapa', function () {
    if (!$(this).closest('#usuarioListaCarteirinhaModalBody').length) abrirModal();
  });

  $(document).on('change', 'input[name="levhubCarteirinhaScope"]', function () {
    const grupo = $(this).closest('.newapp-carteirinha-modal-scope');
    grupo.find('.bto')
      .removeClass('bto-phsa')
      .addClass('border-phsa text-phsa');

    grupo.find('input[name="levhubCarteirinhaScope"]:checked + .bto')
      .removeClass('border-phsa text-phsa')
      .addClass('bto-phsa');
  });

  $(document).on('click', '#levhubCarteirinhaPrint', function () { const escopo = escopoSelecionado(); fecharModal(); imprimir(escopo); });
  $(document).on('click', '#levhubCarteirinhaDownload', function () { const escopo = escopoSelecionado(); fecharModal(); baixar(escopo); });
  $(document).on('click', '#levhubCarteirinhaCopy', function () { const escopo = escopoSelecionado(); fecharModal(); copiar(escopo); });
  $(document).on('click', '#levhubCarteirinhaShare', function () { const escopo = escopoSelecionado(); fecharModal(); compartilhar(escopo); });
}());
