"""
Download Required Fonts for Image Composition
Downloads Cairo (Arabic) and Roboto (English) from Google Fonts
"""
import os
import requests
import zipfile
import io
import shutil

FONTS_DIR = "app/fonts"
os.makedirs(FONTS_DIR, exist_ok=True)

def download_and_extract_font(url, font_name, target_files):
    """Download zip from URL and extract specific font files"""
    print(f"[Fonts] Downloading {font_name}...")
    
    try:
        response = requests.get(url, timeout=30)
        response.raise_for_status()
        
        with zipfile.ZipFile(io.BytesIO(response.content)) as zip_file:
            # List all files in zip
            all_files = zip_file.namelist()
            print(f"[Fonts] Found {len(all_files)} files in archive")
            
            # Extract target files
            extracted = 0
            for file_path in all_files:
                filename = os.path.basename(file_path)
                if any(target in filename for target in target_files):
                    target_path = os.path.join(FONTS_DIR, filename)
                    with zip_file.open(file_path) as source:
                        with open(target_path, 'wb') as target:
                            target.write(source.read())
                    print(f"[Fonts] Extracted: {filename}")
                    extracted += 1
            
            if extracted == 0:
                print(f"[Fonts] WARNING: No matching files found for {font_name}")
                print(f"[Fonts] Available files: {[os.path.basename(f) for f in all_files]}")
            else:
                print(f"[Fonts] Successfully extracted {extracted} files for {font_name}")
            
            return extracted > 0
            
    except Exception as e:
        print(f"[Fonts] ERROR downloading {font_name}: {e}")
        return False


def main():
    print("\n" + "="*60)
    print("Downloading Fonts for Image Composition")
    print("="*60 + "\n")
    
    success = True
    
    # Cairo Font (Arabic) - Using direct download link
    print("1. Cairo Font (Arabic)")
    cairo_url = "https://github.com/google/fonts/raw/main/ofl/cairo/Cairo%5Bslnt%2Cwght%5D.ttf"
    try:
        response = requests.get(cairo_url, timeout=30)
        if response.status_code == 200:
            with open(os.path.join(FONTS_DIR, "Cairo-Bold.ttf"), 'wb') as f:
                f.write(response.content)
            print("[Fonts] Downloaded: Cairo-Bold.ttf")
        else:
            print(f"[Fonts] Failed to download Cairo, status: {response.status_code}")
            success = False
    except Exception as e:
        print(f"[Fonts] ERROR: {e}")
        success = False
    
    print()
    
    # Roboto Font (English) - Using direct download link
    print("2. Roboto Font (English)")
    roboto_urls = [
        "https://github.com/googlefonts/roboto/raw/main/src/hinted/Roboto-Bold.ttf",
        "https://github.com/google/fonts/raw/main/apache/roboto/static/Roboto-Bold.ttf"
    ]
    roboto_downloaded = False
    for roboto_url in roboto_urls:
        try:
            print(f"[Fonts] Trying URL: {roboto_url}")
            response = requests.get(roboto_url, timeout=30)
            if response.status_code == 200:
                with open(os.path.join(FONTS_DIR, "Roboto-Bold.ttf"), 'wb') as f:
                    f.write(response.content)
                print("[Fonts] Downloaded: Roboto-Bold.ttf")
                roboto_downloaded = True
                break
            else:
                print(f"[Fonts] Failed, status: {response.status_code}")
        except Exception as e:
            print(f"[Fonts] ERROR: {e}")
    
    if not roboto_downloaded:
        success = False
    
    print("\n" + "="*60)
    if success:
        print("[Fonts] SUCCESS: All fonts downloaded successfully!")
    else:
        print("[Fonts] PARTIAL: Some fonts failed to download")
        print("[Fonts] You can download manually from:")
        print("  - Cairo: https://fonts.google.com/specimen/Cairo")
        print("  - Roboto: https://fonts.google.com/specimen/Roboto")
    print("="*60 + "\n")
    
    # Verify fonts exist
    cairo_exists = os.path.exists(os.path.join(FONTS_DIR, "Cairo-Bold.ttf"))
    roboto_exists = os.path.exists(os.path.join(FONTS_DIR, "Roboto-Bold.ttf"))
    
    print("Font Status:")
    print(f"  Cairo-Bold.ttf: {'OK Found' if cairo_exists else 'X Missing'}")
    print(f"  Roboto-Bold.ttf: {'OK Found' if roboto_exists else 'X Missing'}")
    print()


if __name__ == "__main__":
    main()

