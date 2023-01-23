const axios = require('axios')

module.exports = (() => {

    const add = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/add-convenio-cobranca.php',
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const get = (convenio) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/get-convenio-processamento.php',
                method: 'GET'
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }


    const getConvenio = (convenio) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/get-convenio-processamento-by-id.php',
                method: 'POST',
                data: { convenio }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const addConvenioProcessing = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/add-convenio-processamento.php',
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const editConvenioProcessing = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/edit-convenio-processamento.php',
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const makePattern = (convenio) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/convenio-turn-pattern.php',
                method: 'POST',
                data: { convenio }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const remove = (convenio) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/delete-convenio-cobranca.php',
                method: 'POST',
                data: { convenio }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const checkFileReplacement = (convenio) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/check-file-replacement.php',
                method: 'POST',
                data: { convenio }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        add: (data) => add(data),
        get: (convenio) => get(convenio),
        getConvenio: (convenio) => getConvenio(convenio),
        addConvenioProcessing: (data) => addConvenioProcessing(data),
        editConvenioProcessing: (data) => editConvenioProcessing(data),
        makePattern: (convenio) => makePattern(convenio),
        remove: (convenio) => remove(convenio),
        checkFileReplacement: (convenio) => checkFileReplacement(convenio)
    }
})()