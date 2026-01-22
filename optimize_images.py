import os
import shutil
import glob
from PIL import Image

ASSETS_DIR = r"d:\Apps\Ampps\www\APLICATIE-WEB-PENTRU-ADMINISTRAREA-SI-PROMOVAREA-UNEI-CAFENELE\assets\img"
MENU_DIR = r"d:\Apps\Ampps\www\APLICATIE-WEB-PENTRU-ADMINISTRAREA-SI-PROMOVAREA-UNEI-CAFENELE\assets\menu\images"

# Explicit list + glob for ASSETS
FILES = [
    os.path.join(ASSETS_DIR, "Coffee Pattern - BLURRED.png"),
    os.path.join(ASSETS_DIR, "GRADIENT.png"),
    os.path.join(ASSETS_DIR, "Coffee_1.png")
]
slider_files = glob.glob(os.path.join(ASSETS_DIR, "slider_*.png"))
FILES.extend(slider_files)

# Add MENU images
menu_files = []
# Include jfif and webp (to re-optimize huge ones)
for ext in ['*.jpg', '*.jpeg', '*.png', '*.jfif', '*.webp']:
    menu_files.extend(glob.glob(os.path.join(MENU_DIR, ext)))

FILES.extend(menu_files)

def optimize_image(path):
    if not os.path.exists(path):
        print(f"Skipping {path}: Not found")
        return

    filename = os.path.basename(path)
    directory = os.path.dirname(path)
    
    # Check if it's already a small WebP
    is_webp = filename.lower().endswith('.webp')
    original_size = os.path.getsize(path)
    
    # If it's a Menu WebP < 150KB, skip it (already optimized)
    if is_webp and "menu" in directory and original_size < 150 * 1024:
        # print(f"Skipping {filename}: Already small WebP")
        return

    # WebP Target
    webp_filename = os.path.splitext(filename)[0] + ".webp"
    webp_path = os.path.join(directory, webp_filename)

    # Optimization
    try:
        with Image.open(path) as img:
            print(f"Processing {filename}...")
            original_size_mb = original_size / 1024 / 1024
            print(f"Original: {img.size}, {original_size_mb:.2f} MB")

            # Strategy:
            # - Backgrounds: Resize to 1920 (or 1600 for performance)
            # - Menu Items: Resize to 800
            
            max_width = 1920
            quality = 85
            
            # Special Case: The massive pattern
            if "Coffee Pattern" in filename:
                max_width = 1600 # Scale down slightly more
                quality = 75     # More aggressive compression
            
            if "menu" in directory:
                max_width = 800
            
            if img.width > max_width:
                ratio = max_width / img.width
                new_height = int(img.height * ratio)
                img = img.resize((max_width, new_height), Image.Resampling.LANCZOS)
            
            # Save as WebP
            if "GRADIENT" in filename:
                quality = 95
            
            img.save(webp_path, "WEBP", quality=quality)

            new_size_mb = os.path.getsize(webp_path) / 1024 / 1024
            print(f"Saved to {webp_filename}")
            print(f"New: {new_size_mb:.2f} MB ({(1 - new_size_mb/original_size_mb)*100:.1f}% reduction)")

    except Exception as e:
        print(f"Error optimizing {filename}: {e}")

if __name__ == "__main__":
    # Deduplicate list just in case
    FILES = list(set(FILES))
    print(f"Found {len(FILES)} images to optimize.")
    for f in FILES:
        optimize_image(f)

