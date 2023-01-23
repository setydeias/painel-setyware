<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
	<section class="row">
		<section class="col-md-12 col-sm-12">
			<h3>Consultar Retornos</h3>
		</section>
	</section>
	<hr />
    <form class="form-inline">
        <div class="form-group">
            <label class="control-label">Nosso Número: </label>
            <input type="text" name="nosso-numero" class="form-control" maxlength="17" placeholder="Apenas números">
        </div>
        <button type="button" name="btn-get" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Consultar</button>
        <div class="form-group">
        </div>
    </form>
    <div id="message" class="row"></div>
    <script src="/painel/dist/consultaRetorno.js"></script>
</div>