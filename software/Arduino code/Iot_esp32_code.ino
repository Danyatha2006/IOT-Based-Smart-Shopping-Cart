// NOTE: This code runs on the ESP32 DevKit board.

#include <WiFi.h>
#include <HTTPClient.h>
#include <LiquidCrystal_I2C.h> // Make sure this library is installed
#include <Wire.h> // Required for I2C communication

// ============================================================
// ===================== CONFIGURATION ========================
// ============================================================
// WIFI Credentials (from original setup)
const char* ssid = "alfiya";
const char* password = "12102006";

// Server URL (from original setup)
String serverURL = "http://10.220.33.101/iot_project/frontend/esp_post.php";

// UART Communication
#define BAUD_RATE 115200
#define RXD2 16 // DevKit RX Pin (connects to Camera TX)
#define TXD2 17 // DevKit TX Pin (connects to Camera RX)

// Hardware Pins
#define BUZZER_PIN 4      // Buzzer connected to GPIO 4
#define LCD_SDA_PIN 21    // I2C Data Pin (DevKit default)
#define LCD_SCL_PIN 22    // I2C Clock Pin (DevKit default)
#define LCD_ADDR 0x27     // Common I2C address for 1602 LCDs (try 0x3F or 0x27)

// --- NEW BUTTON PINS (Use INPUT_PULLUP) ---
#define BTN_WIFI_PIN 13   // Blue Button: Connect to Wi-Fi
#define BTN_SHOOT_PIN 12  // Green Button: Take Photo/Scan Barcode
#define BTN_DECLINE_PIN 14 // Red Button: Decline Order/Cancel Scan

// Protocol markers (must match the sender)
const String START_MARKER = "<IMG_START>";
const String END_MARKER = "<IMG_END>";
const String TRIGGER_MARKER = "SHOOT"; // Command sent to Camera to take photo
const String DECLINE_SERVER_DATA = "DECLINE_ORDER_BUTTON"; // Data sent to server on decline

// Memory Allocation
const size_t MAX_IMAGE_SIZE = 1024 * 100; // 100KB maximum allowed size
uint8_t *imageBuffer = NULL; // Pointer for dynamic allocation

// State Variables
LiquidCrystal_I2C lcd(LCD_ADDR, 16, 2); 
long lastDebounceTime = 0; 
long debounceDelay = 50;

// ============================================================
// =================== HELPER FUNCTIONS =======================
// ============================================================

void updateStatusLCD() {
    lcd.clear();
    if (WiFi.status() == WL_CONNECTED) {
        lcd.print("WiFi: Connected");
        lcd.setCursor(0, 1);
        lcd.print("Ready to Shoot!");
    } else {
        lcd.print("WiFi: DISCONNECTED");
        lcd.setCursor(0, 1);
        lcd.print("Press BLUE to Connect");
    }
}

// Function to connect to Wi-Fi
void initWiFi() {
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("WiFi already connected.");
        return;
    }
    
    lcd.clear();
    lcd.print("Connecting...");
    Serial.println("\nStarting Wi-Fi connection...");
    WiFi.begin(ssid, password);
    
    int connect_tries = 0;
    while (WiFi.status() != WL_CONNECTED && connect_tries < 40) { // Max 20 seconds
        delay(500);
        connect_tries++;
        lcd.print(".");
        Serial.print(".");
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\nConnected!");
        Serial.print("IP Address: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println("\nWiFi connection failed.");
        lcd.clear();
        lcd.print("WiFi Failed!");
        delay(2000);
    }
    updateStatusLCD();
}

// Function to provide audio feedback
void notifyBuzzer(int durationMs, int count) {
    for (int i = 0; i < count; i++) {
        digitalWrite(BUZZER_PIN, HIGH);
        delay(durationMs / (2 * count));
        digitalWrite(BUZZER_PIN, LOW);
        delay(durationMs / (2 * count));
    }
}

