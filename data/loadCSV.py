# Load CSV of customer data into the customers table of thed atabase
import os           # File operations
import csv          # Read CSV
import sqlite3      # Write to database
import re           # Validate email address


DATA_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__))).split("\\")
del DATA_DIR[-1]
DATA_DIR = "\\".join(DATA_DIR)

CSV_FILE = "{}\\uploads\\latest_customer_upload.csv".format(DATA_DIR)

dbFile = "{}\\mailTime_data.db".format(DATA_DIR)  # Main database
db = ""
c = ""


def readCSV():
    invalidCustomers = []
    duplicateCustomers = []

    f = open(CSV_FILE, 'rt')
    try:
        reader = csv.reader(f)
        rowCount = 0
        for row in reader:
            if rowCount > 0:
                customer = {"first_name":row[0],
                            "surname":row[1],
                            "email":row[2],
                            "data_1":row[3],
                            "data_2":row[4],
                            "data_3":row[5],
                            "opt_out":row[6]}

                # Check email contains an '@' and at least one '.' after the '@'
                if not re.match(r"[^@]+@[^@]+\.[^@]+", customer["email"]):
                    # Failed criteria, invalid email address:
                    invalidCustomers.append(customer)
                else:
                    # Valid, check if they're already in the database:
                    if not uniqueCustomer(customer["email"]):
                        # This email already exists in the database
                        duplicateCustomers.append(customer)
                    else:
                        # Vald & unique! Add to database
                        writeCustomer(customer)
            rowCount = rowCount + 1

    finally:
        f.close()

    return rowCount-1, invalidCustomers, duplicateCustomers

def initialise():
    "Initialize database connection"
    global dbFile, c, db
    db = sqlite3.connect(dbFile) # Open database (create it if it does't exist)
    db.text_factory = str
    c = db.cursor()

def uniqueCustomer(email):
    if c == "":
        initialise()

    c.execute("SELECT EXISTS(SELECT 1 FROM customers WHERE EMAIL=? LIMIT 1);", (email,))
    if c.fetchall()[0][0] == 1:
        return False # Email already in database so customer is not unique
    else:
        return True # Thuis email doesn't exist in the database


def writeCustomer(details):
    if c == "":
        initialise()

    c.execute("""INSERT INTO customers (FIRST_NAME, LAST_NAME, EMAIL, DATA_FIELD_1, DATA_FIELD_2, DATA_FIELD_3, OPT_OUT)
                VALUES (?, ?, ?, ?, ?, ?, ?)""", (details["first_name"], details["surname"], details["email"], details["data_1"], details["data_2"], details["data_3"], details["opt_out"], ))
    db.commit()

if __name__ == "__main__":
    customerCount, invalidCustomers, duplicateCustomers = readCSV()
    invalid = len(invalidCustomers)
    duplicate = len(duplicateCustomers)
    uploaded = customerCount - (invalid + duplicate)
    print "{}/{} customers added ({} invalid email addresses, {} duplicates)".format(uploaded, customerCount, invalid, duplicate)
