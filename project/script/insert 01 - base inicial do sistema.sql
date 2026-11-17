-- 01 - BASE INICIAL DO SISTEMA
-- Execute este script após o create 01 - estrutura do sistema.sql (ou use rodar_scripts.bat).
-- Base de demonstração do Simple Web: framework SaaS em funcionamento básico, sem regra de negócio aplicada.

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `spw_usuario_empresa`;
TRUNCATE TABLE `spw_usuario_nivelacesso`;
TRUNCATE TABLE `spw_usuario_organizacao`;
TRUNCATE TABLE `spw_permissaorelatorio`;
TRUNCATE TABLE `spw_permissao`;
TRUNCATE TABLE `spw_conhecimento_midia`;
TRUNCATE TABLE `spw_conhecimento`;
TRUNCATE TABLE `spw_conhecimento_categoria`;
TRUNCATE TABLE `spw_usuario`;
TRUNCATE TABLE `spw_nivelacesso`;
TRUNCATE TABLE `spw_empresa`;
TRUNCATE TABLE `spw_endereco`;
TRUNCATE TABLE `spw_naturalidade`;
TRUNCATE TABLE `spw_naturezajuridica`;
TRUNCATE TABLE `spw_sexo`;
TRUNCATE TABLE `spw_estadocivil`;
TRUNCATE TABLE `spw_organizacao`;
TRUNCATE TABLE `spw_projeto`;
TRUNCATE TABLE `spw_contato`;
TRUNCATE TABLE `spw_arquivo`;
TRUNCATE TABLE `spw_email_enviado`;
TRUNCATE TABLE `spw_email_template`;
TRUNCATE TABLE `spw_plano_contratado`;
TRUNCATE TABLE `spw_plano`;
TRUNCATE TABLE `spw_palavra_chave`;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- PROJETO SIMPLE WEB
-- -----------------------------------------------------
SET @termos_uso = '<h2>Termos de uso</h2><p>Estes Termos de uso estabelecem as condições para utilização da plataforma Simple Web por empresas, profissionais e organizações.</p><h3>1. Utilização da plataforma</h3><p>A organização deve fornecer informações verdadeiras, manter seus dados atualizados e utilizar o sistema de acordo com a legislação aplicável.</p><h3>2. Conta e responsabilidades</h3><p>O usuário responsável pela contratação responde pela administração da organização, pela proteção de suas credenciais e pelas informações inseridas no Simple Web.</p><h3>3. Planos e pagamentos</h3><p>Os recursos disponíveis dependem do plano contratado e de sua situação financeira. Planos comerciais podem exigir confirmação de pagamento para ativação ou continuidade do acesso.</p><h3>4. Atendimento e alterações</h3><p>O Simple Web poderá aprimorar seus recursos e atualizar estes termos. A versão aceita será preservada na contratação correspondente.</p><p>Ao contratar, a organização declara que leu e aceita estes Termos de uso.</p>';
SET @aviso_privacidade = '<h2>Aviso de privacidade</h2><p>O Simple Web trata dados pessoais para permitir o cadastro, a autenticação, a administração das organizações, empresas e usuários e a execução dos serviços contratados.</p><h3>1. Dados tratados</h3><p>Podem ser tratados dados de identificação, contato, acesso, organização, empresa e informações necessárias à utilização dos recursos da plataforma.</p><h3>2. Finalidades</h3><p>Os dados são utilizados para criar e administrar contas, disponibilizar funcionalidades, processar pagamentos, prestar suporte, manter a segurança e cumprir obrigações legais.</p><h3>3. Compartilhamento</h3><p>Quando necessário, os dados poderão ser compartilhados com prestadores essenciais à operação, como serviços de hospedagem, comunicação e pagamento, observadas as medidas de segurança aplicáveis.</p><h3>4. Direitos do titular</h3><p>O titular pode solicitar informações sobre o tratamento de seus dados e exercer os direitos previstos na legislação aplicável pelos canais de atendimento do Simple Web.</p><h3>5. Atualizações</h3><p>Este aviso poderá ser atualizado. A versão vigente será identificada no momento da contratação ou aceite.</p>';

