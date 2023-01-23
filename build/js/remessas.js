const remessa = require('../api/remessa')
const format = new Format()

const content = document.getElementById('contents')
content.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="30" />')

remessa.list().then( res => {
    try {
        const result = res.data        
        content.innerHTML = ""

        if ( result.success ) {
            const ul = document.createElement('ul')
            ul.setAttribute('class', 'list-group')
            
            const customer = result.status.customer
            //Função que insere os dados na lista
            const putInList = (obj) => {
                const customer = obj.customer
                const li = `
                    <li class="list-group-item">
                        <input type="checkbox" name="remessas" checked="checked" value="${customer}" />
                        ${customer}
                    </li>
                `
                ul.insertAdjacentHTML('beforeend', li);
            }
            //Opção de escrever na conta transitória
            const form = `
                <b>Deseja escrever os custos nas respectivas contas transitórias?</b>
                <section style="display:block;margin: 10px 15px 10px 0;">
                    <label for="write-ct" style="margin: 0 10px 0 0;">
                        <input type="radio" name="write-ct" value="true" /> Sim
                    </label>
                    <label for="write-ct">
                        <input type="radio" name="write-ct" value="false" checked /> Não
                    </label>
                </section>
                <section style="display:none;">
                <b>Deseja escrever as informações da remessa no AdminPJ?</b>
                <section style="display:block;margin: 10px 15px 10px 0;">
                    <label for="write-adminpj" style="margin: 0 10px 0 0;">
                        <input type="radio" name="write-adminpj" value="true" /> Sim
                    </label>
                    <label for="write-adminpj">
                        <input type="radio" name="write-adminpj" value="false" checked /> Não
                    </label>
                </section>
                </section>
                <b>Deseja enviar o email de processamento?</b>
                <section style="display:block;margin: 10px 15px 10px 0;">
                    <label for="send-mail" style="margin: 0 10px 0 0;">
                        <input type="radio" name="send-mail" value="true" /> Sim
                    </label>
                    <label for="send-mail">
                        <input type="radio" name="send-mail" value="false" checked /> Não
                    </label>
                    <section id="attach-file" style="display:none;margin: 10px 15px 10px 0;">
                            <label><input type="checkbox" name="attach-file" /> Anexar recibo de pagamento</label>
                    </section>
                </section>
            `
            content.insertAdjacentHTML('afterbegin', form)
            //Inserindo os dados da remessa na lista
            customer.map( el => putInList({list: ul, customer: el}) )
            //Inserindo a lista no DOM
            content.insertAdjacentElement('afterbegin', ul)
            content.insertAdjacentHTML('afterbegin', '<label><input type="checkbox" name="toggle" checked="checked" /> marcar/desmarcar todos</label>')
            //Botão "processar"
            const btn = document.createElement('button')
            btn.setAttribute('type', 'button')
            btn.setAttribute('id', 'processing-shipps')
            btn.setAttribute('class', 'btn btn-primary')
            btn.insertAdjacentHTML('afterbegin', '<span class="glyphicon glyphicon-cloud-upload"></span> Processar arquivos de remessa')
            content.appendChild(btn)
        } else {
            const h4 = document.createElement('h4')
            h4.insertAdjacentHTML('afterbegin', `<span class="glyphicon glyphicon-alert"></span> ${result.status}`)
            content.insertAdjacentElement('afterbegin', h4)
        }
    } catch (e) {
        console.log(e)
    }
}).catch( err => console.log(err) )

//Toggle
const toggleCheckbox = (e) => {
    const target = e.target
    const { tagName, name } = target
    
    if ( tagName == "INPUT" && name == "toggle" ) {
        const { checked } = target
        Array.from(document.querySelectorAll('input[name=remessas]')).map( checkbox => checkbox.checked = checked )
    }
}

EventHandler.bind(document, 'click', toggleCheckbox)

const toggleAttach = (e) => {
    const { tagName, name, value } = e.target

    if ( tagName == "INPUT" && name == "send-mail" ) {
        
        const attach = document.querySelector('#attach-file')
        attach.style.display = value == "true" ? 'block' : 'none'
    }
}

EventHandler.bind(document, 'click', toggleAttach)

