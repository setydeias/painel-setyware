const axios = require('axios')

module.exports = (() => {

    const listRemessaGraficaToExport = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/remessas/list-remessa-grafica-exportar.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const exportRemessaGrafica = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/remessas/exportar-remessa-grafica.php',
                method: 'POST',
                data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const list = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/remessas/list-remessa-grafica.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const listRemessaRegistrada = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/remessas/list-remessa-registrada.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const processing = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/remessas/processamento-remessas.php',
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const processingRemessaRegistrada = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/remessas/processamento-remessas-registradas.php',
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        listRemessaGraficaToExport: () => listRemessaGraficaToExport(),
        exportRemessaGrafica: (data) => exportRemessaGrafica(data),
        list: () => list(),
        listRemessaRegistrada: () => listRemessaRegistrada(),
        processing: (obj) => processing(obj),
        processingRemessaRegistrada: (obj) => processingRemessaRegistrada(obj)
    }

})()