INSERT INTO `spw_projeto` (
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
  'Simple Web',
  'Framework SaaS da PHSA Tecnologia: base multiempresa com contexto, usuários, permissões, planos e financeiro, pronta para receber a regra de negócio de cada produto.',
  @termos_uso,
  CONCAT(DATE_FORMAT(NOW(), '%d%m%Y%H%i%s'), '-', LEFT(SHA2(@termos_uso, 256), 32)),
  @aviso_privacidade,
  CONCAT(DATE_FORMAT(NOW(), '%d%m%Y%H%i%s'), '-', LEFT(SHA2(@aviso_privacidade, 256), 32)),
  '<div style="max-width:640px;margin:0 auto;background:#F7FBFE;border-bottom:3px solid #5BC2F2;border-radius:14px 14px 0 0;padding:28px 32px;color:#0B2540;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="vertical-align:middle;"><img src="{projeto.logo}" alt="{projeto.titulo}" style="display:block;max-width:180px;max-height:58px;width:auto;height:auto;"></td><td style="vertical-align:middle;text-align:right;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#dbe8ff;">{projeto.titulo}<br><span style="font-size:12px;">A base que não decide por você</span></td></tr></table></div>',
  '<div style="max-width:640px;margin:0 auto;border-radius:0 0 14px 14px;padding:22px 32px;background:#f7f9fc;border-top:1px solid #e7edf5;color:#718096;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;text-align:center;"><strong style="color:#40577a;">{projeto.titulo}</strong><br>A base que não decide por você.<br><a href="{projeto.url}" style="color:#0E7CC2;text-decoration:none;">{projeto.url}</a> · <a href="mailto:contato@phsa.com.br" style="color:#0E7CC2;text-decoration:none;">contato@phsa.com.br</a><br><span>Este é um e-mail automático. Não responda diretamente a esta mensagem.</span></div>',
  'https://simpleweb.phsa.com.br',
  'https://simpleweb.phsa.com.br',
  'Pagar.me',
  '',
  '',
  '{"rules": [{"type": "percentage", "value": 100, "options": {"liable": true, "charge_remainder_fee": true, "charge_processing_fee": true}, "recipient_id": "re_cmhxeexhm015m0m9t33njdyq6"}], "enabled": true, "account_id": "acc_Lxm2KGLIOFxW2dXz", "environment": "test"}',
  'storage/global/logo.png',
  'storage/global/favicon.png',
  'storage/global/logonegativa.png',
  '[]'
);

-- -----------------------------------------------------
-- TEMPLATE DE E-MAIL DO SISTEMA
-- -----------------------------------------------------
INSERT INTO `spw_email_template` (
  `emt_id`, `emt_idorganizacao`, `emt_idempresa`, `emt_templateprojeto`,
  `emt_palavrachave`, `emt_nome`, `emt_status`, `emt_statusclass`,
  `emt_host`, `emt_user`, `emt_pass`, `emt_port`, `emt_remetentenome`,
  `emt_remetenteemail`, `emt_replyto`, `emt_title`, `emt_lang`, `emt_charset`,
  `emt_conteudo`, `emt_log`
) VALUES (
  1, 0, 0, 'nova_contratacao',
  '["projeto","organizacao","empresa","usuario"]',
  'Nova contratação', 'Ativo', 'success', 'smtp.hostinger.com',
  'naoresponda@phsa.com.br', NULL, 587, 'Simple Web',
  'naoresponda@phsa.com.br', 'contato@phsa.com.br',
  'Sua organização está pronta no Simple Web', 'pt_br', 'UTF-8',
  '<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body style="margin:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#24344d;"><div style="max-width:640px;margin:32px auto;background:#ffffff;border:1px solid #dbe4f0;border-radius:12px;overflow:hidden;"><div style="padding:28px 32px;background:#0B2540;color:#ffffff;"><h1 style="margin:0;font-size:25px;">Bem-vindo ao Simple Web</h1><p style="margin:8px 0 0;font-size:15px;opacity:.9;">A base que não decide por você.</p></div><div style="padding:32px;"><p style="margin:0 0 18px;font-size:16px;">Olá, <strong>{usuario.nome}</strong>!</p><p style="margin:0 0 16px;line-height:1.7;">É uma alegria receber você no Simple Web, a base SaaS da PHSA Tecnologia para organizar organizações, empresas, usuários e financeiro em um só lugar.</p><p style="margin:0 0 16px;line-height:1.7;">Sua organização, <strong>{organizacao.nomefantasia}</strong>, está pronta para ser administrada. A empresa <strong>{empresa.nomefantasia}</strong> já foi preparada para você começar.</p><p style="margin:0 0 24px;line-height:1.7;">Acesse o sistema pelo botão abaixo e comece sua jornada de gestão com simplicidade, clareza e propósito.</p><div style="text-align:center;margin:28px 0;"><a href="{projeto.url}/login" style="display:inline-block;padding:14px 24px;background:#0E7CC2;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:bold;">Acessar o Simple Web</a></div><p style="margin:0 0 10px;font-size:14px;line-height:1.6;">Se preferir, acesse diretamente:</p><p style="margin:0 0 24px;font-size:14px;"><a href="{projeto.url}/login" style="color:#0E7CC2;">{projeto.url}/login</a></p><p style="margin:0;font-size:13px;color:#66758a;line-height:1.6;">Guarde este e-mail para consultar o endereço de acesso sempre que precisar.</p></div><div style="padding:20px 32px;background:#f7f9fc;border-top:1px solid #e7edf5;color:#718096;font-size:12px;text-align:center;">{projeto.titulo} — a base que não decide por você.</div></div></body></html>',
  '[]'
);

