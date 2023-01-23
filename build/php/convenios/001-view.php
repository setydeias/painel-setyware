<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label for="agencia">
                Agência
                <input type="text" class="form-control" name="agencia" maxlength="4" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label for="agencia-dv">
                DV
                <input type="text" class="form-control" name="agencia-dv" maxlength="1" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="conta">
                Conta
                <input type="text" class="form-control" name="conta" maxlength="12" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label for="conta-dv">
                DV
                <input type="text" class="form-control" name="conta-dv" maxlength="1" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label for="numero-convenio">
                Nº do convênio
                <input type="text" class="form-control" name="numero-convenio" maxlength="8" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label for="carteira">
                Carteira
                <input type="text" class="form-control" name="carteira" maxlength="2">
                <div class="error-handler-area"></div>	
            </label>
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label for="variacao">
                Variação
                <input type="text" class="form-control" name="variacao" maxlength="3">
                <div class="error-handler-area"></div>	
            </label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label for="tipo-multa">
                Tipo de multa
                <select class="form-control" name="tipo-multa">
                    <option value="0">Sem Multa</option>
                    <option value="1">Valor Fixo (R$)</option>
                    <option value="2">Percentual (%)</option>
                </select>
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="multa">
                Multa
                <input type="text" class="form-control" name="multa" maxlength="10" disabled="disabled" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <div class="form-group">
            <label for="tipo-juros">
                Tipo de juros
                <select class="form-control" name="tipo-juros">
                    <option value="0">Sem Juros</option>
                    <option value="1">Valor por dia (R$)</option>
                    <option value="2">Mensal (%)</option>
                </select>
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label for="juros">
                Juros
                <input type="text" class="form-control" name="juros" maxlength="10" disabled="disabled" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-1">
        <div class="form-group">
            <label for="protesto">
                Protesto
                <input type="text" class="form-control" name="protesto" maxlength="2" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label for="validade">
                Validade
                <input type="text" class="form-control" name="validade" maxlength="2" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
    <div class="col-md-1">
        <div class="form-group">
            <label for="carencia">
                Carência
                <input type="text" class="form-control" name="carencia" maxlength="2" />
                <div class="error-handler-area"></div>
            </label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
        <div class="form-group">
            <label for="padrao">
                <input type="checkbox" name="padrao" checked="checked" /> 
                Deseja cadastrar este convênio como padrão?
            </label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <button type="button" class="btn btn-primary" name="btn-cadastrar">Cadastrar</button>
    </div>
</div>