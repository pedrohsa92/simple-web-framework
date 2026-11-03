# Simple Web 2.0

## Visão geral

O Simple Web 2.0 é uma base modular em PHP para aplicações web que precisam de
uma estrutura organizada sem depender de um grande framework externo. O projeto
combina um núcleo pequeno, um roteador explícito, modelos com escopos de dados,
controllers orientados a fluxo e uma camada de apresentação baseada em templates.

O objetivo desta distribuição é oferecer uma fundação clara para novos sistemas:
as decisões de domínio ficam na aplicação que utiliza a base, enquanto o núcleo
concentra o ciclo de requisição, a resolução de rotas, o acesso a dados, a
autenticação e os componentes reutilizáveis.

## Princípios

- Separar infraestrutura, regras de negócio e apresentação.
- Manter o fluxo de uma requisição legível do ponto de entrada até a resposta.
- Usar módulos independentes, que possam ser adicionados ou removidos sem
  reescrever o núcleo.
- Centralizar convenções repetidas em componentes simples e extensíveis.
- Preferir configuração explícita a comportamento implícito.
- Permitir que cada aplicação defina seus próprios modelos, permissões e regras.

## Ciclo de uma requisição

O arquivo index.php é o ponto de entrada público. O fluxo principal é:

~~~
HTTP request
    │
    ▼
index.php
    │  carrega ambiente, constantes e dependências
    ▼
SimpleWeb
    │  prepara sessão, banco, usuário e contexto
    ▼
SimpleRouter
    │  encontra o primeiro grupo de rota compatível
    ▼
Controller
    │  valida entrada e coordena o caso de uso
    ├──────────────► Model / Service
    │                    │
    │                    ▼
    └──────────────► View / resposta HTTP
~~~

As rotas são declaradas no próprio index.php. Cada grupo pode informar prefixo,
middleware, controller padrão e uma rota pública ou autenticada. A ordem de
declaração é importante: o roteador avalia os grupos de cima para baixo e
utiliza a primeira correspondência válida.

## Organização do código

~~~
.
├── index.php                 # bootstrap e mapa de rotas
├── source/
│   ├── Core/                 # núcleo da aplicação
│   ├── Controller/           # coordenação dos casos de uso
│   ├── Model/               # persistência e consultas
│   ├── Service/              # serviços transversais
│   └── Web/                  # templates e respostas de apresentação
├── framework/                # dependências distribuídas com a base
├── project/
│   ├── config/               # configuração da aplicação
│   ├── docs/                 # documentação técnica
│   └── script/               # schema e dados iniciais
├── .env.example              # variáveis de ambiente esperadas
└── composer.json             # metadados e dependências PHP
~~~

Os diretórios Controller, Model, Service e Web representam a aplicação que será
construída sobre a base. O diretório Core fornece os mecanismos compartilhados,
mas não define um domínio comercial específico.

## Núcleo

### SimpleWeb

source/Core/SimpleWeb.php prepara o contexto da aplicação, instancia os
componentes principais, administra a sessão e oferece métodos comuns para
autenticação, autorização, redirecionamento, mensagens e resposta.

### SimpleRouter

source/Core/SimpleRouter.php recebe o caminho atual, compara os grupos
declarados e encaminha a execução para o controller correspondente. O desenho
é deliberadamente direto: novas rotas são adicionadas no mapa de rotas, sem
registradores mágicos ou geração automática de arquivos.

### SimpleModel

source/Core/SimpleModel.php concentra operações comuns de persistência,
paginação, filtros, ordenação, transações e conversão de resultados. Os
modelos específicos informam sua tabela, chave primária e regras de escopo.

Os escopos permitem restringir os dados conforme o contexto da aplicação,
por exemplo:

- nenhum: consulta sem filtro adicional;
- organizacao: dados pertencentes a uma organização;
- empresa: dados da unidade operacional atual;
- cliente: dados associados ao cliente autenticado;
- hierarquico: dados do contexto atual e de níveis subordinados;
- manual: o modelo monta explicitamente sua própria condição.

