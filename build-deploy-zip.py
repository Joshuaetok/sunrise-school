#!/usr/bin/env python3
"""
Packages all production-ready website assets for Hostinger hPanel File Manager upload.
Creates site-deploy.zip containing only production files for public_html.
"""

import os
import zipfile

def build_zip():
    root_dir = os.path.dirname(os.path.abspath(__file__))
    zip_path = os.path.join(root_dir, 'site-deploy.zip')
    
    if os.path.exists(zip_path):
        os.remove(zip_path)

    included_extensions = {'.html', '.css', '.js', '.png', '.jpg', '.jpeg', '.svg', '.ico', '.txt', '.xml'}
    excluded_dirs = {'screenshots', '.git', '.github', 'node_modules', '__pycache__'}
    excluded_files = {'site-deploy.zip', 'link.md', 'build-deploy-zip.sh', 'build-deploy-zip.py', '.DS_Store', '.nojekyll'}

    count = 0
    with zipfile.ZipFile(zip_path, 'w', compression=zipfile.ZIP_DEFLATED) as zf:
        for current_root, dirs, files in os.walk(root_dir):
            # Prune excluded directories
            dirs[:] = [d for d in dirs if d not in excluded_dirs and not d.startswith('.')]
            
            for file in sorted(files):
                if file in excluded_files or file.startswith('.') or file.endswith('.pdf'):
                    continue
                ext = os.path.splitext(file)[1].lower()
                if ext not in included_extensions and file != 'robots.txt':
                    continue

                full_path = os.path.join(current_root, file)
                rel_path = os.path.relpath(full_path, root_dir)
                zf.write(full_path, rel_path)
                count += 1

    size_mb = os.path.getsize(zip_path) / (1024 * 1024)
    print(f"Successfully packaged {count} files into site-deploy.zip ({size_mb:.2f} MB)")
    print(f"Location: {zip_path}")

if __name__ == '__main__':
    build_zip()
