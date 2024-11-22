import os
import requests
from telegram import Update
from telegram.ext import Updater, CommandHandler, MessageHandler, Filters, CallbackContext

# Replace with your actual API keys
TG_BOT_API = '7830451221:AAFf1mYKOoJuAE69TV2wa5z3iymZZPXugvc'
CLOTHOFF_API_KEY = '9f8244f793ebfa28e183172ef8b36b1053f845eap'  # Replace with your Clothoff API key

def start(update: Update, context: CallbackContext) -> None:
    update.message.reply_text('Welcome to the Cloth Remover Bot! Send me an image to remove clothes.')

def help_command(update: Update, context: CallbackContext) -> None:
    update.message.reply_text('Send me an image, and I will remove the clothes from it!')

def handle_photo(update: Update, context: CallbackContext) -> None:
    photo_file = update.message.photo[-1].get_file()
    photo_file.download('user_photo.jpg')

    # Call Clothoff API to remove clothes
    response = remove_clothes('user_photo.jpg')
    
    if response:
        # Save the processed image
        with open('output_image.png', 'wb') as f:
            f.write(response.content)
        
        # Send the processed image back to the user
        with open('output_image.png', 'rb') as f:
            update.message.reply_photo(photo=f)
    else:
        update.message.reply_text('Failed to process the image. Please try again.')

def remove_clothes(image_path: str):
    url = 'https://api.clothoff.io/remove-clothes'  # Replace with the actual Clothoff API endpoint
    headers = {
        'Authorization': f'Bearer {CLOTHOFF_API_KEY}',
        'Content-Type': 'application/json'
    }
    
    with open(image_path, 'rb') as image_file:
        files = {'file': image_file}
        response = requests.post(url, headers=headers, files=files)
        
        if response.status_code == 200:
            return response
        else:
            print(f"Error: {response.status_code}, {response.text}")
            return None

def main() -> None:
    updater = Updater(TG_BOT_API)

    dispatcher = updater.dispatcher

    dispatcher.add_handler(CommandHandler("start", start))
    dispatcher.add_handler(CommandHandler("help", help_command))
    dispatcher.add_handler(MessageHandler(Filters.photo, handle_photo))

    updater.start_polling()
    updater.idle()

if name == 'main':
    main()
