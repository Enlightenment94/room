from selenium import webdriver
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.firefox.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

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
        
        wait = WebDriverWait(driver, 10)
        
        #driver.switch_to.window(driver.window_handles[0])
        
        btn_cli_elements = wait.until(EC.presence_of_all_elements_located((By.CLASS_NAME, 'cli')))
        
        print(f"Found {len(btn_cli_elements)} elements with class 'cli' in the first tab.")
        
        for index, element in enumerate(btn_cli_elements):
            print(f"Element {index + 1} text: {element.text}")

        btn_send = wait.until(EC.presence_of_element_located((By.ID, 'btn-send')))
        if btn_cli_elements:
            last_element = btn_cli_elements[-2]
            print(f"Clicking on the last element with text: {last_element.text}")
            last_element.click()
            
            for _ in range(10):
                print("Clicking " + btn_send.text)  
                btn_send.click()  
                time.sleep(1)

        
        time.sleep(5)

    finally:
        pass
        #driver.quit()

if __name__ == '__main__':
    open_browser()
