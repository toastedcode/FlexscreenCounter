# Flexscreen kiosk script

# Disable screensaver and display power management
xset s noblank
xset s off
xset -dpms

# Clear Chrome flags to disable pop-ups.
sed -i 's/"exited_cleanly":false/"exited_cleanly":true/' ~/.config/chromium/Default/Preferences
sed -i 's/"exit_type":"Crashed"/"exit_type":"Normal"/' ~/.config/chromium/Default/Preferences

# Launch Chromium browser in kiosk mode.
/usr/bin/chromium-browser --noerrdialogs --disable-infobars --kiosk &
