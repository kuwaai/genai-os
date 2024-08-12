This is a tutorial for updating from the initial version to the stable version v0.1.0.

<!-- truncate -->

1. First, clone the repository using `git clone https://github.com/kuwaai/genai-os.git --tag v0.1.0`, or download and extract it from [here](https://github.com/kuwaai/genai-os/releases/tag/v0.1.0) to get a clean copy of the v0.1.0 project.
2. Here, the old version of the project is referred to as the `old` folder, and the newly obtained version is referred to as the `new` folder. If you have these files, please copy them completely and replace them in the corresponding locations:
   - `old/multi-chat/storage/app/` => `new/src/multi-chat/storage/app/`
   - `old/multi-chat/database/database.sqlite` => `new/src/multi-chat/database/database.sqlite`
   - `old/multi-chat/public` => `new/src/multi-chat/public`
   - `old/multi-chat/.env` => `new/src/multi-chat/.env`
3. In addition to these files mentioned in point two, if you have modified or added any other files, please copy them over as well.
4. If you are using the Windows portable version, please move the following folders or files to their respective locations (since the Python version has changed, there is no need to move the Python folder):
   - `old/windows/nginx-1.24.0/` => `new/windows/packages/nginx-1.24.0/`
   - `old/windows/node-v20.11.1-win-x64/` => `new/windows/packages/node-v20.11.1-win-x64/`
   - `old/windows/php-8.1.27-Win32-vs16-x64/` => `new/windows/packages/php-8.1.27-Win32-vs16-x64/`
   - `old/windows/Redis-6.0.20-Windows-x64-msys2/` => `new/windows/packages/Redis-6.0.20-Windows-x64-msys2/`
   - `old/windows/RunHiddenConsole/` => `new/windows/packages/RunHiddenConsole/`
   - `old/windows/composer.phar` => `new/windows/packages/composer.phar`
5. If you are running on Linux, navigate to `new/src/multi-chat/executables/sh/` and run `production_update.sh`. If you are using the Windows Portable version, run `build.bat` in `new/windows/`.
6. The file update should be completed at this point. You can now check if anything is broken. For the Windows Portable version, please proceed to configure the models according to the [tutorial for the new version](https://github.com/kuwaai/genai-os/blob/v0.1.0/windows/workers/README.md).