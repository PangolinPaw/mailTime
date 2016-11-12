# Load CSV of customer data into the customers table of thed atabase
import os
import csv
import sqlite3

CSV_FILE = "{}\\latest_customer_upload.csv".format(os.path.abspath(os.path.join(os.path.dirname(__file__), os.path.pardir)))

def readCSV():
    f = open(CSV_FILE, 'rt')
    try:
        reader = csv.reader(f)
        for row in reader:
            print row
    finally:
        f.close()

def writeCustomer(details):
    pass

def main():
    pass

if __name__ == "__main__":
    print CSV_FILE
