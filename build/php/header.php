<?php 
	session_start();
	if(!isset($_SESSION['id'])):
		unset($_SESSION);
		header('Location: /painel/login');
	endif;
	$nome = explode(' ', $_SESSION['nome']);
	include_once 'functions.php';
	$con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
	$query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
	$ip_server = ibase_fetch_object($query)->IP_SERVER;
	ibase_close($con);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Painel Administrativo Setydeias</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" type="image/gif" href="/painel/build/images/favicon.png" />
	<link rel="stylesheet" type="text/css" href="/painel/src/css/main.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/painel/src/css/print.css" media="print" />
	<link rel="stylesheet" type="text/css" href="/painel/node_modules/bootstrap/dist/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="/painel/node_modules/toastr/build/toastr.min.css" />
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" />
	<script src="/painel/node_modules/vanilla-masker/build/vanilla-masker.min.js"></script>
	<script src="/painel/node_modules/jquery/dist/jquery.min.js"></script>
	<script src="/painel/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="/painel/node_modules/toastr/build/toastr.min.js"></script>
	<script src="/painel/build/js/event-handler.js"></script>
	<script src="/painel/build/js/Format.class.js"></script>
	<script src="http://<?php echo $ip_server; ?>:7774/socket.io/socket.io.js"></script>
</head>
<body>
<div id="navbar">
	<div id="navbar-content">
		<div id="logo">
			<p><a>Painel Administrativo Setydeias</a></p>
		</div>
		<div id="nav-options">
			<ul>
				<li class="ativo"><a href="#"><span class="glyphicon glyphicon-export"></span> Exportar</a>
					<ul>
						<li><a href="/painel/exportar-remessa-registro">Remessa para Banco</a></li>
						<li><a href="/painel/exportar-contas-transitorias">Conta Transitória</a></li>
						<li><a href="/painel/exportar-adm77777">ADM77777.GDB</a></li>
					</ul>
				</li>
				<li class="ativo"><a href="#"><span class="glyphicon glyphicon-refresh"></span> Processar &nbsp;<span style="background:red;" class="pull-right badge" id="TotalShipp"></span></a>
					<ul>
						<li><a href="/painel/index">Retornos</a></li>
						<li><a href="/painel/remessas">Remessas para Gráfica <span style="background:red;" class="pull-right badge" id="ShippSTL"></span></a></li>
						<li><a href="/painel/registrar-remessas">Remessas para Banco <span style="background:red;" class="pull-right badge" id="ShippBank"></span></a></li>
						<li><a href="/painel/conversor">Conversor CNAB 240/400</a></li>
						<li><a href="/painel/mensalidades">Mensalidades</a></li>
						<li><a href="/painel/sync-database">Sincronizar bases de dados</a></li>
					</ul>
				</li>
				<li class="ativo"><a href="#"><span class="glyphicon glyphicon-cog"></span> Gerenciar</a>
					<ul>
						<li><a href="/painel/list/customers">Clientes</a></li>
						<li><a href="/painel/convenio-de-cobranca">Convênios de Cobrança</a></li>
						<li><a href="/painel/create-return-file">Gerar retorno</a></li>
						<li><a href="/painel/parametros">Parâmetros</a></li>
						<li><a href="/painel/reset-password">Resetar senha da 2º via</a></li>
						<li><a href="/painel/users">Usuários do sistema</a></li>
						<li><a href="/painel/premio-adimplencia">Prêmio adimplência</a></li>
					</ul>
				</li>
				<li class="ativo"><a href="#"><span class="glyphicon glyphicon-search"></span> Consultar</a>
					<ul>
						<li><a href="/painel/gerenciar-remessas-banco">Remessas para Banco</a></li>
						<li><a href="/painel/consultar-ocorrencias-retornos">Retornos</a></li>
					</ul>
				</li>
				<li class="ativo" style="padding:0;">
					<a href="#">
						<?php
							$gender = $_SESSION['sexo'] === 'm' ? 'male' : 'female';
							echo is_null($_SESSION['foto']) 
								? "<img src='/painel/build/images/avatar-default-$gender.png' width='30' class='img-circle' />&nbsp;&nbsp;"
								: '<img src="/painel/build/images/perfil/'.$_SESSION['foto'].'" width="30" class="img-circle" />&nbsp;&nbsp;';
						?>
						<?php echo $nome[0]; ?>
					</a>
					<ul>
						<li><a href="/painel/build/php/loggout.php"><span class="glyphicon glyphicon-log-out"></span> Sair</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>
<script>
	try {
		//Socket
		var socket = io.connect('http://<?php echo $ip_server; ?>:7774/'),
		  ShippBank = document.getElementById('ShippBank'),
		  ShippSTL = document.getElementById('ShippSTL'),
		  TotalShipp = document.getElementById('TotalShipp');

		//Socket Listeners
		socket.on('remessa banco', function(files){
			ShippBank.innerHTML = (files.length > 0) ? files.length : '';
		});

		socket.on('remessa stl', function(files){
			ShippSTL.innerHTML = (files.length > 0) ? files.length : '';
		});

		socket.on('total', function(data){
			TotalShipp.innerHTML = (data > 0) ?  data : '';
		});
	} catch (e) {
		console.log(new Error('Erro ao conectar-se com o servidor'));
	}

	$(document).ready(function(){
		$(".collapse").collapse({ hide: true });
	});
</script>