<?php
	include_once '../header.php';
	include_once '../functions.php';
?>
<div id="content">
	<h3>
		Gerar Retorno
	</h3>
	<hr />
    <section class="row">
        <form class="form col-md-4">
            <section class="form-group">
                <label>Cliente <span class="error">*</span></label>
                <select name="customer" class="form-control"></select>
            </section>
            <section id="info-area">
                <section class="row">
                    <section class="form-group col-md-12">
                        <label>Data do arquivo</label>
                        <section class="row">
                            <label class="col-md-5">
                                De <span class="error">*</span>
                                <input name="dataDe" type="text" maxlength="10" class="form-control date-mask" />
                            </label>
                            <label class="col-md-5">
                                Até
                                <input name="dataAte" type="text" maxlength="10" class="form-control date-mask" />
                            </label>
                        </section>
                    </section>
                </section>
                <section class="row">
                    <section class="form-group col-md-8">
                        <section class="form-group">
                            <label>Convênio</label>
                            <select name="convenio" class="form-control"></select>
                        </section>
                    </section>
                </section>
                <section class="form-group">
                    <small><span class="error">*</span> Campos obrigatórios</small>
                    <button type="button" name="btn-criar-retorno" class="btn btn-primary"><span class="glyphicon glyphicon-refresh"></span> Gerar arquivo de retorno</button>
                </section>
            </section>
            <hr />
        </form>
    </section>
    <section class="row">
        <section class="col-md-6">
            <section id="message-area"></section>
        </section>
    </section>
</div>
<script src="/painel/dist/criarRetorno.js"></script>