UPDATE `spw_projeto`
SET
  `prj_email_header` = '<div style="max-width:640px;margin:0 auto;background:#F7FBFE;border-bottom:3px solid #5BC2F2;border-radius:14px 14px 0 0;padding:28px 32px;color:#0B2540;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="vertical-align:middle;"><img src="{projeto.logo}" alt="{projeto.titulo}" style="display:block;max-width:180px;max-height:58px;width:auto;height:auto;"></td><td style="vertical-align:middle;text-align:right;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#dbe8ff;">{projeto.titulo}<br><span style="font-size:12px;">A base que n&atilde;o decide por voc&ecirc;</span></td></tr></table></div>',
  `prj_email_footer` = '<div style="max-width:640px;margin:0 auto;border-radius:0 0 14px 14px;padding:22px 32px;background:#f7f9fc;border-top:1px solid #e7edf5;color:#718096;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;text-align:center;"><strong style="color:#40577a;">{projeto.titulo}</strong><br>A base que não decide por você.<br><a href="{projeto.url}" style="color:#0E7CC2;text-decoration:none;">{projeto.url}</a> &middot; <a href="mailto:contato@phsa.com.br" style="color:#0E7CC2;text-decoration:none;">contato@phsa.com.br</a><br><span>Este &eacute; um e-mail autom&aacute;tico. N&atilde;o responda diretamente a esta mensagem.</span></div>'
WHERE `prj_id` = 1;

UPDATE `spw_email_template`
SET `emt_conteudo` = '<div style="font-family:Arial,Helvetica,sans-serif;color:#24344d;"><p style="margin:0 0 18px;font-size:17px;">Ol&aacute;, <strong>{usuario.nome}</strong>!</p><p style="margin:0 0 16px;line-height:1.7;">&Eacute; uma alegria receber voc&ecirc; no Simple Web, a base SaaS da PHSA Tecnologia para organizar organiza&ccedil;&otilde;es, empresas, usu&aacute;rios e financeiro em um s&oacute; lugar.</p><p style="margin:0 0 16px;line-height:1.7;">Sua organiza&ccedil;&atilde;o, <strong>{organizacao.nomefantasia}</strong>, est&aacute; pronta para ser administrada. A empresa <strong>{empresa.nomefantasia}</strong> j&aacute; foi preparada para voc&ecirc; come&ccedil;ar.</p><p style="margin:0 0 24px;line-height:1.7;">Acesse o sistema pelo bot&atilde;o abaixo e comece sua jornada de gest&atilde;o com simplicidade, clareza e prop&oacute;sito.</p><div style="text-align:center;margin:28px 0;"><a href="{projeto.url}/login" style="display:inline-block;padding:14px 24px;background:#0E7CC2;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:bold;">Acessar o Simple Web</a></div><p style="margin:0 0 10px;font-size:14px;line-height:1.6;">Se preferir, acesse diretamente:</p><p style="margin:0 0 24px;font-size:14px;"><a href="{projeto.url}/login" style="color:#0E7CC2;">{projeto.url}/login</a></p><p style="margin:0;font-size:13px;color:#66758a;line-height:1.6;">Guarde este e-mail para consultar o endere&ccedil;o de acesso sempre que precisar.</p></div>'
WHERE `emt_id` = 1 AND `emt_templateprojeto` = 'nova_contratacao';

