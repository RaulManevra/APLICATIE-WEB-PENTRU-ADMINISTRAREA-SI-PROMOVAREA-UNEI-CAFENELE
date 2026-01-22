import os
import shutil
from PIL import Image

ASSETS_DIR = r"d:\Apps\Ampps\www\APLICATIE-WEB-PENTRU-ADMINISTRAREA-SI-PROMOVAREA-UNEI-CAFENELE\assets\img"
FILES = [
    "Coffee Pattern - BLURRED.png",
    "GRADIENT.png"
]

def optimize_image(filename):
    path = os.path.join(ASSETS_DIR, filename)
    if not os.path.exists(path):
        print(f"Skipping {filename}: Not found")
        return

    # Backup
    backup_path = path + ".bak"
    if not os.path.exists(backup_path):
        shutil.copy2(path, backup_path)
        print(f"Backed up {filename} to {backup_path}")

    # Optimization
    try:
        with Image.open(path) as img:
            print(f"Optimizing {filename}...")
            print(f"Original size: {img.size}, {os.path.getsize(path) / 1024 / 1024:.2f} MB")

            # Resize if huge (max 1920 width)
            max_width = 1920
            if img.width > max_width:
                ratio = max_width / img.width
                new_height = int(img.height * ratio)
                img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)

            # Save optimized
            # We keep PNG to preserve transparency if it exists, but optimize it
            # For GRADIENT.png, checking if it has transparency. 
            # If completely opaque, JPG is better, but safer to stick to PNG optimized or check mode.
            
            if filename == "Coffee Pattern - BLURRED.png":
                # This likely doesn't need alpha if it's a background pattern, but let's be safe.
                # Actually, standard PNG compression args help a lot.
                img.save(path, "PNG", optimize=True, quality=80)
            else:
                 img.save(path, "PNG", optimize=True)

            print(f"New size: {img.size}, {os.path.getsize(path) / 1024 / 1024:.2f} MB")

    except Exception as e:
        print(f"Error optimizing {filename}: {e}")

if __name__ == "__main__":
    for f in FILES:
        optimize_image(f)
