const axios = require('axios')
const leftPad = require('left-pad')

module.exports = (() => {

    const getParametersToCreate = ( codsac ) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/generate_database.php',
                method: 'POST',
                data: { codsac: codsac }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const copy = ( customer ) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/create-database.php',
                method: 'POST',
                data: { customer: customer }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const update = ( obj ) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/customers/update-new-database.php',
                method: 'POST',
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const create = ( codsac ) => {
        return new Promise((resolve, reject) => {
            getParametersToCreate( codsac )
            .then( res => {
                const { data } = res
                const customer = `${data.CLI_SIGLA[0]}${leftPad(codsac, 5, '0')}`
                copy( customer )
                .then( res => {
                    const obj = Object.assign({}, res.data, {data: data, codsac: codsac})
                    update( obj )
                    .then( res => resolve(res) )
                    .catch( err => reject(err) )
                })
                .catch( err => reject(err) )
            })
            .catch( err => reject(err) )
        })
    }

    return {
        create: (codsac) => create(codsac)
    }

})()