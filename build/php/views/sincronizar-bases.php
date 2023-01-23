<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
	<section class="row">
		<section class="col-md-12 col-sm-12">
			<h3>Sincronizar bases de dados</h3>
		</section>
	</section>
	<hr />
    <form class="form-inline">
        <button type="button" name="btn-sync" class="btn btn-primary"><span class="glyphicon glyphicon-refresh"></span> Sincronizar</button>
    </form>
    <div id="message" style="margin: 10px 0;"></div>
    <script src="/painel/dist/syncDatabase.js"></script>
</div>