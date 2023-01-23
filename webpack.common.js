const path = require('path')
const CleanWebpackPlugin = require('clean-webpack-plugin')

module.exports = {
    entry: {
        adm77777: ['babel-polyfill', './build/js/adm77777.js'],
        cadastroCliente: ['babel-polyfill', './build/js/cadastro-clientes.js'],
        convenioCobranca: ['babel-polyfill', './build/js/convenios-cobranca.js'],
        conversor: ['babel-polyfill', './build/js/conversor.js'],
        consultaRetorno: ['babel-polyfill', './build/js/consulta-retorno.js'],
        criarRetorno: ['babel-polyfill', './build/js/criar-retorno.js'],
        conversorCnab240toSQL: ['./build/js/conversorCnab240toSQL.js'],
        conversorCnab400to240: ['./build/js/conversorCnab400to240.js'],
        editarUsuarios: ['./build/js/editar-usuarios.js'],
        exportarContasTransitorias: ['babel-polyfill', './build/js/export-conta-transitoria.js'],
        exportarRemessaGrafica: ['babel-polyfill', './build/js/exportar-remessa-grafica.js'],
        exportarRemessaRegistro: ['babel-polyfill', './build/js/exportar-remessa-registro.js'],
        gerenciarRemessaRegistro: ['babel-polyfill', './build/js/gerenciar-remessa-registro.js'],
        listarClientes: ['babel-polyfill', './build/js/listar-clientes.js'],
        login: ['babel-polyfill', './build/js/login.js'],
        main: ['babel-polyfill', './build/js/main.js'],
        mensalidade: ['babel-polyfill', './build/js/mensalidade.js'],
        parametros: ['babel-polyfill', './build/js/parametros.js'],
        processamento: ['babel-polyfill', './build/js/processamento.js'],
        remessa: ['babel-polyfill', './build/js/remessas.js'],
        remessasRegistradas: ['babel-polyfill', './build/js/remessas-registradas.js'],
        resetarSenha: ['babel-polyfill', './build/js/resetar-senha.js'],
        syncDatabase: ['babel-polyfill', './build/js/sync-database.js'],
        usuarios: ['babel-polyfill', './build/js/usuarios.js'],
        premioAdimplencia: ['babel-polyfill', './build/js/premio-adimplencia.js'],
        premioAdimplenciaSorteios: ['babel-polyfill', './build/js/premio-adimplencia-sorteios.js']
    },
    plugins: [
        new CleanWebpackPlugin(['dist'])
    ],
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: '[name].js'
    }
}