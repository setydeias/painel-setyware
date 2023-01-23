const axios = require('axios')

module.exports = (() => {

    const getGenID = (gen_name) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/get_gen_sacados.php',
                method: 'POST',
                data: { gen_name: gen_name.toString().toUpperCase() }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getSalarioMinimo = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/mensalidades/get-salario-minimo.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getTarifas = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/tarifas/get-tarifas.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getConvenios = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/convenios/get-convenio-processamento.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        getGenID: (gen_name) => getGenID(gen_name),
        getSalarioMinimo: () => getSalarioMinimo(),
        getTarifas: () => getTarifas(),
        getConvenios: () => getConvenios()
    }

})()