//Botão processar
function ProcessarRemessa(e) {
    const { target } = e
    const { tagName, id } = target
    const not_processed_shipping = []
    
    if ( tagName == "BUTTON" && id == "processing-shipps" ) {
        //Imprime os dados da remessa na tela
        //@el é a variável que vai receber os dados que serão mostrados na tela
        const showShipping = (el, obj) => {
            const sigla = obj.SIGLA
            const sequencial = obj.SEQUENCIAL
            const data_recebimento = obj.DATA_RECEBIMENTO
            const pacotes = [obj.PACOTES]
            const bancos = [obj.BANCO]
            let sumValor = 0
            let sumQtde = 0

            //Informa se a remessa já foi processada
            const isShippingProcessed = obj => obj.already_processed

            //Alerta de remessa processada
            if ( isShippingProcessed(obj) ) {
                const { SIGLA, SEQUENCIAL } = obj
                not_processed_shipping.push(`${SIGLA}${SEQUENCIAL}`)
                return
            }

            //Processamento de pacotes
            let i = 1
            const packageDetails = (dom, element) => {
                for ( const prop in element ) {
                    const props = element[prop]
                    const qtde = props.QTDE_TITULOS
                    if ( qtde === 0 ) continue //Se não houver nenhum título no pacote, passa para o outro pacote
                    sumQtde += qtde
                    const valor = props.VALOR_TOTAL
                    sumValor += valor;
                    const desc = props.DESC
                    
                    let collapse = `
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="headingOne" style="background:#fff">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#${sigla}${sequencial}${i}" aria-expanded="true" aria-controls="${sigla}${sequencial}${i}">
                                            Pacote ${i} - ${desc}
                                        </a>
                                    </h4>
                                </div>
                                <div id="${sigla}${sequencial}${i}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                                    <div class="panel-body" style="padding:0 0 0 10px;">
                                        <table class="table table-condensed">
                                            <thead>
                                                <tr>
                                                    <td>Quantidade de títulos</td>
                                                    <td>Valor Total (R$)</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>${qtde}</td>
                                                    <td>${format.number_format(valor, 2, ',', '.')}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `
                    dom.insertAdjacentHTML(`beforeend`, collapse)
                    i++
                }
            
            }

            //Recupera o total da quantidade de títulos e valores de cada pacote
            const packageProperties = (obj) => {
                const menor_vencimento = obj.remessa.MENOR_VCTO.split('-').reverse().join('/')
                const maior_vencimento = obj.remessa.MAIOR_VCTO.split('-').reverse().join('/')
                const pacotes = obj.pacotes
                const dom = obj.dom
                let qtde = 0
                let valor = 0

                for ( const prop in pacotes ) {
                    qtde += pacotes[prop].QTDE_TITULOS
                    valor += pacotes[prop].VALOR_TOTAL
                }

                let table = `
                    <table class="table table-condensed table-bordered" style="background:#fff">
                        <thead>
                            <tr>
                                <td>Quantidade de Títulos</td>
                                <td>Valor Total (R$)</td>
                                <td>Vencimento</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${qtde}</td>
                                <td>${format.number_format(valor, 2, ',', '.')}</td>
                                <td>De <b>${menor_vencimento}</b> até <b>${maior_vencimento}</b></td>
                            </tr>
                        </tbody>
                    </table>
                `
                dom.insertAdjacentHTML('beforeend', table)
            }


            //Imprime os dados bancários na tela
            const bankDetails = (el, elem) => {

                const showElementBank = (el, element) => {
                    for ( const prop in element ) {
                        let banco = ""
                        let calloutClass = "" 
                        switch ( prop ) {
                            case '001':
                                banco = 'Banco do Brasil'
                                calloutClass = "warning"
                            break
                            case '104':
                                banco = 'Caixa Econômica Federal'
                                calloutClass = "primary"
                            break
                            case '237':
                                banco = "Bradesco S/A"
                                calloutClass = "danger"
                            break
                        }

                        const convenios = element[prop]
                        let callout = `<div class="bs-callout bs-callout-${calloutClass}" style="background:#fff">`
                        callout += `<h4>${banco}</h4>`
                        for ( const convenio in convenios ) {
                            callout += `O convênio <b>${convenio}</b> contém ${convenios[convenio]} títulos<br />`
                        }
                        callout += `</div>`

                        el.insertAdjacentHTML('beforeend', callout)
                    } 
                }
                
                showElementBank(el, elem)
            }

            //Caso a remessa esteja em atraso
            //Mostra o alerta
            const deliveryDeadlineAlert = (el, obj) => {
                const menor_vcto = new Date(obj.MENOR_VCTO)
                const data_recebimento = new Date(obj.DATA_RECEBIMENTO.split(' ')[0].split('/').reverse().join('-'))
                const MS_PER_DAY = 1000 * 60 * 60 * 24
                
                const diffDays = (a, b) => {
                    const vcto = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate())
                    const recebimento = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate())
                    
                    return Math.floor((vcto - recebimento) / MS_PER_DAY)
                }

                const diff = diffDays(menor_vcto, data_recebimento)
                
                if ( diff < 13 ) {
                    let panel = `
                        <div class="panel panel-danger">
                            <div class="panel-heading">
                                <span class="glyphicon glyphicon-exclamation-sign"></span> Solicitação em atraso
                            </div>
                            <div class="panel-body">
                                <p>
                                    Remessa atrasada há <u>${15 - diff}</u> dias
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Obs:
                                </p>
                                <p>AUTORIZADA [&nbsp;&nbsp;&nbsp;&nbsp;] Sim [&nbsp;&nbsp;&nbsp;&nbsp;] Não</p>
                                <p>Por _______________________________________</p>
                                <p>Em ____ / ____ / ________ às ____ : ____</p>
                            </div>
                        </div>
                    `
                    el.insertAdjacentHTML('beforeend', panel)
                }
            }

            //Mostra as etapas do processamento
            const showProcessingSteps = (el, obj) => {
                let valor = 0
                
                const { PACOTES, DATA_PAGAMENTO, REPASSE, PROCESSADO_POR, EMAIL_STATUS, CONTA_TRANSITORIA_STATUS, ADMINPJ_STATUS } = obj
                
                for ( const key in PACOTES ) {
                    const { CUSTO } = PACOTES[key]
                    valor += CUSTO.IMPRESSAO + CUSTO.ENTREGA
                }
                //Verifica se existe tarifa de débito em conta
                //Se houver, soma com o valor total
                if ( !obj.ISENTO_TARIFA_DEBITO_AUTOMATICO ) {
                    valor += parseFloat(obj.TARIFA_DEBITO_CONTA)
                }
                let step = `
                    <h4><span class="glyphicon glyphicon-tag"></span> Etapas do processamento</h4>
                    <p>
                        [${ EMAIL_STATUS ? '&nbsp;X&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;'}] Email de processamento para o cliente&nbsp;&nbsp;&nbsp;&nbsp;
                        [${ CONTA_TRANSITORIA_STATUS.status ? '&nbsp;X&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;'}] Atualização da conta transitória&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <p>
                        ${!REPASSE ? `[&nbsp;&nbsp;&nbsp;&nbsp;] Gerar, imprimir e registrar <b>Débito em Conta</b> e ou Boleto.` : '' }
                    </p>
                    <p>
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Salvar em <b>.htm</b>&nbsp;&nbsp;&nbsp;&nbsp;
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Upload da conta transitória&nbsp;&nbsp;&nbsp;&nbsp;
                    </p>
                    <p>[${ ADMINPJ_STATUS.status ? '&nbsp;X&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;'}] Atualização de dados no AdminPJ</p>
                    <p>
                        <b>Avisos/Balancetes</b><br /> 
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Sim - Enviar E-mail de Alteração do Formulário<br />
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Não<br />
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Envio para a gráfica ____/____/_______<br />
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Lançar a Nota fiscal e aquivar na pasta do cliente.<br />
                        [&nbsp;&nbsp;&nbsp;&nbsp;] Laçar os impostos da NFe na tabela AdminPJ.                       
                    </p>
                    <h4><span class="glyphicon glyphicon-tag"></span> Informações de pagamento</h4>
                    <p>[${ REPASSE ? '&nbsp;X&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;'}] Repasse (RE) &nbsp;&nbsp;&nbsp;&nbsp;[${ !REPASSE ? '&nbsp;X&nbsp;' : '&nbsp;&nbsp;&nbsp;&nbsp;'}] Débito em Conta (DC)</p>
                    <p>Custo: <b>R$ ${format.number_format(valor, 2, ',', '.')}</b> | Data de pagamento: <b>${DATA_PAGAMENTO}</b></p>
                    <p>Processado por <b>${PROCESSADO_POR}</b></p><br />
                    <b>Obs:</b> 
                `
                el.insertAdjacentHTML('beforeend', step)
            }

            //Título da remessa
            let title = `
                <h5>
                    <span class="glyphicon glyphicon-user"></span> ${sigla}
                    | <span class="glyphicon glyphicon-file"></span> ${sequencial}
                    | <span class="glyphicon glyphicon-download-alt"></span> Recebido em ${data_recebimento}
                </h5>
            `
            el.insertAdjacentHTML('beforeend', title)
            //Informações bancárias
            bancos.map((elem) => bankDetails(el, elem))
            //Alerta de prazo
            deliveryDeadlineAlert(el, obj)
            //Pacotes
            pacotes.map((elem) => packageDetails(el, elem))
            //Total (qtde, valor)
            pacotes.map((elem) => packageProperties({remessa: obj, dom: el, pacotes: elem}))
            //Etapas do processamento
            showProcessingSteps(el, obj)
            //Linha divisória
            el.insertAdjacentHTML('beforeend', '<br clear="all" style="page-break-after:always;" />')
        }

        //Caso alguma remessa não tenha sido processada
        const notProcessedShipping = (data) => {
            if ( data.length > 0 ) {
                return `
                    <div class="panel panel-primary">
                        <div class="panel-heading">Remessas já processadas anteriormente</div>
                        <div class="panel-body">
                        ${data.map( el => el.toString() ).sort().join(', ')}
                        </div>
                    </div>
                `
            }

            return ''
        }

        //Caso alguma conta transitória não tenha sido escrita
        const CTNotFound = (ct_not_found) => {
            if ( ct_not_found.length > 0 ) {
                return `<section style="margin: 20px 0 0 0" class="alert alert-danger">Contas transitórias não encontradas: <b>${ct_not_found.join(', ')}</b></section>`
            }

            return ''
        }

        //Caso algum arquivo não seja enviado para a pasta do cliente
        const FailedCopy = (failed) => {
            if ( failed.length > 0 ) {
                return `<section style="margin: 20px 0 0 0" class="alert alert-danger">Os arquivos abaixo não foram enviados para sua pasta de destino: <br /><b>${failed.join(',<br/>')}</b></section>`
            }

            return ''
        }

        const writeCt = document.querySelector('input[name=write-ct]:checked')
        const writeAdminPJ = document.querySelector('input[name=write-adminpj]:checked')
        const sendMail = document.querySelector('input[name=send-mail]:checked')
        const remessas = Array.from(document.querySelectorAll('input[name=remessas]:checked'))
                            .map( checkbox => checkbox.value )
        const attach = document.querySelector('input[name=attach-file]').checked
        
        content.innerHTML = ""
        content.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="30" />')
        
        remessa.processing({
            shipping: remessas,
            write: writeCt.value,
            writeAdminPJ: writeAdminPJ.value, 
            send_mail: sendMail.value,
            attach: attach
        }).then( res => {
            try {
                content.innerHTML = ""
                const result = res.data

                if ( result.error ) {
                    return content.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${result.message}</section>`)
                }
                //Data/Hora do processamento
                const addZero = (time) => time < 10 ? `0${time}` : time
                const date = new Date()
                const today = date.toLocaleDateString('pt-BR').split('-').reverse().join('/')
                const hour = `${addZero(date.getHours())}:${addZero(date.getMinutes())}:${addZero(date.getSeconds())}`
                content.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-exclamation-sign"></span> Processado em <b>${today}</b> às <b>${hour}</b></h4><hr />`)
                result.data.map((el) => showShipping(content, el))
                content.insertAdjacentHTML('afterbegin', notProcessedShipping(not_processed_shipping))
                content.insertAdjacentHTML('beforeend', CTNotFound(result.ct_not_found))
                content.insertAdjacentHTML('beforeend', FailedCopy(result.copy_errors))
                content.insertAdjacentHTML('beforeend', '<button type="button" id="print" onClick="window.print()" style="margin: 20px 0 0 0" class="btn btn-primary"><span class="glyphicon glyphicon-print"></span> Imprimir relatório</button>')
            } catch (e) {
                console.log(e)
            }
        })
    }
}

EventHandler.bind(document, 'click', ProcessarRemessa)