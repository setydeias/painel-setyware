;(function(){

	let content = document.getElementById('detail')
	  , pathname = window.location.pathname.split('/')
	  , customer = pathname[3].toUpperCase()
	  , remessa = pathname[4]
	  , format = new Format()

	AjaxRequest.init({
		url: '/painel/build/php/remessas/get-remessa-details.php'
	  , method: 'POST'
	  , loading: content
	  , loadImg: '<center><img src="/painel/build/images/loading.gif" width="30" style="margin:0;" /></center>'
	  , data: {
			customer: customer
		  , remessa: remessa
		}
	}).then((res) => {
		Dom.ClearElement([content])
		let result = JSON.parse(res)
		try {
			let title = `<h4><span class='glyphicon glyphicon-list-alt'></span> Dados da remessa ${customer+remessa}</h4>`
			  , status = result.DESC_STATUS[0] == "ENTREGUE" ? 'online' : 'offline'
			  , nome_pacote = result.NOME_PACOTE
			  , tipo_pacote = result.TIPO_PACOTE
			  , venc_inicial = result.DATA_VENC_INICIAL
			  , venc_final = result.DATA_VENC_FINAL
			  , qtde_titulos = result.QTDE_TITULOS
			  , custo_impressao = result.CUSTO_UNITARIO_IMPRESSAO
			  , custo_entrega = result.CUSTO_UNITARIO_ENTREGA
			  , valor_pacote = result.VALOR_TOTAL_PACOTE
			  , data_recebimento = result.DATA_RECEBIMENTO[0] || '---'
			  , data_recebimento_formulario = result.DATA_RECEPCAO_FORMULARIO[0] || '---'
			  , data_envio_grafica = result.DATA_ENVIO_GRAFICA[0] || '---'

			let painel = "<div class='panel panel-default'>"
			painel += "<div class='panel-heading' style='background:#0d0b58;color:#fff;padding:5px 10px;'>"
			painel += `${title} <img src='/painel/build/images/${status}.png' width='10'/> ${result.DESC_STATUS[0]}</div>`
			painel += "<div class='panel-body' style='padding: 10px;'>"
			//Informações sobre os pacotes
			painel += "<div><img src='/painel/build/images/info.png' style='float:left;clear:left;' width='30' /> <h4 style='padding: 8px 50px;'>Informações da remessa</h4></div>"
			painel += "<ul class='list-group'>"
			for ( let i = 0, leng = result.NOME_PACOTE.length; i < leng; i++ ) {
				let initial_date = new Date(venc_inicial[i]).toLocaleDateString()
				  , final_date = new Date(venc_final[i]).toLocaleDateString()
				  , custo_de_impressao = format.FormatMoney(qtde_titulos[i] * custo_impressao[i], 'BRL')
				  , custo_de_entrega = tipo_pacote[i] == "Único" ? format.FormatMoney(custo_entrega[i], 'BRL') : format.FormatMoney(custo_entrega[i] * qtde_titulos[i], 'BRL')

				painel += "<li class='list-group-item'>"
				painel += `<p><img src='/painel/build/images/package.png' width='20' /> Nome do pacote: <b>${nome_pacote[i]} (${tipo_pacote[i]})</b></p>`
				painel += `<p><img src='/painel/build/images/item.png' width='20' /> Vencimento inicial e final: <b>${initial_date} | ${final_date}</b></p>`
				painel += `<p><img src='/painel/build/images/qtde.png' width='20' /> Quantidade de Títulos: <b>${qtde_titulos[i]}</b></p>`
				painel += `<p><img src='/painel/build/images/money.png' width='20' /> Valor do pacote: <b>R$ ${format.FormatMoney(valor_pacote[i], 'BRL')}</b>`
				painel += ` | Custo de impressão: <b><span style='color:red;'>R$ ${custo_de_impressao}</span></b>`
				painel += ` | Custo de entrega: <b><span style='color:red;'>R$ ${custo_de_entrega}</span></b></p>`
				painel += "</li>"
			}
			painel += "</ul>";
			//Processamento na Setydeias
			painel += "<hr /><div><img src='/painel/build/images/wait.png' style='float:left;clear:left;' width='30' /> <h4 style='padding: 8px 50px;'>Processamento na Setydeias</h4></div>"
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Recebimento: <b>${data_recebimento}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Recebimento do formulário: <b>${data_recebimento_formulario}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Envio para a gráfica: <b>${data_recebimento}</b></p>`
			//Processamento na STL
			painel += "<hr /><div><img src='/painel/build/images/print.png' style='float:left;clear:left;' width='30' /> <h4 style='padding: 8px 50px;'>Processamento na STL</h4></div>"
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Confirmação da gráfica: <b>${data_recebimento}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Início da impressão: <b>${data_recebimento_formulario}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Fim da impressão: <b>${data_recebimento}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Início do envelopamento: <b>${data_recebimento}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Fim do envelopamento: <b>${data_recebimento_formulario}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Finalização: <b>${data_recebimento}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Postagem: <b>${data_recebimento}</b></p>`
			//Entrega
			painel += "<hr /><div><img src='/painel/build/images/message.png' style='float:left;clear:left;' width='30' /> <h4 style='padding: 8px 50px;'>Entrega</h4></div>"
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Saída da remessa: <b>${data_recebimento_formulario}</b></p>`
			painel += `<p style='padding:5px 50px;'><img src='/painel/build/images/item.png' width='20' /> Entrega: <b>${data_recebimento_formulario}</b></p>`
			painel += "</div>"
			painel += "</div>"
			painel += "</div>"

			content.insertAdjacentHTML('afterbegin', painel)
		} catch (e) {
			var html = `<h4><span class='glyphicon glyphicon-alert'></span> ${result.error}</h4>`

			content.insertAdjacentHTML('afterbegin', html)
		}
	}, (err) => console.log(new Error(`Erro ao tratar informações: ${err}`)))
})();