// Function to POST the result to the server
void postResultToServer(String decodedResult) {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("[HTTP] Skipping POST: Wi-Fi not connected.");
        lcd.setCursor(0, 1);
        lcd.print("No WiFi for POST");
        return;
    }
    
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String httpRequestData = "barcode_data=" + decodedResult;
    
    lcd.setCursor(0, 1);
    lcd.print("Posting...");

    int httpResponseCode = http.POST(httpRequestData);

    if (httpResponseCode > 0) {
        String response = http.getString();
        Serial.printf("[HTTP] POST Success Code: %d\n", httpResponseCode);
        Serial.print("Server Response: ");
        Serial.println(response);
        lcd.setCursor(0, 1);
        lcd.print("POST OK ");
        lcd.print(httpResponseCode);
    } else {
        Serial.printf("[HTTP] POST Failed Code: %d\n", httpResponseCode);
        lcd.setCursor(0, 1);
        lcd.print("POST Failed!");
    }

    http.end();
}

// Function to send the 'SHOOT' trigger command to the ESP32-CAM
void sendTriggerToCamera() {
    if (WiFi.status() != WL_CONNECTED) {
        lcd.clear();
        lcd.print("Connect WiFi first!");
        notifyBuzzer(500, 2);
        delay(1500);
        updateStatusLCD();
        return;
    }

    Serial.println("[DEV] Sending SHOOT command...");
    Serial2.printf("%s\n", TRIGGER_MARKER.c_str());
    Serial2.flush(); 
    
    Serial.println("[DEV] 'SHOOT' trigger SENT.");
    lcd.clear();
    lcd.print("Trigger Sent!");
    lcd.setCursor(0, 1);
    lcd.print("Waiting for image...");
}

// Function for the Red Button Action (Declining Order)
void declineOrder() {
    Serial.println("[DEV] Red Button Pressed: Order Declined.");
    
    // 1. Notify server of the decline action
    postResultToServer(DECLINE_SERVER_DATA);
    
    // 2. Notify Success
    notifyBuzzer(150, 2); // Two quick beeps
    
    // 3. Display result on LCD
    lcd.clear();
    lcd.print("ORDER DECLINED");
    lcd.setCursor(0, 1);
    lcd.print("Ready for Next Scan");
    
    delay(3000); 
    updateStatusLCD();
}


// Function to check and handle button presses
void checkButtons() {
    // Basic debouncing logic
    if ((millis() - lastDebounceTime) < debounceDelay) {
        return;
    }

    // Check Blue Button (Connect WiFi)
    if (digitalRead(BTN_WIFI_PIN) == LOW) {
        Serial.println("Blue Button (WiFi) pressed.");
        initWiFi();
        lastDebounceTime = millis();
    }

    // Check Green Button (Take Photo/Shoot)
    else if (digitalRead(BTN_SHOOT_PIN) == LOW) {
        Serial.println("Green Button (Shoot) pressed.");
        sendTriggerToCamera();
        lastDebounceTime = millis();
    }
    
    // Check Red Button (Decline Order)
    else if (digitalRead(BTN_DECLINE_PIN) == LOW) {
        Serial.println("Red Button (Decline) pressed.");
        declineOrder();
        lastDebounceTime = millis();
    }
}


// ============================================================
// ========================= SETUP ============================
// ============================================================
void setup() {
    Serial.begin(115200); // Standard serial for debugging (USB)
    
    // 1. Initialize Outputs
    pinMode(BUZZER_PIN, OUTPUT);
    digitalWrite(BUZZER_PIN, LOW);

    // 2. Initialize Inputs (Buttons) with internal pull-up resistors
    pinMode(BTN_WIFI_PIN, INPUT_PULLUP);
    pinMode(BTN_SHOOT_PIN, INPUT_PULLUP);
    pinMode(BTN_DECLINE_PIN, INPUT_PULLUP);

    // 3. Initialize LCD
    Wire.begin(LCD_SDA_PIN, LCD_SCL_PIN); // Custom I2C pins for DevKit
    lcd.init();
    lcd.backlight();
    lcd.print("System Booting...");
    
    // 4. Initialize Serial2 for inter-ESP communication
    Serial2.begin(BAUD_RATE, SERIAL_8N1, RXD2, TXD2);
    
    Serial.println("ESP32 DevKit Processor Ready.");
    
    // 5. Initial Display State
    updateStatusLCD(); 
}

