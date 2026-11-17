# Script para iniciar quaisquer projeto!
# Certifique-se de ter criado o banco:
-- O banco configurado para desenvolvimento é `simpleweb` (prefixo `spw_`).
-- Este script cria a estrutura inicial do Simple Web e pode ser executado novamente
-- enquanto o projeto estiver em desenvolvimento.

SET FOREIGN_KEY_CHECKS = 0;

################################################################
######################### PROJETO ##############################
################################################################

  -- -----------------------------------------------------
  -- Table `spw_projeto`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_projeto`;

    CREATE TABLE IF NOT EXISTS `spw_projeto` (
      `prj_id` INT NOT NULL UNIQUE DEFAULT 1,
      -- Status
        `prj_status` ENUM('Ativo','Inativo','Bloqueado','Manutencao') NOT NULL DEFAULT 'Ativo',
        `prj_statusclass` ENUM('success','danger','dark','warning') NOT NULL DEFAULT 'success',
      -- Identificação
        `prj_nome` VARCHAR(100) NOT NULL DEFAULT 'simpleweb',
        `prj_titulo` VARCHAR(150) NOT NULL DEFAULT 'Simple Web',
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
        `prj_author` VARCHAR(150) NULL DEFAULT 'PHSA Tecnologia - https://simpleweb.phsa.com.br',
        `prj_robots` VARCHAR(100) NULL DEFAULT 'index, follow, noarchive',
        `prj_googletag` VARCHAR(50) NULL,
      -- Ambiente da instalação
        `prj_tipo` ENUM('Compartilhado','Dedicado') NOT NULL DEFAULT 'Compartilhado',
      -- Central / sincronização
        `prj_idorganizacao_central` INT NULL,
        `prj_chave` VARCHAR(255) NULL,
        `prj_dominio` VARCHAR(255) NULL,
        `prj_dtultimasincronizacao` DATETIME NULL,
        `prj_url_central` VARCHAR(255) NULL DEFAULT 'https://simpleweb.phsa.com.br',
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
  -- Table `spw_palavra_chave`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_palavra_chave`;

    CREATE TABLE IF NOT EXISTS `spw_palavra_chave` (
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
  -- Table `spw_organizacao`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_organizacao`;

    CREATE TABLE IF NOT EXISTS `spw_organizacao` (
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
  -- Table `spw_estadocivil`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_estadocivil`;

    CREATE TABLE IF NOT EXISTS `spw_estadocivil` (
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
  -- Table `spw_sexo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_sexo`;

    CREATE TABLE IF NOT EXISTS `spw_sexo` (
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
  -- Table `spw_naturalidade`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_naturalidade`;

    CREATE TABLE IF NOT EXISTS `spw_naturalidade` (
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
  -- Table `spw_naturezajuridica`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_naturezajuridica`;

    CREATE TABLE IF NOT EXISTS `spw_naturezajuridica` (
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

    ALTER TABLE `spw_organizacao`
      ADD INDEX `idx_org_idnaturezajuridica` (`org_idnaturezajuridica`),
      ADD CONSTRAINT `fk_spw_organizacao_spw_naturezajuridica1`
        FOREIGN KEY (`org_idnaturezajuridica`)
        REFERENCES `spw_naturezajuridica` (`ntj_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION;

################################################################
######################### ESSENCIAIS ###########################
################################################################

  -- -----------------------------------------------------
  -- Table `spw_endereco`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_endereco`;

    CREATE TABLE IF NOT EXISTS `spw_endereco` (
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
  -- Table `spw_arquivo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_arquivo`;

    CREATE TABLE IF NOT EXISTS `spw_arquivo` (
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
  -- Table `spw_contato`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_contato`;

    CREATE TABLE IF NOT EXISTS `spw_contato` (
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
  -- Table `spw_empresa`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_empresa`;

  -- -----------------------------------------------------
  -- Table `spw_empresa`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_empresa`;

    CREATE TABLE IF NOT EXISTS `spw_empresa` (
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
      CONSTRAINT `fk_spw_empresa_spw_organizacao1`
        FOREIGN KEY (`emp_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_empresa_spw_empresa1`
        FOREIGN KEY (`emp_idempresa_pai`)
        REFERENCES `spw_empresa` (`emp_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_empresa_spw_sexo1`
        FOREIGN KEY (`emp_idsexo`)
        REFERENCES `spw_sexo` (`sex_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_empresa_spw_estadocivil1`
        FOREIGN KEY (`emp_idestadocivil`)
        REFERENCES `spw_estadocivil` (`ecv_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_empresa_spw_naturalidade1`
        FOREIGN KEY (`emp_idnaturalidade`)
        REFERENCES `spw_naturalidade` (`nat_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_empresa_spw_naturezajuridica1`
        FOREIGN KEY (`emp_idnaturezajuridica`)
        REFERENCES `spw_naturezajuridica` (`ntj_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_pwa`
  -- Configuração do Progressive Web App da página da empresa
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_pwa`;

    CREATE TABLE IF NOT EXISTS `spw_pwa` (
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
      CONSTRAINT `fk_spw_pwa_spw_organizacao1`
        FOREIGN KEY (`pwa_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_pwa_spw_empresa1`
        FOREIGN KEY (`pwa_idempresa`)
        REFERENCES `spw_empresa` (`emp_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

################################################################
##################### USUÁRIO E PERMISSÕES #####################
################################################################

  -- -----------------------------------------------------
  -- Table `spw_nivelacesso`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_nivelacesso`;

    CREATE TABLE IF NOT EXISTS `spw_nivelacesso` (
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
  -- Table `spw_permissao`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_permissao`;

    CREATE TABLE IF NOT EXISTS `spw_permissao` (
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
      CONSTRAINT `fk_spw_permissao_spw_nivelacesso1`
        FOREIGN KEY (`per_idnivelacesso`)
        REFERENCES `spw_nivelacesso` (`nva_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_permissaorelatorio`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_permissaorelatorio`;

    CREATE TABLE IF NOT EXISTS `spw_permissaorelatorio` (
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
      CONSTRAINT `fk_spw_permissaorelatorio_spw_nivelacesso1`
        FOREIGN KEY (`pmr_idnivelacesso`)
        REFERENCES `spw_nivelacesso` (`nva_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_usuario`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_usuario_nivelacesso`;
    DROP TABLE IF EXISTS `spw_usuario_organizacao`;
    DROP TABLE IF EXISTS `spw_usuario`;

    CREATE TABLE IF NOT EXISTS `spw_usuario` (
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
      CONSTRAINT `fk_spw_usuario_spw_organizacao1`
        FOREIGN KEY (`usu_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_spw_empresa1`
        FOREIGN KEY (`usu_idempresa`)
        REFERENCES `spw_empresa` (`emp_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_spw_organizacaoprincipal1`
        FOREIGN KEY (`usu_idorganizacaoprincipal`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_spw_empresaprincipal1`
        FOREIGN KEY (`usu_idempresaprincipal`)
        REFERENCES `spw_empresa` (`emp_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_spw_sexo1`
        FOREIGN KEY (`usu_idsexo`)
        REFERENCES `spw_sexo` (`sex_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_spw_estadocivil1`
        FOREIGN KEY (`usu_idestadocivil`)
        REFERENCES `spw_estadocivil` (`ecv_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_spw_naturalidade1`
        FOREIGN KEY (`usu_idnaturalidade`)
        REFERENCES `spw_naturalidade` (`nat_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_usuario_organizacao`
  -- Identidade global vinculada às organizações em que pode atuar.
  -- -----------------------------------------------------
    CREATE TABLE IF NOT EXISTS `spw_usuario_organizacao` (
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
      CONSTRAINT `fk_spw_usuario_organizacao_spw_usuario1`
        FOREIGN KEY (`uor_idusuario`)
        REFERENCES `spw_usuario` (`usu_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_organizacao_spw_organizacao1`
        FOREIGN KEY (`uor_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_usuario_empresa`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_usuario_empresa`;

    CREATE TABLE IF NOT EXISTS `spw_usuario_empresa` (
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
      CONSTRAINT `fk_spw_usuario_empresa_spw_organizacao1`
        FOREIGN KEY (`uem_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_empresa_spw_usuario1`
        FOREIGN KEY (`uem_idusuario`)
        REFERENCES `spw_usuario` (`usu_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_empresa_spw_empresa1`
        FOREIGN KEY (`uem_idempresa`)
        REFERENCES `spw_empresa` (`emp_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_usuario_nivelacesso`
 -- Níveis de acesso do usuário por projeto, organização e empresa.
  -- -----------------------------------------------------
    CREATE TABLE IF NOT EXISTS `spw_usuario_nivelacesso` (
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
      CONSTRAINT `fk_spw_usuario_nivelacesso_spw_usuario1`
        FOREIGN KEY (`una_idusuario`)
        REFERENCES `spw_usuario` (`usu_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_nivelacesso_spw_organizacao1`
        FOREIGN KEY (`una_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_nivelacesso_spw_empresa1`
        FOREIGN KEY (`una_idempresa`)
        REFERENCES `spw_empresa` (`emp_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_usuario_nivelacesso_spw_nivelacesso1`
        FOREIGN KEY (`una_idnivelacesso`)
        REFERENCES `spw_nivelacesso` (`nva_id`)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

################################################################
##################### PLANO E CONTRATAÇÃO #####################
################################################################

  -- -----------------------------------------------------
  -- Table `spw_plano`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_plano`;

    CREATE TABLE IF NOT EXISTS `spw_plano` (
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
  -- Table `spw_plano_contratado`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_plano_contratado`;

    CREATE TABLE IF NOT EXISTS `spw_plano_contratado` (
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
      CONSTRAINT `fk_spw_plano_contratado_spw_organizacao1`
        FOREIGN KEY (`plc_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_plano_contratado_spw_plano1`
        FOREIGN KEY (`plc_idplano`)
        REFERENCES `spw_plano` (`pla_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_plano_contratado_spw_usuario1`
        FOREIGN KEY (`plc_idusuariocontratante`)
        REFERENCES `spw_usuario` (`usu_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_plano_contratado_spw_plano_contratado_anterior1`
        FOREIGN KEY (`plc_idcontratacaoanterior`)
        REFERENCES `spw_plano_contratado` (`plc_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_email_template`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_email_template`;

    CREATE TABLE IF NOT EXISTS `spw_email_template` (
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
  -- Table `spw_email_enviado`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_email_enviado`;
    
    CREATE TABLE IF NOT EXISTS `spw_email_enviado` (
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
      CONSTRAINT `fk_spw_email_enviado_spw_email_template1`
        FOREIGN KEY (`eme_idtemplate`)
        REFERENCES `spw_email_template` (`emt_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

################################################################
######################## FINANCEIRO CORE #######################
################################################################

  -- -----------------------------------------------------
  -- Table `spw_conta_financeira`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_conta_financeira`;

    CREATE TABLE IF NOT EXISTS `spw_conta_financeira` (
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
      CONSTRAINT `fk_spw_conta_financeira_spw_organizacao1`
        FOREIGN KEY (`ctf_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_forma_pagamento`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_forma_pagamento`;

    CREATE TABLE IF NOT EXISTS `spw_forma_pagamento` (
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
      CONSTRAINT `fk_spw_forma_pagamento_spw_organizacao1`
        FOREIGN KEY (`fpg_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_condicao_pgto`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_condicao_pgto`;

    CREATE TABLE IF NOT EXISTS `spw_condicao_pgto` (
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
      CONSTRAINT `fk_spw_condicao_pgto_spw_forma_pagamento1`
        FOREIGN KEY (`cpg_idformapagamento`)
        REFERENCES `spw_forma_pagamento` (`fpg_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_condicao_pgto_spw_organizacao1`
        FOREIGN KEY (`cpg_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_plano_conta`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_plano_conta`;

    CREATE TABLE IF NOT EXISTS `spw_plano_conta` (
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
      CONSTRAINT `fk_spw_plano_conta_spw_plano_conta1`
        FOREIGN KEY (`pct_idpai`)
        REFERENCES `spw_plano_conta` (`pct_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_plano_conta_spw_organizacao1`
        FOREIGN KEY (`pct_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_centro_custo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_centro_custo`;

    CREATE TABLE IF NOT EXISTS `spw_centro_custo` (
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
      CONSTRAINT `fk_spw_centro_custo_spw_organizacao1`
        FOREIGN KEY (`cdc_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_titulo`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_titulo`;

    CREATE TABLE IF NOT EXISTS `spw_titulo` (
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
      CONSTRAINT `fk_spw_titulo_spw_plano_conta1`
        FOREIGN KEY (`tit_idplanoconta`)
        REFERENCES `spw_plano_conta` (`pct_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_titulo_spw_organizacao1`
        FOREIGN KEY (`tit_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_lancamento`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_lancamento`;

    CREATE TABLE IF NOT EXISTS `spw_lancamento` (
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
      CONSTRAINT `fk_spw_lancamento_spw_titulo1`
        FOREIGN KEY (`lan_idtitulo`)
        REFERENCES `spw_titulo` (`tit_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_conta_financeira1`
        FOREIGN KEY (`lan_idcontafinanceira`)
        REFERENCES `spw_conta_financeira` (`ctf_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_centro_custo1`
        FOREIGN KEY (`lan_idcentrocusto`)
        REFERENCES `spw_centro_custo` (`cdc_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_plano_conta1`
        FOREIGN KEY (`lan_idplanoconta`)
        REFERENCES `spw_plano_conta` (`pct_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_forma_pagamento1`
        FOREIGN KEY (`lan_idformapagamento`)
        REFERENCES `spw_forma_pagamento` (`fpg_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_condicao_pgto1`
        FOREIGN KEY (`lan_idcondicaopgto`)
        REFERENCES `spw_condicao_pgto` (`cpg_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_lancamento_vinculado1`
        FOREIGN KEY (`lan_idlancamento_vinculado`)
        REFERENCES `spw_lancamento` (`lan_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_lancamento_origem1`
        FOREIGN KEY (`lan_idlancamento_origem`)
        REFERENCES `spw_lancamento` (`lan_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_lancamento_spw_organizacao1`
        FOREIGN KEY (`lan_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

  -- -----------------------------------------------------
  -- Table `spw_fornecedor`
  -- -----------------------------------------------------
    DROP TABLE IF EXISTS `spw_fornecedor`;

    CREATE TABLE IF NOT EXISTS `spw_fornecedor` (
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
      CONSTRAINT `fk_spw_fornecedor_spw_organizacao1`
        FOREIGN KEY (`for_idorganizacao`)
        REFERENCES `spw_organizacao` (`org_id`)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_fornecedor_spw_sexo1`
        FOREIGN KEY (`for_idsexo`)
        REFERENCES `spw_sexo` (`sex_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION,
      CONSTRAINT `fk_spw_fornecedor_spw_estadocivil1`
        FOREIGN KEY (`for_idestadocivil`)
        REFERENCES `spw_estadocivil` (`ecv_id`)
        ON DELETE SET NULL
        ON UPDATE NO ACTION
    ) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Central de conhecimento do projeto
-- -----------------------------------------------------
DROP TABLE IF EXISTS `spw_conhecimento_midia`;
DROP TABLE IF EXISTS `spw_conhecimento`;
DROP TABLE IF EXISTS `spw_conhecimento_categoria`;

CREATE TABLE IF NOT EXISTS `spw_conhecimento_categoria` (
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

CREATE TABLE IF NOT EXISTS `spw_conhecimento` (
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
  CONSTRAINT `fk_spw_conhecimento_categoria`
    FOREIGN KEY (`con_idcategoria`)
    REFERENCES `spw_conhecimento_categoria` (`cct_id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `spw_conhecimento_midia` (
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
  CONSTRAINT `fk_spw_conhecimento_midia_conhecimento`
    FOREIGN KEY (`cmi_idconhecimento`)
    REFERENCES `spw_conhecimento` (`con_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE = InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
