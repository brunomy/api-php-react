/*
	USANDO O SERVIÇO PM2 NO SERVIDOR PARA MANTER A APLICAÇÃO RODANDO EM BACKGROUND
	https://www.terlici.com/2015/06/20/running-node-forever.html
*/

const moment = require("moment");
const mysql = require("mysql");

const config = require(__dirname + '/../sistema/System/Core/configs.json');

const ws_server = require('ws').Server;
const wss = new ws_server({clientTracking: true, port: 8002});


//INIT --------------------------------

    let srvr = {
        id:0,
        clients: [],
        channels: []
	};

	setInterval(() => {
		for(i in srvr.clients){
			srvr.clients[i].send('{"heartbeat":"1"}');
		}
	}, 30000);

//HANDLERS ----------------------------

	wss.on('connection', function(ws){
		ws.id = srvr.id++;
		ws.send('{"action":"id","id":"'+ws.id+'"}');
		srvr.clients.push(ws);

		ws.on('message', function (msg) {
			//w(msg);
			let obj = JSON.parse(msg);

			if(obj.request=="connect" && typeof obj.mesa !== 'undefined'){
				connectChannel(ws.id, obj.mesa);
			}

			else if(obj.request=="view" && typeof obj.mesa !== 'undefined'){
				joinChannel(ws.id, obj.mesa);
			}

			else if(obj.request=="titulo" && typeof obj.mesa !== 'undefined'){
				trocarTitulo(obj.mesa, obj.titulo);
			}

			else if(obj.request=="numeros" && typeof obj.mesa !== 'undefined'){
				trocarNumeros(obj.mesa);
			}

			else if(obj.request=="num_back" && typeof obj.mesa !== 'undefined'){
				backspace(obj.mesa);
			}

			else if(obj.request=="close"){
				ws.close();
			}
		});

		ws.on('close',function(){
			for(i in srvr.clients){
                if(srvr.clients[i].id == ws.id){
                    srvr.clients.splice(i,1);
                    logoutChannel(ws.id);
                }
			}
		});

        ws.on('error',function(){
            w("WSS ERROS");
		});
	});


