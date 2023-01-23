# Painel Administrativo Setydeias
Obs: Todos os comandos devem ser executados no `Node.js command prompt`.
## Dependências

* [NodeJS 8.11.3](https://nodejs.org/en/blog/release/v8.11.3/ "NodeJS")
* [Gulp](https://gulpjs.com/ "Gulp") - Build tasks
```sh
$ npm install -g gulp-cli
$ npm install gulp -D
```
* [Git](https://git-scm.com/downloads "Git") - Gerenciador de versionamento
* [XAMPP PHP 5.6](https://www.apachefriends.org/download.html "XAMPP") - Serviços PHP e MySQL
* [Composer](https://getcomposer.org/download/ "Composer") - Gerenciador de dependências do PHP
* [Firebird 2.0](https://firebirdsql.org/en/firebird-2-0/ "Firebird 2.0") - Serviço de banco de dados
```sh
Marcar a opção “Copiar a biblioteca do cliente Firebird para a pasta de <system>?
```
* [webpack](https://webpack.js.org/ "webpack") - Empacotador de módulos
```sh
$ npm install -g webpack webpack-cli
```

## Como instalar

### Local

1. Acesse `D:/Instaladores/Setydeias/Painel Administrativo Setydeias` e copie o arquivo `painel.rar` para a pasta `C:/xampp/htdocs`
2. Descompacte `painel.rar`

### via GitHub

Para clonar o repositório, insira as seguintes linhas de comando no Node.js command prompt:

```sh
$ cd c:/xampp/htdocs
$ git clone https://github.com/setydeias/painel-setyware.git
```
Login: setydeias
Senha: "padrão"

## Carregando as dependências

Após clonar o repositório:

```sh
$ cd c:/xampp/htdocs/painel
$ npm run start
```

Obs: Caso apresente erros relacionados ao Gulp sass, execute os comandos
```sh
$ npm audit fix
$ npm run start
```

## Configurando o PHP

Abra o arquivo C:\xampp\php\php.ini e descomente as seguintes linhas, removendo o ; do começo:

```
;extension=php_interbase.dll
;extension=php_pdo_firebird.dll
```

Troque o valor do comando ```max_execution_time``` de ```30``` para ```0```:

```
max_execution_time=0
```

Reinicie o XAMPP.

## Estrutura de pastas

Esta estrutura de pastas é necessária para que todos os processos do projeto funcionem corretamente:

```sh
C:/Setydeias/
C:/Setydeias/Setyware/
C:/Setydeias/Setyware/ADM77777/
C:/Setydeias/Setyware/ADM77777/Adm/
C:/Setydeias/Setyware/ADM77777/Adm/Clientes/
C:/Setydeias/Setyware/ADM77777/Adm/Remessas/
C:/Setydeias/Setyware/ADM77777/Adm/Remessas/Processadas/
C:/Setydeias/Setyware/ADM77777/Adm/Remessas/Processadas/Originais/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/Pagamentos em Cheque/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/Processadas/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/Processadas/Originais/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/Titulos Baixados/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/Titulos LQR/
C:/Setydeias/Setyware/ADM77777/Adm/Retornos/Titulos Rejeitados/
```

## Banco de dados

Certifique-se que o banco de dados `ADM77777.gdb` esteja na pasta `C:/Setydeias/Setyware/ADM77777`

## Dicas

1. Sempre após o término desses procedimentos é imprescindível limpar o cache dos navegadores (CTRL + SHIFT + DELETE)
