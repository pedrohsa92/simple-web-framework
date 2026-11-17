# Instruções do Simple Web para IA

Este arquivo é o **índice de instruções** para qualquer IA (ou pessoa) que vá trabalhar neste repositório. Leia-o inteiro antes de implementar, refatorar ou revisar código. Quando uma convenção nova for validada, registre-a aqui, na seção certa.

## O que é este projeto

O **Simple Web** é o framework SaaS multiempresa da **PHSA Tecnologia** (Pedro HSA), em PHP 8 + MySQL. Lema: **"A base que não decide por você."**

- Este repositório é a **base em funcionamento básico, para demonstração**: organizações, empresas, usuários, permissões, planos de exemplo (com Contrate, cadastro e login funcionando), financeiro básico, fornecedor, mensagens, relatórios, central de ajuda, projeto e página pública da empresa.
- **Não há regra de negócio de produto aplicada.** Não introduza aqui domínio de um produto específico (pet, igreja, serviços etc.). Produtos (ex.: Prestivo) nascem de uma cópia desta base e recebem a própria regra.
- Prefixo de banco `spw` (`DB_PREFIX`), prefixo de sessão `SPW` (`SS_PREFIX`). Nunca fixe prefixo no PHP.

## Mapa da documentação

| Arquivo | Quando consultar |
|---|---|
| `AGENTS.md` (este) | Sempre. Padrões de código, telas, permissões e scripts. |
| `project/docs/01 - DESCRIÇÃO TÉCNICA DO PROJETO.md` | Arquitetura: boot, contexto, escopos, planos, BCC/BCD, módulos da base. |
| `project/identidade/README.md` e `simpleweb-manual-de-marca.pdf` | Cores, fontes, logo e uso da marca. |
| `project/script/` | Estrutura (`create 01`), base inicial (`insert 01`) e `rodar_scripts.bat`. |

Use os padrões e componentes **deste** repositório; não dependa de documentação ou implementação de outros projetos para decidir o comportamento esperado.

## Estrutura e responsabilidades

```text
index.php                 boot + todas as rotas (SimpleRouter)
source/Core/              SimpleConnect, SimpleController, SimpleModel, SimpleResponse,
                          SimpleRouter, SimpleSession, SimpleUpload, SimpleValidator, SimpleWeb
source/Controller/sistema área administrativa (login obrigatório)
source/Controller/site    site público, Contrate, webhooks
source/Model/             dados, validação e segurança final
source/Service/           processos entre módulos (LimitePlanoService, EmailService...)
source/Web/               views (League Plates): template/, sistema/, site/, assets/
storage/                  arquivos físicos (storage/global = imagens da plataforma)
```

- **Controller** valida acesso e orquestra; **Model** consulta, valida e persiste (é a última barreira de segurança); **Service** concentra processos compartilhados; **View** só renderiza.
- Views não fazem SQL, não leem a sessão diretamente e não montam regra de negócio.
- Retorno de operações: sempre `SimpleResponse` (`->json()` nas rotas POST). No front, use `swAguarde`, `swResponse` e `swError`.

## Padrões do projeto

### Princípios gerais

- **Maiúsculas e minúsculas:** o servidor (Linux) diferencia letras maiúsculas de minúsculas, o Windows não. Namespace e nome de pasta precisam ser idênticos: os controllers usam `namespace Source\Controller\sistema;` e `Source\Controller\site;` (minúsculo, como as pastas). O mesmo vale para nomes de classes, arquivos, views e imagens.
- Reutilize componentes, helpers, estilos e fluxos existentes antes de criar variantes locais. Quando vários módulos compartilham um comportamento, prefira uma implementação configurável a duplicar telas e regras.
- Valide permissões, contexto ativo, relacionamentos e escopo no servidor. Uma rota ou ID recebido do navegador nunca é autorização suficiente.
- Use os nomes oficiais do domínio de forma consistente em telas, mensagens, rotas e código. No módulo de Conhecimento, o agrupamento chama-se **Categoria** (nunca "Grupo").
- Escape dados dinâmicos na saída e valide os dados novamente no backend, mesmo com validação no navegador.
- Evite complexidade antes da necessidade. `rawQuery` só para consultas complexas ou relatórios.

### Rotas

- Cada módulo é um grupo em `index.php`: `$router->group('modulo', 'sistema', 'ModuloController', function ($router) { ... }, 'modulo.');`.
- Padrão de rotas de um CRUD (referência: `fornecedor`):

```php
$router->group('fornecedor', 'sistema', 'FornecedorController', function ($router) {
  $router->get('form', 'form', 'formNovo');   // novo cadastro
  $router->get('form/{id}', 'form');          // edição / visualização
  $router->get('list');                       // listagem
  $router->post('pesquisarFornecedor');       // busca da listagem (ajax)
  $router->post('salvarFornecedor');          // criar/atualizar
  $router->post('alterarStatusFornecedor');   // transição de status
}, 'fornecedor.');
```