Esse mecanismo mantém as consultas consistentes e reduz a duplicação de
condições de segurança entre controllers.

## Controllers, services e views

Controllers devem ser pequenos e coordenar um fluxo: ler a entrada, validar os
dados, chamar o modelo ou serviço adequado e escolher a resposta. Regras que
precisam ser usadas por mais de um controller devem ficar em Service.

As views em source/Web recebem dados preparados e cuidam apenas da apresentação.
A aplicação pode manter templates para áreas públicas, autenticadas,
administrativas e respostas parciais. A convenção de nomes e a separação por
contexto tornam mais simples localizar um fluxo completo.

## Sessão, autenticação e autorização

O núcleo fornece o contexto de sessão e os pontos de integração usados pela
aplicação. A autenticação identifica o usuário atual; os models e services
podem consultar o contexto organizacional e aplicar os escopos de dados.

As permissões são tratadas por chaves de recurso e ação. Um módulo pode
declarar seus próprios recursos sem alterar o roteador ou o núcleo. A aplicação
deve manter a lista de permissões coerente com suas rotas e exibir apenas as
ações autorizadas.

## Configuração

O arquivo .env.example documenta as principais variáveis esperadas:

- ambiente de execução e modo de depuração;
- conexão com o banco de dados;
- prefixo das tabelas;
- URL base e diretórios de armazenamento;
- parâmetros de e-mail e integrações opcionais.

Os arquivos em project/config transformam essas variáveis em constantes e
configurações utilizadas pelo bootstrap. Segredos reais não devem ser
versionados.

## Banco de dados

O schema inicial e os dados mínimos de suporte estão reunidos em
project/script/simpleweb.sql. O script deve ser executado em uma base vazia
antes da primeira execução da aplicação.

O projeto utiliza um prefixo configurável para tabelas. Isso permite instalar
mais de uma aplicação ou contexto no mesmo banco sem alterar os nomes usados
pelos models.

## Como criar um módulo

1. Defina o caso de uso e os dados necessários.
2. Crie um model derivado de SimpleModel, informando tabela e escopo.
3. Crie um service quando houver regra compartilhada ou uma operação composta.
4. Implemente um controller para coordenar as ações.
5. Adicione as views no contexto correspondente em source/Web.
6. Registre o grupo de rotas em index.php.
7. Declare permissões e limites somente se o módulo realmente precisar deles.
8. Atualize o schema e a documentação quando o módulo criar persistência nova.

Essa sequência mantém a infraestrutura independente do domínio e facilita
retirar ou substituir um módulo sem comprometer o restante do sistema.

## Extensão e manutenção

O framework não tenta esconder a aplicação atrás de convenções difíceis de
rastrear. As extensões recomendadas são:

- novos grupos de rotas;
- novos models com os escopos necessários;
- services para integrações e regras transversais;
- templates reutilizáveis;
- adaptadores de armazenamento e notificações;
- componentes de autorização específicos da aplicação.

Antes de alterar o núcleo, verifique se a necessidade pertence ao domínio da
aplicação. A maior parte das evoluções deve ser implementada em módulos para
preservar a estabilidade da base compartilhada.

## Referência rápida

| Necessidade | Local principal |
| --- | --- |
| Adicionar uma URL | index.php |
| Alterar o ciclo de inicialização | source/Core/SimpleWeb.php |
| Alterar a resolução de rotas | source/Core/SimpleRouter.php |
| Criar uma consulta persistente | source/Model/ |
| Compartilhar uma regra | source/Service/ |
| Criar uma tela | source/Web/ |
| Alterar tabelas | project/script/simpleweb.sql |
| Documentar uma decisão | project/docs/ |

O Simple Web 2.0 é uma base de partida: cada aplicação deve manter seu domínio,
suas decisões de negócio e seus dados fora do núcleo compartilhado.
