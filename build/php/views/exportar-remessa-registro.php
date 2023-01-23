<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<section id="content">
    <section class="row">
        <section class="col-md-12 col-sm-12">
            <h3>Exportar remessas para o banco</h3>
        </section>
    </section>
    <hr />
    <section>
        <span id="total-shipping"></span>
		<ul class="list-group" id="shipping-area"></ul>
        <span class="error"></span>
    </section>
</section>
<script src="/painel/build/js/Format.class.js"></script>
<script src="/painel/dist/exportarRemessaRegistro.js"></script>