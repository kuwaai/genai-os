## Portable Installation Guide

We provide a portable version for Windows x64, which uses SQLite as the database. Follow these steps to set it up:

### Prerequisites
- Make sure you have [VC_redist.exe](https://learn.microsoft.com/zh-tw/cpp/windows/latest-supported-vc-redist?view=msvc-170) installed.
Here's a revised version of the installation guide with the commands integrated:

### Installation Steps

1. **Clone the Repository and Navigate to the Windows Folder:**
   ```bat
<<<<<<< HEAD
   git clone https://github.com/kuwaai/gai-os.git
   cd gai-os/windows
=======
   git clone https://github.com/kuwaai/genai-os.git
   cd genai-os/windows
>>>>>>> 0cbbb60a4f1bce269c45504f8d6008ef1cb1e4d1
   ```

2. **Download Dependencies and Configure Packages:**
   ```bat
   .\build.bat
   ```

3. **Start the Application:**
   - Execute `start.bat` to start the application. Note: If you have any of the following services running (nginx, php, php-cgi, python, redis-server), the script will terminate them. Ensure that ports 80, 9000, and 6379 are not in use.
   ```bat
   .\start.bat
   ```

4. **Check Application Status:**
   - Your browser should open to `127.0.0.1` automatically. If you see the web interface, the application is likely working.

5. **Create Admin Account (If you haven't):**
   - Run `seed.bat` and follow the prompts to create an admin account (provide a name, email, and password). You can use this account to log in.

6. **Configure Model and Start Using Kuwa GenAI OS:**
   - Set up your model configurations and start using Kuwa GenAI OS.

Feel free to reach out if you encounter any issues during the installation process.