## Website Real Poker

Projeto foi desenvolvido em PHP utiliza banco de dados MySQL

Obs. Em maio de 2022 foi realizada uma atualização na estrutura de todo o projeto e criado um novo repositorio para facilitar o remanejamento dos arquivos entre pastas

---

## Requisitos


**PHP 7.2 (mysql (pdo), mbstring), Git, Apache2, libapache2-mod-php7.1, mysql-server, mysql-workbench**

Stack padrão de desenvolvimento web com php e mysql.


---

## Estrutura de pastas


**root/classes**
Classes que são incorporadas no início de cada requisição da parte do website.

**root/modelagem**
Pasta contém arquivo de modelagem do banco de dados. Atenção, sempre sincronizar imediatamente quando realizar uma modificação na modelagem.

**root/public**
Frontend do website

**root/public/sistema**
Pasta que contem todos os arquivos do sistema administrativo do website

**root/payments**
Pasta com as bibliotecas de cada integração que temos com diversos gateway

**root/scripts**
Pasta destinadas a scripts que rodam no servidor via cron/scheduler


---

## CONFIGURAÇÕES

### Local

Com o **mysql-server** instalado, crie um banco de dados (de preferencia com o nome **realpoker**), e restaure um dump atualizado.

Para atualizar o banco de dados utilize **Mysql Workbench**, abra o modelo ```public/sistema/db_modelagem/modelagem.mwb``` e faça a sincronização com o banco de dados acima (com o modelo aberto, vá em: **Database > Synchronize Model**)

Configure o arquivo .env a partir do .env.example


**Pastas**

Restaure as pastas ```files``` e ```uploads``` 


**Apache**

Configure o vhost do apache para apontar diretamente para a pasta ```root```:
```
<VirtualHost *:80>
	ServerName local.realpoker.com.br

	ServerAdmin webmaster@localhost
	DocumentRoot /path/to/realpoker

	ErrorLog ${APACHE_LOG_DIR}/local.realpoker.com.br-error.log
	CustomLog ${APACHE_LOG_DIR}/local.realpoker.com.br-access.log combined

	<Directory "/path/to/realpoker">
		Options Indexes FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>
</VirtualHost>
```
(**Não esqueça de configurar o arquivo ```/etc/hosts``` para redirecionar ```local.realpoker.com.br``` para ```127.0.0.1```**)


**SSL**
exemplo: https://stackoverflow.com/questions/25946170/how-can-i-install-ssl-on-localhost-in-ubuntu

**Login no sistema**

http://local.realpoker.com.br/sistema

usuário: guest

senha: 1

---

### Servidor

**FORGE**

A partir da atualização do sistema em maio de 2022, passamos a utilizar a ferramenta de gestão de servidores cloud e projetos web forge.laravel.com
Através dessa ferramenta é possivel configurar o servidor e as automatizações.



**SSL**

A partir da atualização do sistema em maio de 2022, passamos a utilizar o serivço da Cloudflare 



**Depĺoy Automático**

Está configurado no Forge para fazer deploy automático de todas os commits realizados na branch master.  Muito cuidado com quem tem acesso a branch master pois estará com permissão para publicar diretamente no servidor.



**Backups**

Para os backups automáticos funcionarem, precisa estar com o AWS CLI instalado e [configurado de acordo com a documentação](https://docs.aws.amazon.com/pt_br/cli/latest/userguide/cli-chap-getting-started.html) e ter dois registros no **crontab**, ex.:

```
0 3,15 * * * /home/forge/realpoker.com.br/scripts/backup_mysql_script.sh {db.name} 127.0.0.1 {db.user} {db.password} >> /home/forge/mysql-backup.log
0 6 * * * /home/forge/realpoker.com.br/scripts/backup_files_script.sh >> /home/forge/files-backup.log
```

Obs. Essa configuração também pode estar no Scheduler do Forge.

(No script de backup do mysql, é necessario passar o **host**, o **usuário** e a **senha** do banco de dados (separados por espaço, conforme o exemplo), para que o script seja capaz de realizar o dump)