import os
import subprocess

def check_php_syntax(file_path):
    """
    Run a syntax check on a PHP file.
    Returns True if no syntax errors are found, False otherwise.
    """
    try:
        result = subprocess.run(["php", "-l", file_path], capture_output=True, text=True)
        if result.returncode != 0:
            print(f"Syntax error in {file_path}:\n{result.stderr.strip()}")
            return False
        else:
            print(f"No syntax errors detected in {file_path}")
            return True
    except Exception as e:
        print(f"Error checking syntax for {file_path}: {e}")
        return False

def check_all_php_files_in_directory(directory):
    """
    Recursively check all PHP files in the given directory for syntax errors.
    """
    # Track files with syntax errors
    error_files = []

    for root, _, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                file_path = os.path.join(root, file)
                if not check_php_syntax(file_path):
                    error_files.append(file_path)
    
    # Report results
    if error_files:
        print("\nFiles with syntax errors:")
        for file in error_files:
            print(f" - {file}")
    else:
        print("\nAll PHP files passed syntax check.")

# Directory to check (use the current directory by default)
directory_to_check = os.path.dirname(os.path.abspath(__file__)) + '/translated'

# Run the check
check_all_php_files_in_directory(directory_to_check)
input()