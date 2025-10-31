# Fonts Directory

This directory contains fonts for text overlay on images.

## Required Fonts

### Arabic Font
- **Cairo-Bold.ttf** - For Arabic text overlays
- License: SIL Open Font License
- Download: https://fonts.google.com/specimen/Cairo

### English Font  
- **Roboto-Bold.ttf** - For English text overlays
- License: Apache License 2.0
- Download: https://fonts.google.com/specimen/Roboto

## How to Add Fonts

1. Download the font files from Google Fonts
2. Extract the `.ttf` files
3. Place them in this directory
4. Update `config.py` if you want to use different fonts

## Usage

Fonts are automatically loaded by the ImageCompositor service based on text language:
- Arabic text → Uses Cairo-Bold.ttf
- English text → Uses Roboto-Bold.ttf

## Alternative: Using System Fonts

If you don't have the fonts, the system will fall back to:
- DejaVuSans for general text
- Arial/Tahoma for Windows

