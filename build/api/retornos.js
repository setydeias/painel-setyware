const axios = require('axios')

module.exports = (() => {

    const list = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/retornos/list-retornos.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const create = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/retornos/create-retorno.php',
                method: 'POST',
                data: { data }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getOccurrencesByOurNumber = (our_number) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/retornos/get-occurrences-by-our-number.php',
                method: 'POST',
                data: { our_number }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        list: () => list(),
        create: (data) => create(data),
        getOccurrencesByOurNumber: (our_number) => getOccurrencesByOurNumber(our_number)
    }
})()