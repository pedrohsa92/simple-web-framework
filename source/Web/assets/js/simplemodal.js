





const SimpleModal = {
  modais: {},
  posicoesRolagem: {},

  focarSemRolar: function (elemento) {
    const alvo = elemento && elemento.jquery ? elemento.get(0) : elemento;

    if (!alvo || typeof alvo.focus !== 'function') return;

    try {
      alvo.focus({ preventScroll: true });
    } catch (erro) {
      alvo.focus();
    }
  },

  criar: function (opcoes = {}) {
    const id = `simplemodal-${Math.random().toString(36).substr(2, 9)}`;
    const modal = $(`
      <div id="${id}" simplemodal="${id}" class="modal fade" data-bs-backdrop="${opcoes.fundo || 'static'}" data-bs-keyboard="${opcoes.teclado || 'false'}" tabindex="-1">
        <div class="modal-dialog ${opcoes.tamanho || ''}">
          <div class="modal-content">
            ${opcoes.header === 'hide' ? '' : `
              <div class="modal-header">
                <h5 class="modal-title">${opcoes.titulo || 'SimpleModal'}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
            `}
            <div class="modal-body" tabindex="0">${opcoes.corpo || 'Conteúdo do modal...'}</div>
            ${opcoes.rodape === 'hide' ? '' : `
              <div class="modal-footer">
                ${opcoes.rodape || '<button type="button" class="btn btn-default" data-bs-dismiss="modal">Fechar</button>'}
              </div>
            `}
          </div>
        </div>
      </div>
    `);

    $('body').append(modal);
    this.modais[id] = modal;

    return id;
  },

  mostrar: function (opcoes = {}, fecharModais = true) {
    const id = this.criar(opcoes);
    this.abrir(id, fecharModais);
    return id;
  },

  abrir: function (id, fecharModais = true) {
    const modal = this.modais[id];
    if (modal) {
      const posicaoRolagem = window.scrollY;
      this.posicoesRolagem[id] = posicaoRolagem;

      if (fecharModais) {
        this.fecharTodos();
      }

      $('body > *').not(modal).attr('aria-hidden', 'true');

      modal.modal('show');

      modal.on('shown.bs.modal', function () {
        const firstFocusableElement = modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').first();
        if (firstFocusableElement.length) {
          SimpleModal.focarSemRolar(firstFocusableElement);
        } else {
          SimpleModal.focarSemRolar(modal.find('.modal-body'));
        }
      });

      modal.on('hidden.bs.modal', function () {
        $('body > *').removeAttr('aria-hidden');

        const restaurarRolagem = function () {
          window.scrollTo(0, SimpleModal.posicoesRolagem[id] ?? posicaoRolagem);
        };

        restaurarRolagem();
        window.requestAnimationFrame(restaurarRolagem);
      });
    } else {
      console.error(`Modal com ID "${id}" não encontrado.`);
    }
  },

  atualizar: function (id, opcoes = {}) {
    const modal = this.modais[id];
    if (modal) {
      if (opcoes.titulo) modal.find('.modal-title').html(opcoes.titulo);
      if (opcoes.corpo) modal.find('.modal-body').html(opcoes.corpo);
      if (opcoes.rodape) modal.find('.modal-footer').html(opcoes.rodape);
    } else {
      console.error(`Modal com ID "${id}" não encontrado.`);
    }
  },

  fechar: function (id, excluir = true) {
    const modal = this.modais[id];
    if (modal) {
      const focusable = $('<div tabindex="0" style="position: absolute; width: 1px; height: 1px; overflow: hidden;"></div>');
      $('body').append(focusable);
      this.focarSemRolar(focusable);
      focusable.remove();

      modal.modal('hide');
      modal.on('hidden.bs.modal', function () {
        if (excluir) {
          modal.remove();
          delete SimpleModal.modais[id];
          delete SimpleModal.posicoesRolagem[id];
          $('body > *').removeAttr('aria-hidden');
        }
      });
    } else {
      console.error(`Modal com ID "${id}" não encontrado.`);
    }
  },

  fecharTodos: function (excluir = true) {
    const focusable = $('<div tabindex="0" style="position: absolute; width: 1px; height: 1px; overflow: hidden;"></div>');
    $('body').append(focusable);
    this.focarSemRolar(focusable);
    focusable.remove();

    Object.entries(this.modais).forEach(([id, modal]) => {
      modal.modal('hide');
      modal.on('hidden.bs.modal', function () {
        if (excluir) {
          modal.remove();
          delete SimpleModal.modais[id];
          delete SimpleModal.posicoesRolagem[id];
          $('body > *').removeAttr('aria-hidden');
        }
      });
    });
  },

  excluir: function (id) {
    const modal = this.modais[id];
    if (modal) {
      modal.modal('hide');
      modal.on('hidden.bs.modal', function () {
        modal.remove();
        delete SimpleModal.modais[id];
        delete SimpleModal.posicoesRolagem[id];
        $('body > *').removeAttr('aria-hidden');
      });
    } else {
      console.error(`Modal com ID "${id}" não encontrado.`);
    }
  },

  excluirTodos: function () {
    Object.values(this.modais).forEach((modal) => {
      modal.modal('hide');
      modal.on('hidden.bs.modal', function () {
        modal.remove();
      });
    });
    this.modais = {};
    this.posicoesRolagem = {};
  },

  destruir: function (id) {
    const modal = this.modais[id];
    if (modal) {
      modal.modal('hide');
      modal.on('hidden.bs.modal', function () {
        modal.remove();
        delete SimpleModal.modais[id];
        delete SimpleModal.posicoesRolagem[id];
        $('body > *').removeAttr('aria-hidden');
      });
    } else {
      console.error(`Modal com ID "${id}" não encontrado.`);
    }
  }
};

window.SimpleModal = SimpleModal;
