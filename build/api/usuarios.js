const axios = require('axios')

module.exports = (() => {

    const get = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/usuarios/get-users.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getById = (user_id) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/usuarios/get-user-by-id.php',
                method: 'POST',
                data: user_id
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const add = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/usuarios/add-users.php',
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }
    
    const edit = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/usuarios/edit-users.php',
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const removePhoto = (user_id) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/usuarios/remove-perfil-image.php',
                method: 'POST',
                data: user_id
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const remove = (user_id) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/usuarios/remove-user.php',
                method: 'POST',
                data: user_id
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const changePassword = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/usuarios/change-password.php',
                method: 'POST',
                data: data
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        get: () => get(),
        getById: (user_id) => getById(user_id),
        removePhoto: (user_id) => removePhoto(user_id),
        add: (data) => add(data),
        edit: (data) => edit(data),
        remove: (data) => remove(data),
        changePassword: (data) => changePassword(data)
    }

})()