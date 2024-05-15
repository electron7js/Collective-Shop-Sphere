import os
from PIL import Image

def convert_images_to_png(folder_path):
    # Ensure the output directory exists
    output_folder = os.path.join(folder_path, 'converted_pngs')
    if not os.path.exists(output_folder):
        os.makedirs(output_folder)

    # Loop through all files in the folder
    for filename in os.listdir(folder_path):
        # Build the full file path
        file_path = os.path.join(folder_path, filename)
        
        # Check if the file is an image
        if os.path.isfile(file_path) and filename.lower().endswith(('.jpg', '.jpeg', '.tiff', '.bmp', '.gif','.webp')):
            # Open the image file
            with Image.open(file_path) as img:
                # Get the file name without extension
                base_filename = os.path.splitext(filename)[0]
                # Define the output path
                output_path = os.path.join(output_folder, f"{base_filename}.png")
                # Convert and save the image as PNG
                img.save(output_path, 'PNG')
                print(f"Converted {filename} to {base_filename}.png")

# Example usage
folder_path = './'  # Change this to your folder path
convert_images_to_png(folder_path)
