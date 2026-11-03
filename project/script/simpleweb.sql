-- Simple Web 2.0 — schema e dados iniciais
-- Execute este arquivo em uma base vazia para preparar a estrutura.

# Script para iniciar quaisquer projeto!
# Certifique-se de ter criado o banco:
-- O banco configurado para desenvolvimento é `simpleweb`.
-- Este script cria a estrutura inicial do simpleweb e pode ser executado novamente
-- enquanto o projeto estiver em desenvolvimento.

SET FOREIGN_KEY_CHECKS = 0;

################################################################
######################### PROJETO ##############################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_projeto`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_projeto`;

    CREATE TABLE IF NOT EXISTS `icv_projeto` (
      `prj_id` INT NOT NULL UNIQUE DEFAULT 1,
      -- Status
        `prj_status` ENUM('Ativo','Inativo','Bloqueado','Manutencao') NOT NULL DEFAULT 'Ativo',
        `prj_statusclass` ENUM('success','danger','dark','warning') NOT NULL DEFAULT 'success',
      -- Identificação
        `prj_nome` VARCHAR(100) NOT NULL DEFAULT 'simpleweb',
        `prj_titulo` VARCHAR(150) NOT NULL DEFAULT 'simpleweb',
        `prj_descricao` VARCHAR(255) NULL,
      -- Documentos legais vigentes
        `prj_termosuso` LONGTEXT NULL,
        `prj_termosusoversao` VARCHAR(100) NULL,
        `prj_avisoprivacidade` LONGTEXT NULL,
        `prj_avisoprivacidadeversao` VARCHAR(100) NULL,
      -- Identidade dos e-mails do projeto
        `prj_email_header` LONGTEXT NULL,
        `prj_email_footer` LONGTEXT NULL,
        `prj_lang` VARCHAR(10) NULL DEFAULT 'pt-BR',
        `prj_author` VARCHAR(150) NULL DEFAULT 'simpleweb - https://simpleweb.example',
        `prj_robots` VARCHAR(100) NULL DEFAULT 'index, follow, noarchive',
        `prj_googletag` VARCHAR(50) NULL,
      -- Ambiente da instalação
        `prj_tipo` ENUM('Compartilhado','Dedicado') NOT NULL DEFAULT 'Compartilhado',
      -- Central / sincronização
        `prj_idorganizacao_central` INT NULL,
        `prj_chave` VARCHAR(255) NULL,
        `prj_dominio` VARCHAR(255) NULL,
        `prj_dtultimasincronizacao` DATETIME NULL,
        `prj_url_central` VARCHAR(255) NULL DEFAULT 'https://simpleweb.example',
      -- Gateway comercial do projeto
        `prj_gateway` ENUM('Pagar.me','MercadoPago','InfinitePay') NOT NULL DEFAULT 'Pagar.me',
        `prj_gateway_sk` VARCHAR(255) NULL,
        `prj_gateway_pk` VARCHAR(255) NULL,
        `prj_gateway_configuracao` JSON NULL,
      -- Visual básico
        `prj_logo` VARCHAR(255) NULL DEFAULT 'storage/global/logo.png',
        `prj_favicon` VARCHAR(255) NULL DEFAULT 'storage/global/favicon.png',
        `prj_logonegativa` VARCHAR(255) NULL DEFAULT 'storage/global/logonegativa.png',
      -- Controle
        `prj_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `prj_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `prj_log` LONGTEXT NULL,
      PRIMARY KEY (`prj_id`),
      INDEX `idx_prj_status` (`prj_status`),
      INDEX `idx_prj_tipo` (`prj_tipo`),
      INDEX `idx_prj_chave` (`prj_chave`),
      INDEX `idx_prj_idorganizacao_central` (`prj_idorganizacao_central`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_palavra_chave`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_palavra_chave`;

    CREATE TABLE IF NOT EXISTS `icv_palavra_chave` (
      `pch_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `pch_grupo` VARCHAR(50) NOT NULL,
      `pch_palavra` VARCHAR(100) NOT NULL,
      `pch_chave` VARCHAR(100) NOT NULL,
      `pch_descricao` VARCHAR(255) NULL,
      `pch_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
      `pch_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      `pch_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `pch_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `pch_log` LONGTEXT NULL,
      PRIMARY KEY (`pch_id`),
      UNIQUE KEY `uk_pch_grupo_palavra` (`pch_grupo`, `pch_palavra`),
      INDEX `idx_pch_grupo` (`pch_grupo`),
      INDEX `idx_pch_palavra` (`pch_palavra`),
      INDEX `idx_pch_chave` (`pch_chave`),
      INDEX `idx_pch_status` (`pch_status`)
    ) ENGINE = InnoDB;

################################################################
######################## ORGANIZAÇÃO ###########################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_organizacao`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_organizacao`;

    CREATE TABLE IF NOT EXISTS `icv_organizacao` (
      `org_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `org_chave` VARCHAR(255) NULL,
      -- Status
        `org_status` ENUM('Ativo','Inativo','Bloqueado','Manutencao') NOT NULL DEFAULT 'Ativo',
        `org_statusclass` ENUM('success','danger','dark','warning') NOT NULL DEFAULT 'success',
        `org_idusuarioresponsavel` INT NOT NULL,
      -- Ambiente comercial / técnico
        `org_ambiente` ENUM('Compartilhado','Dedicado') NOT NULL DEFAULT 'Compartilhado',
        `org_url_dedicada` VARCHAR(255) NULL,
        `org_banco_dedicado` VARCHAR(100) NULL,
        `org_statusambiente` ENUM('Ativo','Inativo','Em migracao','Erro') NOT NULL DEFAULT 'Ativo',
        `org_dtmigracao` DATETIME NULL,
        `org_dtultimasincronizacao` DATETIME NULL,
      -- Dados básicos
        `org_cnpjcpf` VARCHAR(20) NOT NULL,
        `org_razaosocial` VARCHAR(255) NOT NULL,
        `org_nomefantasia` VARCHAR(255) NULL,
        `org_observacao` VARCHAR(255) NULL,
        `org_im` VARCHAR(20) NULL,
        `org_ie` VARCHAR(20) NULL,
        `org_rg` VARCHAR(20) NULL,
        `org_dtnasc` DATE NULL,
        `org_dtnasc_notificacao_wpp` DATE NULL,
        `org_dtnasc_notificacao_email` DATE NULL,
        `org_nomemae` VARCHAR(255) NULL,
        `org_nomepai` VARCHAR(255) NULL,
        `org_idsexo` INT NULL,
        `org_idestadocivil` INT NULL,
        `org_idnaturalidade` INT NULL,
      -- Dados complementares
        `org_idnaturezajuridica` INT NULL,
        `org_googletag` VARCHAR(50) NULL,
        `org_descricao` LONGTEXT NULL,
      -- Visual / preferências / padrões
        `org_favicon` VARCHAR(255) NULL DEFAULT 'storage/global/favicon.png',
        `org_logo` VARCHAR(255) NULL DEFAULT 'storage/global/semperfil.png',
        `org_planodefundo` VARCHAR(255) NULL DEFAULT 'storage/global/planodefundo.jpg',
        `org_sidebar` INT NOT NULL DEFAULT 1,
        `org_home` VARCHAR(50) NULL DEFAULT 'dashboard',
        `org_paletacores` LONGTEXT NULL,
        `org_tema` ENUM('light','dark') NOT NULL DEFAULT 'light',
      -- Página Webapp pública da organização
        `org_urlamigavel` VARCHAR(50) NULL,
        `org_webapp_status` ENUM('Offline','Publica','Privada') NOT NULL DEFAULT 'Publica',
        `org_webapp_conteudo` LONGTEXT NULL,
      -- Configurações
        `org_parametro` LONGTEXT NULL,
        `org_cadastrobasico_preferencia` LONGTEXT NULL,
      -- Controle
        `org_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `org_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `org_log` LONGTEXT NULL,
      PRIMARY KEY (`org_id`),
      INDEX `idx_org_ambiente` (`org_ambiente`),
      INDEX `idx_org_statusambiente` (`org_statusambiente`),
      INDEX `idx_org_status` (`org_status`),
      INDEX `idx_org_idusuarioresponsavel` (`org_idusuarioresponsavel`),
      INDEX `idx_org_cnpjcpf` (`org_cnpjcpf`),
      INDEX `idx_org_razaosocial` (`org_razaosocial`),
      INDEX `idx_org_nomefantasia` (`org_nomefantasia`),
      UNIQUE KEY `uk_org_urlamigavel` (`org_urlamigavel`)
    ) ENGINE = InnoDB;

################################################################
###################### CADASTRO BÁSICO ########################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_estadocivil`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_estadocivil`;

    CREATE TABLE IF NOT EXISTS `icv_estadocivil` (
      `ecv_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `ecv_idorganizacao` INT NOT NULL,
      `ecv_idempresa` INT NOT NULL DEFAULT 0,
      `ecv_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      `ecv_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
      `ecv_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      `ecv_nome` VARCHAR(50) NOT NULL,
      `ecv_padrao` INT NOT NULL DEFAULT 0,
      `ecv_ordem` INT NOT NULL DEFAULT 0,
      `ecv_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `ecv_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `ecv_log` LONGTEXT NULL,
      PRIMARY KEY (`ecv_id`),
      UNIQUE KEY `uk_ecv_org_empresa_nome` (`ecv_idorganizacao`, `ecv_idempresa`, `ecv_nome`),
      INDEX `idx_ecv_idorganizacao` (`ecv_idorganizacao`),
      INDEX `idx_ecv_idempresa` (`ecv_idempresa`),
      INDEX `idx_ecv_tipocadastro` (`ecv_tipocadastro`),
      INDEX `idx_ecv_status` (`ecv_status`),
      INDEX `idx_ecv_statusclass` (`ecv_statusclass`),
      INDEX `idx_ecv_padrao` (`ecv_padrao`),
      INDEX `idx_ecv_ordem` (`ecv_ordem`),
      CONSTRAINT `chk_ecv_escopo` CHECK (
        `ecv_idorganizacao` > 0 AND
        ((`ecv_tipocadastro` = 'Organizacao' AND `ecv_idempresa` = 0)
          OR (`ecv_tipocadastro` = 'Empresa' AND `ecv_idempresa` > 0))
      )
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_sexo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_sexo`;

    CREATE TABLE IF NOT EXISTS `icv_sexo` (
      `sex_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `sex_idorganizacao` INT NOT NULL,
      `sex_idempresa` INT NOT NULL DEFAULT 0,
      `sex_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      `sex_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
      `sex_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      `sex_nome` VARCHAR(50) NOT NULL,
      `sex_padrao` INT NOT NULL DEFAULT 0,
      `sex_ordem` INT NOT NULL DEFAULT 0,
      `sex_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `sex_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `sex_log` LONGTEXT NULL,
      PRIMARY KEY (`sex_id`),
      UNIQUE KEY `uk_sex_org_empresa_nome` (`sex_idorganizacao`, `sex_idempresa`, `sex_nome`),
      INDEX `idx_sex_idorganizacao` (`sex_idorganizacao`),
      INDEX `idx_sex_idempresa` (`sex_idempresa`),
      INDEX `idx_sex_tipocadastro` (`sex_tipocadastro`),
      INDEX `idx_sex_status` (`sex_status`),
      INDEX `idx_sex_statusclass` (`sex_statusclass`),
      INDEX `idx_sex_padrao` (`sex_padrao`),
      INDEX `idx_sex_ordem` (`sex_ordem`),
      CONSTRAINT `chk_sex_escopo` CHECK (
        `sex_idorganizacao` > 0 AND
        ((`sex_tipocadastro` = 'Organizacao' AND `sex_idempresa` = 0)
          OR (`sex_tipocadastro` = 'Empresa' AND `sex_idempresa` > 0))
      )
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_naturalidade`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_naturalidade`;

    CREATE TABLE IF NOT EXISTS `icv_naturalidade` (
      `nat_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `nat_idorganizacao` INT NOT NULL,
      `nat_idempresa` INT NOT NULL DEFAULT 0,
      `nat_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      `nat_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
      `nat_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      `nat_nome` VARCHAR(100) NOT NULL,
      `nat_padrao` INT NOT NULL DEFAULT 0,
      `nat_ordem` INT NOT NULL DEFAULT 0,
      `nat_uf` VARCHAR(2) NULL,
      `nat_pais` VARCHAR(50) NULL DEFAULT 'Brasil',
      `nat_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `nat_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `nat_log` LONGTEXT NULL,
      PRIMARY KEY (`nat_id`),
      UNIQUE KEY `uk_nat_org_empresa_nome_uf_pais` (`nat_idorganizacao`, `nat_idempresa`, `nat_nome`, `nat_uf`, `nat_pais`),
      INDEX `idx_nat_idorganizacao` (`nat_idorganizacao`),
      INDEX `idx_nat_idempresa` (`nat_idempresa`),
      INDEX `idx_nat_tipocadastro` (`nat_tipocadastro`),
      INDEX `idx_nat_status` (`nat_status`),
      INDEX `idx_nat_statusclass` (`nat_statusclass`),
      INDEX `idx_nat_padrao` (`nat_padrao`),
      INDEX `idx_nat_ordem` (`nat_ordem`),
      INDEX `idx_nat_nome` (`nat_nome`),
      INDEX `idx_nat_uf` (`nat_uf`),
      INDEX `idx_nat_pais` (`nat_pais`),
      CONSTRAINT `chk_nat_escopo` CHECK (
        `nat_idorganizacao` > 0 AND
        ((`nat_tipocadastro` = 'Organizacao' AND `nat_idempresa` = 0)
          OR (`nat_tipocadastro` = 'Empresa' AND `nat_idempresa` > 0))
      )
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_naturezajuridica`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_naturezajuridica`;

    CREATE TABLE IF NOT EXISTS `icv_naturezajuridica` (
      `ntj_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `ntj_idorganizacao` INT NOT NULL,
      `ntj_idempresa` INT NOT NULL DEFAULT 0,
      `ntj_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      `ntj_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
      `ntj_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      `ntj_nome` VARCHAR(50) NOT NULL,
      `ntj_padrao` INT NOT NULL DEFAULT 0,
      `ntj_ordem` INT NOT NULL DEFAULT 0,
      `ntj_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `ntj_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `ntj_log` LONGTEXT NULL,
      PRIMARY KEY (`ntj_id`),
      UNIQUE KEY `uk_ntj_org_empresa_nome` (`ntj_idorganizacao`, `ntj_idempresa`, `ntj_nome`),
      INDEX `idx_ntj_idorganizacao` (`ntj_idorganizacao`),
      INDEX `idx_ntj_idempresa` (`ntj_idempresa`),
      INDEX `idx_ntj_tipocadastro` (`ntj_tipocadastro`),
      INDEX `idx_ntj_status` (`ntj_status`),
      INDEX `idx_ntj_statusclass` (`ntj_statusclass`),
      INDEX `idx_ntj_padrao` (`ntj_padrao`),
      INDEX `idx_ntj_ordem` (`ntj_ordem`),
      CONSTRAINT `chk_ntj_escopo` CHECK (
        `ntj_idorganizacao` > 0 AND
        ((`ntj_tipocadastro` = 'Organizacao' AND `ntj_idempresa` = 0)
          OR (`ntj_tipocadastro` = 'Empresa' AND `ntj_idempresa` > 0))
      )
    ) ENGINE = InnoDB;

    ALTER TABLE `icv_organizacao`
      ADD INDEX `idx_org_idnaturezajuridica` (`org_idnaturezajuridica`),
      ADD CONSTRAINT `fk_icv_organizacao_icv_naturezajuridica1`
        FOREIGN KEY (`org_idnaturezajuridica`)
        REFERENCES `icv_naturezajuridica` (`ntj_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION;

################################################################
######################### ESSENCIAIS ###########################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_endereco`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_endereco`;

    CREATE TABLE IF NOT EXISTS `icv_endereco` (
      `end_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Status
        `end_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `end_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `end_principal` INT NOT NULL DEFAULT 1,
        `end_nome` VARCHAR(100) NULL,
        `end_cep` VARCHAR(10) NULL,
        `end_logradouro` VARCHAR(100) NULL,
        `end_numero` VARCHAR(20) NULL,
        `end_complemento` VARCHAR(100) NULL,
        `end_bairro` VARCHAR(100) NULL,
        `end_cidade` VARCHAR(100) NULL,
        `end_estado` VARCHAR(20) NULL,
        `end_uf` VARCHAR(2) NULL,
        `end_pais` VARCHAR(50) NULL,
        `end_referencia` VARCHAR(100) NULL,
        `end_googlemaps` TEXT NULL,
      -- Escopo
        `end_idorganizacao` INT NOT NULL DEFAULT 0,
        `end_idempresa` INT NOT NULL DEFAULT 0,
      -- Referência
        `end_idorigem` INT NOT NULL,
        `end_origem` VARCHAR(50) NOT NULL,
      -- Controle
        `end_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `end_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `end_log` LONGTEXT NULL,
      PRIMARY KEY (`end_id`),
      INDEX `idx_end_status` (`end_status`),
      INDEX `idx_end_cep` (`end_cep`),
      INDEX `idx_end_idorganizacao` (`end_idorganizacao`),
      INDEX `idx_end_idempresa` (`end_idempresa`),
      INDEX `idx_end_idorigem` (`end_idorigem`),
      INDEX `idx_end_origem` (`end_origem`),
      INDEX `idx_end_origem_id` (`end_origem`, `end_idorigem`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_arquivo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_arquivo`;

    CREATE TABLE IF NOT EXISTS `icv_arquivo` (
      `arq_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Status
        `arq_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `arq_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `arq_nome` VARCHAR(255) NULL,
        `arq_nomeoriginal` VARCHAR(255) NULL,
        `arq_descricao` VARCHAR(255) NULL,
        `arq_tipo` VARCHAR(50) NULL,
        `arq_mime` VARCHAR(100) NULL,
        `arq_extensao` VARCHAR(20) NULL,
        `arq_tamanho_bytes` BIGINT NOT NULL DEFAULT 0,
        `arq_tamanho` VARCHAR(20) NULL,
        `arq_medida` VARCHAR(10) NULL,
      -- Imagem / mídia
        `arq_largura` INT NULL,
        `arq_altura` INT NULL,
        `arq_duracao` DECIMAL(10,2) NULL,
      -- Armazenamento
        `arq_storage` VARCHAR(255) NULL,
        `arq_diretorio` VARCHAR(255) NULL,
        `arq_nomearquivo` VARCHAR(255) NULL,
        `arq_caminho` VARCHAR(500) NOT NULL,
        `arq_hash` VARCHAR(255) NULL,
      -- Escopo
        `arq_idorganizacao` INT NOT NULL DEFAULT 0,
        `arq_idempresa` INT NOT NULL DEFAULT 0,
      -- Referência
        `arq_idorigem` INT NOT NULL,
        `arq_origem` VARCHAR(50) NOT NULL,
      -- Controle
        `arq_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `arq_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `arq_log` LONGTEXT NULL,
      PRIMARY KEY (`arq_id`),
      INDEX `idx_arq_status` (`arq_status`),
      INDEX `idx_arq_nome` (`arq_nome`),
      INDEX `idx_arq_nomeoriginal` (`arq_nomeoriginal`),
      INDEX `idx_arq_tipo` (`arq_tipo`),
      INDEX `idx_arq_mime` (`arq_mime`),
      INDEX `idx_arq_extensao` (`arq_extensao`),
      INDEX `idx_arq_idorganizacao` (`arq_idorganizacao`),
      INDEX `idx_arq_idempresa` (`arq_idempresa`),
      INDEX `idx_arq_idorigem` (`arq_idorigem`),
      INDEX `idx_arq_origem` (`arq_origem`),
      INDEX `idx_arq_origem_id` (`arq_origem`, `arq_idorigem`),
      INDEX `idx_arq_contexto_origem` (`arq_idorganizacao`, `arq_idempresa`, `arq_origem`, `arq_idorigem`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_contato`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_contato`;

    CREATE TABLE IF NOT EXISTS `icv_contato` (
      `ctt_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Status
        `ctt_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `ctt_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `ctt_tipo` ENUM(
          'Celular',
          'Telefone',
          'Email',
          'WhatsApp',
          'Instagram',
          'Facebook',
          'X',
          'LinkedIn',
          'Site',
          'Outro'
        ) NOT NULL DEFAULT 'Outro',
        `ctt_identificacao` VARCHAR(100) NULL,
        `ctt_conteudo` VARCHAR(255) NOT NULL,
        `ctt_observacao` VARCHAR(255) NULL,
      -- Parâmetros
        `ctt_exibiremrelatorio` INT NOT NULL DEFAULT 0,
        `ctt_ordem` INT NOT NULL DEFAULT 0,
      -- Escopo
        `ctt_idorganizacao` INT NOT NULL DEFAULT 0,
        `ctt_idempresa` INT NOT NULL DEFAULT 0,
      -- Referência
        `ctt_idorigem` INT NOT NULL,
        `ctt_origem` VARCHAR(50) NOT NULL,
      -- Controle
        `ctt_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `ctt_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `ctt_log` LONGTEXT NULL,
      PRIMARY KEY (`ctt_id`),
      INDEX `idx_ctt_status` (`ctt_status`),
      INDEX `idx_ctt_tipo` (`ctt_tipo`),
      INDEX `idx_ctt_identificacao` (`ctt_identificacao`),
      INDEX `idx_ctt_conteudo` (`ctt_conteudo`),
      INDEX `idx_ctt_idorganizacao` (`ctt_idorganizacao`),
      INDEX `idx_ctt_idempresa` (`ctt_idempresa`),
      INDEX `idx_ctt_idorigem` (`ctt_idorigem`),
      INDEX `idx_ctt_origem` (`ctt_origem`),
      INDEX `idx_ctt_origem_id` (`ctt_origem`, `ctt_idorigem`),
      INDEX `idx_ctt_contexto_origem` (`ctt_idorganizacao`, `ctt_idempresa`, `ctt_origem`, `ctt_idorigem`),
      INDEX `idx_ctt_tipo_conteudo` (`ctt_tipo`, `ctt_conteudo`),
      INDEX `idx_ctt_origem_tipo` (`ctt_origem`, `ctt_tipo`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_empresa`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_empresa`;

  -- -----------------------------------------------------
  -- Table `icv_empresa`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_empresa`;

    CREATE TABLE IF NOT EXISTS `icv_empresa` (
      `emp_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `emp_chave` VARCHAR(255) NULL,
      `emp_idorganizacao` INT NOT NULL,
      `emp_idempresa_pai` INT NULL,
      -- Tipo
        `emp_tipo` ENUM('Matriz','Filial') NOT NULL DEFAULT 'Matriz',
      -- Status
        `emp_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `emp_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
        `emp_status_motivo` TEXT NULL,
      -- Dados básicos
        `emp_cnpjcpf` VARCHAR(20) NOT NULL,
        `emp_razaosocial` VARCHAR(255) NOT NULL,
        `emp_nomefantasia` VARCHAR(255) NULL,
        `emp_observacao` VARCHAR(255) NULL,
        `emp_im` VARCHAR(20) NULL,
        `emp_ie` VARCHAR(20) NULL,
        `emp_rg` VARCHAR(20) NULL,
        `emp_dtnasc` DATE NULL,
        `emp_dtnasc_notificacao_wpp` DATE NULL,
        `emp_dtnasc_notificacao_email` DATE NULL,
        `emp_nomemae` VARCHAR(255) NULL,
        `emp_nomepai` VARCHAR(255) NULL,
        `emp_idsexo` INT NULL,
        `emp_idestadocivil` INT NULL,
        `emp_idnaturalidade` INT NULL,
      -- Dados complementares
        `emp_idnaturezajuridica` INT NULL,
        `emp_googletag` VARCHAR(50) NULL,
        `emp_descricao` LONGTEXT NULL,
      -- Visual / preferências / padrões
        `emp_logo` VARCHAR(255) NULL DEFAULT 'storage/global/semperfil.png',
        `emp_planodefundo` VARCHAR(255) NULL DEFAULT 'storage/global/planodefundo.jpg',
        `emp_frente_carteirinha` VARCHAR(255) NULL DEFAULT 'storage/global/capadacarteirinha.jpg',
        `emp_topo_carteirinha` VARCHAR(255) NULL DEFAULT 'storage/global/topodacarteirinha.jpg',
        `emp_assinatura_carteirinha` VARCHAR(255) NULL,
        `emp_lema` VARCHAR(120) NULL,
        `emp_texto_carteirinha` VARCHAR(120) NULL,
        `emp_texto_carteirinha2` VARCHAR(80) NULL,
        `emp_sidebar` INT NOT NULL DEFAULT 1,
        `emp_home` VARCHAR(50) NULL DEFAULT 'dashboard',
        `emp_paletacores` LONGTEXT NULL,
        `emp_tema` ENUM('light','dark') NOT NULL DEFAULT 'light',
      -- Página Webapp pública da empresa
        `emp_urlamigavel` VARCHAR(50) NULL,
        `emp_webapp_status` ENUM('Offline','Publica','Privada') NOT NULL DEFAULT 'Publica',
        `emp_webapp_conteudo` LONGTEXT NULL,
      -- Configurações
        `emp_parametro` LONGTEXT NULL,
        `emp_cadastrobasico_preferencia` LONGTEXT NULL,
        `emp_configuracao_inicial` TINYINT(1) NOT NULL DEFAULT 0,
      -- Controle
        `emp_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `emp_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `emp_log` LONGTEXT NULL,
      PRIMARY KEY (`emp_id`),
      UNIQUE KEY `uk_emp_chave` (`emp_chave`),
      UNIQUE KEY `uk_emp_org_cnpjcpf` (`emp_idorganizacao`, `emp_cnpjcpf`),
      UNIQUE KEY `uk_emp_urlamigavel` (`emp_urlamigavel`),
      INDEX `idx_emp_idorganizacao` (`emp_idorganizacao`),
      INDEX `idx_emp_idempresa_pai` (`emp_idempresa_pai`),
      INDEX `idx_emp_tipo` (`emp_tipo`),
      INDEX `idx_emp_status` (`emp_status`),
      INDEX `idx_emp_statusclass` (`emp_statusclass`),
      INDEX `idx_emp_cnpjcpf` (`emp_cnpjcpf`),
      INDEX `idx_emp_razaosocial` (`emp_razaosocial`),
      INDEX `idx_emp_nomefantasia` (`emp_nomefantasia`),
      INDEX `idx_emp_im` (`emp_im`),
      INDEX `idx_emp_ie` (`emp_ie`),
      INDEX `idx_emp_rg` (`emp_rg`),
      INDEX `idx_emp_dtnasc` (`emp_dtnasc`),
      INDEX `idx_emp_dtnasc_notificacao_wpp` (`emp_dtnasc_notificacao_wpp`),
      INDEX `idx_emp_dtnasc_notificacao_email` (`emp_dtnasc_notificacao_email`),
      INDEX `idx_emp_idsexo` (`emp_idsexo`),
      INDEX `idx_emp_idestadocivil` (`emp_idestadocivil`),
      INDEX `idx_emp_idnaturalidade` (`emp_idnaturalidade`),
      INDEX `idx_emp_idnaturezajuridica` (`emp_idnaturezajuridica`),
      CONSTRAINT `fk_icv_empresa_icv_organizacao1`
        FOREIGN KEY (`emp_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_empresa_icv_empresa1`
        FOREIGN KEY (`emp_idempresa_pai`)
        REFERENCES `icv_empresa` (`emp_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_empresa_icv_sexo1`
        FOREIGN KEY (`emp_idsexo`)
        REFERENCES `icv_sexo` (`sex_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_empresa_icv_estadocivil1`
        FOREIGN KEY (`emp_idestadocivil`)
        REFERENCES `icv_estadocivil` (`ecv_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_empresa_icv_naturalidade1`
        FOREIGN KEY (`emp_idnaturalidade`)
        REFERENCES `icv_naturalidade` (`nat_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_empresa_icv_naturezajuridica1`
        FOREIGN KEY (`emp_idnaturezajuridica`)
        REFERENCES `icv_naturezajuridica` (`ntj_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_pwa`
  -- Configuração do Progressive Web App da página da empresa
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_pwa`;

    CREATE TABLE IF NOT EXISTS `icv_pwa` (
      `pwa_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `pwa_chave` VARCHAR(255) NULL,
      `pwa_idorganizacao` INT NOT NULL,
      `pwa_idempresa` INT NOT NULL,
      -- Status
        `pwa_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Inativo',
        `pwa_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'danger',
        `pwa_offline_ativo` TINYINT(1) NOT NULL DEFAULT 1,
      -- Manifesto PWA
        `pwa_nome` VARCHAR(100) NOT NULL,
        `pwa_nomecurto` VARCHAR(50) NOT NULL,
        `pwa_descricao` VARCHAR(255) NULL,
        `pwa_idioma` VARCHAR(10) NOT NULL DEFAULT 'pt-BR',
        `pwa_direcao` ENUM('ltr','rtl') NOT NULL DEFAULT 'ltr',
        `pwa_exibicao` ENUM('fullscreen','standalone','minimal-ui','browser') NOT NULL DEFAULT 'standalone',
        `pwa_exibicaoalternativa` JSON NULL,
        `pwa_orientacao` ENUM('any','portrait','landscape') NOT NULL DEFAULT 'portrait',
        `pwa_cor_tema` CHAR(7) NOT NULL DEFAULT '#0d6efd',
        `pwa_cor_fundo` CHAR(7) NOT NULL DEFAULT '#ffffff',
        `pwa_categorias` JSON NULL,
        `pwa_classificacao_iarc` VARCHAR(20) NULL,
        `pwa_aplicativosrelacionados` JSON NULL,
        `pwa_preferiraplicativosrelacionados` TINYINT(1) NOT NULL DEFAULT 0,
        `pwa_atalhos` JSON NULL,
      -- Controle de versão e geração
        `pwa_versao` INT UNSIGNED NOT NULL DEFAULT 1,
        `pwa_nomeversao` VARCHAR(50) NOT NULL DEFAULT 'Produção',
        `pwa_versaomanifesto` INT UNSIGNED NOT NULL DEFAULT 1,
        `pwa_ultimageracao` DATETIME NULL,
      -- Auditoria
        `pwa_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `pwa_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `pwa_log` LONGTEXT NULL,
      PRIMARY KEY (`pwa_id`),
      UNIQUE KEY `uk_pwa_chave` (`pwa_chave`),
      UNIQUE KEY `uk_pwa_idempresa` (`pwa_idempresa`),
      INDEX `idx_pwa_idorganizacao` (`pwa_idorganizacao`),
      INDEX `idx_pwa_status` (`pwa_status`),
      INDEX `idx_pwa_statusclass` (`pwa_statusclass`),
      CONSTRAINT `fk_icv_pwa_icv_organizacao1`
        FOREIGN KEY (`pwa_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_pwa_icv_empresa1`
        FOREIGN KEY (`pwa_idempresa`)
        REFERENCES `icv_empresa` (`emp_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

################################################################
##################### USUÁRIO E PERMISSÕES #####################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_nivelacesso`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_nivelacesso`;

    CREATE TABLE IF NOT EXISTS `icv_nivelacesso` (
      `nva_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `nva_idorganizacao` INT NOT NULL DEFAULT 0,
      `nva_idempresa` INT NOT NULL DEFAULT 0,
      -- Escopo
        `nva_escopo` ENUM('Projeto','Organizacao','Empresa') NOT NULL DEFAULT 'Empresa',
      -- Status
        `nva_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `nva_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `nva_imutavel` INT NOT NULL DEFAULT 0,
        `nva_nome` VARCHAR(50) NOT NULL,
        `nva_descricao` VARCHAR(255) NULL,
        `nva_padrao_inicial` INT NOT NULL DEFAULT 0,
      -- Controle
        `nva_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `nva_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `nva_log` LONGTEXT NULL,
      PRIMARY KEY (`nva_id`),
      UNIQUE KEY `uk_nva_org_empresa_nome` (`nva_idorganizacao`, `nva_idempresa`, `nva_nome`),
      INDEX `idx_nva_idorganizacao` (`nva_idorganizacao`),
      INDEX `idx_nva_idempresa` (`nva_idempresa`),
      INDEX `idx_nva_escopo` (`nva_escopo`),
      INDEX `idx_nva_status` (`nva_status`),
      INDEX `idx_nva_padrao_inicial` (`nva_padrao_inicial`),
      INDEX `idx_nva_imutavel` (`nva_imutavel`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_permissao`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_permissao`;

    CREATE TABLE IF NOT EXISTS `icv_permissao` (
      `per_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `per_idorganizacao` INT NOT NULL DEFAULT 0,
      `per_idempresa` INT NOT NULL DEFAULT 0,
      `per_idnivelacesso` INT NOT NULL,
      -- Status
        `per_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `per_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `per_nome` VARCHAR(100) NOT NULL,
        `per_descricao` VARCHAR(255) NULL,
        `per_nivel` INT NOT NULL DEFAULT 1,
      -- Controle
        `per_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `per_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `per_log` LONGTEXT NULL,
      PRIMARY KEY (`per_id`),
      UNIQUE KEY `uk_per_nivel_nome` (`per_idnivelacesso`, `per_nome`),
      INDEX `idx_per_idorganizacao` (`per_idorganizacao`),
      INDEX `idx_per_idempresa` (`per_idempresa`),
      INDEX `idx_per_idnivelacesso` (`per_idnivelacesso`),
      INDEX `idx_per_status` (`per_status`),
      INDEX `idx_per_nome` (`per_nome`),
      CONSTRAINT `fk_icv_permissao_icv_nivelacesso1`
        FOREIGN KEY (`per_idnivelacesso`)
        REFERENCES `icv_nivelacesso` (`nva_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_permissaorelatorio`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_permissaorelatorio`;

    CREATE TABLE IF NOT EXISTS `icv_permissaorelatorio` (
      `pmr_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `pmr_idorganizacao` INT NOT NULL DEFAULT 0,
      `pmr_idempresa` INT NOT NULL DEFAULT 0,
      `pmr_idnivelacesso` INT NOT NULL,
      -- Status
        `pmr_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `pmr_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `pmr_dir` VARCHAR(100) NOT NULL,
        `pmr_nome` VARCHAR(100) NOT NULL,
        `pmr_descricao` VARCHAR(255) NULL,
      -- Controle
        `pmr_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `pmr_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `pmr_log` LONGTEXT NULL,
      PRIMARY KEY (`pmr_id`),
      UNIQUE KEY `uk_pmr_nivel_dir` (`pmr_idnivelacesso`, `pmr_dir`),
      INDEX `idx_pmr_idorganizacao` (`pmr_idorganizacao`),
      INDEX `idx_pmr_idempresa` (`pmr_idempresa`),
      INDEX `idx_pmr_idnivelacesso` (`pmr_idnivelacesso`),
      INDEX `idx_pmr_status` (`pmr_status`),
      INDEX `idx_pmr_dir` (`pmr_dir`),
      CONSTRAINT `fk_icv_permissaorelatorio_icv_nivelacesso1`
        FOREIGN KEY (`pmr_idnivelacesso`)
        REFERENCES `icv_nivelacesso` (`nva_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_usuario`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_usuario_nivelacesso`;
    DROP TABLE IF EXISTS `icv_usuario_organizacao`;
    DROP TABLE IF EXISTS `icv_usuario`;

    CREATE TABLE IF NOT EXISTS `icv_usuario` (
      `usu_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `usu_chave` VARCHAR(255) NULL,
      -- Contexto principal mantido apenas para compatibilidade com versões anteriores.
        `usu_idorganizacao` INT NULL,
        `usu_idempresa` INT NULL,
        `usu_idorganizacaoprincipal` INT NULL,
        `usu_idempresaprincipal` INT NULL,
      -- Status
        `usu_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `usu_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
        `usu_status_motivo` TEXT NULL,
      -- Dados básicos
        `usu_cnpjcpf` VARCHAR(20) NOT NULL,
        `usu_razaosocial` VARCHAR(255) NOT NULL,
        `usu_nomefantasia` VARCHAR(255) NULL,
        `usu_observacao` TEXT NULL,
        `usu_im` VARCHAR(20) NULL,
        `usu_ie` VARCHAR(20) NULL,
        `usu_rg` VARCHAR(20) NULL,
        `usu_dtnasc` DATE NULL,
        `usu_dtnasc_notificacao_wpp` DATE NULL,
        `usu_dtnasc_notificacao_email` DATE NULL,
        `usu_nomemae` VARCHAR(255) NULL,
        `usu_nomepai` VARCHAR(255) NULL,
      -- Dados complementares
        `usu_idsexo` INT NULL,
        `usu_idestadocivil` INT NULL,
        `usu_idnaturalidade` INT NULL,
      -- Visual / preferências
        `usu_logo` VARCHAR(255) NULL DEFAULT 'storage/global/semperfil.png',
        `usu_planodefundo` VARCHAR(255) NULL DEFAULT 'storage/global/planodefundo.jpg',
        `usu_sidebar` INT NOT NULL DEFAULT 1,
        `usu_home` VARCHAR(50) NULL DEFAULT 'dashboard',
        `usu_paletacores` LONGTEXT NULL,
        `usu_tema` ENUM('light','dark') NOT NULL DEFAULT 'light',
      -- Acesso
        `usu_niveisacesso` LONGTEXT NULL,
        `usu_email` VARCHAR(100) NOT NULL,
        `usu_celular` VARCHAR(20) NULL,
        `usu_username` VARCHAR(50) NULL,
        `usu_senha` LONGTEXT NOT NULL,
        `usu_dtexpiracaosenha` DATE NULL,
        `usu_codigo` VARCHAR(20) NULL,
      -- Sessão / segurança
        `usu_http_user_agent` VARCHAR(255) NULL,
        `usu_remote_addr` VARCHAR(255) NULL,
        `usu_online` INT NULL DEFAULT 0,
        `usu_dtonline` DATETIME NULL,
        `usu_dtlogin` DATETIME NULL,
      -- Comunicações
        `usu_newsletter` TINYINT(1) NOT NULL DEFAULT 0,
        `usu_dtnewsletter` DATETIME NULL,
      -- Controle
        `usu_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `usu_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `usu_log` LONGTEXT NULL,
      PRIMARY KEY (`usu_id`),
      UNIQUE KEY `uk_usu_chave` (`usu_chave`),
      UNIQUE KEY `uk_usu_cnpjcpf` (`usu_cnpjcpf`),
      UNIQUE KEY `uk_usu_email` (`usu_email`),
      UNIQUE KEY `uk_usu_celular` (`usu_celular`),
      UNIQUE KEY `uk_usu_username` (`usu_username`),
      INDEX `idx_usu_idorganizacao` (`usu_idorganizacao`),
      INDEX `idx_usu_idempresa` (`usu_idempresa`),
      INDEX `idx_usu_idorganizacaoprincipal` (`usu_idorganizacaoprincipal`),
      INDEX `idx_usu_idempresaprincipal` (`usu_idempresaprincipal`),
      INDEX `idx_usu_status` (`usu_status`),
      INDEX `idx_usu_statusclass` (`usu_statusclass`),
      INDEX `idx_usu_cnpjcpf` (`usu_cnpjcpf`),
      INDEX `idx_usu_razaosocial` (`usu_razaosocial`),
      INDEX `idx_usu_nomefantasia` (`usu_nomefantasia`),
      INDEX `idx_usu_im` (`usu_im`),
      INDEX `idx_usu_ie` (`usu_ie`),
      INDEX `idx_usu_rg` (`usu_rg`),
      INDEX `idx_usu_dtnasc` (`usu_dtnasc`),
      INDEX `idx_usu_dtnasc_notificacao_wpp` (`usu_dtnasc_notificacao_wpp`),
      INDEX `idx_usu_dtnasc_notificacao_email` (`usu_dtnasc_notificacao_email`),
      INDEX `idx_usu_idsexo` (`usu_idsexo`),
      INDEX `idx_usu_idestadocivil` (`usu_idestadocivil`),
      INDEX `idx_usu_idnaturalidade` (`usu_idnaturalidade`),
      INDEX `idx_usu_email` (`usu_email`),
      INDEX `idx_usu_celular` (`usu_celular`),
      INDEX `idx_usu_username` (`usu_username`),
      INDEX `idx_usu_online` (`usu_online`),
      INDEX `idx_usu_dtonline` (`usu_dtonline`),
      INDEX `idx_usu_dtlogin` (`usu_dtlogin`),
      CONSTRAINT `fk_icv_usuario_icv_organizacao1`
        FOREIGN KEY (`usu_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_icv_empresa1`
        FOREIGN KEY (`usu_idempresa`)
        REFERENCES `icv_empresa` (`emp_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_icv_organizacaoprincipal1`
        FOREIGN KEY (`usu_idorganizacaoprincipal`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_icv_empresaprincipal1`
        FOREIGN KEY (`usu_idempresaprincipal`)
        REFERENCES `icv_empresa` (`emp_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_icv_sexo1`
        FOREIGN KEY (`usu_idsexo`)
        REFERENCES `icv_sexo` (`sex_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_icv_estadocivil1`
        FOREIGN KEY (`usu_idestadocivil`)
        REFERENCES `icv_estadocivil` (`ecv_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_icv_naturalidade1`
        FOREIGN KEY (`usu_idnaturalidade`)
        REFERENCES `icv_naturalidade` (`nat_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_usuario_organizacao`
  -- Identidade global vinculada às organizações em que pode atuar.
  -- -----------------------------------------------------
    CREATE TABLE IF NOT EXISTS `icv_usuario_organizacao` (
      `uor_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `uor_idusuario` INT NOT NULL,
      `uor_idorganizacao` INT NOT NULL,
      -- Status
        `uor_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `uor_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Configuração
        `uor_principal` INT NOT NULL DEFAULT 0,
      -- Controle
        `uor_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `uor_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `uor_log` LONGTEXT NULL,
      PRIMARY KEY (`uor_id`),
      UNIQUE KEY `uk_uor_usuario_organizacao` (`uor_idusuario`, `uor_idorganizacao`),
      INDEX `idx_uor_idusuario` (`uor_idusuario`),
      INDEX `idx_uor_idorganizacao` (`uor_idorganizacao`),
      INDEX `idx_uor_status` (`uor_status`),
      INDEX `idx_uor_principal` (`uor_principal`),
      CONSTRAINT `fk_icv_usuario_organizacao_icv_usuario1`
        FOREIGN KEY (`uor_idusuario`)
        REFERENCES `icv_usuario` (`usu_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_organizacao_icv_organizacao1`
        FOREIGN KEY (`uor_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_usuario_empresa`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_usuario_empresa`;

    CREATE TABLE IF NOT EXISTS `icv_usuario_empresa` (
      `uem_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `uem_idorganizacao` INT NOT NULL,
      `uem_idusuario` INT NOT NULL,
      `uem_idempresa` INT NOT NULL,
      -- Status
        `uem_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `uem_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Configuração
        `uem_principal` INT NOT NULL DEFAULT 0,
      -- Controle
        `uem_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `uem_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `uem_log` LONGTEXT NULL,
      PRIMARY KEY (`uem_id`),
      UNIQUE KEY `uk_uem_usuario_empresa` (`uem_idusuario`, `uem_idempresa`),
      INDEX `idx_uem_idorganizacao` (`uem_idorganizacao`),
      INDEX `idx_uem_idusuario` (`uem_idusuario`),
      INDEX `idx_uem_idempresa` (`uem_idempresa`),
      INDEX `idx_uem_status` (`uem_status`),
      INDEX `idx_uem_principal` (`uem_principal`),
      CONSTRAINT `fk_icv_usuario_empresa_icv_organizacao1`
        FOREIGN KEY (`uem_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_empresa_icv_usuario1`
        FOREIGN KEY (`uem_idusuario`)
        REFERENCES `icv_usuario` (`usu_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_empresa_icv_empresa1`
        FOREIGN KEY (`uem_idempresa`)
        REFERENCES `icv_empresa` (`emp_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_usuario_nivelacesso`
 -- Níveis de acesso do usuário por projeto, organização e empresa.
  -- -----------------------------------------------------
    CREATE TABLE IF NOT EXISTS `icv_usuario_nivelacesso` (
      `una_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `una_idusuario` INT NOT NULL,
      `una_idorganizacao` INT NULL DEFAULT NULL,
      `una_idempresa` INT NULL DEFAULT NULL,
      `una_idnivelacesso` INT NOT NULL,
      -- Status
        `una_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `una_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Configuração
        `una_principal` INT NOT NULL DEFAULT 0,
      -- Controle
        `una_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `una_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `una_log` LONGTEXT NULL,
      PRIMARY KEY (`una_id`),
      UNIQUE KEY `uk_una_usuario_contexto_nivel` (`una_idusuario`, `una_idorganizacao`, `una_idempresa`, `una_idnivelacesso`),
      INDEX `idx_una_idusuario` (`una_idusuario`),
      INDEX `idx_una_idorganizacao` (`una_idorganizacao`),
      INDEX `idx_una_idempresa` (`una_idempresa`),
      INDEX `idx_una_idnivelacesso` (`una_idnivelacesso`),
      INDEX `idx_una_status` (`una_status`),
      CONSTRAINT `fk_icv_usuario_nivelacesso_icv_usuario1`
        FOREIGN KEY (`una_idusuario`)
        REFERENCES `icv_usuario` (`usu_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_nivelacesso_icv_organizacao1`
        FOREIGN KEY (`una_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_nivelacesso_icv_empresa1`
        FOREIGN KEY (`una_idempresa`)
        REFERENCES `icv_empresa` (`emp_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_usuario_nivelacesso_icv_nivelacesso1`
        FOREIGN KEY (`una_idnivelacesso`)
        REFERENCES `icv_nivelacesso` (`nva_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

################################################################
##################### PLANO E CONTRATAÇÃO #####################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_plano`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_plano`;

    CREATE TABLE IF NOT EXISTS `icv_plano` (
      `pla_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Status
        `pla_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `pla_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `pla_tipo` ENUM('Teste','Comercial','Dedicado','Interno') NOT NULL DEFAULT 'Comercial',
        `pla_nome` VARCHAR(50) NOT NULL,
        `pla_descricaoresumida` VARCHAR(255) NULL,
        `pla_descricao` LONGTEXT NULL,
      -- Apresentacao comercial
        `pla_rotulo` VARCHAR(80) NULL,
        `pla_selo` VARCHAR(80) NULL,
        `pla_beneficios` JSON NULL,
        `pla_textobotao` VARCHAR(80) NULL,
        `pla_ambiente` ENUM('Compartilhado','Dedicado') NOT NULL DEFAULT 'Compartilhado',
        `pla_valormensal` DECIMAL(10,2) NULL,
        `pla_valoranual` DECIMAL(10,2) NULL,
        `pla_valormensal_original` DECIMAL(10,2) NULL,
        `pla_valoranual_original` DECIMAL(10,2) NULL,
        `pla_dias_teste` INT NULL,
      -- Regras e parâmetros
        `pla_parametro` LONGTEXT NULL,
        `pla_contatocomercial` LONGTEXT NULL,
      -- Controle
        `pla_ordem` INT NOT NULL DEFAULT 0,
        `pla_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `pla_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `pla_log` LONGTEXT NULL,
      PRIMARY KEY (`pla_id`),
      UNIQUE KEY `uk_pla_nome` (`pla_nome`),
      INDEX `idx_pla_status` (`pla_status`),
      INDEX `idx_pla_tipo` (`pla_tipo`),
      INDEX `idx_pla_ambiente` (`pla_ambiente`),
      INDEX `idx_pla_ordem` (`pla_ordem`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_plano_contratado`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_plano_contratado`;

    CREATE TABLE IF NOT EXISTS `icv_plano_contratado` (
      `plc_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo da contratação
        `plc_idorganizacao` INT NOT NULL,
        `plc_idplano` INT NOT NULL,
        `plc_idusuariocontratante` INT NOT NULL,
        `plc_idcontratacaoanterior` INT NULL,
      `plc_grupo_contratacao` VARCHAR(50) NOT NULL,
      -- Status
        `plc_status` ENUM('Pendente','Agendado','Teste','Ativo','Suspenso','Cancelado','Expirado') NOT NULL DEFAULT 'Pendente',
        `plc_statusclass` ENUM('success','danger','warning','secondary','info') NOT NULL DEFAULT 'warning',
        `plc_statusfinanceiro` ENUM('Isento','Em aberto','Pago','Pago Manual','Falhou','Vencido','Cancelado') NOT NULL DEFAULT 'Em aberto',
      -- Origem e ciclo comercial
        `plc_origem` ENUM('Autocadastro','Renovacao','Manual','Migracao','Cortesia','Sistema') NOT NULL DEFAULT 'Manual',
        `plc_ciclo` ENUM('Teste','Mensal','Anual') NOT NULL DEFAULT 'Mensal',
        `plc_ambiente` ENUM('Compartilhado','Dedicado') NOT NULL DEFAULT 'Compartilhado',
      -- Período
        `plc_dtinicio` DATETIME NULL,
        `plc_dtfim` DATETIME NULL,
        `plc_dtvencimento` DATETIME NULL,
        `plc_dtpagamento` DATETIME NULL,
      -- Cancelamento
        `plc_dtcancelamento` DATETIME NULL,
        `plc_motivocancelamento` VARCHAR(255) NULL,
      -- Suspensão
        `plc_dtsuspensao` DATETIME NULL,
        `plc_motivosuspensao` VARCHAR(255) NULL,
      -- Dados comerciais
        `plc_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `plc_observacao` TEXT NULL,
      -- Regras e parâmetros contratados
        `plc_parametro` LONGTEXT NULL,
      -- Aceite dos documentos legais
        `plc_aceite` TINYINT(1) NOT NULL DEFAULT 0,
        `plc_termosuso` LONGTEXT NULL,
        `plc_termosusoversao` VARCHAR(100) NULL,
        `plc_avisoprivacidade` LONGTEXT NULL,
        `plc_avisoprivacidadeversao` VARCHAR(100) NULL,
        `plc_assinatura` LONGTEXT NULL,
      -- Integração de cobrança avulsa
        `plc_pagarme_id` VARCHAR(255) NULL,
        `plc_pagarme_tipo` ENUM('Cobranca','Link') NULL,
        `plc_pagarme_url` VARCHAR(500) NULL,
        `plc_pagarme_status` VARCHAR(80) NULL,
        `plc_pagarme_dtultimasync` DATETIME NULL,
        `plc_pagarme_ultimoevento` VARCHAR(255) NULL,
        `plc_pagarme_request` LONGTEXT NULL,
        `plc_pagarme_response` LONGTEXT NULL,
      -- Controle
        `plc_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `plc_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `plc_log` LONGTEXT NULL,
      PRIMARY KEY (`plc_id`),
      INDEX `idx_plc_idorganizacao` (`plc_idorganizacao`),
      INDEX `idx_plc_idplano` (`plc_idplano`),
      INDEX `idx_plc_idusuariocontratante` (`plc_idusuariocontratante`),
      INDEX `idx_plc_idcontratacaoanterior` (`plc_idcontratacaoanterior`),
      INDEX `idx_plc_grupo_contratacao` (`plc_grupo_contratacao`),
      INDEX `idx_plc_status` (`plc_status`),
      INDEX `idx_plc_statusfinanceiro` (`plc_statusfinanceiro`),
      INDEX `idx_plc_origem` (`plc_origem`),
      INDEX `idx_plc_ciclo` (`plc_ciclo`),
      INDEX `idx_plc_ambiente` (`plc_ambiente`),
      INDEX `idx_plc_periodo` (`plc_dtinicio`, `plc_dtfim`),
      INDEX `idx_plc_org_periodo` (`plc_idorganizacao`, `plc_dtinicio`, `plc_dtfim`),
      INDEX `idx_plc_pagarme_status` (`plc_pagarme_status`),
      INDEX `idx_plc_pagarme_ultimoevento` (`plc_pagarme_ultimoevento`),
      UNIQUE KEY `uk_plc_pagarme_id` (`plc_pagarme_id`),
      CONSTRAINT `fk_icv_plano_contratado_icv_organizacao1`
        FOREIGN KEY (`plc_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_plano_contratado_icv_plano1`
        FOREIGN KEY (`plc_idplano`)
        REFERENCES `icv_plano` (`pla_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_plano_contratado_icv_usuario1`
        FOREIGN KEY (`plc_idusuariocontratante`)
        REFERENCES `icv_usuario` (`usu_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_plano_contratado_icv_plano_contratado_anterior1`
        FOREIGN KEY (`plc_idcontratacaoanterior`)
        REFERENCES `icv_plano_contratado` (`plc_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_email_template`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_email_template`;

    CREATE TABLE IF NOT EXISTS `icv_email_template` (
      `emt_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `emt_idorganizacao` INT NOT NULL DEFAULT 0,
      `emt_idempresa` INT NOT NULL DEFAULT 0,
      `emt_templateprojeto` VARCHAR(100) NULL,
      `emt_palavrachave` JSON NULL,
      `emt_nome` VARCHAR(150) NOT NULL,
      `emt_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
      `emt_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      `emt_host` VARCHAR(255) NULL,
      `emt_user` VARCHAR(255) NULL,
      `emt_pass` VARCHAR(500) NULL,
      `emt_port` INT NOT NULL DEFAULT 587,
      `emt_remetentenome` VARCHAR(150) NULL,
      `emt_remetenteemail` VARCHAR(255) NULL,
      `emt_replyto` VARCHAR(255) NULL,
      `emt_title` VARCHAR(255) NOT NULL,
      `emt_lang` VARCHAR(10) NOT NULL DEFAULT 'pt_br',
      `emt_charset` VARCHAR(20) NOT NULL DEFAULT 'UTF-8',
      `emt_conteudo` LONGTEXT NOT NULL,
      `emt_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `emt_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `emt_log` LONGTEXT NULL,
      PRIMARY KEY (`emt_id`),
      UNIQUE KEY `uk_emt_contexto_templateprojeto` (`emt_idorganizacao`, `emt_idempresa`, `emt_templateprojeto`),
      INDEX `idx_emt_idorganizacao` (`emt_idorganizacao`),
      INDEX `idx_emt_idempresa` (`emt_idempresa`),
      INDEX `idx_emt_status` (`emt_status`),
      INDEX `idx_emt_templateprojeto` (`emt_templateprojeto`)
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_email_enviado`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_email_enviado`;
    
    CREATE TABLE IF NOT EXISTS `icv_email_enviado` (
      `eme_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `eme_idorganizacao` INT NOT NULL DEFAULT 0,
      `eme_idempresa` INT NOT NULL DEFAULT 0,
      `eme_idtemplate` INT NULL,
      `eme_tentativa` INT NOT NULL DEFAULT 1,
      `eme_status` ENUM('Pendente','Agendado','Enviado','Falhou','Cancelado') NOT NULL DEFAULT 'Pendente',
      `eme_statusclass` ENUM('secondary','primary','success','danger','warning') NOT NULL DEFAULT 'secondary',
      `eme_dtagendada` DATETIME NULL,
      `eme_dtenvio` DATETIME NULL,
      `eme_destinarionome` VARCHAR(150) NULL,
      `eme_destinatarioemail` VARCHAR(255) NOT NULL,
      `eme_title` VARCHAR(255) NOT NULL,
      `eme_mensagem` LONGTEXT NOT NULL,
      `eme_snapshot` LONGTEXT NULL,
      `eme_response` LONGTEXT NULL,
      `eme_erro` LONGTEXT NULL,
      `eme_dtcad` DATETIME NOT NULL DEFAULT NOW(),
      `eme_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `eme_log` LONGTEXT NULL,
      PRIMARY KEY (`eme_id`),
      INDEX `idx_eme_idorganizacao` (`eme_idorganizacao`),
      INDEX `idx_eme_idempresa` (`eme_idempresa`),
      INDEX `idx_eme_idtemplate` (`eme_idtemplate`),
      INDEX `idx_eme_status` (`eme_status`),
      INDEX `idx_eme_destinatarioemail` (`eme_destinatarioemail`),
      INDEX `idx_eme_dtcad` (`eme_dtcad`),
      CONSTRAINT `fk_icv_email_enviado_icv_email_template1`
        FOREIGN KEY (`eme_idtemplate`)
        REFERENCES `icv_email_template` (`emt_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

################################################################
######################## FINANCEIRO CORE #######################
################################################################

  -- -----------------------------------------------------
  -- Table `icv_conta_financeira`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_conta_financeira`;

    CREATE TABLE IF NOT EXISTS `icv_conta_financeira` (
      `ctf_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `ctf_idorganizacao` INT NOT NULL,
        `ctf_idempresa` INT NOT NULL DEFAULT 0,
        `ctf_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      -- Status
        `ctf_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `ctf_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `ctf_nome` VARCHAR(80) NOT NULL,
        `ctf_tipo` ENUM('Caixa','Conta corrente','Conta poupanca','Cartao','Carteira digital','Outra') NOT NULL DEFAULT 'Outra',
        `ctf_padrao` INT NOT NULL DEFAULT 0,
        `ctf_saldo_inicial` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `ctf_dt_saldo_inicial` DATE NULL,
        `ctf_observacao` VARCHAR(255) NULL,
      -- Controle
        `ctf_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `ctf_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `ctf_log` LONGTEXT NULL,
      PRIMARY KEY (`ctf_id`),
      UNIQUE KEY `uk_ctf_org_empresa_nome` (`ctf_idorganizacao`, `ctf_idempresa`, `ctf_nome`),
      INDEX `idx_ctf_idorganizacao` (`ctf_idorganizacao`),
      INDEX `idx_ctf_idempresa` (`ctf_idempresa`),
      INDEX `idx_ctf_tipocadastro` (`ctf_tipocadastro`),
      INDEX `idx_ctf_status` (`ctf_status`),
      INDEX `idx_ctf_tipo` (`ctf_tipo`),
      INDEX `idx_ctf_padrao` (`ctf_padrao`),
      CONSTRAINT `fk_icv_conta_financeira_icv_organizacao1`
        FOREIGN KEY (`ctf_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_forma_pagamento`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_forma_pagamento`;

    CREATE TABLE IF NOT EXISTS `icv_forma_pagamento` (
      `fpg_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `fpg_idorganizacao` INT NOT NULL,
        `fpg_idempresa` INT NOT NULL DEFAULT 0,
        `fpg_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      -- Status
        `fpg_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `fpg_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `fpg_nome` VARCHAR(80) NOT NULL,
        `fpg_padrao` INT NOT NULL DEFAULT 0,
        `fpg_aceita_parcela` INT NOT NULL DEFAULT 0,
        `fpg_observacao` VARCHAR(255) NULL,
      -- Controle
        `fpg_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `fpg_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `fpg_log` LONGTEXT NULL,
      PRIMARY KEY (`fpg_id`),
      UNIQUE KEY `uk_fpg_org_empresa_nome` (`fpg_idorganizacao`, `fpg_idempresa`, `fpg_nome`),
      INDEX `idx_fpg_idorganizacao` (`fpg_idorganizacao`),
      INDEX `idx_fpg_idempresa` (`fpg_idempresa`),
      INDEX `idx_fpg_tipocadastro` (`fpg_tipocadastro`),
      INDEX `idx_fpg_status` (`fpg_status`),
      INDEX `idx_fpg_padrao` (`fpg_padrao`),
      INDEX `idx_fpg_aceita_parcela` (`fpg_aceita_parcela`),
      CONSTRAINT `fk_icv_forma_pagamento_icv_organizacao1`
        FOREIGN KEY (`fpg_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_condicao_pgto`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_condicao_pgto`;

    CREATE TABLE IF NOT EXISTS `icv_condicao_pgto` (
      `cpg_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `cpg_idorganizacao` INT NOT NULL,
        `cpg_idempresa` INT NOT NULL DEFAULT 0,
        `cpg_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      -- Status
        `cpg_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `cpg_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `cpg_idformapagamento` INT NOT NULL,
        `cpg_nome` VARCHAR(80) NOT NULL,
        `cpg_qtdparcela` INT NOT NULL DEFAULT 1,
        `cpg_intervalodias` INT NOT NULL DEFAULT 30,
        `cpg_padrao` INT NOT NULL DEFAULT 0,
        `cpg_observacao` VARCHAR(255) NULL,
      -- Controle
        `cpg_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `cpg_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `cpg_log` LONGTEXT NULL,
      PRIMARY KEY (`cpg_id`),
      UNIQUE KEY `uk_cpg_org_empresa_forma_nome_parcela` (`cpg_idorganizacao`, `cpg_idempresa`, `cpg_idformapagamento`, `cpg_nome`, `cpg_qtdparcela`),
      INDEX `idx_cpg_idorganizacao` (`cpg_idorganizacao`),
      INDEX `idx_cpg_idempresa` (`cpg_idempresa`),
      INDEX `idx_cpg_tipocadastro` (`cpg_tipocadastro`),
      INDEX `idx_cpg_status` (`cpg_status`),
      INDEX `idx_cpg_idformapagamento` (`cpg_idformapagamento`),
      INDEX `idx_cpg_qtdparcela` (`cpg_qtdparcela`),
      INDEX `idx_cpg_padrao` (`cpg_padrao`),
      CONSTRAINT `fk_icv_condicao_pgto_icv_forma_pagamento1`
        FOREIGN KEY (`cpg_idformapagamento`)
        REFERENCES `icv_forma_pagamento` (`fpg_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_condicao_pgto_icv_organizacao1`
        FOREIGN KEY (`cpg_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_plano_conta`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_plano_conta`;

    CREATE TABLE IF NOT EXISTS `icv_plano_conta` (
      `pct_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `pct_idorganizacao` INT NOT NULL,
        `pct_idempresa` INT NOT NULL DEFAULT 0,
        `pct_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      -- Status
        `pct_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `pct_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `pct_natureza` ENUM('Entrada','Saida') NOT NULL,
        `pct_nivel` ENUM('Grupo','Item') NOT NULL DEFAULT 'Item',
        `pct_idpai` INT NULL,
        `pct_codigo` VARCHAR(30) NULL,
        `pct_nome` VARCHAR(80) NOT NULL,
        `pct_descricao` VARCHAR(255) NULL,
        `pct_padrao` INT NOT NULL DEFAULT 0,
      -- Controle
        `pct_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `pct_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `pct_log` LONGTEXT NULL,
      PRIMARY KEY (`pct_id`),
      UNIQUE KEY `uk_pct_org_empresa_pai_nome` (`pct_idorganizacao`, `pct_idempresa`, `pct_idpai`, `pct_nome`),
      UNIQUE KEY `uk_pct_org_empresa_codigo` (`pct_idorganizacao`, `pct_idempresa`, `pct_codigo`),
      INDEX `idx_pct_idorganizacao` (`pct_idorganizacao`),
      INDEX `idx_pct_idempresa` (`pct_idempresa`),
      INDEX `idx_pct_tipocadastro` (`pct_tipocadastro`),
      INDEX `idx_pct_status` (`pct_status`),
      INDEX `idx_pct_natureza` (`pct_natureza`),
      INDEX `idx_pct_nivel` (`pct_nivel`),
      INDEX `idx_pct_idpai` (`pct_idpai`),
      INDEX `idx_pct_padrao` (`pct_padrao`),
      CONSTRAINT `fk_icv_plano_conta_icv_plano_conta1`
        FOREIGN KEY (`pct_idpai`)
        REFERENCES `icv_plano_conta` (`pct_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_plano_conta_icv_organizacao1`
        FOREIGN KEY (`pct_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_centro_custo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_centro_custo`;

    CREATE TABLE IF NOT EXISTS `icv_centro_custo` (
      `cdc_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `cdc_idorganizacao` INT NOT NULL,
        `cdc_idempresa` INT NOT NULL DEFAULT 0,
        `cdc_tipocadastro` ENUM('Organizacao','Empresa') NOT NULL DEFAULT 'Organizacao',
      -- Status
        `cdc_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `cdc_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
      -- Dados principais
        `cdc_nome` VARCHAR(80) NOT NULL,
        `cdc_codigo` VARCHAR(30) NULL,
        `cdc_descricao` VARCHAR(255) NULL,
        `cdc_padrao` INT NOT NULL DEFAULT 0,
      -- Controle
        `cdc_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `cdc_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `cdc_log` LONGTEXT NULL,
      PRIMARY KEY (`cdc_id`),
      UNIQUE KEY `uk_cdc_org_empresa_nome` (`cdc_idorganizacao`, `cdc_idempresa`, `cdc_nome`),
      UNIQUE KEY `uk_cdc_org_empresa_codigo` (`cdc_idorganizacao`, `cdc_idempresa`, `cdc_codigo`),
      INDEX `idx_cdc_idorganizacao` (`cdc_idorganizacao`),
      INDEX `idx_cdc_idempresa` (`cdc_idempresa`),
      INDEX `idx_cdc_tipocadastro` (`cdc_tipocadastro`),
      INDEX `idx_cdc_status` (`cdc_status`),
      INDEX `idx_cdc_padrao` (`cdc_padrao`),
      CONSTRAINT `fk_icv_centro_custo_icv_organizacao1`
        FOREIGN KEY (`cdc_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_titulo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_titulo`;

    CREATE TABLE IF NOT EXISTS `icv_titulo` (
      `tit_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `tit_idorganizacao` INT NOT NULL,
        `tit_idempresa` INT NOT NULL DEFAULT 0,
      -- Status
        `tit_status` ENUM('Cadastrado','Confirmado','Cancelado') NOT NULL DEFAULT 'Cadastrado',
        `tit_statusclass` ENUM('secondary','success','danger') NOT NULL DEFAULT 'secondary',
        `tit_statusfinanceiro` ENUM('Aguardando confirmação','Aguardando lançamento','Lançamento registrado','Parcial','Pago','Recebido','Cancelado') NOT NULL DEFAULT 'Aguardando confirmação',
        `tit_statusfinanceiroclass` ENUM('secondary','primary','warning','success','danger') NOT NULL DEFAULT 'secondary',
      -- Dados principais
        `tit_natureza` ENUM('Entrada','Saida') NOT NULL,
        `tit_nome` VARCHAR(100) NOT NULL,
        `tit_descricao` VARCHAR(255) NULL,
        `tit_idplanoconta` INT NULL,
        `tit_pct_nome` VARCHAR(80) NULL,
        `tit_referencia` DATE NULL,
        `tit_dtvencimento` DATE NULL,
        `tit_valortotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `tit_valormovimentado` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
      -- Origem polimorfica
        `tit_origem` VARCHAR(50) NULL,
        `tit_idorigem` INT NULL,
      -- Cancelamento
        `tit_dtcancelamento` DATETIME NULL,
        `tit_idusu_cancelamento` INT NULL,
        `tit_usu_nome_cancelamento` VARCHAR(255) NULL,
        `tit_motivo_cancelamento` VARCHAR(255) NULL,
      -- Confirmacao
        `tit_dtconfirmacao` DATETIME NULL,
        `tit_idusu_confirmacao` INT NULL,
        `tit_usu_nome_confirmacao` VARCHAR(255) NULL,
      -- Controle
        `tit_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `tit_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `tit_log` LONGTEXT NULL,
      PRIMARY KEY (`tit_id`),
      INDEX `idx_tit_idorganizacao` (`tit_idorganizacao`),
      INDEX `idx_tit_idempresa` (`tit_idempresa`),
      INDEX `idx_tit_status` (`tit_status`),
      INDEX `idx_tit_statusfinanceiro` (`tit_statusfinanceiro`),
      INDEX `idx_tit_natureza` (`tit_natureza`),
      INDEX `idx_tit_idplanoconta` (`tit_idplanoconta`),
      INDEX `idx_tit_dtvencimento` (`tit_dtvencimento`),
      INDEX `idx_tit_idusu_cancelamento` (`tit_idusu_cancelamento`),
      INDEX `idx_tit_idusu_confirmacao` (`tit_idusu_confirmacao`),
      INDEX `idx_tit_origem_id` (`tit_origem`, `tit_idorigem`),
      CONSTRAINT `fk_icv_titulo_icv_plano_conta1`
        FOREIGN KEY (`tit_idplanoconta`)
        REFERENCES `icv_plano_conta` (`pct_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_titulo_icv_organizacao1`
        FOREIGN KEY (`tit_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_lancamento`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_lancamento`;

    CREATE TABLE IF NOT EXISTS `icv_lancamento` (
      `lan_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      -- Escopo
        `lan_idorganizacao` INT NOT NULL,
        `lan_idempresa` INT NOT NULL DEFAULT 0,
      -- Status
        `lan_status` ENUM('A pagar','A receber','Pago','Recebido','Cancelado','Estornado','Corrigido') NOT NULL,
        `lan_statusclass` ENUM('secondary','primary','success','danger','warning') NOT NULL DEFAULT 'secondary',
      -- Dados principais
        `lan_natureza` ENUM('Entrada','Saida') NOT NULL,
        `lan_tipo` ENUM('Normal','Titulo','Transferencia') NOT NULL DEFAULT 'Normal',
        `lan_idtitulo` INT NULL,
        `lan_idcontafinanceira` INT NULL,
        `lan_idcentrocusto` INT NULL,
        `lan_idplanoconta` INT NULL,
        `lan_idformapagamento` INT NULL,
        `lan_idcondicaopgto` INT NULL,
        `lan_descricao` VARCHAR(255) NOT NULL,
        `lan_observacao` VARCHAR(255) NULL,
        `lan_valorinformado` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `lan_tipodesconto` ENUM('Valor','Percentual') NULL,
        `lan_valordesconto` DECIMAL(12,2) NULL,
        `lan_desconto` DECIMAL(12,2) NULL,
        `lan_tipoacrescimo` ENUM('Valor','Percentual') NULL,
        `lan_valoracrescimo` DECIMAL(12,2) NULL,
        `lan_acrescimo` DECIMAL(12,2) NULL,
        `lan_valor` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `lan_dtvencimento` DATE NULL,
        `lan_dtcompetencia` DATE NULL,
        `lan_dtbaixa` DATE NULL,
      -- Transferencia
        `lan_grupo_transferencia` VARCHAR(50) NULL,
        `lan_idlancamento_vinculado` INT NULL,
      -- Parcelamento
        `lan_grupo_parcelamento` VARCHAR(50) NULL,
        `lan_parcela` INT NOT NULL DEFAULT 1,
        `lan_totalparcela` INT NOT NULL DEFAULT 1,
      -- Acao / reversao
        `lan_tipoacao` ENUM('Cancelamento','Estorno','Correcao') NULL,
        `lan_idlancamento_origem` INT NULL,
        `lan_dtacao` DATETIME NULL,
        `lan_motivoacao` VARCHAR(255) NULL,
      -- Origem polimorfica
        `lan_origem` VARCHAR(50) NULL,
        `lan_idorigem` INT NULL,
      -- Controle
        `lan_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `lan_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `lan_log` LONGTEXT NULL,
      PRIMARY KEY (`lan_id`),
      INDEX `idx_lan_idorganizacao` (`lan_idorganizacao`),
      INDEX `idx_lan_idempresa` (`lan_idempresa`),
      INDEX `idx_lan_status` (`lan_status`),
      INDEX `idx_lan_natureza` (`lan_natureza`),
      INDEX `idx_lan_tipo` (`lan_tipo`),
      INDEX `idx_lan_idtitulo` (`lan_idtitulo`),
      INDEX `idx_lan_idcontafinanceira` (`lan_idcontafinanceira`),
      INDEX `idx_lan_idcentrocusto` (`lan_idcentrocusto`),
      INDEX `idx_lan_idplanoconta` (`lan_idplanoconta`),
      INDEX `idx_lan_idformapagamento` (`lan_idformapagamento`),
      INDEX `idx_lan_idcondicaopgto` (`lan_idcondicaopgto`),
      INDEX `idx_lan_dtvencimento` (`lan_dtvencimento`),
      INDEX `idx_lan_dtcompetencia` (`lan_dtcompetencia`),
      INDEX `idx_lan_dtbaixa` (`lan_dtbaixa`),
      INDEX `idx_lan_grupo_transferencia` (`lan_grupo_transferencia`),
      INDEX `idx_lan_idlancamento_vinculado` (`lan_idlancamento_vinculado`),
      INDEX `idx_lan_idlancamento_origem` (`lan_idlancamento_origem`),
      INDEX `idx_lan_origem_id` (`lan_origem`, `lan_idorigem`),
      INDEX `idx_lan_contexto_baixa` (`lan_idorganizacao`, `lan_idempresa`, `lan_dtbaixa`),
      CONSTRAINT `fk_icv_lancamento_icv_titulo1`
        FOREIGN KEY (`lan_idtitulo`)
        REFERENCES `icv_titulo` (`tit_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_conta_financeira1`
        FOREIGN KEY (`lan_idcontafinanceira`)
        REFERENCES `icv_conta_financeira` (`ctf_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_centro_custo1`
        FOREIGN KEY (`lan_idcentrocusto`)
        REFERENCES `icv_centro_custo` (`cdc_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_plano_conta1`
        FOREIGN KEY (`lan_idplanoconta`)
        REFERENCES `icv_plano_conta` (`pct_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_forma_pagamento1`
        FOREIGN KEY (`lan_idformapagamento`)
        REFERENCES `icv_forma_pagamento` (`fpg_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_condicao_pgto1`
        FOREIGN KEY (`lan_idcondicaopgto`)
        REFERENCES `icv_condicao_pgto` (`cpg_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_lancamento_vinculado1`
        FOREIGN KEY (`lan_idlancamento_vinculado`)
        REFERENCES `icv_lancamento` (`lan_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_lancamento_origem1`
        FOREIGN KEY (`lan_idlancamento_origem`)
        REFERENCES `icv_lancamento` (`lan_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_lancamento_icv_organizacao1`
        FOREIGN KEY (`lan_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `icv_fornecedor`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `icv_fornecedor`;

    CREATE TABLE IF NOT EXISTS `icv_fornecedor` (
      `for_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
      `for_chave` VARCHAR(50) NOT NULL,
      -- Escopo / contexto operacional
        `for_idorganizacao` INT NOT NULL,
        `for_idempresa` INT NOT NULL DEFAULT 0,
      -- Status
        `for_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
        `for_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
        `for_status_motivo` TEXT NULL,
      -- Dados principais
        `for_cnpjcpf` VARCHAR(20) NOT NULL,
        `for_razaosocial` VARCHAR(255) NOT NULL,
        `for_nomefantasia` VARCHAR(255) NULL,
        `for_observacao` VARCHAR(255) NULL,
        `for_im` VARCHAR(20) NULL,
        `for_ie` VARCHAR(20) NULL,
        `for_rg` VARCHAR(20) NULL,
        `for_dtnasc` DATE NULL,
      -- Dados complementares para pessoa física
        `for_idsexo` INT NULL,
        `for_idestadocivil` INT NULL,
      -- Contato
        `for_celular` VARCHAR(20) NULL,
        `for_email` VARCHAR(100) NULL,
      -- Acesso do fornecedor
        `for_username` VARCHAR(50) NULL,
        `for_senha` LONGTEXT NULL,
      -- Controle
        `for_dtcad` DATETIME NOT NULL DEFAULT NOW(),
        `for_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `for_log` LONGTEXT NULL,
      PRIMARY KEY (`for_id`),
      UNIQUE KEY `uk_for_org_empresa_chave` (`for_idorganizacao`, `for_idempresa`, `for_chave`),
      UNIQUE KEY `uk_for_org_empresa_cnpjcpf` (`for_idorganizacao`, `for_idempresa`, `for_cnpjcpf`),
      UNIQUE KEY `uk_for_org_empresa_email` (`for_idorganizacao`, `for_idempresa`, `for_email`),
      UNIQUE KEY `uk_for_org_empresa_celular` (`for_idorganizacao`, `for_idempresa`, `for_celular`),
      UNIQUE KEY `uk_for_org_empresa_username` (`for_idorganizacao`, `for_idempresa`, `for_username`),
      INDEX `idx_for_idorganizacao` (`for_idorganizacao`),
      INDEX `idx_for_idempresa` (`for_idempresa`),
      INDEX `idx_for_chave` (`for_chave`),
      INDEX `idx_for_status` (`for_status`),
      INDEX `idx_for_statusclass` (`for_statusclass`),
      INDEX `idx_for_cnpjcpf` (`for_cnpjcpf`),
      INDEX `idx_for_razaosocial` (`for_razaosocial`),
      INDEX `idx_for_nomefantasia` (`for_nomefantasia`),
      INDEX `idx_for_im` (`for_im`),
      INDEX `idx_for_ie` (`for_ie`),
      INDEX `idx_for_rg` (`for_rg`),
      INDEX `idx_for_dtnasc` (`for_dtnasc`),
      INDEX `idx_for_idsexo` (`for_idsexo`),
      INDEX `idx_for_idestadocivil` (`for_idestadocivil`),
      INDEX `idx_for_email` (`for_email`),
      INDEX `idx_for_celular` (`for_celular`),
      INDEX `idx_for_username` (`for_username`),
      CONSTRAINT `fk_icv_fornecedor_icv_organizacao1`
        FOREIGN KEY (`for_idorganizacao`)
        REFERENCES `icv_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_fornecedor_icv_sexo1`
        FOREIGN KEY (`for_idsexo`)
        REFERENCES `icv_sexo` (`sex_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_icv_fornecedor_icv_estadocivil1`
        FOREIGN KEY (`for_idestadocivil`)
        REFERENCES `icv_estadocivil` (`ecv_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Central de conhecimento do projeto
-- -----------------------------------------------------
DROP TABLE IF EXISTS `icv_conhecimento_midia`;
DROP TABLE IF EXISTS `icv_conhecimento`;
DROP TABLE IF EXISTS `icv_conhecimento_categoria`;

CREATE TABLE IF NOT EXISTS `icv_conhecimento_categoria` (
  `cct_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
  `cct_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `cct_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
  `cct_nome` VARCHAR(150) NOT NULL,
  `cct_ordem` INT NOT NULL DEFAULT 0,
  `cct_dtcad` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cct_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cct_log` LONGTEXT NULL,
  PRIMARY KEY (`cct_id`),
  UNIQUE KEY `uk_cct_nome` (`cct_nome`),
  INDEX `idx_cct_status` (`cct_status`),
  INDEX `idx_cct_ordem` (`cct_ordem`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `icv_conhecimento` (
  `con_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
  `con_idcategoria` INT NOT NULL,
  `con_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `con_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
  `con_titulo` VARCHAR(255) NOT NULL,
  `con_descricao_resumida` VARCHAR(500) NULL,
  `con_conteudo` LONGTEXT NULL,
  `con_tags` JSON NULL,
  `con_ordem` INT NOT NULL DEFAULT 0,
  `con_visualizacoes` INT UNSIGNED NOT NULL DEFAULT 0,
  `con_dtcad` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `con_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `con_log` LONGTEXT NULL,
  PRIMARY KEY (`con_id`),
  INDEX `idx_con_idcategoria` (`con_idcategoria`),
  INDEX `idx_con_status` (`con_status`),
  INDEX `idx_con_ordem` (`con_ordem`),
  CONSTRAINT `fk_icv_conhecimento_categoria`
    FOREIGN KEY (`con_idcategoria`)
    REFERENCES `icv_conhecimento_categoria` (`cct_id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `icv_conhecimento_midia` (
  `cmi_id` INT NOT NULL UNIQUE AUTO_INCREMENT,
  `cmi_idconhecimento` INT NOT NULL,
  `cmi_tipo` ENUM('Upload','Link') NOT NULL DEFAULT 'Upload',
  `cmi_status` ENUM('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `cmi_statusclass` ENUM('success','danger') NOT NULL DEFAULT 'success',
  `cmi_nome` VARCHAR(255) NULL,
  `cmi_url` VARCHAR(1000) NULL,
  `cmi_json` JSON NULL,
  `cmi_ordem` INT NOT NULL DEFAULT 0,
  `cmi_dtcad` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmi_dtalter` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cmi_log` LONGTEXT NULL,
  PRIMARY KEY (`cmi_id`),
  INDEX `idx_cmi_idconhecimento` (`cmi_idconhecimento`),
  INDEX `idx_cmi_status` (`cmi_status`),
  CONSTRAINT `fk_icv_conhecimento_midia_conhecimento`
    FOREIGN KEY (`cmi_idconhecimento`)
    REFERENCES `icv_conhecimento` (`con_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;



-- Dados iniciais
-- 01 - BASE INICIAL DO SISTEMA
-- Execute este script após o create 01 - estrutura do sistema.sql e o create 02 - regra de negocio.sql.

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `icv_usuario_empresa`;
TRUNCATE TABLE `icv_usuario_nivelacesso`;
TRUNCATE TABLE `icv_usuario_organizacao`;
TRUNCATE TABLE `icv_permissaorelatorio`;
TRUNCATE TABLE `icv_permissao`;
TRUNCATE TABLE `icv_conhecimento_midia`;
TRUNCATE TABLE `icv_conhecimento`;
TRUNCATE TABLE `icv_conhecimento_categoria`;
TRUNCATE TABLE `icv_usuario`;
TRUNCATE TABLE `icv_nivelacesso`;
TRUNCATE TABLE `icv_empresa`;
TRUNCATE TABLE `icv_endereco`;
TRUNCATE TABLE `icv_naturalidade`;
TRUNCATE TABLE `icv_naturezajuridica`;
TRUNCATE TABLE `icv_sexo`;
TRUNCATE TABLE `icv_estadocivil`;
TRUNCATE TABLE `icv_organizacao`;
TRUNCATE TABLE `icv_projeto`;
TRUNCATE TABLE `icv_contato`;
TRUNCATE TABLE `icv_arquivo`;
TRUNCATE TABLE `icv_email_enviado`;
TRUNCATE TABLE `icv_email_template`;
TRUNCATE TABLE `icv_plano_contratado`;
TRUNCATE TABLE `icv_plano`;
TRUNCATE TABLE `icv_palavra_chave`;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- PROJETO simpleweb
-- -----------------------------------------------------
SET @termos_uso_simpleweb = '<h2>Termos de uso</h2><p>Estes Termos de uso estabelecem as condições para utilização da plataforma simpleweb por empresas, profissionais e organizações.</p><h3>1. Utilização da plataforma</h3><p>A organização deve fornecer informações verdadeiras, manter seus dados atualizados e utilizar o sistema de acordo com a legislação aplicável.</p><h3>2. Conta e responsabilidades</h3><p>O usuário responsável pela contratação responde pela administração da organização, pela proteção de suas credenciais e pelas informações inseridas no simpleweb.</p><h3>3. Planos e pagamentos</h3><p>Os recursos disponíveis dependem do plano contratado e de sua situação financeira. Planos comerciais podem exigir confirmação de pagamento para ativação ou continuidade do acesso.</p><h3>4. Atendimento e alterações</h3><p>O simpleweb poderá aprimorar seus recursos e atualizar estes termos. A versão aceita será preservada na contratação correspondente.</p><p>Ao contratar, a organização declara que leu e aceita estes Termos de uso.</p>';
SET @aviso_privacidade_simpleweb = '<h2>Aviso de privacidade</h2><p>O simpleweb trata dados pessoais para permitir o cadastro, a autenticação, a administração das organizações, empresas, clientes e animais e a execução dos serviços contratados.</p><h3>1. Dados tratados</h3><p>Podem ser tratados dados de identificação, contato, acesso, organização, empresa, cliente, animal e informações necessárias à utilização dos recursos da plataforma.</p><h3>2. Finalidades</h3><p>Os dados são utilizados para criar e administrar contas, disponibilizar funcionalidades, processar pagamentos, prestar suporte, manter a segurança e cumprir obrigações legais.</p><h3>3. Compartilhamento</h3><p>Quando necessário, os dados poderão ser compartilhados com prestadores essenciais à operação, como serviços de hospedagem, comunicação e pagamento, observadas as medidas de segurança aplicáveis.</p><h3>4. Direitos do titular</h3><p>O titular pode solicitar informações sobre o tratamento de seus dados e exercer os direitos previstos na legislação aplicável pelos canais de atendimento do simpleweb.</p><h3>5. Atualizações</h3><p>Este aviso poderá ser atualizado. A versão vigente será identificada no momento da contratação ou aceite.</p>';

INSERT INTO `icv_projeto` (
  `prj_id`,
  `prj_nome`,
  `prj_titulo`,
  `prj_descricao`,
  `prj_termosuso`,
  `prj_termosusoversao`,
  `prj_avisoprivacidade`,
  `prj_avisoprivacidadeversao`,
  `prj_email_header`,
  `prj_email_footer`,
  `prj_dominio`,
  `prj_url_central`,
  `prj_gateway`,
  `prj_gateway_sk`,
  `prj_gateway_pk`,
  `prj_gateway_configuracao`,
  `prj_logo`,
  `prj_favicon`,
  `prj_logonegativa`,
  `prj_log`
) VALUES (
  1,
  'simpleweb',
  'simpleweb',
  'Plataforma SaaS de gestão, presença digital e conexão para empresas do segmento de animais.',
  @termos_uso_simpleweb,
  CONCAT(DATE_FORMAT(NOW(), '%d%m%Y%H%i%s'), '-', LEFT(SHA2(@termos_uso_simpleweb, 256), 32)),
  @aviso_privacidade_simpleweb,
  CONCAT(DATE_FORMAT(NOW(), '%d%m%Y%H%i%s'), '-', LEFT(SHA2(@aviso_privacidade_simpleweb, 256), 32)),
  '<div style="max-width:640px;margin:0 auto;background:#173d7a;border-radius:14px 14px 0 0;padding:28px 32px;color:#ffffff;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="vertical-align:middle;"><img src="{projeto.logo}" alt="{projeto.titulo}" style="display:block;max-width:180px;max-height:58px;width:auto;height:auto;"></td><td style="vertical-align:middle;text-align:right;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#dbe8ff;">{projeto.titulo}<br><span style="font-size:12px;">GestÃ£o para empresas do segmento de animais</span></td></tr></table></div>',
  '<div style="max-width:640px;margin:0 auto;border-radius:0 0 14px 14px;padding:22px 32px;background:#f7f9fc;border-top:1px solid #e7edf5;color:#718096;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;text-align:center;"><strong style="color:#40577a;">{projeto.titulo}</strong><br>Conectando empresas, profissionais e clientes.<br><a href="{projeto.url}" style="color:#2f5db3;text-decoration:none;">{projeto.url}</a> Â· <a href="mailto:contato@simpleweb.example" style="color:#2f5db3;text-decoration:none;">contato@simpleweb.example</a><br><span>Este Ã© um e-mail automÃ¡tico. NÃ£o responda diretamente a esta mensagem.</span></div>',
  'https://simpleweb.example',
  'https://simpleweb.example',
  'Pagar.me',
  'sk_test_755a3ac3f0da4f229a62e025f3234df9',
  'pk_test_jAMW1dt0ZIeXn7q5',
  '{"rules": [{"type": "percentage", "value": 100, "options": {"liable": true, "charge_remainder_fee": true, "charge_processing_fee": true}, "recipient_id": "re_cmhxeexhm015m0m9t33njdyq6"}], "enabled": true, "account_id": "acc_Lxm2KGLIOFxW2dXz", "environment": "test"}',
  'storage/global/logo.png',
  'storage/global/favicon.png',
  'storage/global/logonegativa.png',
  '[]'
);

-- -----------------------------------------------------
-- TEMPLATE DE E-MAIL DO SISTEMA
-- -----------------------------------------------------
INSERT INTO `icv_email_template` (
  `emt_id`, `emt_idorganizacao`, `emt_idempresa`, `emt_templateprojeto`,
  `emt_palavrachave`, `emt_nome`, `emt_status`, `emt_statusclass`,
  `emt_host`, `emt_user`, `emt_pass`, `emt_port`, `emt_remetentenome`,
  `emt_remetenteemail`, `emt_replyto`, `emt_title`, `emt_lang`, `emt_charset`,
  `emt_conteudo`, `emt_log`
) VALUES (
  1, 0, 0, 'nova_contratacao',
  '["projeto","organizacao","empresa","usuario"]',
  'Nova contratação', 'Ativo', 'success', 'smtp.hostinger.com',
  'contato@simpleweb.example', NULL, 587, 'simpleweb',
  'contato@simpleweb.example', 'contato@simpleweb.example',
  'Sua organização está pronta no simpleweb', 'pt_br', 'UTF-8',
  '<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body style="margin:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#24344d;"><div style="max-width:640px;margin:32px auto;background:#ffffff;border:1px solid #dbe4f0;border-radius:12px;overflow:hidden;"><div style="padding:28px 32px;background:#173d7a;color:#ffffff;"><h1 style="margin:0;font-size:25px;">Bem-vindo ao simpleweb</h1><p style="margin:8px 0 0;font-size:15px;opacity:.9;">Conectando empresas, profissionais e clientes.</p></div><div style="padding:32px;"><p style="margin:0 0 18px;font-size:16px;">Olá, <strong>{usuario.nome}</strong>!</p><p style="margin:0 0 16px;line-height:1.7;">É uma alegria receber você no simpleweb, a plataforma criada para ajudar empresas e profissionais do segmento de animais a organizar sua operação, atender clientes e cuidar dos animais.</p><p style="margin:0 0 16px;line-height:1.7;">Sua organização, <strong>{organizacao.nomefantasia}</strong>, está pronta para ser administrada. A empresa <strong>{empresa.nomefantasia}</strong> já foi preparada para você começar.</p><p style="margin:0 0 24px;line-height:1.7;">Acesse o sistema pelo botão abaixo e comece sua jornada de gestão com simplicidade, clareza e propósito.</p><div style="text-align:center;margin:28px 0;"><a href="{projeto.url}/login" style="display:inline-block;padding:14px 24px;background:#2f5db3;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:bold;">Acessar o simpleweb</a></div><p style="margin:0 0 10px;font-size:14px;line-height:1.6;">Se preferir, acesse diretamente:</p><p style="margin:0 0 24px;font-size:14px;"><a href="{projeto.url}/login" style="color:#2f5db3;">{projeto.url}/login</a></p><p style="margin:0;font-size:13px;color:#66758a;line-height:1.6;">Guarde este e-mail para consultar o endereço de acesso sempre que precisar.</p></div><div style="padding:20px 32px;background:#f7f9fc;border-top:1px solid #e7edf5;color:#718096;font-size:12px;text-align:center;">{projeto.titulo} — gestão para empresas do segmento de animais.</div></div></body></html>',
  '[]'
);

UPDATE `icv_projeto`
SET
  `prj_email_header` = '<div style="max-width:640px;margin:0 auto;background:#173d7a;border-radius:14px 14px 0 0;padding:28px 32px;color:#ffffff;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="vertical-align:middle;"><img src="{projeto.logo}" alt="{projeto.titulo}" style="display:block;max-width:180px;max-height:58px;width:auto;height:auto;"></td><td style="vertical-align:middle;text-align:right;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#dbe8ff;">{projeto.titulo}<br><span style="font-size:12px;">Gest&atilde;o para empresas do segmento de animais</span></td></tr></table></div>',
  `prj_email_footer` = '<div style="max-width:640px;margin:0 auto;border-radius:0 0 14px 14px;padding:22px 32px;background:#f7f9fc;border-top:1px solid #e7edf5;color:#718096;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;text-align:center;"><strong style="color:#40577a;">{projeto.titulo}</strong><br>Conectando empresas, profissionais e clientes.<br><a href="{projeto.url}" style="color:#2f5db3;text-decoration:none;">{projeto.url}</a> &middot; <a href="mailto:contato@simpleweb.example" style="color:#2f5db3;text-decoration:none;">contato@simpleweb.example</a><br><span>Este &eacute; um e-mail autom&aacute;tico. N&atilde;o responda diretamente a esta mensagem.</span></div>'
WHERE `prj_id` = 1;

UPDATE `icv_email_template`
SET `emt_conteudo` = '<div style="font-family:Arial,Helvetica,sans-serif;color:#24344d;"><p style="margin:0 0 18px;font-size:17px;">Ol&aacute;, <strong>{usuario.nome}</strong>!</p><p style="margin:0 0 16px;line-height:1.7;">&Eacute; uma alegria receber voc&ecirc; no simpleweb, a plataforma criada para ajudar empresas e profissionais do segmento de animais a organizar sua opera&ccedil;&atilde;o, atender clientes e cuidar dos animais.</p><p style="margin:0 0 16px;line-height:1.7;">Sua organiza&ccedil;&atilde;o, <strong>{organizacao.nomefantasia}</strong>, est&aacute; pronta para ser administrada. A empresa <strong>{empresa.nomefantasia}</strong> j&aacute; foi preparada para voc&ecirc; come&ccedil;ar.</p><p style="margin:0 0 24px;line-height:1.7;">Acesse o sistema pelo bot&atilde;o abaixo e comece sua jornada de gest&atilde;o com simplicidade, clareza e prop&oacute;sito.</p><div style="text-align:center;margin:28px 0;"><a href="{projeto.url}/login" style="display:inline-block;padding:14px 24px;background:#2f5db3;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:bold;">Acessar o simpleweb</a></div><p style="margin:0 0 10px;font-size:14px;line-height:1.6;">Se preferir, acesse diretamente:</p><p style="margin:0 0 24px;font-size:14px;"><a href="{projeto.url}/login" style="color:#2f5db3;">{projeto.url}/login</a></p><p style="margin:0;font-size:13px;color:#66758a;line-height:1.6;">Guarde este e-mail para consultar o endere&ccedil;o de acesso sempre que precisar.</p></div>'
WHERE `emt_id` = 1 AND `emt_templateprojeto` = 'nova_contratacao';

INSERT INTO `icv_email_template` (
  `emt_id`, `emt_idorganizacao`, `emt_idempresa`, `emt_templateprojeto`,
  `emt_palavrachave`, `emt_nome`, `emt_status`, `emt_statusclass`,
  `emt_host`, `emt_user`, `emt_pass`, `emt_port`, `emt_remetentenome`,
  `emt_remetenteemail`, `emt_replyto`, `emt_title`, `emt_lang`, `emt_charset`,
  `emt_conteudo`, `emt_log`
) VALUES (
  2, 0, 0, 'pagamento_confirmado',
  '["projeto","organizacao","empresa","usuario","plano","contratacao","pagamento"]',
  'Pagamento confirmado', 'Ativo', 'success', 'smtp.hostinger.com',
  'contato@simpleweb.example', NULL, 587, 'simpleweb',
  'contato@simpleweb.example', 'contato@simpleweb.example',
  'Pagamento confirmado — {organizacao.nomefantasia}', 'pt_br', 'UTF-8',
  '<div style="font-family:Arial,Helvetica,sans-serif;color:#24344d;"><p style="margin:0 0 18px;font-size:17px;">Ol&aacute;, <strong>{usuario.nome}</strong>!</p><p style="margin:0 0 16px;line-height:1.7;">Recebemos a confirma&ccedil;&atilde;o do pagamento da organiza&ccedil;&atilde;o <strong>{organizacao.nomefantasia}</strong>.</p><p style="margin:0 0 20px;line-height:1.7;">A contrata&ccedil;&atilde;o do plano <strong>{plano.nome}</strong> foi confirmada com sucesso.</p><div style="margin:24px 0;padding:18px;background:#f7f9fc;border:1px solid #dbe4f0;border-radius:10px;"><p style="margin:0 0 8px;"><strong>Plano:</strong> {plano.nome}</p><p style="margin:0 0 8px;"><strong>Ciclo:</strong> {contratacao.ciclo}</p><p style="margin:0 0 8px;"><strong>Valor pago:</strong> R$ {contratacao.valor}</p><p style="margin:0 0 8px;"><strong>Pagamento:</strong> {contratacao.dtpagamento}</p><p style="margin:0;"><strong>Vig&ecirc;ncia:</strong> {contratacao.dtinicio} at&eacute; {contratacao.dtfim}</p></div><p style="margin:0 0 24px;line-height:1.7;">Sua empresa j&aacute; pode acessar os recursos liberados para o plano contratado.</p><div style="text-align:center;margin:28px 0;"><a href="{projeto.url}/login" style="display:inline-block;padding:14px 24px;background:#2f5db3;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:bold;">Acessar o simpleweb</a></div><p style="margin:0;font-size:13px;color:#66758a;line-height:1.6;">Se precisar de ajuda, entre em contato com a equipe simpleweb.</p></div>',
  '[]'
);

-- -----------------------------------------------------
-- ORGANIZAÇÃO CENTRAL DA PLATAFORMA
-- -----------------------------------------------------
INSERT INTO `icv_organizacao` (
  `org_id`,
  `org_idusuarioresponsavel`,
  `org_chave`,
  `org_cnpjcpf`,
  `org_razaosocial`,
  `org_nomefantasia`,
  `org_observacao`,
  `org_dtnasc`,
  `org_idnaturezajuridica`,
  `org_descricao`,
  `org_logo`,
  `org_planodefundo`,
  `org_home`,
  `org_urlamigavel`,
  `org_webapp_status`,
  `org_webapp_conteudo`,
  `org_parametro`,
  `org_log`
) VALUES (
  1,
  1,
  'ORG_simpleweb',
  '40190325000107',
  'simpleweb ORG',
  'simpleweb ORG',
  'Organização central da plataforma simpleweb.',
  '2026-01-01',
  NULL,
  'Organização administrativa e técnica da plataforma simpleweb.',
  'storage/global/simpleweb.png',
  'storage/global/planodefundo.jpg',
  'dashboard',
  '40190325000107',
  'Publica',
  '<section><h1>simpleweb ORG</h1><p>Bem-vindo à página oficial da organização simpleweb ORG.</p><p>Acompanhe informações, serviços e novidades da nossa comunidade.</p></section>',
  '{}',
  '[]'
);

-- -----------------------------------------------------
-- Os cadastros básicos utilizados no primeiro acesso são criados pelo assistente.
-- Por isso, não são semeados aqui para evitar duplicidade entre organização e empresa.
-- -----------------------------------------------------
-- -----------------------------------------------------
-- EMPRESA PRINCIPAL DA ORGANIZAÇÃO CENTRAL
-- -----------------------------------------------------
INSERT INTO `icv_empresa` (
  `emp_id`,
  `emp_chave`,
  `emp_idorganizacao`,
  `emp_cnpjcpf`,
  `emp_razaosocial`,
  `emp_nomefantasia`,
  `emp_observacao`,
  `emp_dtnasc`,
  `emp_idnaturezajuridica`,
  `emp_descricao`,
  `emp_logo`,
  `emp_planodefundo`,
  `emp_urlamigavel`,
  `emp_webapp_status`,
  `emp_webapp_conteudo`,
  `emp_configuracao_inicial`,
  `emp_log`
) VALUES (
  1,
  'EMP_simpleweb_BH',
  1,
  '40190325000107',
  'simpleweb BH',
  'simpleweb BH',
  'Empresa principal da organização central do simpleweb.',
  '2026-01-01',
  NULL,
  'Ambiente operacional principal da plataforma simpleweb.',
  'storage/global/simpleweb.png',
  'storage/global/planodefundo.jpg',
  'teste',
  'Publica',
  '<section><h1>simpleweb BH</h1><p>Bem-vindo à página oficial da empresa simpleweb BH.</p><p>Conheça nossos serviços e acompanhe as novidades.</p></section>',
  0,
  '[]'
);

INSERT INTO `icv_contato` (
  `ctt_id`, `ctt_tipo`, `ctt_identificacao`, `ctt_conteudo`,
  `ctt_idorganizacao`, `ctt_idempresa`, `ctt_idorigem`, `ctt_origem`, `ctt_log`
) VALUES
  (1, 'Email', 'Comercial', 'contato@simpleweb.example', 1, 1, 1, 'empresa', '[]'),
  (2, 'WhatsApp', 'Comercial', '(31) 99999-0001', 1, 1, 1, 'empresa', '[]'),
  (3, 'Site', 'Institucional', 'https://simpleweb.example', 1, 1, 1, 'empresa', '[]');

INSERT INTO `icv_endereco` (
  `end_id`, `end_principal`, `end_nome`, `end_cep`, `end_logradouro`, `end_numero`,
  `end_bairro`, `end_cidade`, `end_estado`, `end_uf`, `end_idorganizacao`,
  `end_idempresa`, `end_idorigem`, `end_origem`, `end_log`
) VALUES (
  1, 1, 'Endereço principal', '30110-000', 'Rua simpleweb', '100',
  'Centro', 'Belo Horizonte', 'Minas Gerais', 'MG', 1, 1, 1, 'empresa', '[]'
), (
  2, 1, 'Endereço institucional do projeto', '30110-000', 'Rua simpleweb', '100',
  'Centro', 'Belo Horizonte', 'Minas Gerais', 'MG', 0, 0, 1, 'projeto', '[]'
);

-- -----------------------------------------------------
-- NÍVEL, PERMISSÕES E USUÁRIO ADMINISTRADOR DA PLATAFORMA (ESCOPO PROJETO)
-- -----------------------------------------------------
INSERT INTO `icv_nivelacesso` (
  `nva_id`, `nva_idorganizacao`, `nva_idempresa`, `nva_escopo`, `nva_status`,
  `nva_statusclass`, `nva_imutavel`, `nva_nome`, `nva_descricao`, `nva_log`
) VALUES (
  1, 0, 0, 'Projeto', 'Ativo', 'success', 1,
  'Administrador do simpleweb',
  'Administrador técnico e comercial da plataforma simpleweb.',
  '[]'
);

INSERT INTO `icv_permissao` (
  `per_idorganizacao`, `per_idempresa`, `per_idnivelacesso`, `per_status`,
  `per_statusclass`, `per_nome`, `per_descricao`, `per_nivel`, `per_log`
) VALUES
  (0, 0, 1, 'Ativo', 'success', 'contexto.trocar', 'Permissão para trocar de contexto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.visualizar', 'Permissão para visualizar o projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.atualizar', 'Permissão para atualizar o projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.plano.visualizar', 'Permissão para visualizar os planos do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.plano.atualizar', 'Permissão para atualizar os planos do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.contratacao.visualizar', 'Permissão para visualizar as contratações do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.contratacao.atualizar', 'Permissão para atualizar as contratações do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.palavrachave.visualizar', 'Permissão para visualizar palavras-chave do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.palavrachave.atualizar', 'Permissão para atualizar palavras-chave do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.email.visualizar', 'Permissão para visualizar templates de e-mail do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.email.atualizar', 'Permissão para atualizar templates de e-mail do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.conhecimento.visualizar', 'Permissão para visualizar os registros de conhecimento do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.conhecimento.criar', 'Permissão para criar registros de conhecimento do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'projeto.conhecimento.atualizar', 'Permissão para atualizar os registros de conhecimento do projeto.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'organizacao.visualizar', 'Permissão para visualizar organizações.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'organizacao.criar', 'Permissão para criar organizações.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'organizacao.atualizar', 'Permissão para atualizar organizações.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'organizacao.plano.contratar', 'Permissão para consultar e renovar planos contratados da organização.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'empresa.visualizar', 'Permissão para visualizar empresas e profissionais.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'empresa.criar', 'Permissão para criar empresas e profissionais.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'empresa.atualizar', 'Permissão para atualizar empresas e profissionais.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'usuario.visualizar', 'Permissão para visualizar usuários.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'usuario.criar', 'Permissão para criar usuários.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'usuario.atualizar', 'Permissão para atualizar usuários.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'nivelacesso.visualizar', 'Permissão para visualizar níveis de acesso.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'nivelacesso.criar', 'Permissão para criar níveis de acesso.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'nivelacesso.atualizar', 'Permissão para atualizar níveis de acesso.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'endereco.visualizar', 'Permissão para visualizar endereços.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'endereco.criar', 'Permissão para criar endereços.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'endereco.atualizar', 'Permissão para atualizar endereços.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'endereco.excluir', 'Permissão para excluir endereços.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'contato.visualizar', 'Permissão para visualizar contatos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'contato.criar', 'Permissão para criar contatos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'contato.atualizar', 'Permissão para atualizar contatos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'contato.excluir', 'Permissão para excluir contatos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.visualizar', 'Permissão para visualizar o financeiro.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.dashboard', 'Permissão para visualizar o dashboard financeiro.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.relatorio', 'Permissão para visualizar relatórios financeiros.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.lancamento.visualizar', 'Permissão para visualizar lançamentos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.lancamento.criar', 'Permissão para criar lançamentos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.lancamento.atualizar', 'Permissão para atualizar lançamentos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.lancamento.cancelar', 'Permissão para cancelar lançamentos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.lancamento.baixar', 'Permissão para baixar lançamentos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.lancamento.estornar', 'Permissão para estornar lançamentos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.transferencia.visualizar', 'Permissão para visualizar transferências financeiras.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.transferencia.criar', 'Permissão para criar transferências financeiras.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.transferencia.cancelar', 'Permissão para estornar transferências financeiras.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.basico.visualizar', 'Permissão para visualizar cadastros financeiros básicos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.basico.criar', 'Permissão para criar cadastros financeiros básicos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'financeiro.basico.atualizar', 'Permissão para atualizar cadastros financeiros básicos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'titulo.visualizar', 'Permissão para visualizar títulos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'titulo.criar', 'Permissão para criar títulos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'titulo.atualizar', 'Permissão para atualizar títulos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'titulo.cancelar', 'Permissão para cancelar títulos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'titulo.confirmar', 'Permissão para confirmar títulos.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'fornecedor.visualizar', 'Permissão para visualizar fornecedores.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'fornecedor.criar', 'Permissão para criar fornecedores.', 1, '[]'),
  (0, 0, 1, 'Ativo', 'success', 'fornecedor.atualizar', 'Permissão para atualizar fornecedores.', 1, '[]');

-- O nível de acesso 1 é imutável e representa o superadministrador da
-- plataforma: todas as permissões cadastradas para ele devem iniciar ativas.
UPDATE `icv_permissao`
SET `per_status` = 'Ativo',
    `per_statusclass` = 'success'
WHERE `per_idnivelacesso` = 1;

INSERT INTO `icv_permissaorelatorio` (
  `pmr_idorganizacao`, `pmr_idempresa`, `pmr_idnivelacesso`, `pmr_status`,
  `pmr_statusclass`, `pmr_dir`, `pmr_nome`, `pmr_descricao`, `pmr_log`
) VALUES
  (0, 0, 1, 'Ativo', 'success', 'dashboard', 'Dashboard', 'Acesso ao dashboard principal.', '[]');

INSERT INTO `icv_usuario` (
  `usu_id`, `usu_chave`, `usu_idorganizacao`, `usu_idempresa`, `usu_cnpjcpf`,
  `usu_razaosocial`, `usu_nomefantasia`, `usu_idsexo`, `usu_idestadocivil`,
  `usu_idnaturalidade`, `usu_niveisacesso`, `usu_email`, `usu_celular`,
  `usu_username`, `usu_senha`, `usu_logo`, `usu_planodefundo`, `usu_newsletter`, `usu_dtnewsletter`, `usu_log`
) VALUES (
  1,
  'USU_simpleweb_ADMIN',
  1,
  1,
  '11024565602',
  'Pedro',
  'Pedro',
  NULL,
  NULL,
  NULL,
  '[{"nva_id":1,"nva_nome":"Administrador do simpleweb"}]',
  'pedro.hsa92@gmail.com',
  '31973423128',
  'admin',
  '$2y$10$2LHo4rBbZggClPvjKCStQeS.6yVROnJ86VxqN6C3fIRI7nzj3hFcm',
  'storage/global/perfil.png',
  'storage/global/planodefundo.jpg',
  0,
  NULL,
  '[]'
);

INSERT INTO `icv_usuario_organizacao` (
  `uor_id`, `uor_idusuario`, `uor_idorganizacao`, `uor_principal`, `uor_log`
) VALUES
  (1, 1, 1, 1, '[]');

INSERT INTO `icv_usuario_nivelacesso` (
  `una_id`, `una_idusuario`, `una_idorganizacao`, `una_idempresa`, `una_idnivelacesso`, `una_principal`, `una_log`
) VALUES
  (1, 1, NULL, NULL, 1, 1, '[]');

INSERT INTO `icv_usuario_empresa` (
  `uem_id`, `uem_idorganizacao`, `uem_idusuario`, `uem_idempresa`, `uem_principal`, `uem_log`
) VALUES (1, 1, 1, 1, 1, '[]');

UPDATE `icv_usuario`
SET `usu_idorganizacaoprincipal` = 1,
    `usu_idempresaprincipal` = 1
WHERE `usu_id` = 1;

-- -----------------------------------------------------
-- PARAMETROS DOS PLANOS
-- -----------------------------------------------------
SET @param_interno = '{
  "usuario": { "status": true, "limite": 0, "arquivo": { "limite": 0 }, "contato": { "limite": 0 }, "endereco": { "limite": 0 } },
  "empresa": { "status": true, "limite": 0, "arquivo": { "limite": 0 }, "contato": { "limite": 0 }, "endereco": { "limite": 0 } },
  "nivelacesso": { "status": true, "limite": 0 },
  "storage": { "limiteMb": 0 }
}';

SET @param_descoberta = '{
  "usuario": { "status": true, "limite": 2, "arquivo": { "limite": 1 }, "contato": { "limite": 1 }, "endereco": { "limite": 1 } },
  "empresa": { "status": true, "limite": 2, "arquivo": { "limite": 1 }, "contato": { "limite": 2 }, "endereco": { "limite": 1 } },
  "nivelacesso": { "status": true, "limite": 2 },
  "storage": { "limiteMb": 250 }
}';

SET @param_essencial = '{
  "usuario": { "status": true, "limite": 5, "arquivo": { "limite": 2 }, "contato": { "limite": 2 }, "endereco": { "limite": 1 } },
  "empresa": { "status": true, "limite": 3, "arquivo": { "limite": 2 }, "contato": { "limite": 2 }, "endereco": { "limite": 2 } },
  "nivelacesso": { "status": true, "limite": 5 },
  "storage": { "limiteMb": 1000 }
}';

SET @param_crescimento = '{
  "usuario": { "status": true, "limite": 15, "arquivo": { "limite": 3 }, "contato": { "limite": 3 }, "endereco": { "limite": 1 } },
  "empresa": { "status": true, "limite": 5, "arquivo": { "limite": 3 }, "contato": { "limite": 3 }, "endereco": { "limite": 3 } },
  "nivelacesso": { "status": true, "limite": 15 },
  "storage": { "limiteMb": 3000 }
}';

SET @param_profissional = '{
  "usuario": { "status": true, "limite": 25, "arquivo": { "limite": 5 }, "contato": { "limite": 5 }, "endereco": { "limite": 1 } },
  "empresa": { "status": true, "limite": 10, "arquivo": { "limite": 5 }, "contato": { "limite": 5 }, "endereco": { "limite": 5 } },
  "nivelacesso": { "status": true, "limite": 25 },
  "storage": { "limiteMb": 5000 }
}';

SET @param_dedicado = '{
  "usuario": { "status": true, "limite": 0, "arquivo": { "limite": 0 }, "contato": { "limite": 0 }, "endereco": { "limite": 0 } },
  "empresa": { "status": true, "limite": 0, "arquivo": { "limite": 0 }, "contato": { "limite": 0 }, "endereco": { "limite": 0 } },
  "nivelacesso": { "status": true, "limite": 0 },
  "storage": { "limiteMb": 0 }
}';

-- -----------------------------------------------------
-- PLANOS INICIAIS
-- -----------------------------------------------------
INSERT INTO `icv_plano` (
  `pla_id`, `pla_tipo`, `pla_nome`, `pla_descricaoresumida`, `pla_descricao`,
  `pla_rotulo`, `pla_selo`, `pla_beneficios`, `pla_textobotao`,
  `pla_ambiente`, `pla_valormensal`, `pla_valoranual`,
  `pla_valormensal_original`, `pla_valoranual_original`,
  `pla_dias_teste`, `pla_parametro`, `pla_contatocomercial`,
  `pla_ordem`, `pla_log`
) VALUES
  (1, 'Interno', 'simpleweb Interno', 'Plano interno da administracao do simpleweb.', '<p>Plano interno da organização administradora da plataforma.</p>',
    'Uso interno', NULL, '["Configuracao central","Administracao da plataforma","Recursos internos"]', NULL,
    'Compartilhado', NULL, NULL, NULL, NULL, NULL, @param_interno, NULL, 1, '[]'),
  (2, 'Teste', 'Descoberta', 'Conheça o simpleweb por 30 dias, sem custo.', '<p>Um primeiro passo simples para conhecer o simpleweb e organizar a rotina do negócio.</p><p>Durante o período de teste, sua equipe pode experimentar os recursos essenciais antes de decidir como continuar.</p>',
    'Para comecar', NULL, '["Presença digital da empresa","Cadastro inicial","Recursos essenciais"]', 'Quero experimentar',
    'Compartilhado', NULL, NULL, NULL, NULL, 30, @param_descoberta, NULL, 2, '[]'),
  (3, 'Comercial', 'Essencial', 'Uma base sólida para cuidar da rotina da empresa.', '<p>O plano Essencial reúne os recursos necessários para começar a organizar clientes, animais, serviços e agenda.</p><p>Uma escolha equilibrada para empresas que desejam simplicidade e clareza na operação.</p>',
    'Para negócios', NULL, '["Clientes e contatos","Serviços e agenda","Gestao por contexto"]', 'Comecar agora',
    'Compartilhado', 99.90, 999.00, 119.90, 1199.00, NULL, @param_essencial, NULL, 3, '[]'),
  (4, 'Comercial', 'Crescimento', 'Mais automação e visão para uma empresa em expansão.', '<p>O plano Crescimento amplia os recursos de gestão para empresas que estão crescendo e precisam acompanhar melhor sua operação.</p><p>Mais organização, contexto e visão para apoiar novas possibilidades.</p>',
    'Para crescer', 'Mais escolhido', '["Mais automacao","Visao da operacao","Recursos conectados"]', 'Comecar agora',
    'Compartilhado', 169.90, 1699.00, 199.90, 1999.00, NULL, @param_crescimento, NULL, 4, '[]'),
  (5, 'Comercial', 'Profissional', 'Recursos completos para uma operação conectada.', '<p>O plano Profissional oferece uma experiência mais completa para empresas com maior volume, múltiplas unidades e processos integrados.</p><p>Inclui recursos avançados para apoiar decisões e dar mais fluidez ao dia a dia.</p>',
    'Para operacoes conectadas', NULL, '["Todos os recursos essenciais","Financeiro e relatorios","Suporte prioritario"]', 'Comecar agora',
    'Compartilhado', 249.90, 2499.00, 299.90, 2999.00, NULL, @param_profissional, NULL, 5, '[]'),
  (6, 'Dedicado', 'Dedicado', 'Uma experiência exclusiva, personalizada e preparada para sua escala.', '<p>O ambiente Dedicado leva a experiência do simpleweb para uma estrutura exclusiva, com personalização, desempenho e acompanhamento próximos.</p><p>Converse com nosso time comercial para desenhar a melhor configuração para sua operação.</p>',
    'Para operacoes unicas', NULL, '["Ambiente dedicado","Alta personalizacao","Acompanhamento proximo"]', 'Falar com a equipe',
    'Dedicado', NULL, NULL, NULL, NULL, NULL, @param_dedicado,
    '[{"nome":"Equipe Comercial simpleweb","celular":"(31) 3333-2026","email":"contato@simpleweb.example"},{"nome":"Atendimento simpleweb","celular":"(31) 3333-2027","email":"contato@simpleweb.example"}]',
    6, '[]');

-- -----------------------------------------------------

-- -----------------------------------------------------
-- CONTRATAÇÃO INTERNA DA PLATAFORMA
-- -----------------------------------------------------
INSERT INTO `icv_plano_contratado` (
  `plc_id`, `plc_idorganizacao`, `plc_idplano`, `plc_idusuariocontratante`,
  `plc_grupo_contratacao`, `plc_status`, `plc_statusclass`,
  `plc_statusfinanceiro`, `plc_origem`, `plc_ciclo`, `plc_ambiente`,
  `plc_dtinicio`, `plc_dtfim`, `plc_dtvencimento`, `plc_dtpagamento`,
  `plc_valor`, `plc_observacao`, `plc_aceite`, `plc_termosuso`,
  `plc_termosusoversao`, `plc_avisoprivacidade`, `plc_avisoprivacidadeversao`,
  `plc_assinatura`, `plc_parametro`, `plc_log`
) VALUES (
  1, 1, 1, 1, 'PLC_simpleweb_INTERNO_202607',
  'Ativo', 'success', 'Isento', 'Sistema', 'Mensal', 'Compartilhado',
  '2026-01-01 00:00:00', '2027-12-31 23:59:59', NULL, NULL, 0.00,
  'Plano interno da organizacao central do simpleweb.',
  0, NULL, NULL, NULL, NULL, NULL, @param_interno, '[]'
);

-- Script de palavras-chave disponíveis para mensagens do sistema.
-- Os registros representam campos autorizados pelo catálogo backend.

INSERT INTO `icv_palavra_chave` (
  `pch_id`, `pch_grupo`, `pch_palavra`, `pch_chave`, `pch_descricao`,
  `pch_status`, `pch_statusclass`, `pch_log`
) VALUES
  (1, 'projeto', 'projeto.nome', 'prj_nome', 'Nome técnico do projeto', 'Ativo', 'success', '[]'),
  (2, 'projeto', 'projeto.titulo', 'prj_titulo', 'Título público do projeto', 'Ativo', 'success', '[]'),
  (3, 'projeto', 'projeto.descricao', 'prj_descricao', 'Descrição do projeto', 'Ativo', 'success', '[]'),
  (4, 'projeto', 'projeto.dominio', 'prj_dominio', 'Domínio do projeto', 'Ativo', 'success', '[]'),
  (5, 'projeto', 'projeto.url', 'prj_url_central', 'URL principal do projeto', 'Ativo', 'success', '[]'),
  (6, 'projeto', 'projeto.status', 'prj_status', 'Status do projeto', 'Ativo', 'success', '[]'),
  (7, 'projeto', 'projeto.tipo', 'prj_tipo', 'Tipo de ambiente do projeto', 'Ativo', 'success', '[]'),

  (8, 'organizacao', 'organizacao.documento', 'org_cnpjcpf', 'Documento da organização', 'Ativo', 'success', '[]'),
  (9, 'organizacao', 'organizacao.nome', 'org_razaosocial', 'Razão social da organização', 'Ativo', 'success', '[]'),
  (10, 'organizacao', 'organizacao.nomefantasia', 'org_nomefantasia', 'Nome fantasia da organização', 'Ativo', 'success', '[]'),
  (11, 'organizacao', 'organizacao.descricao', 'org_descricao', 'Descrição da organização', 'Ativo', 'success', '[]'),
  (12, 'organizacao', 'organizacao.status', 'org_status', 'Status da organização', 'Ativo', 'success', '[]'),
  (13, 'organizacao', 'organizacao.ambiente', 'org_ambiente', 'Ambiente da organização', 'Ativo', 'success', '[]'),
  (14, 'organizacao', 'organizacao.logo', 'org_logo', 'Logo da organização', 'Ativo', 'success', '[]'),

  (15, 'empresa', 'empresa.documento', 'emp_cnpjcpf', 'Documento da empresa ou profissional', 'Ativo', 'success', '[]'),
  (16, 'empresa', 'empresa.nome', 'emp_razaosocial', 'Razão social da empresa ou profissional', 'Ativo', 'success', '[]'),
  (17, 'empresa', 'empresa.nomefantasia', 'emp_nomefantasia', 'Nome fantasia da empresa ou profissional', 'Ativo', 'success', '[]'),
  (18, 'empresa', 'empresa.descricao', 'emp_descricao', 'Descrição da empresa ou profissional', 'Ativo', 'success', '[]'),
  (19, 'empresa', 'empresa.status', 'emp_status', 'Status da empresa ou profissional', 'Ativo', 'success', '[]'),
  (20, 'empresa', 'empresa.tipo', 'emp_tipo', 'Tipo da empresa ou profissional', 'Ativo', 'success', '[]'),
  (21, 'empresa', 'empresa.logo', 'emp_logo', 'Logo da empresa ou profissional', 'Ativo', 'success', '[]'),

  (22, 'usuario', 'usuario.documento', 'usu_cnpjcpf', 'Documento do usuário', 'Ativo', 'success', '[]'),
  (23, 'usuario', 'usuario.nome', 'usu_razaosocial', 'Nome completo do usuário', 'Ativo', 'success', '[]'),
  (24, 'usuario', 'usuario.nomefantasia', 'usu_nomefantasia', 'Nome social do usuário', 'Ativo', 'success', '[]'),
  (25, 'usuario', 'usuario.email', 'usu_email', 'E-mail do usuário', 'Ativo', 'success', '[]'),
  (26, 'usuario', 'usuario.celular', 'usu_celular', 'Celular do usuário', 'Ativo', 'success', '[]'),
  (27, 'usuario', 'usuario.username', 'usu_username', 'Nome de usuário para acesso', 'Ativo', 'success', '[]'),
  (28, 'usuario', 'usuario.status', 'usu_status', 'Status do usuário', 'Ativo', 'success', '[]'),
  (29, 'usuario', 'usuario.newsletter', 'usu_newsletter', 'Aceite de comunicações do usuário', 'Ativo', 'success', '[]'),
  (30, 'usuario', 'usuario.idorganizacao', 'usu_idorganizacao', 'Identificador da organização do usuário', 'Ativo', 'success', '[]'),
  (31, 'usuario', 'usuario.idempresa', 'usu_idempresa', 'Identificador da empresa do usuário', 'Ativo', 'success', '[]'),

  (32, 'plano', 'plano.id', 'pla_id', 'Identificador do plano', 'Ativo', 'success', '[]'),
  (33, 'plano', 'plano.nome', 'pla_nome', 'Nome do plano', 'Ativo', 'success', '[]'),
  (34, 'plano', 'plano.tipo', 'pla_tipo', 'Tipo do plano', 'Ativo', 'success', '[]'),

  (35, 'contratacao', 'contratacao.id', 'plc_id', 'Identificador da contratação', 'Ativo', 'success', '[]'),
  (36, 'contratacao', 'contratacao.status', 'plc_status', 'Status da contratação', 'Ativo', 'success', '[]'),
  (37, 'contratacao', 'contratacao.statusfinanceiro', 'plc_statusfinanceiro', 'Status financeiro da contratação', 'Ativo', 'success', '[]'),
  (38, 'contratacao', 'contratacao.ciclo', 'plc_ciclo', 'Ciclo da contratação', 'Ativo', 'success', '[]'),
  (39, 'contratacao', 'contratacao.valor', 'plc_valor', 'Valor da contratação', 'Ativo', 'success', '[]'),
  (40, 'contratacao', 'contratacao.dtpagamento', 'plc_dtpagamento', 'Data do pagamento da contratação', 'Ativo', 'success', '[]'),
  (41, 'contratacao', 'contratacao.dtinicio', 'plc_dtinicio', 'Início da vigência da contratação', 'Ativo', 'success', '[]'),
  (42, 'contratacao', 'contratacao.dtfim', 'plc_dtfim', 'Fim da vigência da contratação', 'Ativo', 'success', '[]'),
  (43, 'contratacao', 'contratacao.dtvencimento', 'plc_dtvencimento', 'Vencimento da contratação', 'Ativo', 'success', '[]'),

  (44, 'pagamento', 'pagamento.status', 'plc_pagarme_status', 'Status do pagamento no gateway', 'Ativo', 'success', '[]'),
  (45, 'pagamento', 'pagamento.evento', 'plc_pagarme_ultimoevento', 'Último evento de pagamento recebido', 'Ativo', 'success', '[]');

-- -----------------------------------------------------
-- CENTRAL DE AJUDA: PRIMEIROS PASSOS DO SIMPLE WEB
-- Conteúdo inicial reutilizável entre projetos que adotam o fluxo Simple Web.
-- Registros localizados por nome/título para evitar duplicidade ao reexecutar
-- este bloco isoladamente. Não inclui mídias nem conteúdo específico de módulos.
-- -----------------------------------------------------
SET NAMES utf8mb4;
START TRANSACTION;

SET @categoria_nome = 'Primeiros passos';
INSERT INTO `icv_conhecimento_categoria` (`cct_status`,`cct_statusclass`,`cct_nome`,`cct_ordem`,`cct_log`)
SELECT 'Ativo','success',@categoria_nome,(SELECT COALESCE(MAX(cct_ordem),0)+1 FROM `icv_conhecimento_categoria`),'[]'
WHERE NOT EXISTS (SELECT 1 FROM `icv_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome);
SET @categoria_primeiros_passos = (SELECT cct_id FROM `icv_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome LIMIT 1);

SET @categoria_nome = 'Navegação e contexto';
INSERT INTO `icv_conhecimento_categoria` (`cct_status`,`cct_statusclass`,`cct_nome`,`cct_ordem`,`cct_log`)
SELECT 'Ativo','success',@categoria_nome,(SELECT COALESCE(MAX(cct_ordem),0)+1 FROM `icv_conhecimento_categoria`),'[]'
WHERE NOT EXISTS (SELECT 1 FROM `icv_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome);
SET @categoria_navegacao = (SELECT cct_id FROM `icv_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome LIMIT 1);

SET @categoria_nome = 'Cadastros e acessos';
INSERT INTO `icv_conhecimento_categoria` (`cct_status`,`cct_statusclass`,`cct_nome`,`cct_ordem`,`cct_log`)
SELECT 'Ativo','success',@categoria_nome,(SELECT COALESCE(MAX(cct_ordem),0)+1 FROM `icv_conhecimento_categoria`),'[]'
WHERE NOT EXISTS (SELECT 1 FROM `icv_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome);
SET @categoria_cadastros = (SELECT cct_id FROM `icv_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome LIMIT 1);

-- PRIMEIROS PASSOS
SET @con_categoria_id = @categoria_primeiros_passos;
SET @con_titulo = 'Primeiro acesso: conclua a configuração inicial';
SET @con_resumo = 'Revise as etapas de configuração para preparar o ambiente antes de iniciar o trabalho.';
SET @con_tags = JSON_ARRAY('primeiro acesso','configuração inicial','começar');
SET @con_conteudo = CONCAT(
  '<p>A configuração inicial prepara os cadastros e opções que serão usados no dia a dia. Entre com uma conta autorizada e confira se está no projeto e na organização corretos antes de começar.</p>',
  '<ol><li>Leia as orientações de cada etapa do assistente.</li><li>Revise as opções sugeridas e mantenha somente as que fazem sentido para a operação.</li><li>Confira os dados e as escolhas no resumo antes de confirmar.</li><li>Se precisar corrigir algo, volte à etapa correspondente antes da confirmação final.</li><li>Depois de concluir, acesse os módulos disponíveis e valide os cadastros criados.</li></ol>',
  '<p>Os nomes e as etapas podem variar conforme o projeto e as permissões da sua conta. Se o assistente já tiver sido concluído, os cadastros poderão ser revisados nas áreas correspondentes do sistema.</p>'
);
INSERT INTO `icv_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

-- NAVEGAÇÃO E CONTEXTO
SET @con_categoria_id = @categoria_navegacao;
SET @con_titulo = 'Navegue pelo sistema e encontre cada recurso';
SET @con_resumo = 'Use o menu, os caminhos de navegação e a pesquisa para localizar as áreas disponíveis.';
SET @con_tags = JSON_ARRAY('navegação','menu','pesquisa','interface');
SET @con_conteudo = CONCAT(
  '<p>O menu principal reúne os módulos liberados para sua conta. A organização visual pode variar entre projetos, mas os recursos costumam estar agrupados por assunto.</p>',
  '<ul><li>Use o menu para abrir um módulo e localizar suas opções.</li><li>Use a pesquisa quando souber o nome do cadastro ou recurso.</li><li>Observe o caminho de navegação no topo da página para entender onde está e retornar a uma área anterior.</li><li>Consulte a Central de Ajuda quando precisar de orientação sobre uma tarefa.</li></ul>',
  '<p>Se um item não aparecer, isso pode ocorrer porque sua conta não tem a permissão correspondente ou porque o recurso não está habilitado para o projeto.</p>'
);
INSERT INTO `icv_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

SET @con_titulo = 'Confira o projeto e o contexto ativo';
SET @con_resumo = 'Verifique o projeto e a organização selecionados antes de consultar ou cadastrar informações.';
SET @con_tags = JSON_ARRAY('projeto','organização','contexto','unidade');
SET @con_conteudo = CONCAT(
  '<p>O contexto ativo define a quais dados você tem acesso e onde novos registros serão criados. Em ambientes com mais de uma organização ou unidade, confirme a identificação exibida no sistema antes de executar uma tarefa.</p>',
  '<ol><li>Confira o nome ou a identificação do projeto e da organização ativos.</li><li>Se houver seletor de contexto, escolha a unidade correta antes de abrir o cadastro.</li><li>Depois de trocar o contexto, confirme novamente os filtros e os dados exibidos.</li><li>Se a unidade esperada não estiver disponível, solicite ao administrador a revisão dos seus vínculos e permissões.</li></ol>',
  '<p>Não tente contornar restrições de acesso alterando endereços ou identificadores na página.</p>'
);
INSERT INTO `icv_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

-- CADASTROS E ACESSOS
SET @con_categoria_id = @categoria_cadastros;
SET @con_titulo = 'Pesquise antes de cadastrar e mantenha os dados organizados';
SET @con_resumo = 'Evite duplicidades, confira os campos e revise o resultado depois de salvar.';
SET @con_tags = JSON_ARRAY('cadastro','pesquisa','duplicidade','atualização');
SET @con_conteudo = CONCAT(
  '<p>Antes de criar um registro, pesquise para verificar se ele já existe. Uma busca cuidadosa reduz duplicidades e ajuda a manter as informações consistentes.</p>',
  '<ol><li>Abra a área de cadastro correspondente e pesquise por nome, documento ou outro dado disponível.</li><li>Se não houver registro correspondente, inicie um novo cadastro.</li><li>Preencha os campos obrigatórios e confira os dados antes de salvar.</li><li>Após salvar, confirme a mensagem de retorno e localize o registro na pesquisa.</li><li>Para corrigir informações, abra o registro existente e atualize-o em vez de criar outro.</li></ol>',
  '<p>Os campos e as regras variam conforme o tipo de cadastro e o projeto.</p>'
);
INSERT INTO `icv_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

SET @con_titulo = 'Entenda status e histórico dos registros';
SET @con_resumo = 'Use o status para controlar a disponibilidade e o histórico para acompanhar alterações.';
SET @con_tags = JSON_ARRAY('status','histórico','alterações','auditoria');
SET @con_conteudo = CONCAT(
  '<p>O status indica se um registro está disponível para uso nos fluxos do sistema. Inativar um cadastro geralmente impede novas seleções, mas não apaga o histórico relacionado.</p>',
  '<p>Quando a tela disponibilizar uma aba de Histórico, consulte-a para verificar alterações registradas. Antes de mudar o status ou editar dados, confira se está no registro e no contexto corretos.</p>',
  '<p>Não exclua nem recrie um registro para corrigir uma informação sem antes verificar as opções de edição e as regras do módulo. Ações disponíveis dependem das permissões da sua conta.</p>'
);
INSERT INTO `icv_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

SET @con_titulo = 'Acessos e permissões: peça somente o necessário';
SET @con_resumo = 'Entenda por que menus e ações variam conforme o nível de acesso da sua conta.';
SET @con_tags = JSON_ARRAY('acesso','permissões','usuário','segurança');
SET @con_conteudo = CONCAT(
  '<p>As permissões determinam quais módulos, registros e ações cada conta pode utilizar. Por isso, duas pessoas podem visualizar menus ou botões diferentes no mesmo projeto.</p>',
  '<ul><li>Use sua própria conta e mantenha suas credenciais em sigilo.</li><li>Solicite ao responsável apenas as permissões necessárias para suas atividades.</li><li>Se uma ação esperada não estiver disponível, informe o módulo e a tarefa ao administrador para que ele revise seu acesso.</li><li>Ao deixar de exercer uma função, peça a revisão ou remoção das permissões relacionadas.</li></ul>',
  '<p>Não compartilhe senhas nem tente acessar dados fora do contexto autorizado.</p>'
);
INSERT INTO `icv_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `icv_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

COMMIT;