INSERT INTO `spw_email_template` (
  `emt_id`, `emt_idorganizacao`, `emt_idempresa`, `emt_templateprojeto`,
  `emt_palavrachave`, `emt_nome`, `emt_status`, `emt_statusclass`,
  `emt_host`, `emt_user`, `emt_pass`, `emt_port`, `emt_remetentenome`,
  `emt_remetenteemail`, `emt_replyto`, `emt_title`, `emt_lang`, `emt_charset`,
  `emt_conteudo`, `emt_log`
) VALUES (
  2, 0, 0, 'pagamento_confirmado',
  '["projeto","organizacao","empresa","usuario","plano","contratacao","pagamento"]',
  'Pagamento confirmado', 'Ativo', 'success', 'smtp.hostinger.com',
  'naoresponda@phsa.com.br', NULL, 587, 'Simple Web',
  'naoresponda@phsa.com.br', 'contato@phsa.com.br',
  'Pagamento confirmado — {organizacao.nomefantasia}', 'pt_br', 'UTF-8',
  '<div style="font-family:Arial,Helvetica,sans-serif;color:#24344d;"><p style="margin:0 0 18px;font-size:17px;">Ol&aacute;, <strong>{usuario.nome}</strong>!</p><p style="margin:0 0 16px;line-height:1.7;">Recebemos a confirma&ccedil;&atilde;o do pagamento da organiza&ccedil;&atilde;o <strong>{organizacao.nomefantasia}</strong>.</p><p style="margin:0 0 20px;line-height:1.7;">A contrata&ccedil;&atilde;o do plano <strong>{plano.nome}</strong> foi confirmada com sucesso.</p><div style="margin:24px 0;padding:18px;background:#f7f9fc;border:1px solid #dbe4f0;border-radius:10px;"><p style="margin:0 0 8px;"><strong>Plano:</strong> {plano.nome}</p><p style="margin:0 0 8px;"><strong>Ciclo:</strong> {contratacao.ciclo}</p><p style="margin:0 0 8px;"><strong>Valor pago:</strong> R$ {contratacao.valor}</p><p style="margin:0 0 8px;"><strong>Pagamento:</strong> {contratacao.dtpagamento}</p><p style="margin:0;"><strong>Vig&ecirc;ncia:</strong> {contratacao.dtinicio} at&eacute; {contratacao.dtfim}</p></div><p style="margin:0 0 24px;line-height:1.7;">Sua empresa j&aacute; pode acessar os recursos liberados para o plano contratado.</p><div style="text-align:center;margin:28px 0;"><a href="{projeto.url}/login" style="display:inline-block;padding:14px 24px;background:#0E7CC2;color:#ffffff;text-decoration:none;border-radius:7px;font-weight:bold;">Acessar o Simple Web</a></div><p style="margin:0;font-size:13px;color:#66758a;line-height:1.6;">Se precisar de ajuda, entre em contato com a equipe Simple Web.</p></div>',
  '[]'
);