// ACTIONS ------------------------------

	connectChannel = (id, mesa) => {
		//reconnect
		if(mesa.id != ""){
			for(i in srvr.channels){
				if(srvr.channels[i].id == id && srvr.channels[i].mesa == mesa.mesa){
					w("TENTAR RECONECTAR USUARIO "+id+" NA MESA "+mesa.mesa);
					reconnetChannel(id, mesa);
				}
			}
		}else{ //new connection
			createChannel(id, mesa.mesa, mesa.titulo);
		}
	};

	createChannel = (id, mesa, titulo) => {
		//verifica se o usário já está em um canal ou se mesa já está sendo usada
			for(i in srvr.channels){
				if(srvr.channels[i].id == id){
					return false;
				}
				if(srvr.channels[i].mesa == mesa){
					for(i in srvr.clients){
						if(srvr.clients[i].id == id){
							srvr.clients[i].send('{"action":"ocupado"}');
						}
					}

					//desconnectClient(id);
					return false;
				}
			}
		w("NOVO CANAL CRIADO "+id+" PARA MESA "+mesa);
		let rodadas_registros = [];
		
		for(i=0;i<=37;i++){
			//obs: indice 37 representa o 00
			rodadas_registros[i]=0;
		}

		let rodadas_qtd = {};

		srvr.channels.push({
			id: id,
			mesa: mesa,
			titulo: titulo,
			views: [],
			ultimos_numeros: [],
			rodadas_registros: rodadas_registros,
			rodadas_qtd: rodadas_qtd
		});

		query("SELECT * FROM tb_cassino_mesas WHERE chaveAcesso = '"+mesa+"'", function(rows){
			if(rows.length > 0){
				index = getMesaIndex(rows[0].chaveAcesso);
				if(rows[0].rodadas_registros_json != null){
				 	srvr.channels[index].rodadas_registros = JSON.parse(rows[0].rodadas_registros_json);
				}
				if(rows[0].rodadas_qtd_json != null){
					srvr.channels[index].rodadas_qtd = JSON.parse(rows[0].rodadas_qtd_json);
				}
			}

			for(i in srvr.clients){
				if(srvr.clients[i].id == id){
					srvr.clients[i].send(JSON.stringify({"action":"connected", "mesa":srvr.channels[index]}));
				}
			}

		});

	};

	joinChannel = (id, mesa) => {
		for(i in srvr.channels){
			if(srvr.channels[i].mesa == mesa){
				if(srvr.channels[i].views.includes(id)){
					w("USUÁRIO "+id+" JÁ ESTÁ NO CANAL "+srvr.channels[i].id+" MESA "+srvr.channels[i].mesa);
				}else{
					w("USUÁRIO "+id+" ENTROU NO CANAL "+srvr.channels[i].id+" MESA "+srvr.channels[i].mesa);
					srvr.channels[i].views.push(id);
					trocarTitulo(mesa, srvr.channels[i].titulo);
				}
			}
		}
	};

	logoutChannel = (id) => {
		for(i in srvr.channels){
			if(srvr.channels[i].id == id){
				destroyChannel(id);
			}else{
				for(j in srvr.channels[i].views){
					if(srvr.channels[i].views[j] == id){
						w("USUÁRIO "+id+" SAIU NO CANAL "+srvr.channels[i].id+" MESA "+srvr.channels[i].mesa);
						srvr.channels[i].views.splice(j,1);
					}
				}
			}
		}
	};

	destroyChannel = (id) => {
		for(i in srvr.channels){
			if(srvr.channels[i].id == id){
				for(j in srvr.channels[i].views){
					for(k in srvr.clients){
						if(srvr.clients[k].id == srvr.channels[i].views[j]){
							w("CANAL "+srvr.channels[i].id+" MESA "+srvr.channels[i].mesa+" FOI REMOVIDO");
							srvr.clients[k].close();
						}
					}
				}
				srvr.channels.splice(i,1);
			}
		}
	};

	desconnectClient = (id) => {
		for(i in srvr.clients){
			if(srvr.clients[i].id == id){
				srvr.clients[i].close();
			}
		}
	};

	trocarTitulo = (mesa, titulo) => {
		query("UPDATE tb_cassino_mesas SET titulo = '"+titulo+"' WHERE chaveAcesso='"+mesa+"'");
		for(i in srvr.channels){
			if(srvr.channels[i].mesa == mesa){
				srvr.channels[i].titulo = titulo;
				for(j in srvr.channels[i].views){
					for(k in srvr.clients){
						if(srvr.clients[k].id == srvr.channels[i].views[j]){
							srvr.clients[k].send('{"action":"titulo", "titulo":"'+titulo+'"}');
						}
					}
				}
			}
		}
	};

	trocarNumeros = (mesa) => {
		let index = getMesaIndex(mesa.mesa);
		atualizaRodada(mesa.rodadas_registros, mesa.rodadas_qtd, mesa.mesa);
		for(i in srvr.channels[index].views){
			for(j in srvr.clients){
				if(srvr.clients[j].id == srvr.channels[index].views[i]){
					srvr.clients[j].send('{"action":"numeros", "numeros":"'+mesa.ultimos_numeros+'"}');
				}
			}
		}
	};

	getMesaIndex = (mesa) => {
		for(i in srvr.channels){
			if(srvr.channels[i].mesa == mesa){
				return i;
			}
		}
	}

	atualizaRodada = (rodadas_registros, rodadas_qtd, mesa) => {
		query("UPDATE tb_cassino_mesas SET rodadas_registros_json = '"+JSON.stringify(rodadas_registros)+"', rodadas_qtd_json = '"+JSON.stringify(rodadas_qtd)+"' WHERE chaveAcesso='"+mesa+"'");
	};



// UTILS ------------------------------

	w = (msg, type="normal") => {
		bg_color = "\033[00m";
	    if(type=="success") bg_color = "\033[42m";
	    if(type=="warning") bg_color = "\033[43m";
	    if(type=="danger") bg_color = "\033[41m";
	    if(type=="info") bg_color = "\033[46m";
		console.log(bg_color+moment().format("YYYY-MM-DD H:mm:ss")+" "+msg+"\033[00m");
	};

	query = (query, callback="undefined") => {
        let mysql_conn = mysql.createConnection(config.db);
        mysql_conn.connect();
        mysql_conn.query(query, function(err, rows, fields){
            mysql_conn.end();
            if (err) throw err;
            if(callback!="undefined") callback(rows);
        });
	}

	getDate = () => {
		let data = new Date().toISOString().slice(0,10);
		return data;
	}

	newHash = () => {
      var text = "";
      var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

      for (var i = 0; i < 6; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

      return text;
    }
