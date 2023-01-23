<?php
	include_once '../header.php';
	include_once '../functions.php';
?>
<div id="content">
	<h3>
		Exportar banco ADM77777.GDB
	</h3>
	<hr />
	<div class="form-group row col-md-6">
        <div class="input-group">
            <div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Banco ADM77777</div>
            <input type="text" class="form-control" id="bancoadm77777" disabled="disabled" name="bancoadm77777" />
        </div>
        <small style="margin:10px 0 0 0">
            <span>Caso queira mudar o local do banco de dados, vá até:</span>
            <ol class="breadcrumb" style="background-color:#fff;margin:10px 0;border:1px solid #ccc;">
                <li>Gerenciar</li>
                <li><a href="/painel/parametros">Parâmetros</a></li>
                <li>Diretórios</li>
            </ol>
        </small>
    </div>
    <div class="row col-md-12">
        <button class="btn btn-primary" name="btn-upload"><span class="glyphicon glyphicon-cloud-upload"></span> Iniciar upload</button>
    </div>
	<div class="row col-md-5">
        <div id="msg" style="margin: 10px 0;"></div>
    </div>
</div>
<script src="/painel/dist/adm77777.js"></script>