-- -----------------------------------------------------
-- ORGANIZAÇÃO CENTRAL DA PLATAFORMA
-- -----------------------------------------------------
INSERT INTO `spw_organizacao` (
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
  'ORG_SIMPLEWEB',
  '11222333000181',
  'PHSA Tecnologia',
  'PHSA Tecnologia',
  'Organização central da plataforma Simple Web.',
  '2026-01-01',
  NULL,
  'Organização administrativa e técnica da plataforma Simple Web (PHSA Tecnologia).',
  'storage/global/logo.png',
  'storage/global/planodefundo.jpg',
  'dashboard',
  '11222333000181',
  'Publica',
  '<section><h1>PHSA Tecnologia</h1><p>Bem-vindo à página oficial da organização PHSA Tecnologia.</p><p>Conheça a base que dá início aos sistemas da PHSA Tecnologia.</p></section>',
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
INSERT INTO `spw_empresa` (
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
  'EMP_SIMPLEWEB',
  1,
  '11222333000181',
  'Simple Web',
  'Simple Web',
  'Empresa principal da organização central do Simple Web.',
  '2026-01-01',
  NULL,
  'Ambiente operacional principal da plataforma Simple Web.',
  'storage/global/logo.png',
  'storage/global/planodefundo.jpg',
  'teste',
  'Publica',
  '<section><h1>Simple Web</h1><p>Bem-vindo à página oficial da empresa Simple Web.</p><p>Conheça nossos serviços e acompanhe as novidades.</p></section>',
  0,
  '[]'
);

INSERT INTO `spw_contato` (
  `ctt_id`, `ctt_tipo`, `ctt_identificacao`, `ctt_conteudo`,
  `ctt_idorganizacao`, `ctt_idempresa`, `ctt_idorigem`, `ctt_origem`, `ctt_log`
) VALUES
  (1, 'Email', 'Comercial', 'contato@phsa.com.br', 1, 1, 1, 'empresa', '[]'),
  (2, 'WhatsApp', 'Comercial', '(31) 99999-0001', 1, 1, 1, 'empresa', '[]'),
  (3, 'Site', 'Institucional', 'https://simpleweb.phsa.com.br', 1, 1, 1, 'empresa', '[]');

INSERT INTO `spw_endereco` (
  `end_id`, `end_principal`, `end_nome`, `end_cep`, `end_logradouro`, `end_numero`,
  `end_bairro`, `end_cidade`, `end_estado`, `end_uf`, `end_idorganizacao`,
  `end_idempresa`, `end_idorigem`, `end_origem`, `end_log`
) VALUES (
  1, 1, 'Endereço principal', '30110-000', 'Rua Exemplo', '100',
  'Centro', 'Belo Horizonte', 'Minas Gerais', 'MG', 1, 1, 1, 'empresa', '[]'
), (
  2, 1, 'Endereço institucional do projeto', '30110-000', 'Rua Exemplo', '100',
  'Centro', 'Belo Horizonte', 'Minas Gerais', 'MG', 0, 0, 1, 'projeto', '[]'
);

-- -----------------------------------------------------
-- NÍVEL, PERMISSÕES E USUÁRIO ADMINISTRADOR DA PLATAFORMA (ESCOPO PROJETO)
-- -----------------------------------------------------
INSERT INTO `spw_nivelacesso` (
  `nva_id`, `nva_idorganizacao`, `nva_idempresa`, `nva_escopo`, `nva_status`,
  `nva_statusclass`, `nva_imutavel`, `nva_nome`, `nva_descricao`, `nva_log`
) VALUES (
  1, 0, 0, 'Projeto', 'Ativo', 'success', 1,
  'Administrador do Simple Web',
  'Administrador técnico e comercial da plataforma Simple Web.',
  '[]'
);

INSERT INTO `spw_permissao` (
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
UPDATE `spw_permissao`
SET `per_status` = 'Ativo',
    `per_statusclass` = 'success'
WHERE `per_idnivelacesso` = 1;

INSERT INTO `spw_permissaorelatorio` (
  `pmr_idorganizacao`, `pmr_idempresa`, `pmr_idnivelacesso`, `pmr_status`,
  `pmr_statusclass`, `pmr_dir`, `pmr_nome`, `pmr_descricao`, `pmr_log`
) VALUES
  (0, 0, 1, 'Ativo', 'success', 'dashboard', 'Dashboard', 'Acesso ao dashboard principal.', '[]');

INSERT INTO `spw_usuario` (
  `usu_id`, `usu_chave`, `usu_idorganizacao`, `usu_idempresa`, `usu_cnpjcpf`,
  `usu_razaosocial`, `usu_nomefantasia`, `usu_idsexo`, `usu_idestadocivil`,
  `usu_idnaturalidade`, `usu_niveisacesso`, `usu_email`, `usu_celular`,
  `usu_username`, `usu_senha`, `usu_logo`, `usu_planodefundo`, `usu_newsletter`, `usu_dtnewsletter`, `usu_log`
) VALUES (
  1,
  'USU_SIMPLEWEB_ADMIN',
  1,
  1,
  '52998224725',
  'Pedro',
  'Pedro',
  NULL,
  NULL,
  NULL,
  '[{"nva_id":1,"nva_nome":"Administrador do Simple Web"}]',
  'contato@phsa.com.br',
  '31900000000',
  'admin',
  '$2y$10$3imnhDfIenB5.x3rVbGemu6EgWlrIN7PgNWAuCnR7jMF0zcJegRJy',
  'storage/global/semperfil.png',
  'storage/global/planodefundo.jpg',
  0,
  NULL,
  '[]'
);

INSERT INTO `spw_usuario_organizacao` (
  `uor_id`, `uor_idusuario`, `uor_idorganizacao`, `uor_principal`, `uor_log`
) VALUES
  (1, 1, 1, 1, '[]');

INSERT INTO `spw_usuario_nivelacesso` (
  `una_id`, `una_idusuario`, `una_idorganizacao`, `una_idempresa`, `una_idnivelacesso`, `una_principal`, `una_log`
) VALUES
  (1, 1, NULL, NULL, 1, 1, '[]');

INSERT INTO `spw_usuario_empresa` (
  `uem_id`, `uem_idorganizacao`, `uem_idusuario`, `uem_idempresa`, `uem_principal`, `uem_log`
) VALUES (1, 1, 1, 1, 1, '[]');

UPDATE `spw_usuario`
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
-- CATALOGO FIXO DE PLANOS
-- -----------------------------------------------------
INSERT INTO `spw_plano` (
  `pla_id`, `pla_tipo`, `pla_nome`, `pla_descricaoresumida`, `pla_descricao`,
  `pla_rotulo`, `pla_selo`, `pla_beneficios`, `pla_textobotao`,
  `pla_ambiente`, `pla_valormensal`, `pla_valoranual`,
  `pla_valormensal_original`, `pla_valoranual_original`,
  `pla_dias_teste`, `pla_parametro`, `pla_contatocomercial`,
  `pla_ordem`, `pla_log`
) VALUES
  (1, 'Interno', 'Simple Web Interno', 'Plano interno da administração do Simple Web.', '<p>Plano interno da organização administradora da plataforma.</p>',
    'Uso interno', NULL, '["Configuracao central","Administracao da plataforma","Recursos internos"]', NULL,
    'Compartilhado', NULL, NULL, NULL, NULL, NULL, @param_interno, NULL, 1, '[]'),
  (2, 'Teste', 'Descoberta', 'Conheça o Simple Web por 30 dias, sem custo.', '<p>Um primeiro passo para conhecer o Simple Web em funcionamento: organização, empresa, usuários e financeiro.</p><p>Durante o período de teste, sua equipe pode experimentar os recursos essenciais antes de decidir como continuar.</p>',
    'Para comecar', NULL, '["Página pública da empresa","Configuração inicial guiada","Recursos essenciais"]', 'Quero experimentar',
    'Compartilhado', NULL, NULL, NULL, NULL, 30, @param_descoberta, NULL, 2, '[]'),
  (3, 'Comercial', 'Essencial', 'Uma base sólida para organizar a operação.', '<p>O plano Essencial reúne os recursos necessários para começar a organizar usuários, empresas, acessos e o financeiro.</p><p>Uma escolha equilibrada para empresas que desejam simplicidade e clareza na operação.</p>',
    'Para negócios', NULL, '["Usuários e permissões","Financeiro básico","Gestão por contexto"]', 'Comecar agora',
    'Compartilhado', 99.90, 999.00, 119.90, 1199.00, NULL, @param_essencial, NULL, 3, '[]'),
  (4, 'Comercial', 'Crescimento', 'Mais automação e visão para uma empresa em expansão.', '<p>O plano Crescimento amplia os recursos de gestão para empresas que estão crescendo e precisam acompanhar melhor sua operação.</p><p>Mais organização, contexto e visão para apoiar novas possibilidades.</p>',
    'Para crescer', 'Mais escolhido', '["Mais automacao","Visao da operacao","Recursos conectados"]', 'Comecar agora',
    'Compartilhado', 169.90, 1699.00, 199.90, 1999.00, NULL, @param_crescimento, NULL, 4, '[]'),
  (5, 'Comercial', 'Profissional', 'Recursos completos para uma operação conectada.', '<p>O plano Profissional oferece uma experiência mais completa para empresas com maior volume, múltiplas unidades e processos integrados.</p><p>Inclui recursos avançados para apoiar decisões e dar mais fluidez ao dia a dia.</p>',
    'Para operacoes conectadas', NULL, '["Todos os recursos essenciais","Financeiro e relatorios","Suporte prioritario"]', 'Comecar agora',
    'Compartilhado', 249.90, 2499.00, 299.90, 2999.00, NULL, @param_profissional, NULL, 5, '[]'),
  (6, 'Dedicado', 'Dedicado', 'Uma experiência exclusiva, personalizada e preparada para sua escala.', '<p>O ambiente Dedicado leva a experiência do Simple Web para uma estrutura exclusiva, com personalização, desempenho e acompanhamento próximos.</p><p>Converse com nosso time comercial para desenhar a melhor configuração para sua operação.</p>',
    'Para operacoes unicas', NULL, '["Ambiente dedicado","Alta personalizacao","Acompanhamento proximo"]', 'Falar com a equipe',
    'Dedicado', NULL, NULL, NULL, NULL, NULL, @param_dedicado,
    '[{"nome":"Equipe Comercial Simple Web","celular":"(31) 3333-2026","email":"contato@phsa.com.br"},{"nome":"Atendimento Simple Web","celular":"(31) 3333-2027","email":"contato@phsa.com.br"}]',
    6, '[]');

-- -----------------------------------------------------

-- -----------------------------------------------------
-- CONTRATAÇÃO INTERNA DA PLATAFORMA
-- -----------------------------------------------------
INSERT INTO `spw_plano_contratado` (
  `plc_id`, `plc_idorganizacao`, `plc_idplano`, `plc_idusuariocontratante`,
  `plc_grupo_contratacao`, `plc_status`, `plc_statusclass`,
  `plc_statusfinanceiro`, `plc_origem`, `plc_ciclo`, `plc_ambiente`,
  `plc_dtinicio`, `plc_dtfim`, `plc_dtvencimento`, `plc_dtpagamento`,
  `plc_valor`, `plc_observacao`, `plc_aceite`, `plc_termosuso`,
  `plc_termosusoversao`, `plc_avisoprivacidade`, `plc_avisoprivacidadeversao`,
  `plc_assinatura`, `plc_parametro`, `plc_log`
) VALUES (
  1, 1, 1, 1, 'PLC_SIMPLEWEB_INTERNO_202607',
  'Ativo', 'success', 'Isento', 'Sistema', 'Mensal', 'Compartilhado',
  '2026-01-01 00:00:00', '2027-12-31 23:59:59', NULL, NULL, 0.00,
  'Plano interno da organizacao central do Simple Web.',
  0, NULL, NULL, NULL, NULL, NULL, @param_interno, '[]'
);

-- Script de palavras-chave disponíveis para mensagens do sistema.
-- Os registros representam campos autorizados pelo catálogo backend.

INSERT INTO `spw_palavra_chave` (
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
INSERT INTO `spw_conhecimento_categoria` (`cct_status`,`cct_statusclass`,`cct_nome`,`cct_ordem`,`cct_log`)
SELECT 'Ativo','success',@categoria_nome,(SELECT COALESCE(MAX(cct_ordem),0)+1 FROM `spw_conhecimento_categoria`),'[]'
WHERE NOT EXISTS (SELECT 1 FROM `spw_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome);
SET @categoria_primeiros_passos = (SELECT cct_id FROM `spw_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome LIMIT 1);

SET @categoria_nome = 'Navegação e contexto';
INSERT INTO `spw_conhecimento_categoria` (`cct_status`,`cct_statusclass`,`cct_nome`,`cct_ordem`,`cct_log`)
SELECT 'Ativo','success',@categoria_nome,(SELECT COALESCE(MAX(cct_ordem),0)+1 FROM `spw_conhecimento_categoria`),'[]'
WHERE NOT EXISTS (SELECT 1 FROM `spw_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome);
SET @categoria_navegacao = (SELECT cct_id FROM `spw_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome LIMIT 1);

SET @categoria_nome = 'Cadastros e acessos';
INSERT INTO `spw_conhecimento_categoria` (`cct_status`,`cct_statusclass`,`cct_nome`,`cct_ordem`,`cct_log`)
SELECT 'Ativo','success',@categoria_nome,(SELECT COALESCE(MAX(cct_ordem),0)+1 FROM `spw_conhecimento_categoria`),'[]'
WHERE NOT EXISTS (SELECT 1 FROM `spw_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome);
SET @categoria_cadastros = (SELECT cct_id FROM `spw_conhecimento_categoria` WHERE BINARY cct_nome = BINARY @categoria_nome LIMIT 1);

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
INSERT INTO `spw_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

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
INSERT INTO `spw_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

SET @con_titulo = 'Confira o projeto e o contexto ativo';
SET @con_resumo = 'Verifique o projeto e a organização selecionados antes de consultar ou cadastrar informações.';
SET @con_tags = JSON_ARRAY('projeto','organização','contexto','unidade');
SET @con_conteudo = CONCAT(
  '<p>O contexto ativo define a quais dados você tem acesso e onde novos registros serão criados. Em ambientes com mais de uma organização ou unidade, confirme a identificação exibida no sistema antes de executar uma tarefa.</p>',
  '<ol><li>Confira o nome ou a identificação do projeto e da organização ativos.</li><li>Se houver seletor de contexto, escolha a unidade correta antes de abrir o cadastro.</li><li>Depois de trocar o contexto, confirme novamente os filtros e os dados exibidos.</li><li>Se a unidade esperada não estiver disponível, solicite ao administrador a revisão dos seus vínculos e permissões.</li></ol>',
  '<p>Não tente contornar restrições de acesso alterando endereços ou identificadores na página.</p>'
);
INSERT INTO `spw_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

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
INSERT INTO `spw_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

SET @con_titulo = 'Entenda status e histórico dos registros';
SET @con_resumo = 'Use o status para controlar a disponibilidade e o histórico para acompanhar alterações.';
SET @con_tags = JSON_ARRAY('status','histórico','alterações','auditoria');
SET @con_conteudo = CONCAT(
  '<p>O status indica se um registro está disponível para uso nos fluxos do sistema. Inativar um cadastro geralmente impede novas seleções, mas não apaga o histórico relacionado.</p>',
  '<p>Quando a tela disponibilizar uma aba de Histórico, consulte-a para verificar alterações registradas. Antes de mudar o status ou editar dados, confira se está no registro e no contexto corretos.</p>',
  '<p>Não exclua nem recrie um registro para corrigir uma informação sem antes verificar as opções de edição e as regras do módulo. Ações disponíveis dependem das permissões da sua conta.</p>'
);
INSERT INTO `spw_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

SET @con_titulo = 'Acessos e permissões: peça somente o necessário';
SET @con_resumo = 'Entenda por que menus e ações variam conforme o nível de acesso da sua conta.';
SET @con_tags = JSON_ARRAY('acesso','permissões','usuário','segurança');
SET @con_conteudo = CONCAT(
  '<p>As permissões determinam quais módulos, registros e ações cada conta pode utilizar. Por isso, duas pessoas podem visualizar menus ou botões diferentes no mesmo projeto.</p>',
  '<ul><li>Use sua própria conta e mantenha suas credenciais em sigilo.</li><li>Solicite ao responsável apenas as permissões necessárias para suas atividades.</li><li>Se uma ação esperada não estiver disponível, informe o módulo e a tarefa ao administrador para que ele revise seu acesso.</li><li>Ao deixar de exercer uma função, peça a revisão ou remoção das permissões relacionadas.</li></ul>',
  '<p>Não compartilhe senhas nem tente acessar dados fora do contexto autorizado.</p>'
);
INSERT INTO `spw_conhecimento` (`con_idcategoria`,`con_status`,`con_statusclass`,`con_titulo`,`con_descricao_resumida`,`con_conteudo`,`con_tags`,`con_ordem`,`con_log`)
SELECT @con_categoria_id,'Ativo','success',@con_titulo,@con_resumo,@con_conteudo,@con_tags,COALESCE((SELECT MAX(con_ordem)+1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id),1),'[]'
WHERE @con_categoria_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `spw_conhecimento` WHERE con_idcategoria=@con_categoria_id AND BINARY con_titulo = BINARY @con_titulo);

COMMIT;
