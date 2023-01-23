<?php
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
    <div class="row">
        <div class="col-md-6">
            <h3>Adicionar usuário</h3>
        </div>
        <div class="col-md-6">
            <a href="/painel/users"><button class="btn btn-primary pull-right" style="margin:20px 0 0 0;">Voltar</button></a>
        </div>
    </div>
    <hr />
    <form class="col-md-6" enctype="multipart/form-data">
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label for="usuario">Usuário: <span class="error">*</span></label>
                    <input type="text" class="form-control" maxlength="15" id="usuario" name="usuario" autofocus />
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label for="nome">Nome: <span class="error">*</span></label>
                    <input type="text" class="form-control" maxlength="30" id="nome" name="nome" />
                </div>
            </div>
        </div>
        <div class="radio">
            <b>Sexo: <span class="error">*</span></b>
            <div class="row">
                <div class="col-md-12">
                    <label class="radio-inline">
                        <input type="radio" name="sexo" value="m" checked="checked" /> Masculino
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="sexo" value="f" /> Feminino
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label for="password">Senha: <span class="error">*</span></label>
                    <input type="password" class="form-control" maxlength="32" id="password" name="password" placeholder="Mínimo de 6 caracteres" />
                </div>  
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <a href="#" id="avatar"><span class="glyphicon glyphicon-plus-sign"></span> Adicionar foto</a>
                    <input type="file" accept="image/*" name="avatar" style="display:none;" />
                </div>  
            </div>
        </div>
        <div class="row">
            <div class="col-md-6" id="msg"></div>
        </div>
        <button type="button" name="btn-action" class="btn btn-primary"><span class="glyphicon glyphicon-ok-sign"></span> Concluir cadastro</button>
    </form>
    <div class="col-md-6">
        <div id="avatar-place" class="pull-right"></div>
    </div>
</div>
<script src="/painel/dist/usuarios.js"></script>