const axios = require('axios')

module.exports = (() => {

    const getDataByCodSac = (codsac) => {
        return new Promise((resolve, reject) => {
            axios({
				url: '/painel/build/php/get-info-clientes.php',
			    method: 'POST',
			    data: { codsac: codsac }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getTarifasByCodSac = (codsac) => {
        return new Promise((resolve, reject) => {
            axios({
				url: '/painel/build/php/tarifas/get-tarifas-by-codsac.php',
			    method: 'POST',
			    data: { codsac: codsac }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const create = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/cadastrar-clientes.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const createUnidadeTemp = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/cadastrar-unidades-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const deleteUnidadeTemp = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/deletar-unidades-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getUnidadeTemp = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/listar-unidades-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateUnidadeTemp = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/update-unidades-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const ativarUnidadeTemp = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/ativar-unidade-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const desativarUnidadeTemp = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/desativar-unidade-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const confirmaContato = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/confirmar-contato-unidade-temp.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const update = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/update-customer.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const get = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/customers/list-all-customers.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const remove = (codsac) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/delete-customer.php',
                method: 'POST',
                data: { codsac }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const removeImage = (codsac) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/remove-customer-image.php',
                method: 'POST',
                data: codsac
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const resetPassword = (sigla, codsac) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/reset-password.php',
                method: 'POST',
                data: { sigla: sigla, codsac: codsac }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        getDataByCodSac: (codsac) => getDataByCodSac(codsac),
        getTarifasByCodSac: (codsac) => getTarifasByCodSac(codsac),
        create: (data) => create(data),
        createUnidadeTemp: (data) => createUnidadeTemp(data),
        deleteUnidadeTemp: (data) => deleteUnidadeTemp(data),
        getUnidadeTemp: (data) => getUnidadeTemp(data),
        updateUnidadeTemp: (data) => updateUnidadeTemp(data),
        ativarUnidadeTemp: (data) => ativarUnidadeTemp(data),
        desativarUnidadeTemp: (data) => desativarUnidadeTemp(data),
        confirmaContato: (data) => confirmaContato(data),
        update: (data) => update(data),
        get: () => get(),
        remove: (codsac) => remove(codsac),
        removeImage: (codsac) => removeImage(codsac),
        resetPassword: (sigla, codsac) => resetPassword(sigla, codsac)
    }

})()