<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;
	use Helpers;

	function fgb_enviarEmail_teste()
	{
		var_dump('teste');
	}

	// Envio de email global
	function fgb_enviarEmail (
		$_titulo,
		$_destinatario,
		$_mensagem,
		$_template="",
		$_variaveis=[],
		$_login="",
		$_remetente="",
		$_anexo="",
		$_config=""
	) {
		// PROVISÓRIO
		// define("CONF_PHPMAILER_HOST", "smtp.limplardiarista.com.br");
		// define("CONF_PHPMAILER_USER", "contato@limplardiarista.com.br");
		// define("CONF_PHPMAILER_PASS", "LimplarDiarista@2023");
		// define("CONF_PHPMAILER_SENDER_NAME", "LIMPLAR DIARISTA");
		// define("CONF_PHPMAILER_SENDER_EMAIL", "contato@limplardiarista.com.br");
		// define("CONF_PHPMAILER_TITLE", "LIMPLAR DIARISTA - TEM MENSAGEM PARA VOCÊ!");
		define("CONF_PHPMAILER_PORT", 587);
		define("CONF_PHPMAILER_OPTION_LANG", "pt_br");
		define("CONF_PHPMAILER_OPTION_CHARSET", "UTF-8");
		define("CONF_PHPMAILER_TEMPLATE_DIR", getcwd() . '/source/Phpmailer/template/');
		define("CONF_PHPMAILER_TEMPLATE", 'default');

		$mail = new PHPMailer; // Instacia de novo email
		$mail->isSMTP(); // Modo SMTP
		$mail->Debugoutput = 'html'; // Solicitar saída de depuração compatível com HTML
		$mail->Encoding = 'base64';
		$mail->SMTPAuth = true; // Se deseja usar a autenticação SMTP
		$mail->SMTPSecure = 'tls';
		// $mail->SMTPSecure = false;
		// $mail->SMTPAutoTLS = false;
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		$mail->SMTPDebug = 0; // 0 = off 1 = client msg 2 = client and server msg

		$mail->Host = CONF_PHPMAILER_HOST; // Host do servidor de email
		$mail->Port = CONF_PHPMAILER_PORT; // 25, 465 ou 587
		$mail->setLanguage(CONF_PHPMAILER_OPTION_LANG);
		$mail->CharSet = CONF_PHPMAILER_OPTION_CHARSET;

		// Usuário para usar para autenticação SMTP
		if ( $_login=="" ) {
			$mail->Username = CONF_PHPMAILER_USER;
			$mail->Password = CONF_PHPMAILER_PASS;
		} else if( is_array($_login) && count($_login)==2 ) {
			$mail->Username = $_login['username'];
			$mail->Password = $_login['password'];
		}else{
			return (object) array(
				'status' 	=> false,
				'info' 		=> 'Parâmetro de login incorreto!'
			);
			exit;
		}

		// REMETENTE
		if ( $_remetente=="" ) {
			$mail->setFrom(CONF_PHPMAILER_SENDER_EMAIL, CONF_PHPMAILER_SENDER_NAME);
			$remetente_nome = CONF_PHPMAILER_SENDER_NAME;
			$remetente_email = CONF_PHPMAILER_SENDER_EMAIL;
		} else if( is_array($_remetente) ) {
			foreach ($_remetente as $nome => $email) {
				$mail->setFrom($email, $nome);
			}
			$mail->addReplyTo(CONF_PHPMAILER_SENDER_EMAIL, CONF_PHPMAILER_SENDER_NAME);

			$primeiro_remt = array_key_first ($_remetente);
			$remetente_nome = $primeiro_remt;
			$remetente_email = $_remetente[$primeiro_remt];
		}else{
			return (object) array(
				'status' 	=> false,
				'info' 		=> 'Parâmetro de remetente incorreto!'
			);
			exit;
		}

		// Extras
		$mail->AddCustomHeader( "X-Confirm-Reading-To: ".$remetente_email );
		$mail->AddCustomHeader( "Return-receipt-to: ".$remetente_email );

		// DESTINATARIO	
		if ( is_array($_destinatario) ) {
			foreach ($_destinatario as $nome => $email) {
				$mail->addAddress($email, $nome);
			}

			$primeiro_dest = array_key_first ($_destinatario);
			$destinatario_nome = $primeiro_dest;
			$destinatario_email = $_destinatario[$primeiro_dest];
		}else{
			return (object) array(
				'status' 	=> false,
				'info' 		=> 'Parâmetro de destinatário incorreto!'
			);
			exit;
		}

		// TITULO	 
		if ( $_titulo == "" ) {
			$_titulo = CONF_PHPMAILER_TITLE;
		}else{
			if ( strlen($_titulo) <= 10 || strlen($_titulo) >= 50 ) {
				return (object) array(
					'status' 	=> false,
					'info' 		=> 'O título deve possuir entre 10 a 50 caracteres!'
				);
				exit;
			}
			$mail->Subject = $_titulo;
		}

		// MENSAGEM
		if ( $_mensagem == "" ) {
			return (object) array(
				'status' 	=> false,
				'info' 		=> 'A mensagem do email não informada ou inválida, a mensagem deve ser acima de 10 caracteres!'
			);
			exit;
		}

		// TEMPLATE
		if ( $_template=="" ) {
			$template_dir 	= CONF_PHPMAILER_TEMPLATE_DIR.CONF_PHPMAILER_TEMPLATE.'/'.CONF_PHPMAILER_TEMPLATE;
		} else {
			$template_dir 	= CONF_PHPMAILER_TEMPLATE_DIR.$_template.'/'.$_template;
		}

		if ( !file_exists($template_dir.'.html') ) {
			return (object) array(
				'status' 	=> false,
				'info' 		=> 'Não foi possível localizar arquivos do template de email!',
				'log'			=> $template_dir,
				'cwd'			=> getcwd()
			);
			exit;
		}

		// GET TEMPLATE
		$template 	= file_get_contents($template_dir.'.html');
		if ( $template==false ) {
			return (object) array(
				'status' 	=> false,
				'info' 		=> 'Não foi possível renderizar o template de email!',
				'log'			=> $template
			);
			exit;
		}

		// Config template - optional
		if ( file_exists($template_dir.'.php') ) {
			include($template_dir.'.php');
		}

		// Replace template default
		$template 	= str_replace('{CONF_PROJECT_NAME}', CONF_PROJECT_NAME, $template);
		$template 	= str_replace('{remetente_nome}', $remetente_nome, $template);
		$template 	= str_replace('{remetente_email}', $remetente_email, $template);
		$template 	= str_replace('{destinatario_nome}', $destinatario_nome, $template);
		$template 	= str_replace('{destinatario_email}', $destinatario_email, $template);
		$template 	= str_replace('{titulo}', $_titulo, $template);
		$template 	= str_replace('{mensagem}', $_mensagem, 	$template);
		$template 	= str_replace('{year}', date('Y'), 	$template);
		$template 	= str_replace('{month}', date('m'), 	$template);
		$template 	= str_replace('{day}', date('d'), 	$template);
		$template 	= str_replace('{date}', date('d/m/Y'), 	$template);
		$template 	= str_replace('{time}',date('h:i'), 	$template);

		if ( is_array($_variaveis) ) {
			if ( count($_variaveis)>0 ) {
				foreach ($_variaveis as $key => $value) {
					$template 	= str_replace('{'.$key.'}', $value, $template);
				}
			}
		}

		// Set HTML Template
		$mail->msgHTML($template);
		$mail->AltBody = $_mensagem;

		// Anexar arquivo
		if ( $_anexo!="" ) {
			if ( is_array($_anexo) ) {
				foreach ($_anexo as $key => $anexo) {
					$mail->addAttachment($anexo);
				}
			} else {
				$mail->addAttachment($_anexo);
			}
		}

		if ( $_config!="" ) {
			switch ($_config) {
				case 'debugtemplate':
					return $template;
					exit;
				break;

				case 'debugmail':
					return $mail;
					exit;
				break;
			}
		}

		// ENVIAR EMAIL
		if ( $mail->send() ) {
			return (object) array(
				'status' 	=> true,
				'info' 		=> 'Email enviado com sucesso!',
				'CharSet'				=> $mail->CharSet,
				'ContentType'		=> $mail->ContentType,
				'Encoding'			=> $mail->Encoding,
				'ErrorInfo'			=> $mail->ErrorInfo,
				'From'					=> $mail->From,
				'FromName'			=> $mail->FromName,
				'Sender'				=> $mail->Sender,
				'Subject'				=> $mail->Subject,
				'Body'					=> $_mensagem,
				'AltBody'				=> $mail->AltBody,
				'Mailer'				=> $mail->Mailer,
				'Host'					=> $mail->Host,
				'Port'					=> $mail->Port,
				'SMTPDebug'			=> $mail->SMTPDebug,
				'Debugoutput'		=> $mail->Debugoutput,
				'Timeout'				=> $mail->Timeout,
				'Template'			=> $template_dir,
			);
			exit;
		} else {
			return (object) array(
				'status' 				=> false,
				'info' 					=> 'Erro ao enviar email!',
				'CharSet'				=> $mail->CharSet,
				'ContentType'		=> $mail->ContentType,
				'Encoding'			=> $mail->Encoding,
				'ErrorInfo'			=> $mail->ErrorInfo,
				'From'					=> $mail->From,
				'FromName'			=> $mail->FromName,
				'Sender'				=> $mail->Sender,
				'Subject'				=> $mail->Subject,
				'Body'					=> $_mensagem,
				'AltBody'				=> $mail->AltBody,
				'Mailer'				=> $mail->Mailer,
				'Host'					=> $mail->Host,
				'Port'					=> $mail->Port,
				'SMTPDebug'			=> $mail->SMTPDebug,
				'Debugoutput'		=> $mail->Debugoutput,
				'Timeout'				=> $mail->Timeout,
				'Template'			=> $template_dir,
				'Destinatario'	=> $_destinatario,
			);
			exit;
		}
	}

?>