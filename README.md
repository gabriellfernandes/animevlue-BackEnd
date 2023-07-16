
# Api de usuario documentation

## Tabela de Conteúdos

1. [Sobre](#sobre)
2. [Instalação](#install)
3. [Documentação](#doc)
4. [Frontend](#frontend)
5. [Desenvolvedor](#devs)
6. [Termos de uso](#terms)


---

<a name="sobre"></a>

## 1. Sobre

- API em Symfony com CRUD de Usuario, Anime, Favorito, Comentario, permitindo gerenciar dados, api feita para utilizar no front end ja criado.

---
<a name="install"></a>

## 2. Instalação e uso

### 2.1 Requisitos:
- PHP a partir da versão 5.5.9
- Composer gerenciador de dependências para PHP
- Banco de dados MySQL

### 2.2 Instalação
2.2.1 - Crie um banco de dados chamado animevlue_api no MySQL

2.2.2 - Após o clone no repositório para adicionar todas as dependências do composer json execute o comando: 
`composer install` 

2.2.3 - Crie um arquivo na raiz do projeto chamado .env e faça as configurações das variáveis de ambiente com base no .env.example do projeto
```
DATABASE_URL="mysql://usuario:senha@127.0.0.1:3306/animevlue_api?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

2.2.4 - Apos criar o arquivo .env e adicionar as informações do banco de dados rode o comando `symfony console doctrine:migrations:migrate` caso nao tenha o CLI do symfone use o comando `php bin/console doctrine:migrations:migrate` se tudo estiver correto é para retonar
```
[OK] Already at the latest version ("DoctrineMigrations\Version20230713170416")

```

2.2.6 - Para rodar projeto utilize o comando `symfony console server:start` ou caso não tenha o CLI do symfony use o comando `php bin/console server:start` no terminal, caso de tudo certo receberá uma mensagem parecida com essa:

```
INFO  Server running on http://localhost:8000
Press Ctrl+C to stop the server
```

<a name="doc"></a>

## 3. Documentação
3.1 Todas as rotas tem que estar authenticado
- <a name="insomina" href="https://drive.google.com/file/d/1Sv83ktRRqDbtTldyXm03xGAvItkVIVQE/view?usp=sharing" target="_blank">Arquivo de importação para o Insomnia</a>

<a name="frontend"></a>

## 4. Front-End
- <a name="Repositorio Front-End" href="https://github.com/gabriellfernandes/" target="_blank">Repositorio Front-End</a>

<a name="devs"></a>

## 5. Desenvolvedor

[ Voltar para o topo ](#tabela-de-conteúdos)

- <a name="Gabriel-Fernandes" href="https://www.linkedin.com/in/gabriel-lima-fernandes/" target="_blank">Gabriel Fernandes</a>

<a name="terms"></a>

## 6. Termos de uso

Este é um projeto Open Source para fins educacionais e não comerciais, **Tipo de licença** - <a name="mit" href="https://opensource.org/licenses/MIT" target="_blank">MIT</a>
