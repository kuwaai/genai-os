const io = require('socket.io')({
	cors: {
		origin: '*',
		methods: ['GET', 'POST'],
		allowedHeaders: ['Content-Type'],
	},
});
dotenv = require('dotenv');
dotenv.config();
tags = ["Info"]

players = {}

function findUserBySocket(socket){
	for (userID in players){
		if (players[userID].socket === socket) return userID;
	}
	return null;
}

function getCurrentDateTime() {
	const now = new Date();

	const formatDatePart = (part) => String(part).padStart(2, '0');
	const dateStr = `${now.getFullYear()}-${formatDatePart(now.getMonth() + 1)}-${formatDatePart(now.getDate())}`;
	const timeStr = `${formatDatePart(now.getHours())}:${formatDatePart(now.getMinutes())}:${formatDatePart(now.getSeconds())}`;

	return `${dateStr} ${timeStr}`;
}

function logger(msg, tag = 0){
	console.log(`[${tags[tag]}][${getCurrentDateTime()}] ${msg}`);
}

io.on('connection', client => {
	logger("An user connected")
	authTimeout = setTimeout(() => {
		client.disconnect(); // Disconnect the client if the "auth" event is not received within 30 seconds
		logger("An user timeout. automatically disconnected.");
	}, 10000); 

	client.on("auth", (token) => {
		logger("Doing auth on the client with API token: " + token)
		fetch(`http://localhost/api_auth?key=${process.env.APP_KEY}&api_token=${token}`)
			.then((response) => {
				if (!response.ok) {
					client.disconnect();
					throw new Error('Can\'t reach the auth server!');
				}
				return response.json(); // Assuming the response is in JSON format
			})
			.then((data) => {
				if (data.status == "success") {
					clearTimeout(authTimeout);
					userID = data.tokenable_id
					if (players.hasOwnProperty(userID)) {
						// Should disconnect the original connection
						logger(`Replaced [${userID},${players[userID].name}]'s old connection`)
						players[userID].socket.disconnect();
					}
					players[userID] = { "status": "Lobby", "socket": client, "openai_token": data.openai_token, "name": data.name };
					
					client.on("Action",(data)=>{
						logger(`${userID},${players[userID].name}: Changed to ${data} State` )
						if (data == "Queue"){
							client.emit('change','Queue')
							players[userID].status = "Queue"
						}else if (data == "Lobby"){
							client.emit('change','Lobby')
							players[userID].status = "Lobby"
						}else if (data == 'Play'){
							client.emit('change','Play')
						}
					})
					client.emit('authed')
				} else {
					client.disconnect();
					logger("An client just failed to do API Auth with token: " + token)
				}
			})
			.catch((error) => {
				logger('Error:' + error);
			});
	});

	client.on('disconnect', () => {
		logger("An user disconnected")
		userID = findUserBySocket(client);
		if (userID != null){
			delete players[userID]
		}else{
			logger("Unauthed user disconnected")
		}
		
	});
});

io.listen(3000);