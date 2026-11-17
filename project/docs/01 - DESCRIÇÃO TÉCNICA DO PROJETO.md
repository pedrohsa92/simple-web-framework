# Descrição técnica do projeto

**Simple Web — framework SaaS da PHSA Tecnologia**

> A base que não decide por você.

O **Simple Web** é o framework pessoal de Pedro HSA (PHSA Tecnologia) para construir sistemas SaaS multiempresa em PHP. Ele nasceu da repetição: todo projeto precisava, antes de chegar à própria regra, de login, organizações, empresas, usuários, permissões, planos, cobrança e financeiro. O Simple Web reúne essa base comum, amadurecida em produtos reais construídos sobre ela, e deixa a regra de negócio livre para cada produto.

Este repositório é a **base em funcionamento básico, para demonstração**. Os módulos funcionam, os planos são exemplos (permitem testar Contrate, cadastro e login), e **nenhuma regra de negócio de produto está aplicada**. Produtos como o Prestivo nascem de uma cópia desta base, com prefixo, identidade e domínio próprios.

As convenções de código e de interface ficam em `AGENTS.md`, na raiz.

---

## Regra central

- O Simple Web define a base técnica e os módulos comuns.
- O produto construído sobre ele define a experiência e a regra de negócio.
- A organização define a estrutura comercial e administrativa.
- A empresa define a operação.
- O contexto define onde a operação administrativa acontece.
- O nível de acesso define o alcance do usuário; as permissões definem as ações.
- O plano contratado define limites e módulos liberados.
- O ambiente BCC ou BCD define isolamento e infraestrutura.

## Decisão de arquitetura: sem escopo Global

Cadastros básicos e registros operacionais não usam escopo Global. Todo cadastro com escopo hierárquico obedece a uma destas combinações:

| Escopo | idorganizacao | idempresa | Alcance |
|---|---|---|---|
| Organização | `> 0` | `= 0` | Compartilhado pelas empresas da organização |
| Empresa | `> 0` | `> 0` | Restrito à empresa, com vínculo à organização validado |

A combinação dos identificadores é a fonte efetiva do escopo e é sempre validada no servidor. Configurações da plataforma ficam no escopo **Projeto**, que é a administração do SaaS e não um cadastro Global.

---

## 1. Proposta

- MVC próprio e previsível, com pouca abstração;
- código simples de entender, manter e diagnosticar;
- componentes reutilizáveis entre projetos;
- conexão, sessão, resposta, validação, upload e roteamento padronizados;
- contexto multiempresa, permissões e planos desde o primeiro dia;
- liberdade total para a regra de negócio de cada produto.

**O Simple Web resolve o que é comum:** boot, configuração, ambiente, banco, sessão, autenticação, rotas, respostas, validações, upload, escopos, logs, templates e helpers, além dos módulos administrativos e comerciais básicos de um SaaS.

**O produto resolve o que é próprio:** suas entidades, fluxos, telas de domínio, integrações específicas e regras comerciais.

## 2. Stack

