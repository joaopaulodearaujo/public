<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

		<link rel="stylesheet" type="text/css" href="css/arm710a.css">

		<script type="text/javascript" src="js/mootools.js"></script>
		<script type="text/javascript" src="js/arm710a.js"></script>

		<title>ARM710a</title>
  	</head>

	<body>

		<!-- CABECALHO -->

		<div id="titulo">ARM710a</div>

<!--		<div align="left">
			* Arquitetura de Computadores 2<br />
			* Professor <b>Luiz Henrique Andrade Correia</b>
		</div>-->

		<!-- FIM - HEADER -->

		<div class="linha">

			<div id="container_codigo">
	
			<!-- ENTRADA -->

				<div class="subtitulo">Código a ser interpretado</div>
		
				<form id="form_entrada" method="post" action="executar.php">
		
					<textarea id="text_entrada" name="entrada"></textarea><br />
						
		
					<div align="center">
	
						<input class="botao" type="submit" value="Executar">
						<input class="botao" type="reset" value="Reset">
	
					</div>

				</form>
	
			</div>

			<!-- FIM - ENTRADA -->
	
			<!-- SAIDA -->
			
			<div id="container_execucao">
	
				<div class="subtitulo">Execu&ccedil;&atilde;o</div>

				<br />

				<div id="conteudo_execucao"></div>
	
			</div>

			<!-- FIM - SAIDA -->

		</div>

		<div class="linha">

			<!-- BANCO DE REGISTRADORES -->
			
			<div id="container_pipeline">
	
				<div class="subtitulo">Est&aacute;gios do Pipeline</div>

				<br />

				<div id="conteudo_pipeline"></div>
	
			</div>

			<!-- FIM - BANCO DE REGISTRADORES -->

			<!-- BANCO DE REGISTRADORES -->
			
			<div id="container_registradores">
	
				<div class="subtitulo">Banco de Registradores</div>

				<br />

				<div id="conteudo_registradores"></div>
	
			</div>
	
			<!-- FIM - BANCO DE REGISTRADORES -->

		</div>

		<div class="linha">

			<!-- MEMORIA DE DADOS -->
			
			<div id="container_dados">
	
				<div class="subtitulo">Mem&oacute;ria de Dados</div>

				<br />

				<div id="conteudo_dados"></div>
	
			</div>

			<!-- FIM - MEMORIA DE DADOS -->

			<!-- MEMORIA DE INSTRUCOES -->
			
			<div id="container_instrucoes">
	
				<div class="subtitulo">Mem&oacute;ria de Instru&ccedil;&otilde;es</div>

				<br />

				<div id="conteudo_instrucoes"></div>
	
			</div>

			<!-- FIM - MEMORIA DE INSTRUCOES -->

		</div>

		<br /><br /><br /><br /><br /><br />

		<!-- RODAPE -->
		
		<div id="container_grupo">

			<b>Jo&atilde;o Paulo de Araujo</b>

		</div>

		<!-- FIM - CONTAINER -->

	</body>

</html>