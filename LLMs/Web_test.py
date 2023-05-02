import requests, time
from bs4 import BeautifulSoup
# Configs
url = "https://chat.gai.tw/"
Test_LLM_index = 1
Test_Message = "Introduce yourself"
Test_count = 10
Test_wait_time = 1.5
Remove_After_Testing = True
# End of configs

session = requests.Session()

# Use GET method to get the CSRF token first
response = session.get(url + "login")

# Send a POST request using the session object
login_data = {
    "email": "dev@chat.gai.tw",
    "password": "develope"
}

if response.status_code == 200:
    print("[GET] Success")
    # Extract CSRF token from result
    soup = BeautifulSoup(response.content, "html.parser")
    csrf = soup.find("input", {"name": "_token"})
    if csrf:
        login_data["_token"] = csrf.get("value")

        # Login with the CSRF token
        response = session.post(url + "login", data=login_data)

        if "Select a chat to begin with" in response.text:
            print("Login successful")
            # Getting LLM link
            soup = BeautifulSoup(response.content, "html.parser")
            chatLink = soup.find_all("a", class_="flex menu-btn flex items-center justify-center w-full h-12 dark:hover:bg-gray-700 hover:bg-gray-200 transition duration-300")[Test_LLM_index].get("href")
            # Open LLM new chat
            response = session.get(chatLink)
            # Fetch the CSRF Token
            soup = BeautifulSoup(response.content, "html.parser")
            csrf = soup.find("form", {"action": url + "chats/create"}).find("input", {"name": "_token"}).get("value")
            llm_id = int(chatLink.split("/")[-1])
            if csrf:
                create_chat_data = {
                    "_token": csrf,
                    "llm_id": llm_id,
                    "input": Test_Message
                }
                start = time.time()
                response = session.post(url + "chats/create", data=create_chat_data)
                print("Created a new chat named", Test_Message)
                print("request 1 sent, status code:",response.status_code)
                soup = BeautifulSoup(response.content, "html.parser")
                csrf = soup.find("form", {"action": url + "chats/request"}).find("input", {"name": "_token"}).get("value")
                chat_id = soup.find("input", {"name": "chat_id"}).get("value")

                request_chat_data = {
                    "_token": csrf,
                    "chat_id": chat_id,
                    "input": Test_Message
                }

                time.sleep(Test_wait_time)
                for i in range(Test_count-1):
                    response = session.post(url + "chats/request", data=request_chat_data)
                    print("request",i+2,"sent, status code:",response.status_code)
                    time.sleep(Test_wait_time)
                    
                soup = BeautifulSoup(response.content, "html.parser")
                #If all message returned, this route return as well.
                response = session.get(url + "chats/stream")
                ends = time.time()
                print("All data received!", response.status_code)
                
                response = session.get(url + "chats/" + str(chat_id))
                soup = BeautifulSoup(response.content, "html.parser")
                csrf = soup.find("form", {"action": url + "chats/delete"}).find("input", {"name": "_token"}).get("value")
                llm = [i.find("p").text for i in soup.find_all("div", class_="flex w-full mt-2 space-x-3")]
                user = [i.find("p").text for i in soup.find_all("div", class_="flex w-full mt-2 space-x-3 ml-auto justify-end")]
                
                user_len = sum(len(i) for i in user)
                llm_len = sum(len(i) for i in llm)
                
                result_time = ends - start - Test_wait_time * Test_count
                
                print("Time used:", ends - start)
                print("Time used(Without waiting time):", result_time)
                
                print("User input lengths", user_len)
                print("LLM output lengths", llm_len)
                
                print("Feedback speed", llm_len / result_time, "chars/second")
                if Remove_After_Testing:
                    delete_chat_data = {
                        "_token": csrf,
                        "_method": "delete",
                        "id": chat_id
                    }
                    response = session.post(url + "chats/delete", data=delete_chat_data)
                    print("Chatroom removed")
                
        else:
            print("Login failed")

print("Program ended")
input()




