# Simple Web 2.0

Framework modular em PHP criado por Pedro HSA para construir aplicações web
com uma base simples, previsível e reutilizável.

Este repositório funciona como portfólio técnico e como base de referência para
projetos que precisam de uma arquitetura própria, com poucas abstrações e
controle direto sobre o fluxo da aplicação.

## Proposta

A Simple Web 2.0 reúne os recursos recorrentes de uma aplicação web:

- inicialização e configuração por ambiente;
- conexão PDO compartilhada;
- sessão com prefixo configurável;
- respostas HTML, JSON e mensagens padronizadas;
- roteamento por grupos, métodos HTTP, nomes e parâmetros;
- controllers, models, services e views organizados por responsabilidade;
- persistência com filtros, escopos de contexto e auditoria;
- upload físico com metadados e diretórios organizados;
- autenticação, permissões, planos e contexto operacional;
- biblioteca de componentes de interface mantida localmente.

A framework fornece infraestrutura. Cada aplicação pode acrescentar seus próprios
módulos, regras de negócio, rotas, modelos, telas e scripts de banco sem alterar
o núcleo compartilhado.

## Como a aplicação funciona

```text
Requisição HTTP
    ↓
.htaccess
    ↓
index.php
    ↓
Ambiente → validação → conexão → boot da aplicação
    ↓
Registro das rotas
    ↓
SimpleRouter::dispatch()
    ↓
Controller → Model/Service → View/Response
```

O `index.php` é o ponto de entrada e o composition root da aplicação. Ele inicia
a sessão, carrega o ambiente, valida as configurações, cria a conexão, prepara o
contexto e registra as rotas do projeto consumidor.

## Estrutura

```text
framework/       Bibliotecas front-end locais e manifesto de assets
project/         Documentação e script SQL de referência
source/
  Controller/    Orquestração das requisições
  Core/          Núcleo da Simple Web 2.0
  Engine/        Helpers reutilizáveis
  Model/         Persistência e regras de dados
  Service/       Processos que atravessam módulos
  Web/           Views, templates, CSS e JavaScript
storage/         Arquivos gerados pela aplicação (não versionado)
vendor/          Dependências instaladas pelo Composer (não versionado)
```

## Núcleo

| Componente | Responsabilidade |
| --- | --- |
| `SimpleConnect` | Cria e reutiliza a conexão PDO com MySQL. |
| `SimpleValidator` | Confere constantes e configurações obrigatórias. |
| `SimpleSession` | Encapsula a sessão e aplica `SS_PREFIX`. |
| `SimpleWeb` | Executa o boot do projeto, usuário, contexto e plano. |
| `SimpleRouter` | Registra, nomeia e despacha rotas HTTP. |
| `SimpleController` | Prepara templates, assets, contexto, permissões e páginas. |
| `SimpleModel` | Centraliza consultas, filtros, transações, escopos e logs. |
| `SimpleResponse` | Padroniza resultados de operações e respostas ao navegador. |
| `SimpleUpload` | Valida, salva e organiza arquivos fora do banco. |

## Rotas

As rotas são registradas em grupos:

```php
$router->group('admin', 'sistema', 'DashboardController', function ($router) {
  $router->get('dashboard');
  $router->post('dashboard/indicadores', 'indicadores');
}, 'dashboard.');
```

O grupo define prefixo, módulo, controller e prefixo dos nomes. A ação pode ser
informada explicitamente ou inferida pelo último segmento estático da rota.
Rotas específicas devem ser registradas antes das rotas parametrizadas ou
genéricas, porque o dispatcher utiliza a primeira correspondência encontrada.

## Configuração

Copie `.env.example` para `.env` e preencha as configurações do ambiente local e
de produção. Os principais valores são:

- conexão de banco;
- URLs da aplicação;
- `DB_PREFIX`, prefixo físico das tabelas;
- `SS_PREFIX`, prefixo lógico das sessões;
- `CONF_ORG_ID`, quando o ambiente for dedicado a uma organização.

Segredos, senhas, tokens e o arquivo `.env` não devem ser versionados.

## Banco de referência

O arquivo [project/script/simpleweb.sql](project/script/simpleweb.sql) reúne a
estrutura e a carga inicial da base de referência em um único script. Ele serve
como ponto de partida para desenvolvimento local; aplicações reais podem criar
seus próprios módulos e migrations conforme o domínio evolui.

## Instalação local

```bash
composer install
copy .env.example .env
```

Depois, configure o banco no `.env`, importe `project/script/simpleweb.sql` e
aponte o servidor web para a raiz do projeto. O Apache deve permitir o uso do
`.htaccess` para que as URLs sejam encaminhadas ao `index.php`.

## Princípios

- simplicidade antes de abstração;
- responsabilidades claras entre camadas;
- regras de autorização no servidor;
- escopo validado no model e no contexto ativo;
- arquivos fora do banco;
- módulos independentes e substituíveis;
- configuração explícita e diagnóstico fácil;
- nenhuma dependência de um domínio de negócio específico.

## Autor

Pedro HSA — desenvolvimento da Simple Web 2.0 e das soluções construídas
sobre ela.
