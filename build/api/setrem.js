const axios = require('axios')

module.exports = (() => {

    const list = () => {
        return new Promise((resolve, reject) => {
            axios(`/painel/build/php/setrem/list.php`)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const listBy = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: `/painel/build/php/setrem/list-by.php`,
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const downloadFile = (id) => {
        return new Promise((resolve, reject) => {
            axios({
                url: `/painel/build/php/setrem/download-file.php`,
                method: 'POST',
                data: { id }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const makeFileAvailable = (id) => {
        return new Promise((resolve, reject) => {
            axios({
                url: `/painel/build/php/setrem/make-file-available.php`,
                method: 'POST',
                data: { id }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        list: () => list(),
        listBy: (data) => listBy(data),
        downloadFile: (id) => downloadFile(id),
        makeFileAvailable: (id) => makeFileAvailable(id)
    }
})()