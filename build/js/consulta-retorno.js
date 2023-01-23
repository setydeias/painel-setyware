const retornos = require('../api/retornos')

/*
* Códigos de movimentação para retornos
*/
const codMovimentoRetorno = {
    '02': 'REGISTRADO',
    '03': 'REJEITADO',
    '06': 'LIQUIDAÇÃO',
    '09': 'BAIXADO (Saiu do sistema bancário)',
    '14': 'ALTERAÇÃO DE VENCIMENTO',
    '17': 'LIQUIDAÇÃO SEM REGISTRO',
}

/*
* Códigos de movimentação para remessas
*/
const codMovimentoRemessa = {
    '01': 'SOLICITAÇÃO DE REGISTRO',
    '02': 'SOLICITAÇÃO DE BAIXA DO TÍTULO',
    '06': 'SOLICITAÇÃO DE ALTERAÇÃO DO VENCIMENTO'
}

/*
* Realiza a consulta de acordo com o nosso número informado
*/
const nosso_numero = document.querySelector('input[name=nosso-numero]')
nosso_numero.focus()

const messageArea = document.querySelector('#message')
const btnGetOccurrences = document.querySelector('button[name=btn-get]')
const getOccurrences = () => {
    const { value } = nosso_numero
    messageArea.innerHTML = ""
    messageArea.insertAdjacentHTML('beforeend', `
        <div class="col-md-5" style="margin: 20px 0;">
            <img src="/painel/build/images/loading.gif" width="30" />
        </div>
    `)
    
    retornos.getOccurrencesByOurNumber(value)
    .then( res => {
        messageArea.innerHTML = ""
        const has_occurrences = !(res.data instanceof Array)
        
        if ( !has_occurrences ) {
            return messageArea.insertAdjacentHTML('beforeend', `
                <div class="col-md-5" style="margin: 20px 0;">
                    <span class="glyphicon glyphicon-info-sign"></span> Nenhuma ocorrência foi encontrada
                </div>
            `)
        }

        const customer = Object.keys(res.data)[0]
        const occurrences = res.data[customer][value].map( occurence => {
            const { TIPO_REGISTRO, COD_MOVIMENTO, DATA_ARQUIVO } = occurence
            const descricaoCodMovimento = TIPO_REGISTRO === 'RETORNO' ? codMovimentoRetorno[COD_MOVIMENTO] : codMovimentoRemessa[COD_MOVIMENTO]
            const data_formatada = DATA_ARQUIVO.split('-').reverse().join('/')

            return `
                <li class="list-group-item">
                    <span class="glyphicon glyphicon-calendar"></span> ${ data_formatada } &nbsp;|&nbsp;
                    <span class="glyphicon glyphicon-info-sign"></span> ${ descricaoCodMovimento ? descricaoCodMovimento : 'MOVIMENTO NÃO IDENTIFICADO'  }
                </li>
            `
        })
        
        messageArea.insertAdjacentHTML('beforeend', `
            <div class="col-md-6" style="margin: 30px 0">
                <ul class="list-group">${occurrences.join('')}</ul>
            </div>
        `)
    })
    .catch( err => {
        messageArea.innerHTML = ""
        return messageArea.insertAdjacentHTML('beforeend', `
            <div class="col-md-5" style="margin: 20px 0;">
                    <span class="glyphicon glyphicon-info-sign"></span> ${err.response.data || err}
            </div>
        `)
    })
}

EventHandler.bind(btnGetOccurrences, 'click', getOccurrences)