#!/usr/bin/env python3
import os
import sys
import zipfile
from datetime import datetime

def read_ignore_list(ignore_file_path):
    """اگر فایل ignore موجود باشد، لیست موارد را می‌خواند."""
    ignore_list = []
    if os.path.exists(ignore_file_path):
        with open(ignore_file_path, 'r', encoding='utf-8') as f:
            for line in f:
                stripped = line.strip()
                if stripped and not stripped.startswith('#'):
                    ignore_list.append(stripped)
    return ignore_list

def main():
    project_root = os.path.abspath(os.path.dirname(__file__))
    build_dir = os.path.join(project_root, "build")
    lock_file = os.path.join(build_dir, "build.lock")

    if os.path.exists(lock_file):
        print("⚠️ Build already exists. Build command is disabled.")
        sys.exit(1)

    if not os.path.isdir(build_dir):
        try:
            os.makedirs(build_dir, exist_ok=True)
        except Exception as e:
            print(f"❌ Failed to create build directory: {e}")
            sys.exit(1)

    default_ignore = ['.git', 'build', 'build.py', 'str.txt']
    ignore_file_path = os.path.join(project_root, "build_ignore.txt")
    extra_ignore = read_ignore_list(ignore_file_path)
    ignore_list = set(default_ignore + extra_ignore)

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    zip_filename = f"project_{timestamp}.zip"
    zip_filepath = os.path.join(build_dir, zip_filename)

    try:
        with zipfile.ZipFile(zip_filepath, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for root, dirs, files in os.walk(project_root):
                dirs[:] = [d for d in dirs if d not in ignore_list]
                for file in files:
                    if file in ignore_list:
                        continue
                    file_path = os.path.join(root, file)
                    rel_path = os.path.relpath(file_path, project_root)
                    zipf.write(file_path, rel_path)
    except Exception as e:
        print(f"❌ Failed to create zip file: {e}")
        sys.exit(1)

    try:
        with open(lock_file, 'w', encoding='utf-8') as lf:
            lf.write("Build completed on " + datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
    except Exception as e:
        print(f"❌ Failed to create lock file: {e}")
        sys.exit(1)

    print(f"✅ Build successful! Zip file created: {zip_filename}")

if __name__ == '__main__':
    main()
