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
rooms = {}

function findUserBySocket(socket) {
	for (userID in players) {
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

function logger(msg, tag = 0) {
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

					client.on("Action", (data) => {
						logger(`${userID},${players[userID].name}: Changed to ${data} State`)
						if (data == "Queue") {
							client.emit('change', 'Queue')
							players[userID].status = "Queue"
						} else if (data == "Lobby") {
							client.emit('change', 'Lobby')
							players[userID].status = "Lobby"
						} else if (data == 'Play') {
							rooms["solo_" + userID] = {"days":10,"score":{},"players":["user_" + userID, "AI_0", "AI_1"]}
							rooms["solo_" + userID].players.forEach(i => {
								rooms["solo_" + userID].score[i] = 0
							});
							players[userID].room = "solo_" + userID;
							players[userID].status = "Play"
							console.log(rooms)
							client.emit('change', 'Play')
						}
					})
					client.on("preview", (data) => {
						players[userID]["last_preview_llm"] = data.llm_id
						$url = `http://localhost/api_auth?key=${process.env.APP_KEY}&api_token=${token}&msg=${data.prompt}&llm_id=${data.llm_id}`
						fetch($url)
							.then((response) => {
								if (!response.ok) {
									client.disconnect();
									throw new Error('Can\'t reach the auth server!');
								}
								return response.json(); // Assuming the response is in JSON format
							})
							.then((data) => {
								client.emit("preview_result", data.output)
								players[userID]["last_preview_output"] = data.output
							})
							.catch((error) => {
								logger('Error:' + error);
							});
					})
					client.on("send", () => {
						console.log(`${players[userID]["last_preview_llm"]} ${players[userID]["last_preview_output"]}`)
						if (players[userID].room.startsWith("solo_")){
							//Solo game, generate the rest of AI's prompts, and calculate the score
						}else{
							//Check if everyone finished prompting
							//if so, continue the next day and calculate the score
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
		if (userID != null) {
			if (players[userID].status == "Play") {
				if (players[userID].room !== undefined) {
					if (players[userID].room.startsWith("solo_")) {
						console.log("A solo game just ended!")
						delete rooms[players[userID].room]
					} else {
						//End the multiplayer game due to someone left the game.
						//Or replace the person into AI so the game can continue to proceed
					}
				}
			}
			delete players[userID]
		} else {
			logger("Unauthed user disconnected")
		}

	});
});

io.listen(3000);