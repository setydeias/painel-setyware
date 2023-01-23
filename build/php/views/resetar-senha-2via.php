<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
	<h3>Resetar senha da 2º via</h3><hr />
    <form>
        <div class="row">
            <div class="form-group col-md-2">
                <label>Informe o usuário:</label>
                <input type="text" class="form-control" id="usuario" maxlength="8" autofocus />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4" id="msg"></div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary" id="btn-search"><span class="glyphicon glyphicon-search"></span> Buscar</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" id="found">
            </div>
        </div>
    </form>
</div>
<script src="/painel/dist/resetarSenha.js"></script>