| Item | Uso |
|---|---|
| PHP `^8.0` | Linguagem (namespace `Source\` via PSR-4) |
| MySQL / PDO | Banco (utf8mb4, InnoDB) |
| League Plates | Templates (`source/Web`) |
| Bootstrap 5, Font Awesome (gratuito), SweetAlert | Interface |
| PHPMailer | E-mail |
| vlucas/phpdotenv | Configuração por `.env` |
| matthiasmullie/minify | Minificação de assets |

## 3. BCC e BCD

- **BCC (Banco Compartilhado Comercial):** várias organizações no mesmo código, banco e storage, com isolamento lógico por organização e empresa. É o modo padrão.
- **BCD (Banco Cliente Dedicado):** mesma aplicação e mesma regra, com domínio, banco, storage e identidade próprios. `CONF_ORG_ID` identifica a organização fixa do ambiente.

BCC e BCD não são produtos diferentes. A diferença está na configuração (`prj_tipo`, `org_ambiente`, `org_url_dedicada`, `org_banco_dedicado`, `pla_ambiente`, `plc_ambiente`), nunca em uma segunda implementação. **A infraestrutura decide o ambiente; o módulo executa o domínio.** Não espalhe condicionais de ambiente por controllers, models e views.

## 4. Camadas conceituais

| Camada | Descrição |
|---|---|
| **Projeto** | A plataforma: identidade, configurações centrais, catálogo de planos, gateways e conteúdo da central de ajuda. |
| **Organização** | Grupo administrativo e comercial que contrata o SaaS. Possui uma ou mais empresas e o plano contratado. |
| **Empresa** | Unidade operacional, com usuários, financeiro, configurações e página pública próprias. |
| **Usuário** | Identidade administrativa de acesso, vinculada a organizações, empresas e níveis de acesso. |
| **Contexto** | Organização e empresa em que a sessão administrativa atua. Referência de todos os módulos internos. |

## 5. Boot da aplicação

O `index.php` é o ponto de entrada:

1. inicia buffer e sessão;
2. carrega o `.env` e define as configurações atuais (dev ou produção);
3. valida configurações obrigatórias (`SimpleValidator`);
4. cria a conexão PDO compartilhada (`SimpleConnect`);
5. carrega o Projeto e identifica BCC ou BCD (`SimpleWeb`);
6. valida a organização fixa em BCD;
7. atualiza o usuário autenticado e carrega organização, empresa e plano do contexto;
8. registra as rotas (`SimpleRouter`) e despacha.

## 6. Configuração e ambientes

| Grupo | Chaves |
|---|---|
| Banco (produção) | `CONF_DB_DRIVE`, `CONF_DB_HOST`, `CONF_DB_PORT`, `CONF_DB_NAME`, `CONF_DB_USER`, `CONF_DB_KEY` |
| Banco (desenvolvimento) | as mesmas com sufixo `_DEV` |
| URLs | `CONF_URL_LOCALHOST`, `CONF_URL_PRODUCAO` |
| Prefixos | `DB_PREFIX` (`spw`, tabelas `spw_*`), `SS_PREFIX` (`SPW`) |
| BCD | `CONF_ORG_ID` (opcional) |

O `.env` não é versionado; `.env.example` traz as chaves sem segredos. Credenciais, tokens de gateway e senhas nunca vão para documentação ou commits.

## 7. Prefixos e reutilização

O model declara o nome lógico e o `SimpleModel` resolve o físico:

```php
protected string $tabela = 'fornecedor';
// acesso físico: DB_PREFIX . '_fornecedor'  →  spw_fornecedor
```

Nenhum PHP fixa prefixo. A sessão guarda apenas o mínimo seguro (identidade e referências de contexto), sem senhas, códigos temporários ou objetos grandes.

## 8. Estrutura

```text
index.php                 boot e rotas
source/
  Core/                   infraestrutura
  Controller/sistema/     área administrativa
  Controller/site/        site público, Contrate e webhooks
  Model/                  dados e segurança final
  Service/                processos entre módulos
  Engine/Helpers.php      helpers globais
  Web/                    views: template/, sistema/, site/, assets/
storage/                  arquivos físicos
project/
  docs/                   esta documentação
  identidade/             marca (SVG, PNG, manual em PDF)
  script/                 create, insert e rodar_scripts.bat
```

| Core | Responsabilidade |
|---|---|
| `SimpleConnect` | Conexão PDO singleton, utf8mb4, exceções e transações |
| `SimpleController` | Usuário, contexto, página, assets, breadcrumb, permissões e render |
| `SimpleModel` | Persistência, filtros, escopos e logs automáticos |
| `SimpleResponse` | Retorno padronizado (sucesso, erro, aviso, JSON) |
| `SimpleRouter` | Grupos, métodos HTTP, rotas nomeadas e parâmetros |
| `SimpleSession` | Leitura e escrita controlada da sessão |
| `SimpleUpload` | Validação, armazenamento físico e metadados de arquivos |
| `SimpleValidator` | Validação de configurações e entradas |
| `SimpleWeb` | Boot, projeto, usuário, contexto, plano e ambiente |

Services atuais: `LimitePlanoService`, `EmailService`, `PaletaCoresService`, `OrdenacaoTexto`.

## 9. SimpleModel e escopo

| Escopo | Uso |
|---|---|
| `nenhum` | Projeto, catálogo de planos e registros da plataforma |
| `organizacao` | Registros da organização sem empresa específica |
| `empresa` | Registros da organização e da empresa ativa |
| `hierarquico` | Alcance entre Projeto, Organização e Empresa (cadastros básicos) |
| `manual` | Models com decisão própria de acesso e filtro |

O escopo automático não substitui a validação explícita de relações. Toda escrita valida contexto, escopo, permissão, plano, status e relacionamentos. **O Model é a camada final de segurança.**

Logs: cada tabela tem a coluna `{prefixo}_log`, alimentada pelo `SimpleModel` (`$usarLog`, `$modoLog`, `$limiteLog`) e exibida na aba Histórico.

## 10. Sessão, autorização e permissões

Nível de acesso define alcance (Projeto, Organização, Empresa). Permissão define ação, no formato `recurso.acao` (`usuario.visualizar`, `fornecedor.criar`, `titulo.confirmar`, `contexto.trocar`...).

```text
ACESSO EFETIVO = CONTRATAÇÃO VÁLIDA + MÓDULO LIBERADO + PERMISSÃO DO USUÁRIO + CONTEXTO VÁLIDO
```

`visualizar` dá acesso ao cadastro em modo leitura; gravação exige `criar` ou `atualizar` (detalhes em `AGENTS.md`).

## 11. Planos e contratações

**Plano** é o catálogo comercial; **plano contratado** é o contrato vigente da organização em um período, sem sobreposição. Os parâmetros ficam em JSON por módulo (`pla_parametro`), convertidos pelo `PlanoContratadoModel` e verificados por `LimitePlanoService::verificar(recurso)`. Zero significa ilimitado quando previsto.

Fluxo de demonstração: a landing lista os planos públicos → `contrate/{pla_id}` coleta organização, empresa e responsável → cria organização, empresa, usuário e contratação → pagamento pelo gateway configurado (Pagar.me, com webhook em `webhook/pagarme`; models também para Mercado Pago e InfinitePay) → login.

| Situação | Comportamento |
|---|---|
| Aguardando pagamento | Login permitido; acesso limitado à regularização |
| Teste vigente | Módulos do plano de teste, com limites |
| Plano ativo | Módulos e limites conforme o plano |
| Expirado ou cancelado | Acesso restrito à regularização; histórico preservado |

## 12. Módulos da base

| Módulo | Conteúdo |
|---|---|
| Site público | Landing do Simple Web (sobre Pedro, PHSA Tecnologia e o propósito), planos de demonstração, Contrate e página pública por empresa (`/{slug}`) |
| Dashboard | Indicadores e assistente inicial de cadastros |
| Projeto | Identidade da plataforma, planos, gateways, templates de e-mail |
| Organização | Dados, cadastros básicos, plano contratado e regularização |
| Empresa | Dados, configurações, PWA e página pública |
| Usuários e acesso | Usuários, níveis de acesso, permissões e troca de contexto |
| Cadastro básico | Estrutura genérica para tabelas simples (sexo, estado civil, naturalidade, natureza jurídica) |
| Financeiro | Conta financeira, forma e condição de pagamento, plano de contas, centro de custo, títulos, lançamentos, transferências, fornecedor, dashboard e relatórios |
| Componentes polimórficos | Endereço, contato e arquivo, ligados por origem e id de origem |
| Central de ajuda | Conteúdos e categorias de conhecimento com registro de leitura |
| E-mail | Templates e registro de envios |

**Pendência conhecida:** `MensagemController` e `RelatorioController` (com views em `source/Web/sistema/mensagem` e `source/Web/sistema/relatorio`) existem, mas não têm rotas registradas no `index.php`.

## 13. Storage e upload

Arquivos não vão para o banco (nem base64, nem blob). O `SimpleUpload` valida tipo, MIME e tamanho, monta o diretório pelo contexto, salva o arquivo, devolve metadados e move versões antigas para a lixeira. Arquivos de `storage/global` são protegidos.

```text
storage/{org_id}_organizacao/{emp_id}_empresa/{modulo}/{id_origem}/
```

O caminho físico nunca é regra de autorização.

## 14. Telas e interface

- Listagens usam `sistema::listBase`; cadastros usam `sistema::sistema` com abas `phsaScrollNav` e aba Histórico padronizada (`historicoPadrao.php`).
- Área administrativa: tema PHSA (`bto bto-phsa*`, `phsa*`). Site público e login: identidade Simple Web (classes `sw-*`, `site.css`).
- Views não acessam banco nem sessão diretamente.

## 15. Limites operacionais

Somente registros ativos consomem limites; reativação revalida; inativação e downgrade não apagam dados; excesso bloqueia novos cadastros e reativações até a regularização. O backend é a fonte da decisão.

## 16. Banco de dados e scripts

| Arquivo | Conteúdo |
|---|---|
| `create 01 - estrutura do sistema.sql` | Todas as tabelas `spw_*` |
| `insert 01 - base inicial do sistema.sql` | Projeto Simple Web, organização PHSA Tecnologia, empresa Simple Web, usuário administrador, permissões, planos de exemplo, templates e conteúdo-base |
| `rodar_scripts.bat` | Apaga e recria o banco local `simpleweb` com os dois scripts |
| `alter.sql` | Vazio de propósito |

Mudanças vão direto nos scripts `create`/`insert`; o banco local é recriado. Não há migrações incrementais.

## 17. Princípios de desenvolvimento

- Manter o Simple Web leve, claro e reutilizável.
- Não fixar prefixo físico no PHP.
- Não espalhar regra de BCC/BCD pelos módulos nem criar uma segunda aplicação para o BCD.
- Não aplicar regra de negócio de produto na base.
- Usar o contexto como referência operacional; validar escopo, relação, permissão e plano no Model.
- Usar `SimpleResponse` como retorno padrão; `rawQuery` só quando necessário.
- Não duplicar regra em view e JavaScript.
- Priorizar operações idempotentes e módulos habilitáveis e limitáveis pelo plano.
- Evitar complexidade antes da necessidade.

## 18. Regra de ouro

- O Simple Web sustenta.
- O produto decide.
- A organização contrata.
- A empresa opera.
- O plano limita.
- O contexto protege.
- O BCC escala.
- O BCD personaliza.

---

## Controle de versão do documento

| Data | Alteração |
|---|---|
| 17/11/2026 | Reescrita do documento para a base Simple Web (sem regra de negócio de produto), prefixo `spw`, nova identidade e alinhamento com os padrões do Prestivo. |
