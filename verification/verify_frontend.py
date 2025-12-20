
import sys
from playwright.sync_api import sync_playwright, expect

def verify_reseller_management(page):
    """
    Navigates to the reseller management page and takes a screenshot.
    """
    # 1. Log in
    page.goto("http://localhost:8080/login.php")
    page.locator('input[name="username"]').fill("admin")
    page.locator('input[name="password"]').fill("admin123")
    page.get_by_role("button", name="Login").click()
    expect(page).to_have_url("http://localhost:8080/index.php")

    # 2. Navigate to the reseller management page
    page.goto(f"http://localhost:8080/{sys.argv[1]}")
    expect(page).to_have_title("Reseller Management - My VPN Panel")

    # 3. Set a small viewport for responsive testing
    page.set_viewport_size({"width": 800, "height": 600})

    # 4. Take a screenshot
    page.screenshot(path="screenshots/reseller_management.png")

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        try:
            verify_reseller_management(page)
        finally:
            browser.close()
