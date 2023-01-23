<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
	<div class="row">
        <div class="col-md-6">
            <h3>Clientes</h3>
        </div>
        <div class="col-md-6">
            <a href="/painel/add/customer">
                <button class="btn btn-primary pull-right" style="margin:20px 0 0 0;">
                    <span class="glyphicon glyphicon-plus-sign"></span> Adicionar cliente
                </button>
            </a>
        </div>
    </div>
	<hr />
	<div class="row">
		<div class="col-md-12">
			<span id="total-customers"></span>
			<form class="form form_filter_customers">
				<label>
					<input type="radio" name="filter_type" value="all" checked="checked" /> Todos os clientes
				</label>
				<label>
					<input type="radio" name="filter_type" value="only_active" /> Apenas ativos
				</label>
				<label>
					<input type="radio" name="filter_type" value="only_desactived" /> Apenas desativados
				</label>
				<input 
					class="form-control" 
					type="text" 
					name="search_customer"
					placeholder="Buscar cliente por CÓDIGO DO SACADO, SIGLA ou NOME" />
			</form>
			<ul class="list-group" id="customers"></ul>
		</div>
	</div>
</div>
<script src="/painel/dist/listarClientes.js"></script>