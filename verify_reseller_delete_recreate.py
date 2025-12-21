
import subprocess
from playwright.sync_api import sync_playwright, expect

def setup_database():
    """Sets up the database by running the necessary PHP scripts."""
    subprocess.run(["mysql", "-u", "cornerst_vpn", "-pcornerst_vpn", "-e", "DROP DATABASE IF EXISTS cornerst_vpn; CREATE DATABASE cornerst_vpn;"], check=True)
    subprocess.run(["php", "install.php"], check=True)
    subprocess.run(["php", "migrate.php"], check=True)

def run(playwright):
    """
    Main function to run the Playwright test.
    """
    # Setup the database before running the test
    setup_database()

    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # --- 1. Login ---
        page.goto("http://localhost:8080/login.php")
        page.fill("input[name=\"username\"]", "admin")
        page.fill("input[name=\"password\"]", "admin123")
        page.click("input[type=\"submit\"]")
        # Wait for navigation to the dashboard
        page.wait_for_url("http://localhost:8080/dashboard.php")
        print("Login successful.")

        # --- 2. Create Initial Reseller ---
        page.goto("http://localhost:8080/reseller_management.php")
        page.click("a[href='add_reseller.php']")
        page.wait_for_url("http://localhost:8080/add_reseller.php")

        reseller_username = "recreate_test"
        reseller_name = "Recreate Test User"
        page.fill("input[name='username']", reseller_username)
        page.fill("input[name='password']", "password123")
        page.fill("input[name='first_name']", reseller_name)
        page.fill("input[name='address']", "123 Test Street")
        page.fill("input[name='contact_number']", "1234567890")
        page.click("input[type='submit']")

        page.wait_for_url("http://localhost:8080/reseller_management.php")
        expect(page.locator(f"td:has-text('{reseller_name}')")).to_be_visible()
        print("Initial reseller created successfully.")

        # --- 3. Delete the Reseller ---
        reseller_row = page.locator(f"tr:has-text('{reseller_name}')")
        delete_button = reseller_row.locator("button:has-text('Delete')")

        # Handle the confirmation dialog
        page.on("dialog", lambda dialog: dialog.accept())

        delete_button.click()

        # Verify the reseller is gone
        page.wait_for_url("http://localhost:8080/reseller_management.php")
        expect(page.locator(f"td:has-text('{reseller_name}')")).not_to_be_visible()
        print("Reseller deleted successfully.")

        # --- 4. Re-create the Reseller ---
        page.click("a[href='add_reseller.php']")
        page.wait_for_url("http://localhost:8080/add_reseller.php")

        page.fill("input[name='username']", reseller_username)
        page.fill("input[name='password']", "password123")
        page.fill("input[name='first_name']", reseller_name)
        page.fill("input[name='address']", "123 Test Street")
        page.fill("input[name='contact_number']", "1234567890")
        page.click("input[type='submit']")

        # This is the critical step. If the bug exists, it will fail here.
        page.wait_for_url("http://localhost:8080/reseller_management.php")

        # Check if the page returned a 500 error
        page_content = page.content()
        if "500" in page.title() or "Internal Server Error" in page_content:
             raise Exception("Bug Confirmed: Received a 500 error when re-creating the reseller.")

        expect(page.locator(f"td:has-text('{reseller_name}')")).to_be_visible()
        print("Reseller re-created successfully.")

        print("\nVerification successful: The add-delete-recreate cycle works correctly.")

    except Exception as e:
        print(f"\nVerification failed: {e}")
        # Optionally save a screenshot for debugging
        page.screenshot(path="failure_screenshot.png")
    finally:
        browser.close()
        # Clean up by resetting the database
        setup_database()


if __name__ == "__main__":
    with sync_playwright() as playwright:
        run(playwright)
