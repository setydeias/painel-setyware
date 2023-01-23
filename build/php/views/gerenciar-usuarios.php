<?php
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
    <div class="row">
        <div class="col-md-6">
            <h3>Usuários do sistema</h3>
        </div>
        <div class="col-md-6">
            <a href="/painel/users/add">
                <button class="btn btn-primary pull-right" style="margin:20px 0 0 0;">
                    <span class="glyphicon glyphicon-plus-sign"></span> Adicionar usuário
                </button>
            </a>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-md-12" id="usuarios"></div>
    </div>
</div>
<script src="/painel/dist/usuarios.js"></script>