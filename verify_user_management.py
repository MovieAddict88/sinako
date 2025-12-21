import os
from playwright.sync_api import sync_playwright, expect

def run(playwright):
    # Reset database
    os.system("mysql -u cornerst_vpn -pcornerst_vpn cornerst_vpn < setup.sql")
    os.system("php install.php")
    os.system("php migrate.php")

    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # Login as admin
        page.goto("http://localhost:8080/login.php")
        page.fill('input[name="username"]', "admin")
        page.fill('input[name="password"]', "admin123")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/index.php", timeout=60000)
        print("Admin login successful.")

        # Navigate to user management page
        page.click("a:has-text('User Management')")
        page.wait_for_url("http://localhost:8080/user_management.php")
        print("Navigated to User Management page.")

        # Add a new user
        page.click("a:has-text('Add New User')")
        page.wait_for_url("http://localhost:8080/add_user.php")
        page.fill('input[name="username"]', "testuser")
        page.fill('input[name="password"]', "testpassword")
        page.fill('input[name="first_name"]', "Test")
        page.fill('input[name="last_name"]', "User")
        page.fill('input[name="contact_number"]', "1234567890")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/user_management.php")
        print("User added successfully.")
        user_table = page.locator(".card", has_text="User Management")
        expect(user_table.locator("tr:has-text('testuser')")).to_be_visible()
        print("User verification successful.")

        # Add a new admin
        page.goto("http://localhost:8080/admin_management.php")
        page.click("a:has-text('Add New Admin')")
        page.wait_for_url("http://localhost:8080/add_admin.php")
        page.fill('input[name="username"]', "testadmin")
        page.fill('input[name="password"]', "testpassword")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/admin_management.php")
        print("Admin added successfully.")
        admin_table = page.locator(".card", has_text="Admins")
        expect(admin_table.locator("tr:has-text('testadmin')")).to_be_visible()
        print("Admin verification successful.")

        # Add a new reseller
        page.goto("http://localhost:8080/reseller_management.php")
        page.click("a:has-text('Add New Reseller')")
        page.wait_for_url("http://localhost:8080/add_reseller.php")
        page.fill('input[name="username"]', "testreseller")
        page.fill('input[name="password"]', "testpassword")
        page.locator('#reseller_first_name').fill("Test Reseller")
        page.locator('#reseller_address').fill("123 Test St")
        page.locator('#reseller_contact_number').fill("1234567890")
        page.click('input[type="submit"]')
        page.wait_for_url("http://localhost:8080/reseller_management.php")
        print("Reseller added successfully.")
        reseller_table = page.locator(".card", has_text="Users")
        expect(reseller_table.locator("tr:has-text('Test Reseller')")).to_be_visible()
        print("Reseller verification successful.")


        page.screenshot(path="/home/jules/verification/user_management_workflow.png")
        print("Verification successful!")

    except Exception as e:
        print(f"Verification failed: {e}")
        print(page.content())
        page.screenshot(path="/home/jules/verification/user_management_workflow_error.png")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
