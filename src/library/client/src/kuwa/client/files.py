from .base import KuwaClient

class FileOperations(KuwaClient):
    def upload_file(self, file_path):
        with open(file_path, "rb") as file:
            return self._request("api/user/upload/file", "POST", files={"file": file})