- Mantenha rotas distintas para listagem/hub, novo cadastro e edição. Não transforme uma única view em fluxos diferentes alternando elementos com JavaScript.

### Página de listagem

- Use o template base: `$this->layout('sistema::listBase', [...])` com `getInfo`, `titulo`, `descricao`, `urlCadastrar`, `urlPesquisar`, `labelPesquisaSimples`, `labelTotal`, `nomeArquivo` e `prefixo`.
- Preencha as seções do template: `listBase_pesquisaPersonalizada` (filtros, ex.: status), `listBase_camposFiltroData`, `listBase_camposOrdenacao` e `listBase_resultado` (tabela).
- O controller `list()` valida `modulo.visualizar`, monta breadcrumb, conta o total no contexto e passa `urlCadastrar` somente se `Helpers::pode('modulo.criar')`.
- Reutilize os recursos globais de tabela, pesquisa, paginação, exportação e impressão. Diferencie modo sintético e analítico quando a tela já adotar essa convenção; campos analíticos (preço, datas) não aparecem no sintético.
- O nome e a ação da linha sempre levam ao cadastro: ícone de lápis "Editar" com `atualizar`, ícone de olho "Visualizar" sem ela. Nunca use "Sem ação" nem texto sem link para um registro visível. Não coloque ações de edição como conteúdo de coluna de dados.
- Preserve cabeçalhos, alinhamento, estados vazios, contadores e responsividade. Em atualizações assíncronas, atualize também contagens e estados relacionados.

### Página de cadastro (CRUD)

- View com `$this->layout('sistema::sistema')`, cabeçalho com **Voltar** (`bto bto-phsa-light`) e abas em `nav.phsaScrollNav`.
- Na criação, apresente só a aba **Cadastro**. Na edição, **Cadastro** + abas complementares (Endereço, Contato, Arquivo quando fizer sentido) + **Histórico** por último.
- O formulário usa `class="needs-validation" novalidate`, `form-floating` e `invalid-feedback`; o botão **Salvar** (`bto bto-phsa`) fica no rodapé da área de cadastro. Botão e Enter passam pelas mesmas validações.
- No controller `form($url)`:
  - registro existente: `validarPermissoes(['modulo.visualizar', 'modulo.atualizar'])`;
  - novo registro: `validarPermissoes(['modulo.visualizar', 'modulo.criar'])` e `LimitePlanoService::verificar('modulo')` (se estourar, renderize `template/sistema/limite`);
  - carregue o registro pelo ID **já limitado ao contexto**; se não existir ou não pertencer ao escopo, renderize `template/sistema/erro` (não exiba nem permita alterar);
  - monte breadcrumb (`Pesquisar x` → `Novo`/assinatura do registro) e as configs dos componentes compartilhados.
- Rotas de gravação exigem `atualizar` (ou `criar` quando `id = 0`); transição de status valida permissão e escopo no backend.
- Componentes compartilhados (endereço, contato, arquivo) recebem `permitir_*` combinado com o `atualizar` do cadastro pai.

### Permissão visualizar x atualizar

- `visualizar` significa entrar no cadastro, não só ver a listagem: quem tem `visualizar` sem `atualizar` abre o formulário, vê as abas e **não grava**. Só crie permissão específica quando uma aba precisar ficar oculta.
- No Prestivo isso é aplicado na view com `fgbSomenteLeitura('#cardX', { aviso: true })` (global.js). **Esse helper ainda não existe no Simple Web**: ao precisar dele, porte-o do Prestivo para `source/Web/assets/js/global.js` (com `.acaoEscrita` e `.fgbManterAtivo`) em vez de criar uma solução local.

### Cadastro básico

- Cadastros simples (sexo, estado civil, naturalidade, natureza jurídica e similares) usam a estrutura genérica `CadastroBasicoModel` + `CadastroBasicoModuloTrait` + views `sistema/cadastrobasico/` (hub, listaModulo, formModulo). Não crie um conjunto completo de arquivos para cada tabela pequena.
- O escopo é Organização (`idorganizacao > 0`, `idempresa = 0`) ou Empresa (ambos `> 0`); não existe cadastro básico Global. A combinação dos IDs é a fonte do escopo, validada no servidor.
- O acesso é autorizado pela permissão do módulo consumidor (`usuario`, `empresa`, `organizacao`...), nunca por `cadastrobasico.*`.

### Formulários e validação

