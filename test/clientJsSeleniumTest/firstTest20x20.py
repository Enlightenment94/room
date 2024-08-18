from selenium import webdriver
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import random
import string

def generate_random_text(length=10):
    return ''.join(random.choices(string.ascii_letters + string.digits, k=length))

def interact_with_elements(driver, wait, offset):
    btn_cli_elements = wait.until(EC.presence_of_all_elements_located((By.CLASS_NAME, 'cli')))
    print(f"Found {len(btn_cli_elements)} elements with class 'cli'.")

    for index, element in enumerate(btn_cli_elements):
        print(f"Element {index + 1} text: {element.text}")

    if btn_cli_elements:
        last_element = btn_cli_elements[offset]
        print(f"Clicking on the second last element with text: {last_element.text}")
        last_element.click()
        
        btn_send = wait.until(EC.presence_of_element_located((By.ID, 'btn-send')))
        
        for _ in range(1):
            btn_msg = wait.until(EC.presence_of_element_located((By.ID, 'msg')))
            random_text = generate_random_text()
            btn_msg.send_keys(random_text)
            print(f"Sending text: {random_text}")
            print(f"Clicking button with text: {btn_send.text}")
            btn_send.click()
            #time.sleep(1)

def open_browser():
    firefox_options = Options()
    firefox_options.add_argument("--start-maximized")
    # firefox_options.add_argument("--headless")  # Uncomment if you want to run in headless mode
    
    driver_path = 'geckodriver'  # Path to your geckodriver
    
    service = Service(driver_path)
    driver = webdriver.Firefox(service=service, options=firefox_options)
    
    try:
        driver.get('file:///home/vel/2a-enl/4-Room/www/client/ws-client.html')
        driver.execute_script("window.open('');")
        
        driver.switch_to.window(driver.window_handles[1])
        driver.get('file:///home/vel/2a-enl/4-Room/www/client/ws-client.html')
        
        wait = WebDriverWait(driver, 1)
        
        interact_with_elements(driver, wait, -2)

        driver.switch_to.window(driver.window_handles[0])
        
        btn_load_clients = wait.until(EC.presence_of_element_located((By.ID, 'btn-load-clients')))
        btn_load_clients.click()
        interact_with_elements(driver, wait, -1)
        
        time.sleep(5)

    except Exception as e:
        print(f"An error occurred: {e}")
    finally:
        pass
        #driver.quit()

if __name__ == '__main__':
    open_browser()