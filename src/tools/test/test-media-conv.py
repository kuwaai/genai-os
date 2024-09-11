import os
import sys
import unittest
import requests_mock
sys.path.append(os.path.join(os.path.dirname(__file__), '../'))
from media_conv import download_file


class TestDownloadFile(unittest.TestCase):

    def test_download_success(self):
        # Mock a successful HTTP response
        with requests_mock.Mocker() as m:
            m.get("https://example.com/test.mp4", content=b"This is a test file")

            # Call the download_video function
            downloaded_file, original_filename = download_file("https://example.com/test.mp4")

            # Assert that the file exists
            self.assertTrue(os.path.exists(downloaded_file))

            # Assert that the filename equals
            self.assertEqual(original_filename, "test.mp4")

            # Assert that the downloaded file content matches the mock response
            with open(downloaded_file, 'rb') as f:
                self.assertEqual(f.read(), b"This is a test file")

            # Clean up the downloaded file
            os.unlink(downloaded_file)

    def test_download_failure(self):
        # Mock a failed HTTP response
        with requests_mock.Mocker() as m:
            m.get("https://example.com/test.mp4", status_code=404)

            with self.assertRaises(Exception) as context:
                download_file("https://example.com/test.mp4")

            self.assertEqual(str(context.exception), "Error downloading file. Status code: 404")


if __name__ == '__main__':
    unittest.main()