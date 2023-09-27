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

async function api_send(token, msg, llm_id){
	$url = `http://localhost/api_auth?key=${encodeURIComponent(process.env.API_Key)}&api_token=${token}&msg=${msg}&llm_id=${llm_id}`
	console.log($url)
	console.log(`sending msg: ${msg} ...`)
	response = await fetch($url)
	if (!response.ok) {
		client.disconnect();
		throw new Error('Can\'t reach the auth server!');
	}
	response = response.json()
	return response
}

io.on('connection', client => {
	logger("An user connected")
	authTimeout = setTimeout(() => {
		client.disconnect(); // Disconnect the client if the "auth" event is not received within 30 seconds
		logger("An user timeout. automatically disconnected.");
	}, 10000);

	client.on("auth", (token) => {
		logger("Doing auth on the client with API token: " + token)
		fetch(`http://localhost/api_auth?key=${encodeURIComponent(process.env.API_Key)}&api_token=${token}`)
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
							rooms["solo_" + userID] = {"days":10,"data":{},"players":["user_" + userID, "AI_0", "AI_1"]}
							rooms["solo_" + userID].players.forEach(i => {
								rooms["solo_" + userID].data[i] = [0] // Score
							});
							players[userID].room = "solo_" + userID;
							players[userID].status = "Play"
							console.log(rooms)
							console.log(players)
							client.emit('change', 'Play')
						}
					})
					client.on("preview", async (data) => {
						players[userID]["last_preview_llm"] = data.llm_id
						players[userID]["last_preview_input"] = data.prompt
						result = await api_send(token, data.prompt, data.llm_id)
						client.emit("preview_result", result.output)
						players[userID]["last_preview_output"] = result.output
					})
					client.on("send", async() => {
						console.log(`${players[userID]["last_preview_llm"]} ${players[userID]["last_preview_output"]}`)
						if (players[userID].room.startsWith("solo_")){
							//Solo game, generate the rest of AI's prompts, and calculate the score
							llm_id = players[userID]["last_preview_llm"]
							prompts = [players[userID]["last_preview_input"]]
							prompts_cal = [`此為user_${userID}的拉票演說，其餘候選人分別叫做AI_0、AI_1，請問此演說對哪些候選人有利，如果都沒有或沒有明確，就是對user_${userID}自己有利，請直接回答此演說有利的候選人名稱，不用別的文字。

${players[userID]["last_preview_output"]}`]
							ids = ["user_" + userID]
							results = [players[userID]["last_preview_output"]]
							rooms["solo_" + userID].players.forEach(i => {
								others = [...rooms["solo_" + userID].players]
								others.splice(others.indexOf(i), 1);
								if (i.startsWith("AI_")){
									ids.push(i)
									prompts.push(`假設這是一場總統選舉，你的名子叫做[${i}]，今天是選舉倒數第${rooms["solo_" + userID].days}天，你需要發表一個演說來拉升自己的選票，也可以選擇專注於攻擊其他候選人：[${others[0]}]與[${others[1]}]，但目前還不知道他們對於此次選舉將會提出什麼政見。`)
								}
							});
							results = []
							for (i = 1; i < 3; i++){
								result = await api_send(token, prompts[i], llm_id)
								results.push(result.output)
								client.emit("ai_result", {"who":ids[i], "msg": result.output})
								others = [...ids]
								others.splice(others.indexOf(ids[i]), 1);
								prompts_cal.push(`此為${ids[i]}的拉票演說，其餘候選人分別叫做${others[0]}、${others[1]}，請問此演說對哪些候選人有利，如果都沒有或沒有明確，就是對${ids[i]}自己有利，請直接回答此演說有利的候選人名稱，不用別的文字。

${result.output}`)
							}
							console.log()
							for (i = 0; i < 3; i++){
								result = await api_send(token, prompts_cal[i], llm_id)
								results.push(result.output)
								console.log(`${ids[i]}'s cal score result: ${result.output}`)
								rooms["solo_" + userID].players.forEach(k => {
									if (result.output.indexOf(k) != -1){
										rooms["solo_" + userID].data[k][0] += 1
									}
									console.log(`${k}: resulted score ${rooms["solo_" + userID].data[k][0]}`)
								})
							}
							console.log(rooms["solo_" + userID])
							console.log("done")
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