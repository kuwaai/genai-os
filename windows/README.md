## Windows Portable Installation Guide

We provide a portable version for Windows x64, which uses SQLite as the default database. The required packages will take up about 1.5 GB of space after decompression. Please pay attention to the network traffic consumed.

Please follow the steps below to build:

### Prerequisites
- Make sure you have installed [VC_redist.exe](https://learn.microsoft.com/en-us/cpp/windows/latest-supported-vc-redist?view=msvc-170).
- If you want to load models on GPU, please install [CUDA](https://developer.nvidia.com/cuda-toolkit) first.

### Installation Steps

1. **Download from the Release or clone the project and switch to the windows directory in the project using git bash with the following commands：**
   ```bat
   git clone https://github.com/kuwaai/genai-os.git
   cd genai-os/windows
   ```

2. **Download dependencies and do a quick setup：**
   ```bat
   .\build.bat
   ```

3. **Start the application：**
   - Run `start.bat` to start the application. Note: If you have any of the following services running (nginx、php、php-cgi、python、redis-server), this executable file will terminate them when it is closed. Also make sure ports 80, 9000, and 6379 are not in use.
   ```bat
   .\start.bat
   ```
   - You should be prompted to create an administrator account (enter a username, email, and password). If you are not prompted, or if you enter an incorrect entry or the creation fails, please refer to [here](#FAQ).

4. **Check the application status：**
   - If successful, your browser will automatically open to `127.0.0.1`. If you see the web interface, the installation should be OK.

5. **How to close the program：**
   - Please try not to force close the .bat file (including closing it directly with the red X). Currently, the .bat file cannot automatically close all open programs and release resources in these cases.

   - **Therefore, please get used to closing the program by entering `stop` when executing `start.bat`.**

6. **Set up models：**
   - The program does not have any models when it is first started, so you will need to set up workers. However, this is a lengthy process, so please refer to the tutorial guide [here](./workers/README.md).

## FAQ

1. **Q: I was not prompted to create an administrator account, the administrator account creation failed, or I entered an error...**
   
   A: Please open `windows\tool.bat`, enter `seed`, and press Enter. This will open the administrator account creation interface. Enter `quit` to close after creation is complete.

2. **Q: I moved the entire project after a successful `start.bat` execution, and the website now returns a 404/500 error.**

   A: Because some parts of the project must use absolute paths, if the path to the project directory has changed (change the parent folder name or move the entire project), you will need to rerun `build.bat` to update the absolute paths and redo `init.bat` in the workers directory to avoid errors.

3. **Q: I accidentally closed the entire `start.bat` program with the red X, and the background programs did not close, causing memory resources to be occupied. What should I do?**

   A: Because the .bat file cannot close all programs when you click the red X, you can open `windows\tool.bat` and enter `stop` to terminate all related programs.

If you encounter any problems during the installation process, please contact us at any time.