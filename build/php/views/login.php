<?php 
	session_start(); 
	$con = ibase_connect('localhost:C:\\Setydeias\\Setyware\\ADM77777\\ADM77777.GDB', 'SYSDBA', 'masterkey');
	$query = ibase_query($con, "SELECT sv.IP_SERVER FROM SERVIDOR_NUVENS_PARAMS sv");
	$ip_server = ibase_fetch_object($query)->IP_SERVER;
	ibase_close($con);
?>
<html>
<head>
	<title>Painel Administrativo Setydeias</title>
	<meta charset="UTF-8" />
	<link rel="stylesheet" type="text/css" href="/painel/src/css/main.css" media="screen">
	<link rel="stylesheet" type="text/css" href="/painel/node_modules/bootstrap/dist/css/bootstrap.min.css">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" />
	<script src="http://<?php echo $ip_server; ?>:7774/socket.io/socket.io.js"></script>
</head>
<body>
	<div id="navbar">
		<div id="navbar-content">
			<div id="logo">
				<p><a>Painel Administrativo Setydeias</a></p>
			</div>
		</div>
	</div>
	<div id="content">
		<div id="login-panel">
			<form>
				<h2>Login</h2><hr/>
				<div class="input-group col-md-12">
				<span class="input-group-addon" id="sizing-addon2"><span class="glyphicon glyphicon-user"></span></span>
				<input type="text" class="form-control" placeholder="Usuário" aria-describedby="sizing-addon2" name="user" id="user" autofocus />
				</div><br/>
				<div class="input-group col-md-12">
				<span class="input-group-addon" id="sizing-addon2"><span class="glyphicon glyphicon-lock"></span></span>
				<input type="password" class="form-control" placeholder="Senha" aria-describedby="sizing-addon2" name="pass" id="pass" />
				</div><br/>
				<div class="col-md-12" id="error-area" style="padding:0;"></div>
				<button type="button" class="btn btn-primary btn-block" id="login" name="login">Entrar</button>
			</form>
		</div>
	</div>
	<script src="/painel/node_modules/jquery/dist/jquery.min.js"></script>
	<script src="/painel/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="/painel/build/js/event-handler.js"></script>
	<script src="/painel/dist/login.js"></script>
</body>
</html>