- Todo campo obrigatório é validado no frontend e no backend. Não remova a obrigatoriedade por estado visual, campo bloqueado ou preenchimento automático.
- Campos bloqueados que continuam obrigatórios precisam de validação explícita e persistência confirmada; não habilite o campo silenciosamente.
- Exemplo estabelecido em Títulos: vencimento é obrigatório; na criação repete a data de referência e pode ser alterado por **Editar vencimento**. A validação exige as duas datas.
- Valores somente leitura usam `.phsaFakeInput` (`source/Web/assets/css/global.css`). Não recrie o componente com CSS local.

### Aba Histórico

- Só aparece na edição e mostra dados reais. Reutilize `source/Web/sistema/historicoPadrao.php` passando `$historicoRegistro`, `$historicoPrefixo`, `$historicoId`, `$historicoStatus`, `$historicoStatusClass`, `$historicoChave`, `$historicoLogs`. Confira o caminho relativo do include.
- Duas sessões, nesta ordem: **Identificação** (ID, Chave e Status em fake inputs; chave com `text-phsa` e cópia) e **Log de alterações** (Cadastrado em, Última alteração e tabela Usuário/Ação/Data/Detalhes; usuário com link para `usuario/form/{id}` quando houver permissão).
- Histórico é informativo: sem ações de edição ou de status, sem o título redundante "Histórico e auditoria".
- Os logs ficam na coluna `{prefixo}_log` de cada tabela, gravados pelo `SimpleModel` (`$usarLog`, `$modoLog`, `$limiteLog`).

### Models e escopo

- O model declara `$tabela` (nome lógico), `$prefixo`, `$identificador` e `$escopoContexto` (`nenhum`, `organizacao`, `empresa`, `hierarquico`, `manual`), com `$campoOrganizacao`/`$campoEmpresa` quando aplicável. O `SimpleModel` monta `DB_PREFIX . '_' . $tabela`.
- Toda escrita valida contexto, escopo, permissão, plano, status e relacionamentos. Regras de limite têm fonte única no backend; revalide ao criar e ao reativar; nunca apague registros como efeito de limite.
- Arquivos ficam fora do banco (só a referência é persistida). O caminho físico nunca é regra de autorização.

### Identidade visual, alertas e espaçamento

- Área do sistema: use a paleta e os componentes do tema PHSA dos estilos globais (`bto bto-phsa*`, `phsa*`, `alert alert-light border`). Não use `alert-info`, `text-primary` e equivalentes do Bootstrap sem adaptação.
- Site público e login: identidade Simple Web (Azul Simple `#5BC2F2`, Azul Base `#0E7CC2`, Navy `#0B2540`, Névoa `#D6F0FF`; Space Grotesk nos títulos, Inter na interface). Classes `sw-*` e variáveis `--sw-*` em `site.css`.
- SweetAlert (Swal): mantenha os padrões globais; não crie configuração divergente no módulo.
- Nenhum item de cadastro ganha sublinhado no hover. Use só ícones do Font Awesome gratuito instalado.
- Evite margens utilitárias redundantes; compare espaçamento, quebra de texto e botões com telas equivalentes no mesmo viewport.

### Conteúdo-base

- Scripts-base criam estrutura e dados mínimos de demonstração da própria base (organização PHSA Tecnologia, empresa Simple Web, planos de exemplo, categorias e orientações genéricas da central de ajuda). Não invente conteúdo de domínio de produto.

### Scripts de banco de dados

- O Simple Web não está em produção como produto. Mudança de estrutura vai direto em `project/script/create 01 - estrutura do sistema.sql`; dado-base em `insert 01 - base inicial do sistema.sql`. Não crie scripts `alter`, de migração ou incrementais; o `alter.sql` fica vazio de propósito.
- O banco local é recriado com `project/script/rodar_scripts.bat` (apaga e recria o banco `simpleweb`). A estrutura atual nunca é obstáculo: proponha a melhor modelagem.
- Para alterações pequenas, ou quando pedido, entregue também o `ALTER` correspondente **na conversa** (não em arquivo).
- Use `spw_` nas tabelas dos scripts; inserções idempotentes quando o script puder ser reaplicado.

### Segurança e segredos

- O `.env` não é versionado. Nunca copie senhas, tokens ou chaves para `.env.example`, documentação, commits ou respostas.

## Conferência antes de concluir

- Já existe componente, tela ou fluxo equivalente aqui? Reutilize.
- Confira vocabulário, permissões (visualizar/criar/atualizar), contexto, rotas, estados de criação/edição e a aba Histórico.
- Revise a tela em largura comparável à referência (espaçamentos, quebra de botões, alertas, hover).
- Rode verificações proporcionais (no mínimo `php -l` nos PHP alterados) e informe claramente o que foi e o que não foi validado.
