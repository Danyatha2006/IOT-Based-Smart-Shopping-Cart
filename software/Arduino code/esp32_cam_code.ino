#include "esp_camera.h"
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

// ============================================================
// ======== SERIAL COMMUNICATION CONFIG (UART2) ===============
// ============================================================
// Using Serial2 (UART2) for communication between the two ESP32s
// NOTE: WIRING MUST BE CROSSED:
// CAM TXD2 (GPIO 17) -> DevKit RXD2 (GPIO 16)
// CAM RXD2 (GPIO 16) -> DevKit TXD2 (GPIO 17)
#define RXD2 16 
#define TXD2 17 
#define BAUD_RATE 115200

// Protocol markers (must match the receiver)
const char* START_MARKER = "<IMG_START>";
const char* END_MARKER = "<IMG_END>";
const char* TRIGGER_MARKER = "SHOOT"; // Command sent by DevKit to take photo

// ============================================================
// ================ ESP32-CAM (AI THINKER) PINS ===============
// ============================================================
#define PWDN_GPIO_NUM       32
#define RESET_GPIO_NUM      -1
#define XCLK_GPIO_NUM        0
#define SIOD_GPIO_NUM       26
#define SIOC_GPIO_NUM       27

#define Y9_GPIO_NUM         35
#define Y8_GPIO_NUM         34
#define Y7_GPIO_NUM         39
#define Y6_GPIO_NUM         36
#define Y5_GPIO_NUM         21
#define Y4_GPIO_NUM         19
#define Y3_GPIO_NUM         18
#define Y2_GPIO_NUM          5

#define VSYNC_GPIO_NUM      25
#define HREF_GPIO_NUM       23
#define PCLK_GPIO_NUM       22


// ============================================================
// ===================== CAMERA INIT ==========================
// ============================================================
void initCamera() {
    camera_config_t config;

    config.ledc_channel = LEDC_CHANNEL_0;
    config.ledc_timer = LEDC_TIMER_0;
    config.pin_d0 = Y2_GPIO_NUM;
    config.pin_d1 = Y3_GPIO_NUM;
    config.pin_d2 = Y4_GPIO_NUM;
    config.pin_d3 = Y5_GPIO_NUM;
    config.pin_d4 = Y6_GPIO_NUM;
    config.pin_d5 = Y7_GPIO_NUM;
    config.pin_d6 = Y8_GPIO_NUM;
    config.pin_d7 = Y9_GPIO_NUM;

    config.pin_xclk = XCLK_GPIO_NUM;
    config.pin_pclk = PCLK_GPIO_NUM;
    config.pin_vsync = VSYNC_GPIO_NUM;
    config.pin_href = HREF_GPIO_NUM;
    config.pin_sscb_sda = SIOD_GPIO_NUM;
    config.pin_sscb_scl = SIOC_GPIO_NUM;
    config.pin_pwdn = PWDN_GPIO_NUM;
    config.pin_reset = RESET_GPIO_NUM;

    config.xclk_freq_hz = 20000000;
    config.pixel_format = PIXFORMAT_JPEG;

    // Frame size set for stability and reduced memory usage
    config.frame_size = FRAMESIZE_CIF;// 400x296 resolution 
    config.jpeg_quality = 12; 
    config.fb_count = 1;

    if (esp_camera_init(&config) != ESP_OK) {
        Serial.println("Camera init failed!");
    } else {
        Serial.println("Camera initialized.");
    }
}


// ============================================================
// ================ SEND IMAGE TO DEVKIT ======================
// ============================================================
void sendImageAndReceiveResult() {
    camera_fb_t *fb = esp_camera_fb_get();
    if (!fb) {
    // This handles the error safely and prevents the crash
    Serial.println("Camera capture failed!");
    return;
}
// ... proceed to use fb->buf

    Serial.printf("Captured Image Size: %u bytes\n", fb->len);

    // 1. Send Start Marker + Size
    Serial2.print(START_MARKER);
    Serial2.print(fb->len);
    Serial2.print(":"); // Separator for size and data
    
    // 2. Send Image Data (the raw JPEG bytes)
    size_t bytes_written = Serial2.write(fb->buf, fb->len);
    
    // 3. Send End Marker
    Serial2.print(END_MARKER);

    esp_camera_fb_return(fb);

    Serial.printf("Sent %u bytes over Serial2. Waiting for result...\n", bytes_written);

    // 4. Wait for and read the result (barcode data) from the DevKit
    String result = "";
    unsigned long startTime = millis();
    bool resultReceived = false;

    while (millis() - startTime < 5000) { // Wait up to 5 seconds
        if (Serial2.available()) {
            result = Serial2.readStringUntil('\n');
            result.trim();
            // Ensure we only read the final data, not stray commands
            if (result.length() > 0 && result != TRIGGER_MARKER) { 
                resultReceived = true;
                break;
            }
        }
    }

    if (resultReceived) {
        Serial.println("--- RECEIVED RESULT FROM DEVKIT ---");
        Serial.printf("Decoded Barcode/Data: %s\n", result.c_str());
        Serial.println("-----------------------------------");
    } else {
        Serial.println("Timeout: Did not receive result from DevKit.");
    }
}


// ============================================================
// ========================= SETUP ============================
// ============================================================
void setup() {
    Serial.begin(115200); // Standard serial for debugging (USB)
    
    // Initialize Serial2 for inter-ESP communication
    Serial2.begin(BAUD_RATE, SERIAL_8N1, RXD2, TXD2);
    
    // Disable Brownout Detector (Crucial for camera stability)
    WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); 
    
    delay(2000);
    Serial.println("ESP32-CAM Sender Initializing...");
    initCamera();
}


// ============================================================
// ========================== LOOP ============================
// ============================================================
void loop() {
    Serial.println("Waiting for 'SHOOT' trigger from DevKit...");
    
    // Blocking wait for the specific trigger command
    String incomingCommand = "";
    while (true) {
        if (Serial2.available()) {
            // Read until the newline character sent by the DevKit
            incomingCommand = Serial2.readStringUntil('\n');
            incomingCommand.trim();
            if (incomingCommand == TRIGGER_MARKER) {
                break;
            }
        }
        delay(10); // Don't hog the CPU
    }
    
    Serial.println("--- Trigger Received. Starting Capture Cycle ---");
    sendImageAndReceiveResult();
    Serial.println("--- Cycle Complete, Waiting for next trigger ---");
    // Loop restarts and waits for the next SHOOT command (i.e., the next button press).
}