// ============================================================
// ========================== LOOP ============================
// ============================================================
void loop() {
    // Check for user input from the physical buttons first
    checkButtons();

    // Check for incoming data (image) from the Camera
    if (Serial2.available()) {
        String header = Serial2.readStringUntil(':');
        
        // Check for the start marker and extract size
        if (header.startsWith(START_MARKER)) {
            // ... (rest of the image receiving logic)
            lcd.clear();
            lcd.print("Receiving Image...");
            
            String sizeStr = header.substring(START_MARKER.length());
            size_t imageSize = sizeStr.toInt();

            if (imageSize == 0 || imageSize > MAX_IMAGE_SIZE) {
                Serial.printf("Invalid size (%u) or too large.\n", imageSize);
                lcd.clear();
                lcd.print("Error: Bad Size");
                notifyBuzzer(500, 3);
                return;
            }
            
            // Dynamic allocation
            imageBuffer = (uint8_t*)malloc(imageSize);
            if (imageBuffer == NULL) {
                Serial.println("Error: Failed to allocate image buffer on heap.");
                lcd.clear();
                lcd.print("Error: Low Memory");
                notifyBuzzer(500, 4); 
                return;
            }
            Serial.printf("\n--- Allocating and Receiving %u bytes ---\n", imageSize);

            // Read the image data into the buffer
            size_t bytesRead = 0;
            unsigned long startTime = millis();
            
            // Loop until we read the full expected size
            while (bytesRead < imageSize) {
                if (Serial2.available()) {
                    imageBuffer[bytesRead] = Serial2.read();
                    bytesRead++;
                }
                // Add a timeout just in case
                if (millis() - startTime > 5000) { 
                    Serial.println("Error: Timeout while reading image data.");
                    lcd.clear();
                    lcd.print("Error: Timeout");
                    notifyBuzzer(500, 3);
                    free(imageBuffer);
                    return;
                }
            }
            
            // Read and discard the remaining END_MARKER
            Serial2.readStringUntil('\n'); 

            Serial.printf("Successfully read %u bytes.\n", bytesRead);
            lcd.setCursor(0, 1);
            lcd.print("Processing...");
            
            // ========================================================
            // == SIMULATE PROCESSING (Where ZBar/PHP logic would go) ==
            // ========================================================
            String decodedResult = "SIMULATED_BARCODE_" + String(millis() % 10000); 
            Serial.printf("Simulated Processing complete. Result: %s\n", decodedResult.c_str());

            free(imageBuffer); 
            imageBuffer = NULL;
            // ========================================================
            
            // 1. POST the result to the server
            postResultToServer(decodedResult);
            
            // 2. Notify Success
            notifyBuzzer(200, 1);
            
            // 3. Display result on LCD
            lcd.setCursor(0, 0);
            lcd.print("Decoded OK!     ");
            lcd.setCursor(0, 1);
            lcd.print(decodedResult.substring(0, 16));

            // 4. Send the result back to the camera board (optional)
            Serial2.printf("%s\n", decodedResult.c_str());
            Serial.println("Result sent back to Camera board.");
            
            delay(5000); // Display result for a few seconds
            updateStatusLCD(); // Return to the main status screen

        } else if (header.length() > 0) {
             // Junk data received
             Serial.printf("[DEV] Loop received junk data: %s\n", header.c_str());
             delay(100);
        }
    }
}